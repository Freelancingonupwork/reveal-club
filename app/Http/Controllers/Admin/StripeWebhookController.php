<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Plan;
use App\Models\Program;
use App\Models\StepsGoal;
use App\Models\StripeCustomer;
use App\Models\SubscriptionHistories;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserProgram;
use App\Models\UserReferenceAnswer;
use App\Models\UsersInitialMeasurement;
use App\Models\UsersSubscriptions;
use App\Models\UsersTargetMeasurement;
use App\Services\CustomerIoService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        $eventData = $event->data->object;
        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($eventData);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($eventData);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($eventData);
                break;
            case 'refund.updated':
            case 'charge.refunded':
                Log::info('Inside the event of refund: ' . $event->type);
                $this->handleSubscriptionAmountRefunded($eventData);
                break;
            default:
                Log::info('Received unhandled webhook event type: ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleInvoicePaymentSucceeded($invoice)
    {
        // Basic safety: invoice should have a subscription id for subscription payments
        if (empty($invoice->subscription)) {
            Log::info('WH - Invoice has no subscription attached - skipping', ['invoice' => $invoice->id]);
            return;
        }

        // this webhook only used for 3ds incomplete payment -> then complete for first time
        $existing = UsersSubscriptions::where('customer_id', $invoice->customer)->first();
        if ($existing) {
            Log::info('WH - Already processed (history exists), skipping', ['customer_id' => $invoice->customer]);
            return;
        }

        $email = $invoice->customer_email;
        $user = $this->createOrRetriveUser($email);
        $sessionId = UserReferenceAnswer::where('key', 'email')->where('value', $email)->value('session_id');

        $subscription = $this->getSubscriptionFromStripe($invoice->subscription);

        $planId = $subscription->plan->metadata->package_id ?? null;
        $planName = $subscription->plan->metadata->package_name ?? '';
        $withKlarna = $subscription->metadata->payment_method ?? null;

        $this->saveSubscriptionData($user->id, $subscription, $planId, $withKlarna);
        // User::where(['id' => $user->id, 'email' => $user->email])->update(['isQuestionsAttempted' => 1]);
        Log::info('WH - Subscription data saved', ['user_id' => $user->id, 'subscription_id' => $subscription->id]);
        // More logic for user and email sending
        $subData = [
            'name' => 'Subscriber',
            'subscriptionId' => $subscription->id,
            'startDate' => date("Y-m-d", $subscription->current_period_start),
            'planName' => $planName,
            'billingDate' => Carbon::now()->format('Y-m-d'),
            'nextBillingDate' => date("Y-m-d", $subscription->current_period_end),
            'totalAmountCharged' => $subscription->plan['amount'] / 100,
        ];

        Log::info('WH - Sending email for finish registration', ['user_email' => $user->email]);
        $mailStatus = $this->sendMailForFinishRegistration($user->email);
        $customerIo = new CustomerIoService();
        $customerIo->sendTransactionalEmail($user->email, '8', $subData);

        if (!$mailStatus) {
            Log::error('WH - Email sending failed for user: ' . $user->email);
        }
        Log::info('WH - Assigning program to user', ['user_id' => $user->id]);
        $assignedProgram = $this->assignProgram($sessionId);
        $this->saveUserProgram($user->id, $assignedProgram);
        Log::info('WH - Saving user evolution data');
        $userEvolutionData = UserReferenceAnswer::where(['session_id' => $sessionId])->get();
        $this->saveUserEvolutionData($user->id, $userEvolutionData);

    }

    protected function handleSubscriptionUpdated($subscription)
    {
        $discount = $subscription->discount === null ? 0 : 1;
        $trial = $subscription->status === 'trialing' ? 1 : 0;

        $data = [
            'status' => $subscription->status,
            'invoice_id' => $subscription->latest_invoice,
            'payment_method_type' => ($subscription->metadata && $subscription->metadata->payment_method === 'klarna') ? 'klarna' : 'card',
            'default_payment_method_type' => ($subscription->metadata && $subscription->metadata->payment_method === 'klarna') ? 'klarna' : 'card',
            'start_date' => date('Y-m-d H:i:s', $subscription->current_period_start),
            'end_date' => date('Y-m-d H:i:s', $subscription->current_period_end),
        ];

        $userId = $this->getUserIdFromCustomerId($subscription->customer);

        $item = $subscription->items->data[0]; 
        $amountInCents = $item->price->unit_amount;
        $amountInDollars = $amountInCents / 100; 
    
        $subscriptionRecord = UsersSubscriptions::where('subscription_id', $subscription->id)
            ->where('customer_id', $subscription->customer)
            ->first();

        if ($trial == 1 && date('Y-m-d', strtotime($data['start_date'])) == date('Y-m-d') && date('Y-m-d', strtotime($data['end_date'])) == date('Y-m-d', strtotime($subscriptionRecord->end_date))) {
            Log::info('2 WH - Trial webhook for plan modification only, no update needed', ['subscription_id' => $subscription->id]);
            return;
        }
        $newPlanId = null;
    
        // Capture original next_plan_id to use later (do not lose it by setting to null early)
        $originalNextPlanId = null;
        if ($subscriptionRecord) {
            $originalNextPlanId = $subscriptionRecord->next_plan_id;
            // If there's a next plan, we'll promote it now
            $newPlanId = $subscriptionRecord->plan_id;

            if ($originalNextPlanId) {
                $newPlanId = $originalNextPlanId;
                // reflect change locally (we will persist these fields with update() below)
                $subscriptionRecord->plan_id = $newPlanId;
                $subscriptionRecord->next_plan_id = null;
            }
        }

        // ============================================================
        //   YEARLY COMMITMENT LOGIC (NEW)
        // ============================================================

        $isYearly = 0;
        $isCancellationLocked = 0;
        $subscriptionCycle = 0;
        $lockDate = null;

        // Load current plan (the newPlanId after possible promotion)
        $plan = $newPlanId ? Plan::find($newPlanId) : null;
        if ($plan) {
            $isYearly = (int) $plan->is_yearly_commitment;
        }

        if ($subscriptionRecord) {
            // Existing values
            $isCancellationLocked = $subscriptionRecord->is_cancellation_locked;
            $subscriptionCycle = (int) $subscriptionRecord->subscription_year_cycle;
            $lockDate = $subscriptionRecord->lockDate;
        }

        // ---------------------------------------------------------
        // CASE 1: If plan is yearly commitment
        // ---------------------------------------------------------
        if ($isYearly === 1) {

            if ($subscriptionCycle >= 12) {
                // New yearly cycle begins
                $subscriptionCycle = 1;

                // Move lock window by 1 year
                $lockDate = $lockDate
                    ? Carbon::parse($lockDate)->addYear()
                    : Carbon::parse($data['start_date'])->addYear();

                $isCancellationLocked = 1;

            } else {
                // Normal increment
                $subscriptionCycle++;

                // Update LockDate & Locked status for new Yearly commitment Plan
                if ($subscriptionCycle == 1){
                    $lockDate = Carbon::createFromTimestamp($subscription->current_period_start)->addMonths(11)->toDateString();
                    $isCancellationLocked = 1;
                }
                // Unlock cancellation only when reaching month 12
                if ($subscriptionCycle == 12) {
                    $isCancellationLocked = 0;
                }
            }
        }

        // ---------------------------------------------------------
        // CASE 2: original next_plan_id exists and current cycle is 12
        // ---------------------------------------------------------
        // Use $originalNextPlanId (captured earlier) — not $subscriptionRecord->next_plan_id (which may have been nulled)
        if ($subscriptionRecord && $originalNextPlanId && $subscriptionCycle == 12) {

            // Next plan is non-yearly → Remove yearly commitment logic for next plan
            $isYearly = 0;
            $isCancellationLocked = 0;
            $subscriptionCycle = 0;
            $lockDate = null;

        }

        // Final yearly data array
        $yearlyData = [
            'is_yearly_commitment' => $isYearly,
            'is_cancellation_locked' => $isCancellationLocked,
            'subscription_year_cycle' => $subscriptionCycle,
            'lockDate' => $lockDate,
        ];

        // ============================================================
        //   UPDATE UsersSubscriptions
        // ============================================================

        if ($subscriptionRecord) {
            $subscriptionRecord->update(array_merge(
                $data,
                [
                    'amount' => $amountInDollars,
                    'plan_id' => $newPlanId,
                ],
                $yearlyData
            ));
        }

        // ============================================================
        //   UPDATE SubscriptionHistories
        // ============================================================

        $previousSubscription = SubscriptionHistories::where('subscription_id', $subscription->id)
            ->where('customer_id', $subscription->customer)
            ->orderBy('created_at', 'desc')
            ->first();

        if (
            $previousSubscription &&
            $previousSubscription->status === 'trialing' &&
            $subscription->status === 'trialing' &&
            $previousSubscription->end_date < $data['end_date']
        ) {
            // Trial extended
            $previousSubscription->update(array_merge([
                'end_date' => $data['end_date'],
                'taken_trial' => 1,
                'amount' => $amountInDollars,
            ], $yearlyData));

        } else {

            // Create new entry
            SubscriptionHistories::create(array_merge([
                'user_id' => $userId,
                'plan_id' => $newPlanId,
                'customer_id' => $subscription->customer,
                'subscription_id' => $subscription->id,
                'taken_trial' => $trial,
                'taken_discount' => $discount,
                'amount' => $amountInDollars,
            ], $data, $yearlyData));
        }

        Log::info('Subscription updated for subscription ID: ' . $subscription->id);
    }

    protected function handleSubscriptionDeleted($subscription)
    {
        // Store subscription history
        $userId = $this->getUserIdFromCustomerId($subscription->customer);
        SubscriptionHistories::create([
            'user_id' => $userId,
            'customer_id' => $subscription->customer,
            'subscription_id' => $subscription->id,
            'amount' => 0, // Assuming amount is not applicable for deletion
            'status' => 'canceled',
            'start_date' => null,
            'end_date' => null,
        ]);

        // Update current subscription record
        UsersSubscriptions::where('subscription_id', $subscription->id)
            ->where('customer_id', $subscription->customer)
            ->update(['status' => 'canceled']);

        Log::info('Subscription canceled for subscription ID: ' . $subscription->id);
    }
    protected function handleSubscriptionAmountRefunded($refund)
    {
        Log::info('Processing refund event.');

        if (empty($refund->payment_intent)) {
            Log::error('Refund event received without payment_intent.');
            return;
        }
    
        // Get customer ID from PaymentIntent
        $customerId = $this->getCustomerIdFromPaymentIntent($refund->payment_intent);
    
        if (!$customerId) {
            Log::error("Customer ID not found for payment intent: {$refund->payment_intent}");
            return;
        }
        // Fetch the latest subscription for the customer
        $subscription = UsersSubscriptions::where('customer_id', $customerId)
            ->latest('id')
            ->first();
    
        if (!$subscription) {
            Log::error("No subscription found for customer ID: {$customerId}");
            return;
        }

        if ($subscription->is_refunded) {
            Log::info("Already refunded, skipping.");
            return;
        }

        // Log refund in subscription history
        SubscriptionHistories::create([
            'user_id'        => $subscription->user_id,
            'customer_id'    => $customerId,
            'subscription_id'=> $subscription->subscription_id,
            'amount'         => -($refund->amount / 100), // Convert cents to dollars
            'status'         => 'refunded',
            'start_date'     => null,
            'end_date'       => null,
        ]);
    
        // Update the fetched subscription's status to refunded
        $subscription->update(['is_refunded' => 1]);
    
        Log::info("Subscription ID: {$subscription->id} updated to refunded.");
    }

    protected function getUserIdFromCustomerId($customerId)
    {
        return StripeCustomer::whereJsonContains('card_details->id', $customerId)
            ->value('user_id');
    }

    protected function getCustomerIdFromPaymentIntent($paymentIntentId)
    {
        try {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, []);

            return $paymentIntent->customer ?? null;
        } catch (\Exception $e) {
            Log::error("Error fetching PaymentIntent: " . $e->getMessage());
            return null;
        }
    }
    protected function getSubscriptionFromStripe($subscriptionId)
    {
        try {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                        $subscription = $stripe->subscriptions->retrieve(
                $subscriptionId,
                [
                    'expand' => ['latest_invoice.payment_intent', 'items.data.price.product']
                ]
            );

            return $subscription;

        } catch (\Exception $e) {
            Log::error("Error fetching PaymentIntent: " . $e->getMessage());
            return null;
        }
    }

    private function createOrRetriveUser($email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User();
            $user->name = "";
            $user->email = $email;
            $user->password = Hash::make('password');
            $user->type = 2;
            $user->mobile = "";
            $user->isQuestionsAttempted = 0;
            $user->isSubscribedUser = 0;
            $user->status = 1;
            $user->save();
        }
        return $user;
    }

    private function saveSubscriptionData($userId, $subscriptionDetail, $planId, $withKlarna = null)
    {
        // Extract necessary details from $subscriptionDetail
        $startDate = date("Y-m-d", $subscriptionDetail->current_period_start);
        $endDate = date("Y-m-d", $subscriptionDetail->current_period_end);
        $customer_id = (is_object($subscriptionDetail->customer)) ? $subscriptionDetail->customer->id : $subscriptionDetail->customer;
        $subsId = $subscriptionDetail->id;
        $amount = $subscriptionDetail->plan['amount'] / 100;
        $billing_cycle = $subscriptionDetail->plan['interval_count'];
        $status = $subscriptionDetail->status;
        $invoice_id = $subscriptionDetail->latest_invoice ?? null;

        $plan = $planId ? Plan::find($planId) : null;
        $isYearlyMonthly = $plan && ((int) ($plan->is_yearly_commitment ?? 0) === 1);

        $is_cancellation_locked = 0;
        $subscription_year_cycle = 0;
        $lockDate = $startDate;
        if ($isYearlyMonthly) {
            $subscription_year_cycle = 1;
            $is_cancellation_locked = 1;
            $lockDate = date('Y-m-d', strtotime('+11 months', strtotime($startDate)));
        }
        
        $payment_method_type = 'card';
        if($withKlarna == 'klarna'){
            $payment_method_type = 'klarna';
        }
        // Check if a record already exists in UsersSubscriptions
        $subscription = UsersSubscriptions::where('user_id', $userId)
                                        ->where('subscription_id', $subsId)
                                        ->where('customer_id', $customer_id)
                                        ->first();

        if ($subscription) {
            // Update existing record in UsersSubscriptions
            $subscription->plan_id = $planId ?? null;
            $subscription->amount = $amount;
            $subscription->billing_cycle = $billing_cycle;
            $subscription->status = $status;
            $subscription->start_date = $startDate;
            $subscription->end_date = $endDate;
            $subscription->is_yearly_commitment = $isYearlyMonthly ?? 0;
            $subscription->is_cancellation_locked = $is_cancellation_locked;
            $subscription->subscription_year_cycle = $subscription_year_cycle;
            $subscription->lockDate = $lockDate;
            $subscription->payment_method_type = $payment_method_type;
            $subscription->default_payment_method_type = $payment_method_type;
            $subscription->invoice_id = $invoice_id;
            $subscription->save();

            // No need to insert a new record in SubscriptionHistories
        } else {
            
            // Delete all old records for this user before creating a new one
            UsersSubscriptions::where('user_id', $userId)->delete();

            // Create new record in UsersSubscriptions
            $subscription = new UsersSubscriptions;
            $subscription->user_id = $userId;
            $subscription->plan_id = $planId ?? null;
            $subscription->customer_id = $customer_id;
            $subscription->subscription_id = $subsId;
            $subscription->payment_method_type = $payment_method_type;
            $subscription->default_payment_method_type = $payment_method_type;
            $subscription->invoice_id = $invoice_id;
            $subscription->amount = $amount;
            $subscription->billing_cycle = $billing_cycle;
            $subscription->status = $status;
            $subscription->start_date = $startDate;
            $subscription->end_date = $endDate;
            $subscription->is_yearly_commitment = $isYearlyMonthly ?? 0;
            $subscription->is_cancellation_locked = $is_cancellation_locked;
            $subscription->subscription_year_cycle = $subscription_year_cycle;
            $subscription->lockDate = $lockDate;
            $subscription->save();

            // Insert a new record in SubscriptionHistories
            $subscriptionHistory = new SubscriptionHistories;
            $subscriptionHistory->plan_id = $planId ?? null;
            $subscriptionHistory->user_id = $userId;
            $subscriptionHistory->customer_id = $customer_id;
            $subscriptionHistory->subscription_id = $subsId;
            $subscriptionHistory->payment_method_type = $payment_method_type;
            $subscriptionHistory->invoice_id = $invoice_id;
            $subscriptionHistory->amount = $amount;
            $subscriptionHistory->status = $status;
            $subscriptionHistory->start_date = $startDate;
            $subscriptionHistory->end_date = $endDate;
            $subscriptionHistory->taken_trial = 0;
            $subscriptionHistory->taken_discount = 0;
            $subscriptionHistory->is_yearly_commitment = $isYearlyMonthly ?? 0;
            $subscriptionHistory->is_cancellation_locked = $is_cancellation_locked;
            $subscriptionHistory->subscription_year_cycle = $subscription_year_cycle;
            $subscriptionHistory->lockDate = $lockDate;
            $subscriptionHistory->save();
        }

        // Update user's subscription-related fields
        $user = User::where('id', $userId)->update(['isQuestionsAttempted' => 1, 'isSubscribedUser' => 1]);
    }

    private function sendMailForFinishRegistration($email)
    {
        // Check if email exists or not
        $user = User::where(['email' => $email])->first();
        if ($user) {
            $token = Str::random(64);

            // Generate the registration URL
            $registrationUrl = url('user-finish-registration/' . $token);

            // Insert reset token into password_resets table
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            $sessionIdData = UserReferenceAnswer::where('key', 'email')
                ->where('value', $email)
                ->first(['session_id']);

            $sessionId = $sessionIdData->session_id;

            // Retrieve all data related to that session ID
            $userReferenceData = UserReferenceAnswer::where('session_id', $sessionId)
                ->get(['session_id', 'key', 'value'])
                ->groupBy('session_id');

            // Prepare user data to send to Customer.io
            $customerData = [
                'session_id' => $sessionId,
                'email' => $user->email,
                'registeration_url' => $registrationUrl, // Include registration URL
            ];

            foreach ($userReferenceData as $sessionId => $data) {
                foreach ($data as $item) {
                    $customerData[$item->key] = $item->value;
                }
            }

            // convert to an object:
            $customerData = (object) $customerData;

            $customerIo = new CustomerIoService();
            $customerIo->addOrUpdateCustomer($sessionId, $customerData, 19);  // Add to Segment 19 => Subscribed User

            $emailsent = $customerIo->sendTransactionalEmail($email, '4', ['registeration_url' => $registrationUrl]);
            return true;
        }
    }

    public function assignProgram($sessionId)
    {
        $userAnswers = UserAnswer::where([
            'question_type' => 1,
            'session_id' => $sessionId
        ])->get();

        $totalPoints = 0;
        $cardioProgramIds = [];
        $muscleProgramIds = [];

        foreach ($userAnswers as $userAnswer) {
            $answer = Answer::with('question')->where([
                'id' => $userAnswer->answer_id,
                'question_id' => $userAnswer->question_id
            ])->first();

            if ($answer) {
                $totalPoints += $answer->ans_points;

                if ($answer->question->ques_for === 'cardio' && $answer->cardio_and_muscle_id) {
                    $cardioProgramIds[] = $answer->cardio_and_muscle_id;
                } elseif ($answer->question->ques_for === 'musclestrengthening' && $answer->cardio_and_muscle_id) {
                    $muscleProgramIds[] = $answer->cardio_and_muscle_id;
                }
            }
        }

        // Determine level based on total points
        $levelId = $this->getLevelIdByPoints($totalPoints);

        // ---- CARDIO PROGRAM SELECTION ----
        $cardioProgram = null;
        if (!empty($cardioProgramIds)) {
            $cardioProgram = Program::whereIn('cardio_id', $cardioProgramIds)
                ->where('status', 1)
                ->where('program_type', 'cardio')
                ->where('level_id', $levelId)
                ->first();
        }

        if (!$cardioProgram) {
            // fallback if no matching ID found
            $cardioProgram = Program::where('status', 1)
                ->where('program_type', 'cardio')
                ->where('level_id', $levelId)
                ->first();
        }

        // ---- MUSCLE PROGRAM SELECTION ----
        $muscleProgram = null;
        if (!empty($muscleProgramIds)) {
            $muscleProgram = Program::whereIn('muscle_strength_id', $muscleProgramIds)
                ->where('status', 1)
                ->where('program_type', 'muscle')
                ->where('level_id', $levelId)
                ->first();
        }

        if (!$muscleProgram) {
            // fallback if no matching ID found
            $muscleProgram = Program::where('status', 1)
                ->where('program_type', 'muscle')
                ->where('level_id', $levelId)
                ->first();
        }

        // Return both programs in a structured array
        return [
            'cardio' => $cardioProgram,
            'muscle' => $muscleProgram,
        ];
    }

    private function getLevelIdByPoints($totalPoints)
    {
        // Try to find a level that fits within the provided range
        $level = DB::table('program_levels')
            ->where('start_range', '<=', $totalPoints)
            ->where('end_range', '>=', $totalPoints)
            ->first();
    
        // If a level is found where totalPoints fall within the range
        if ($level) {
            return $level->id;
        }
    
        // If no level matches, find the lowest start_range and give its ID
        $lowestStartRangeLevel = DB::table('program_levels')
            ->orderBy('start_range', 'asc')
            ->first();  // Get the level with the lowest start_range
    
        // If the totalPoints are less than the lowest start_range, return its ID
        if ($totalPoints < $lowestStartRangeLevel->start_range) {
            return $lowestStartRangeLevel->id;
        }
    
        // If no level matches and totalPoints are higher than any level's end_range, 
        // find the level with the highest end_range and return its ID
        $highestEndRangeLevel = DB::table('program_levels')
            ->orderBy('end_range', 'desc')
            ->first();  // Get the level with the highest end_range
    
        // If the totalPoints are greater than the highest end_range, return its ID
        if ($totalPoints > $highestEndRangeLevel->end_range) {
            return $highestEndRangeLevel->id;
        }
    
        // Default return value if no condition matches, this should not usually be hit
        return null;
    }
    private function selectProgram($programs)
    {
        if (empty($programs)) {
            return null; // No programs available for the specified level
        }

        // Convert the array to a zero-indexed array if it's not already
        $programs = array_values($programs);

        // If there are multiple programs, you might want to select the first one
        // or apply additional criteria if needed
        return $programs[0]; // Selecting the first program in the filtered list
    }

    private function saveUserProgram($userId, $programs)
    {
        // Check if a record with the same user_id and program_id already exists
        if (isset($programs['cardio']) && $programs['cardio']) {
            $userProgram = UserProgram::where('user_id', $userId)
                                    ->where('program_id', $programs['cardio']->id)
                                    ->first();

            if ($userProgram) {
                // If the record exists, update it
                $userProgram->join_date = date("Y-m-d");  // Update the join date if needed
                $userProgram->status = 1;  // Ensure the status is set to 1 (active)
                $userProgram->save();
            } else {
                // Create new record for cardio program
                $userProgram = new UserProgram;
                $userProgram->user_id = $userId;
                $userProgram->program_id = $programs['cardio']->id;
                $userProgram->join_date = date("Y-m-d");
                $userProgram->status = 1;
                $userProgram->program_type = 1; // 1 means cardio
                $userProgram->save();
            }
        }

        if (isset($programs['muscle']) && $programs['muscle']) {
            $userProgram = UserProgram::where('user_id', $userId)
                                    ->where('program_id', $programs['muscle']->id)
                                    ->first();

            if ($userProgram) {
                // If the record exists, update it
                $userProgram->join_date = date("Y-m-d");  // Update the join date if needed
                $userProgram->status = 1;  // Ensure the status is set to 1 (active)
                $userProgram->save();
            } else {
                // Create new record for muscle program
                $userProgram = new UserProgram;
                $userProgram->user_id = $userId;
                $userProgram->program_id = $programs['muscle']->id;
                $userProgram->join_date = date("Y-m-d");
                $userProgram->status = 1;
                $userProgram->program_type = 2; // 2 means muscle
                $userProgram->save();
            }
        }
    }

    private function saveUserEvolutionData($userId, $userEvolutionData)
    {
        // Fetch column listings for initial and target measurements
        $dbColumnsForInitialMeasurements = Schema::getColumnListing('users_initial_measurements');
        $dbColumnsForTargetMeasurements = Schema::getColumnListing('users_target_measurements');

        foreach ($userEvolutionData as $userEvolution) {
            $key = $userEvolution['key'];
            $value = $userEvolution['value'];
            $answer = $userEvolution['answer'];

            // Handle initial measurements
            if ($key == 'current_weight') {
                UsersInitialMeasurement::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'weight' => $value,
                        'added_date' => Carbon::today()
                    ]
                );
            } else if (in_array($key, $dbColumnsForInitialMeasurements)) {
                UsersInitialMeasurement::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        $key => $value,
                        'added_date' => Carbon::today()
                    ]
                );
            }

            // Handle target measurements
            if ($key == 'desire_weight') {
                UsersTargetMeasurement::updateOrCreate(
                    ['user_id' => $userId],
                    ['weight' => $value]
                );
            } else if (in_array($key, $dbColumnsForTargetMeasurements)) {
                UsersTargetMeasurement::updateOrCreate(
                    ['user_id' => $userId],
                    [$key => $value]
                );
            }

            // Handle User Steps Goal
            if ($key == 'steps_goal') {
                $goalDate = new DateTime('now');
                $goalDate = $goalDate->format('Y-m-d');

                StepsGoal::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'steps_goal' => $value,
                        'goal_date' => $goalDate
                    ]
                );
            }

            if ($key == 'activity_level') {
                StepsGoal::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'activity_level' => $answer,
                        'activity_factor' => $value,
                    ]
                );
            }

            if ($key == 'name') {
                User::updateOrCreate(
                    ['id' => $userId],
                    [
                        'name' => $value
                    ]
                );
            }

            if ($key == 'height') {
                User::where(['id' => $userId])->update([$key => $value]);
            }

            if ($key == 'gender') {
                User::where(['id' => $userId])->update([$key => $value]);
            }
        }
    }
}

