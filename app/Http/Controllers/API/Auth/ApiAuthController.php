<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\Controller;
use App\Mail\ForgetPassword;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserReferenceAnswer;
use App\Models\UserSecurityToken;
use App\Models\UsersInitialMeasurement;
use App\Services\CustomerIoService;
use App\Services\IntercomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class ApiAuthController extends ApiController
{
    public function userRegister(Request $request)
    {
        try {
            // Validate Data
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'email' => ['required', 'regex:/(.+)@(.+)\.(.+)/i'],
                'password' => [
                    'required',
                    'min:6',
                    // 'regex:/[a-z]/',     // must contain at least one lowercase letter
                    // 'regex:/[A-Z]/',     // must contain at least one uppercase letter
                    // 'regex:/[0-9]/'      // must contain at least one digit
                ],
                'cnf_password' => ['required', 'same:password'],
                'mobile' => ['required', 'numeric'],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                'device_type' => ['required', 'in:I,A'],
            ], [
                'name.required' => 'The Name field is required.',
                'email.required' => 'The E-Mail field is required.',
                'email.regex' => 'Enter valid email address.',
                'password.required' => 'The Password field is required.',
                // 'password.regex' => 'Password must contain at least one lowercase, one uppercase and one digit.',
                'cnf_password.required' => 'The Confirm Password field is required.',
                'cnf_password.same' => 'The Password and Confirm Password should be the same.',
                'mobile.required' => 'The Mobile field is required.',
                'mobile.numeric' => 'Enter a valid mobile number.',
                'device_type.required' => 'The Device Type field is required.',
                'device_type.in' => 'The Device Type should be either I or A.',
            ]);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $data = $request->all();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $profilePic = time() . '.' . $data['avatar']->extension();
                Storage::disk('public')->putFileAs('users/avatars', $data['avatar'], $profilePic);
                $data['avatar'] = "users/avatars/$profilePic";
            } else {
                $data['avatar'] = "";
            }

            // Create and save user
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'mobile' => $data['mobile'],
                    'avatar' => $data['avatar'],
                    'type' => 2,
                    'isQuestionsAttempted' => 1,
                    'isSubscribedUser' => 1,
                    'iosSubscribedUser' => 1,
                ]
            );
            $platform = 'Android'; // Set the platform to Android by default
            if ($data['device_type'] === 'I') {
                $platform = 'IOS';
            }
            // dd($user);
            $intercomService = new IntercomService();
            $intercomData = $intercomService->registerWithIntercom($user, $platform);

            return $this->getSingleResponse("USER REGISTERED SUCCESSFULLY", $user, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function userLogin(Request $request)
    {
        try {
            $data = $request->all();

            // Validate Data
            $validator = Validator::make($data, [
                'email' => ['required', 'regex:/(.+)@(.+)\.(.+)/i'],
                'password' => ['required'],
                'device_token' => ['required'],
                'device_type' => ['required', 'in:I,A'],
            ], [
                'email.required' => 'The E-Mail field is required.',
                'email.regex' => 'Enter a valid email address.',
                'password.required' => 'The Password field is required.',
                'device_token.required' => 'The Device Token field is required.',
                'device_type.required' => 'The Device Type field is required.',
                'device_type.in' => 'The Device Type should be either I or A.',
            ]);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $remember = $request->has('remember');
            $isSubscribeduser = User::where('email', $data['email'])->value('isSubscribedUser');
            if ($isSubscribeduser == 2) {
                return $this->errorResponse("Veuillez d'abord compléter votre paiement", [], 200);
            }
            if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
                $user = Auth::user();
                $loginData = array(
                    'login_key' => $this->getLoginKey($user->id),
                    'device_token' => $data['device_token'],
                    'device_type' => $data['device_type']
                );

                $user->update($loginData);

                $platform = 'Android'; // Set the platform to Android by default
                if ($data['device_type'] === 'I') {
                    $platform = 'IOS';
                }

                if (!$user->intercom_hash) {
                    $intercomService = new IntercomService();
                    $intercomData = $intercomService->registerWithIntercom($user, $platform);

                    if ($intercomData) {
                        $user->update([
                            'intercom_id' => $intercomData['intercom_user_id'],
                            'intercom_hash' => $intercomData['intercom_hash']
                        ]);
                        $user->refresh(); // Ensure the model is updated
                    }
                }

                // If token(s) already exist for this user, delete them
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                $token = $user->createToken('user')->accessToken;

                return $this->successResponse("USER LOGGED IN SUCCESSFULLY", [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "mobile" => $user->mobile,
                    "avatar" => $user->avatar ? asset(Storage::url($user->avatar)) : '',
                    "device_type" => $user->device_type,
                    "device_token" => $user->device_token,
                    "login_key" => $user->login_key,
                    "status" => $user->status,
                    "token" => $token,
                    "intercom_id" => $user->intercom_id,
                    "intercom_hash" => $user->intercom_hash,
                ]);
            } else {
                return $this->errorResponse("E-mail ou mot de passe incorrecte", [], 200);
            }
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();

                /* Validate Data */
                $validation = [
                    'email' => ['required', 'regex:/(.+)@(.+)\.(.+)/i'],
                ];
                $validation_messages = [
                    'email.required' => 'The E-Mail field is required.',
                    'email.regex' => 'Enter valid email address.',
                ];
                $validator = Validator::make($request->all(), $validation, $validation_messages);
                if ($validator->fails()) {
                    return $this->validationError("Fail", $validator->errors()->first(), 200);
                }
                $user = User::where(['email' => $data['email']])->first();

                if ($user->isSubscribedUser == 2) {
                    return $this->errorResponse("Veuillez d'abord compléter votre paiement", [], 200);
                }
                if ($user) {
                    $token = $token = Str::random(64);
                    DB::table('password_resets')->insert([
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => Carbon::now()
                    ]);

                    $resetPasswordUrl = url('user-reset-password/'. $token);
                    $customerIo = new CustomerIoService();
                    $customerIo->sendTransactionalEmail($request->email, '5', ['reset_password_url' => $resetPasswordUrl, 'name' => $user->name]);

                    $res['token'] = $token;
                    $res['url'] = url('user-reset-password/'. $token);
                    return $this->getSingleResponse("Veuillez vérifier votre e-mail.", $res);
                } else {
                    return $this->errorResponse("Nous n'avons pas trouvé d'utilisateur avec cet email.", [], 400);
                }
            }
        } catch (Throwable $ex) {
            return response()->json(
                [
                    "status" => "fail",
                    'errors' => $ex->getMessage(),
                    "message" => "Something went wrong",
                ],
                500
            );
        }
    }

    public function resetPassword(Request $request, $token = null)
    {
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();

                /* Validate Data */
                $validation = [
                    'email' => ['required', 'regex:/(.+)@(.+)\.(.+)/i'],
                    'password' => [
                        'required',
                        'min:6',
                        'regex:/[a-z]/',     // must contain at least one lowercase letter
                        'regex:/[A-Z]/',     // must contain at least one uppercase letter
                        'regex:/[0-9]/'     // must contain at least one digit
                    ],
                    'cnf_password' => ['required', 'same:password'],
                    'token' => ['required']
                ];
                $validation_messages = [
                    'email.required' => 'The E-Mail field is required.',
                    'email.regex' => 'Enter valid email address.',
                    'password.required' => 'The Password field is required.',
                    'password.regex' => 'Password must contains at least one lowercase, one uppercase and one digit.',
                    'cnf_password.required' => 'The Confirm Password field is required.',
                    'cnf_password.same' => 'The Password and Confirm Password should be same.',
                    'token.required' => 'The Token field is required.',
                ];
                $validator = Validator::make($request->all(), $validation, $validation_messages);
                if ($validator->fails()) {
                    return $this->validationError("Fail", $validator->errors()->first(), 200);
                }

                $resetPassData = DB::table('password_resets')->where(['token' => $data['token']])->first();

                if (!$resetPassData) {
                    return $this->errorResponse("Invalid Token", [], 400);
                }

                if ($resetPassData->email === $data['email']) {
                    $user = User::where('email', $resetPassData->email)->update(['password' => Hash::make($request->password)]);
                    if ($user) {

                        $resetPassData = DB::table('password_resets')->where(['token' => $data['token']])->delete();
                        $userDetail = User::where(['email' => $data['email']])->first();
                        $customerIo = new CustomerIoService();
                        $customerIo->sendTransactionalEmail($userDetail->email, '6', ['name' => $userDetail->name]);

                        return $this->getSingleResponse("PASSWORD RESET SUCCESSFULLY.", []);
                    }
                } else {
                    return $this->errorResponse("Invalid Email", [], 200);
                }
            }
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            // Validation
            $validation = [
                'old_password' => ['required'],
                'new_password' => [
                    'required',
                    'min:6',
                    'regex:/[a-z]/',     // must contain at least one lowercase letter
                    'regex:/[A-Z]/',     // must contain at least one uppercase letter
                    'regex:/[0-9]/'     // must contain at least one digit
                ],
                'confirm_password' => ['required', 'same:new_password'],
            ];

            $validator = Validator::make($request->all(), $validation);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $data = $request->all();

            // Check if User Exists or not
            $currentPasswordStatus = Hash::check($data['old_password'], auth()->user()->password);
            if ($currentPasswordStatus) {
                $password = Hash::make($data['new_password']);
                User::where(['id' => Auth::user()->id])->update(['password' => $password]);

                return $this->successResponse("Your Password Changed Successfully.", [], 200);
            } else {
                return $this->errorResponse("Your current password is wrong.", [], 200);
            }
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $id = $request->user()->id;

            // Reassign nutrition_ingredients to a placeholder user ID instead of deleting
            DB::table('nutrition_ingredients')
                ->where('user_id', $id)
                ->update(['user_id' => 0]);

            $tables = [
                'challenge_user_statuses',
                'challenge_users',
                'common_grocery_ingredients',
                'discussion_replies',
                'discussions',
                'favourite_recipes',
                'food_planners',
                'lessons_planners',
                'nutrition_favourites',
                'promo_code_usages',
                'steps_goals',
                'stripe_customers',
                'subscription_histories',
                'user_recipes',
                'user_security_tokens',
                'user_session_statuses',
                'users_appearance_infos',
                'users_current_measurements',
                'users_initial_measurements',
                'users_subscriptions',
                'users_target_measurements',
            ];

            // Loop through each table and delete dependent records
            foreach ($tables as $table) {
                if($table == 'discussions'){
                    DB::table($table)
                        ->where('added_by', $id)
                        ->delete();
                }else{
                    DB::table($table)
                        ->where('user_id', $id)
                        ->delete();
                }
            }
            $user = User::find($request->user()->id);
            $user->delete();
            return $this->successResponse('Account deleted successfully', [], 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user()->token();
        $user->revoke();
        return $this->successResponse('You have logged out successfully', $user);
    }

    public function registerGuestUser(Request $request)
    {
        try {
            // Validate Data
            $validator = Validator::make($request->all(), [
                'device_token' => ['required'],
            ]);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            if (!isset($request->name) || empty($request->name)) {
                $guestName = "Guest_" . rand(00000, 99999);
            } else {
                $guestName = $request->name;
            }

            // Check if User already exists
            if (User::where('name', $guestName)->exists()) {
                return response()->json([
                    "status" => "fail",
                    'message' => 'User name ' . $guestName . ' already exists',
                ], 400);
            }

            // Create a new guest user
            $user = User::create([
                'name' => $guestName,
                'device_token' => $request->device_token,
            ]);

            $token = $user->createToken('guest')->accessToken;

            return $this->successResponse('Guest Created', [
                'id' => $user->id,
                'name' => $user->name,
                'token' => $token,
            ]);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function socialLogin(Request $request)
    {
        try {
            $data = $request->all();
            if (!isset($data['login_type']) || empty($data['login_type'])) {
                $message = "PLEASE ENTER LOGIN TYPE";
                return $this->validationError("Fail", $message, 200);
            }

            $loginType = strtoupper($data['login_type']);

            if ($loginType === "APPLE" || $loginType === "GOOGLE") {

                if ($loginType === "APPLE") {
                    $rules['apple_id'] = 'required';
                } else {
                    $rules = [
                        'name'          => 'required',
                        'login_type'    => 'required|in:GOOGLE,FACEBOOK,APPLE',
                        'social_key'    => 'required',
                        'device_token'  => 'required',
                        'device_type'   => 'required',
                        'email'         => 'required|email',
                        'social_email'  => 'required|email',
                        'login_from'    => 'required|in:register,login'
                    ];
                }

                if ($loginType ===  'GOOGLE') {
                    $rules['username'] = 'required';
                } else {
                    $rules['username'] = 'nullable';
                }

                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return $this->validationError("Fail", $validator->errors()->first(), 200);
                }

                // Check if the user already exists
                $user = null;
                if ($loginType === "APPLE") {
                    $user = User::where('apple_id', $data['apple_id'])->first();
                } else {
                    $user = User::where('email', $data['email'])->first();
                }

                if (isset($data['login_from']) && !empty($data['login_from']) && $data['login_from'] === 'register') {
                    if (!$user) {
                        return $this->successResponse("You doesn't have attempted questions with {$data['email']}", (object)[], 200);
                    }
                    if (isset($data['social_email']) && !empty($data['social_email']) && !empty($user)) {
                        if ($data['login_type'] === 'GOOGLE') {
                            $userExistsFromSocialEmail = User::where('email', $data['social_email'])->first();
                            if ($userExistsFromSocialEmail) {
                                return $this->successResponse("The email you provided is already exists");
                            }
                        }

                        if ($data['login_type'] === 'APPLE') {
                            $userExistsFromSocialEmail = User::where('apple_id', $data['apple_id'], 'email', $data['social_email'])->first();
                            if ($userExistsFromSocialEmail) {
                                return $this->successResponse("The email you provided is already exists");
                            }
                        }

                        $loginData = [
                            'name'                  => isset($data['name']) ? $data['name'] : '',
                            'username'              => isset($data['username']) ? $data['username'] : '',
                            'first_name'            => isset($data['first_name']) ? $data['first_name'] : '',
                            'last_name'             => isset($data['last_name']) ? $data['last_name'] : '',
                            'email'                 => isset($data['social_email']) ? $data['social_email'] : $data['email'],
                            'social_key'            => isset($data['social_key']) ? $data['social_key'] : '',
                            'apple_id'              => isset($data['apple_id']) ? $data['apple_id'] : $user->apple_id,
                            'login_type'            => $loginType,
                            'login_key'             => $this->getLoginKey($user->id),
                            'device_token'          => $data['device_token'],
                            'device_type'           => $data['device_type'],
                            'isQuestionsAttempted'  => 1,
                            'isSubscribedUser'      => 1
                        ];
                    }
                } else {
                    if (!$user) {
                        $data['name']           = isset($data['name']) ? $data['name'] : '';
                        $data['username']       = isset($data['username']) ? $data['username'] : '';
                        $data['first_name']     = isset($data['first_name']) ? $data['first_name'] : '';
                        $data['last_name']      = isset($data['last_name']) ? $data['last_name'] : '';
                        $data['social_key']     = isset($data['social_key']) ? $data['social_key'] : '';
                        $data['login_type']     = $loginType;
                        $data['type']           = 2;
                        $data['status']         = 1;
                        $data['password']       = Hash::make("Test@12345");
                        $user = User::create($data);
                    }

                    $loginData = [
                        'login_key' => $this->getLoginKey($user->id),
                        'device_token' => $data['device_token'],
                        'device_type' => $data['device_type']
                    ];
                }

                $user->update($loginData);
                // Create a token for the user
                $token = $user->createToken('user')->accessToken;

                $user = User::where(['id' => $user->id])->first();
                $response = $user;
                $response['token'] = $token;

                if ($response['isQuestionsAttempted'] != 1) {
                    return $this->successResponse("Please attempt questions to register successfully.", $response, 200);
                } else {
                    return $this->successResponse("USER LOGGED IN SUCCESSFULLY", $response, 200);
                }
            } else {
                $message = "INVALID LOGIN TYPE";
                return $this->validationError("Fail", $message, 200);
            }
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function userProfile(Request $request)
    {
        try {
            $user = User::where(['id' => Auth::id()])->first();

            if ($user) {
                $userProfileData = $this->generateUserProfileResponse($user);
                return $this->successResponse("User profile data found", $userProfileData, 200);
            } else {
                return $this->errorResponse("User profile data Not found", [], 200);
            }
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function updateUserProfile(Request $request)
    {
        try {
            $userId = Auth::id();

            $validation = [
                'name' => ['nullable', 'string'],
                'mobile' => ['nullable', 'string'],
                'email' => ['required', 'string', 'email', 'unique:users,email,' . $userId],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'remove_avatar' => ['boolean'], // New key for removing avatar
            ];

            // Perform the validation
            $validator = Validator::make($request->all(), $validation);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $data = $request->all();
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Handle avatar update or deletion
            // If remove_avatar is true (1 or '1'), delete existing avatar and do not upload a new one
            if ($request->input('remove_avatar', false)) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                    $user->avatar = null;
                }
            } elseif ($request->hasFile('avatar')) {
                // Only upload new avatar if remove_avatar is not set or is false/0
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $filename = 'avatar_' . $userId . '_' . uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension();
                $avatarPath = $request->file('avatar')->storeAs('user_avatar', $filename, 'public');
                $user->avatar = $avatarPath;
            }

            // Update other fields
            if(isset($data['name'])){
                $user->name = $data['name'];
            }
            if(isset($data['mobile'])){
                $user->mobile = $data['mobile'];
            }
            $user->email = $data['email'];

            $user->save();

            // Generate the same response as in userProfile
            $userProfileData = $this->generateUserProfileResponse($user);

            return $this->successResponse("User profile updated successfully", $userProfileData, 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }
    public function preSignUp(Request $request)
    {
        try {
            /* Validate Data */
            $validation = [
                'id' => ['nullable', 'integer'],
                'sessionId' => ['required_without:id', 'string']
            ];
            $validator = Validator::make($request->all(), $validation);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }
            $data = $request->all();

            if (isset($data['sessionId']) && !empty($data['sessionId'])) {
                $userReferenceData = UserReferenceAnswer::where(['session_id' => $data['sessionId'], 'key' => 'email'])->first();
                $result['id'] = $userReferenceData['session_id'];
                $result['email'] = $userReferenceData['value'];
            } else {
                $result = User::where(['id' => $data['id']])->first();
            }

            if ($result) {
                $userData = [
                    "id"    =>  $result['id'],
                    "email" =>  $result['email'],
                ];
                return $this->successResponse("User record found", $userData, 200);
            } else {
                return $this->successResponse("User record Not found", (object)[], 200);
            }
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    private function generateUserProfileResponse(User $user)
    {
        $UsersInitialMeasurement = UsersInitialMeasurement::where(['user_id' => $user->id])->first();

        $gender = $UsersInitialMeasurement['gender'] ?? "";
        $age = $UsersInitialMeasurement['age'] ?? 0; // we only stored the age of the user at the moment
        $height = 0; // static value

        $avatarUrl = $user->avatar ? asset(Storage::url($user->avatar)) : '';

        $userProfileData = [
            "user_id"    =>  $user->id,
            "name"       =>  $user->name ?? "",
            "username"   =>  $user->username ?? "",
            "height"     =>  $height,
            "email"      =>  $user->email,
            "mobile"     =>  $user->mobile ?? "",
            "gender"     =>  $gender,
            "age"        =>  $age,
            "avatar"     =>  $avatarUrl, // Return the full URL of the avatar
        ];

        return $userProfileData;
    }

    public function updateOrCreateSecurityToken(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Generate a random security token
        $securityToken = Str::random(60);

        // Update or create the security token
        $token = UserSecurityToken::updateOrCreate(
            ['user_id' => $request->user_id],
            ['security_token' => $securityToken]
        );

        return response()->json($token, 200);
    }

    public function userLocation(Request $request) {
        try {
            if ($request->has('user_id') && !empty($request->user_id)) {
                $authUserId = Auth::user()->id;
                $user_id = $request->user_id;
                if ($authUserId == $user_id) {

                    $user = User::find($authUserId);
                    $message = "User location found.";

                    if ($request->isMethod('POST')) {
                        $validator = Validator::make($request->all(),
                        [
                            'name' => 'required',
                            'address' => 'required',
                            'postal_code' => 'required|numeric',
                            'city' => 'required',
                            'country' => 'required'
                        ], [
                            'name.required' => 'Please enter your name.',
                            'address.required' => 'Please enter your address.',
                            'postal_code.required' => 'Please enter your postal code.',
                            'postal_code.numeric' => 'Invalid postal code, please enter numeric value.',
                            'city.required' => 'Please enter your city.',
                            'country.required' => 'Please enter your country.'
                        ]);

                        if ($validator->fails()) {
                            return $this->validationError("Fail", $validator->errors()->first(), 200);
                        }

                        $user->name = $request->name;
                        $user->address = $request->address;
                        $user->postal_code = (int)$request->postal_code;
                        $user->city = $request->city;
                        $user->country = $request->country;
                        if ($request->has('company') && !empty($request->company)) {
                            $user->company = $request->company;
                        }
                        if ($request->has('first_name') && !empty($request->first_name)) {
                            $user->first_name = $request->first_name;
                        }
                        if ($request->has('last_name') && !empty($request->last_name)) {
                            $user->last_name = $request->last_name;
                        }
                        $user->save();
                        $message = "User location saved.";
                    }

                    $data = [
                        'id' => $user->id,
                        'name' => $user->name ?? "",
                        'address' => $user->address ?? "",
                        'postal_code' => (int)$user->postal_code ?? 0,
                        'city' => $user->city ?? "",
                        'country' => $user->country ?? "",
                        'company' => $user->company ?? "",
                        'first_name' => $user->first_name ?? "",
                        'last_name' => $user->last_name ?? ""
                    ];

                    return $this->successResponse($message, $data, 200);
                }
                return $this->errorResponse("Unauthenticated!", [], 401);
            }
            return $this->validationError("Fail", "User id is missing!", 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }
}
