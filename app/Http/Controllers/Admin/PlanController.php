<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
// new Imports
use App\Models\UsersSubscriptions;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Services\CustomerIoService;

class PlanController extends Controller
{
    public function index()
    {
        Session::put('page', 'plans');
        $planData = Plan::get()->toArray();
        return view('admin.plans.index', ['planData' => $planData]);
    }


    public function create(Request $request)
    {
        Session::put('page', 'plans');
        if ($request->isMethod('post')) {
            $data = $request->all();

            $validation = [
                'name' => ['required', 'string', 'nullable', 'max:255'],
                'description' => ['string', 'nullable'],
                'price' => ['required', 'max:255'],
                'discprice' => ['required', 'max:255'],
            ];

            // If freetrial is enabled, validate trialdays
            if (!empty($data['freetrial']) && ($data['freetrial'] == 'on')) {
                $validation['trialdays'] = ['required', 'integer', 'min:1'];
            }

            $validator = Validator::make($data, $validation);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            // Determine default dayscount by plan_type
            switch ($data['plan_type']) {
                case 0:
                    $dayscount = 30;
                    break;
                case 1:
                    $dayscount = 90;
                    break;
                case 2:
                    $dayscount = 365;
                    break;
                case 3:
                    $dayscount = 730;
                    break;
                case 4:
                    $dayscount = 182; // 182 days for 6 months
                    break;
                default:
                    $dayscount = 30;
            }

            $status = !empty($data['status']) ? 1 : 0;
            $for_klarna = !empty($data['for_klarna']) ? 1 : 0;

            // Handle freetrial - If for_klarna is checked, disable freetrial
            if ($for_klarna) {
                $freetrial = 0;
                $trialdays = null;
            } elseif (!empty($data['freetrial']) && ($data['freetrial'] == 'on') && in_array($data['plan_type'], [1, 2, 3, 4])) {
                $freetrial = 1;
                $trialdays = isset($data['trialdays']) ? (int)$data['trialdays'] : 0;
                $dayscount += $trialdays;
            } else {
                $freetrial = 0;
                $trialdays = null;
            }
            $slug = Str::slug($data['name']);

            if (!isset($data['image']) || empty($data['image'])) {
                $planImage = '';
            } else {
                $planImage = time() . '.' . $data['image']->extension();
                if (!Storage::disk('public')->exists("/plans/plan_images")) {
                    Storage::disk('public')->makeDirectory("/plans/plan_images");
                }
                $request->image->storeAs("/plans/plan_images/", $planImage, 'public');
                $planImage = "/plans/plan_images/$planImage";
            }

            $plan = new Plan;
            $plan->name = $data['name'];
            $plan->offer_label = $data['offer_label'];
            $plan->price = $data['price'];
            $plan->discprice = $data['discprice'];
            $plan->total_price = $data['total_price'];
            $plan->total_disc_price = $data['total_disc_price'];
            $plan->plan_type = $data['plan_type'];
            $plan->freetrial = $freetrial;
            $plan->trialdays = $trialdays;
            $plan->dayscount = $dayscount;
            $plan->description = $data['description'] ?? '';
            $plan->slug = $slug;
            $plan->image = $planImage;
            $plan->for_klarna = $for_klarna;
            $plan->status = $status;
            $plan->is_yearly_commitment = ($data['plan_type'] == 0 && !empty($data['is_yearly_commitment'])) ? 1 : 0;
            $plan->save();
            return redirect('/admin/plan-index')->with('success', 'Plan inserted successfully !!!');
        }
        return view('admin.plans.create');
    }

    public function update(Request $request, $slug, $id)
    {
        Session::put('page', 'plans');

        // Get the plan by ID
        $plan = Plan::where(['id' => $id])->first();

        if ($request->isMethod('post')) {
            $data = $request->all();

            // Validation rules
            $validation = [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['string', 'nullable'],
                'price' => ['required', 'max:255'],
                'discprice' => ['required', 'max:255'],
            ];

            // If freetrial is checked, validate trialdays
            if (!empty($data['freetrial']) && ($data['freetrial'] == 'on')) {
                $validation['trialdays'] = ['required', 'integer', 'min:1'];
            }

            $validator = Validator::make($data, $validation);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            // Set the default dayscount based on plan type
            switch ($data['plan_type']) {
                case 0:
                    $dayscount = 30;
                    break;
                case 1:
                    $dayscount = 90;
                    break;
                case 2:
                    $dayscount = 365;
                    break;
                case 3:
                    $dayscount = 730;
                    break;
                case 4:
                    $dayscount = 182; // 6 months
                    break;
                default:
                    $dayscount = 30;
            }

            // Determine the status
            $status = !empty($data['status']) ? 1 : 0;
            $for_klarna = !empty($data['for_klarna']) ? 1 : 0;

            // Handle freetrial and trialdays - If for_klarna is checked, disable freetrial
            if ($for_klarna) {
                $freetrial = 0;
                $trialdays = null;
            } elseif (!empty($data['freetrial']) && ($data['freetrial'] == 'on') && in_array($data['plan_type'], [1, 2, 3, 4])) {
                $freetrial = 1;
                $trialdays = isset($data['trialdays']) ? (int)$data['trialdays'] : 0;
                $dayscount += $trialdays; // Add trialdays to the total dayscount
            } else {
                $freetrial = 0;
                $trialdays = null; // If no freetrial, set trialdays to null
            }

            $slug = Str::slug($data['name']);

            if (!isset($data['image']) || empty($data['image'])) {
                $planImage = $plan->image; // Keep the existing image if none is uploaded
            } else {
                $planImage = time() . '.' . $data['image']->extension();
                if (!Storage::disk('public')->exists("/plans/plan_images")) {
                    Storage::disk('public')->makeDirectory("/plans/plan_images");
                }

                // Delete the old image if it exists
                if (Storage::disk('public')->exists("/plans/" . $plan->image)) {
                    Storage::disk('public')->delete("/plans/" . $plan->image);
                }
                $request->image->storeAs("plans/plan_images", $planImage, 'public');
                $planImage = "plans/plan_images/$planImage";
            }

            // Get old price before updating (for comparison)
            $oldPrice = $plan->total_disc_price ?? $plan->total_price;
            $newPrice = $data['total_disc_price'] ?? $data['total_price'];

            // Check if admin wants to apply to existing customers
            $applyToExisting = $request->has('apply_to_existing') && $request->apply_to_existing == '1';

            // Update the plan data in the database
            $updatePlan = Plan::where(['id' => $id])->update([
                'name' => $data['name'],
                'offer_label' => $data['offer_label'],
                'description' => $data['description'] ?? "",
                'slug' => $slug,
                'price' => $data['price'],
                'discprice' => $data['discprice'],
                'total_price' => $data['total_price'],
                'total_disc_price' => $data['total_disc_price'],
                'plan_type' => $data['plan_type'],
                'image' => $planImage,
                'status' => $status,
                'for_klarna' => $for_klarna,
                'freetrial' => $freetrial,
                'trialdays' => $trialdays,
                'dayscount' => $dayscount,
                'is_yearly_commitment' => ($data['plan_type'] == 0 && !empty($data['is_yearly_commitment'])) ? 1 : 0,
                'price_updated_at' => now(),
                'apply_to_existing' => $applyToExisting ? 1 : 0,
            ]);

            // If checkbox is checked, update existing subscriptions to the current plan price.
            if ($applyToExisting) {
                try {
                    $this->updateExistingSubscriptionsToNewPrice($id, $oldPrice, $newPrice);
                    Session::flash('success', 'Plan updated successfully! Existing subscriptions have been updated and users have been notified.');
                } catch (\Exception $e) {
                    Log::error('Error updating existing subscriptions: ' . $e->getMessage());
                    Session::flash('warning', 'Plan updated successfully, but there was an error updating some existing subscriptions. Please check logs.');
                }
            } else {
                Session::flash('success', 'Plan updated Successfully !!!');
            }

            return redirect('/admin/plan-index')->with('success', 'Plan updated Successfully !!!');
        }

        return view('admin.plans.create',['plan' => $plan]);
    }

    public function destroy($slug, $id)
    {
        Session::put('page', 'plans');
        Plan::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(1);
        return redirect()->back();
    }

    private function updateExistingSubscriptionsToNewPrice($planId, $oldPrice, $newPrice)
    {
        // Get the plan
        $plan = Plan::find($planId);
        if (!$plan) {
            throw new \Exception("Plan not found");
        }

        // Find all active subscriptions for this plan
        $subscriptions = UsersSubscriptions::where('plan_id', $planId)
            ->whereIn('status', ['active', 'trialing'])
            ->get();

        if ($subscriptions->isEmpty()) {
            Log::info("No active subscriptions found for plan ID: {$planId}");
            return;
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $successCount = 0;
        $failureCount = 0;

        foreach ($subscriptions as $subscriptionRecord) {
            try {
                // Get user
                $user = User::find($subscriptionRecord->user_id);
                if (!$user) {
                    Log::warning("User not found for subscription ID: {$subscriptionRecord->id}");
                    $failureCount++;
                    continue;
                }

                // Retrieve Stripe subscription
                $stripeSubscription = $stripe->subscriptions->retrieve(
                    $subscriptionRecord->subscription_id,
                    ['expand' => ['items.data.price']]
                );

                // Get current price ID
                $currentPriceId = $stripeSubscription->items->data[0]->price->id;
                $currentAmount = $stripeSubscription->items->data[0]->price->unit_amount / 100;

                // Only update if price is different
                if ($currentAmount == $newPrice) {
                    Log::info("Subscription {$subscriptionRecord->subscription_id} already has the new price. Skipping.");
                    continue;
                }

                // Get interval details based on plan type
                $intervalData = $this->getStripeIntervalData($plan->plan_type);

                // Create new Stripe Price with new amount
                $newStripePrice = $stripe->prices->create([
                    'unit_amount' => $newPrice * 100, // Convert to cents
                    'currency' => 'eur', // Adjust based on your currency
                    'recurring' => [
                        'interval' => $intervalData['interval'],
                        'interval_count' => $intervalData['interval_count'],
                    ],
                    'product' => $stripeSubscription->items->data[0]->price->product,
                ]);

                // Update Stripe subscription
                $stripe->subscriptions->update($subscriptionRecord->subscription_id, [
                    'items' => [[
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $newStripePrice->id,
                    ]],
                    'proration_behavior' => 'none', // No immediate proration
                ]);

                // Store previous amount before updating local database
                $previousAmount = $subscriptionRecord->amount;

                // Update local database with new amount
                $subscriptionRecord->amount = $newPrice;
                $subscriptionRecord->save();

                // Send email notification using CustomerIO
                try {
                    $subData = [
                        'name' => $user->name,
                        'planName' => $plan->name,
                        'oldPrice' => $previousAmount,
                        'newPrice' => $newPrice,
                        'currentYear' => date('Y'),
                        'url' => url('/subscription-details'),
                    ];
                    $customerIo = new CustomerIoService();
                    $customerIo->sendTransactionalEmail($user->email, '13', $subData);
                    Log::info("Price update email sent to user: {$user->email}");
                } catch (\Exception $emailException) {
                    Log::error("Failed to send email to {$user->email}: " . $emailException->getMessage());
                    // Continue even if email fails
                }

                $successCount++;
                Log::info("Successfully updated subscription {$subscriptionRecord->subscription_id} to new price {$newPrice}");

            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Failed to update subscription {$subscriptionRecord->subscription_id}: " . $e->getMessage());
                // Continue with next subscription
            }
        }

        Log::info("Price update completed for plan {$planId}. Success: {$successCount}, Failures: {$failureCount}");
    }

    // this function is used to get the stripe interval data based on the plan type
    private function getStripeIntervalData($planType)
    {
        switch ($planType) {
            case 0: // Monthly
                return ['interval' => 'month', 'interval_count' => 1];
            case 1: // Quarterly (3 months)
                return ['interval' => 'month', 'interval_count' => 3];
            case 2: // Yearly
                return ['interval' => 'year', 'interval_count' => 1];
            case 3: // Bi-Yearly (2 years)
                return ['interval' => 'year', 'interval_count' => 2];
            case 4: // Half Yearly (6 months)
                return ['interval' => 'month', 'interval_count' => 6];
            default:
                return ['interval' => 'month', 'interval_count' => 1];
        }
    }
}
