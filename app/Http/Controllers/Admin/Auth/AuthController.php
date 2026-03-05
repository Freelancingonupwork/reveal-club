<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')) {
            Session::put('page', 'dashboard');
            return view('admin.dashboard');
        }
        return redirect()->route('admin.login');
    }

    public function login(Request $request)
    {
        if(Auth::guard('admin')->check()){
            return redirect()->route('admin.dashboard');
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'email' => 'required|email',
                'password' => 'required'
            ];

            $messages = [
                'email.required' => 'Please enter your email',
                'email.email' => 'Please enter valid email',
                'password' => 'Please enter password'
            ];

            $validation = Validator::make($data, $rules, $messages);

            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation->messages());
            }

            if (isset($data['remember']) || !empty($data['remember'])) {
                $remember = true;
            } else {
                $remember = false;
            }
            if (Auth::guard('admin')->attempt(['email' => $data['email'], 'password' => $data['password'], 'type' => 0], $remember)) {
                if ($remember) {
                    setcookie('email', $data['email'], time() + 3600);
                    setcookie('password', $data['password'], time() + 3600);
                } else {
                    setcookie('email', '');
                    setcookie('password', '');
                }

                if (!isset($data['device_token']) || empty($data['device_token'])) {
                    $data['device_token'] = "";
                }

                if (!isset($data['device_type']) || empty($data['device_type'])) {
                    $data['device_type'] = "";
                }

                $login = array(
                    'login_key' => $this->getLoginKey(Auth::guard('admin')->user()->id),
                    'device_token' => $data['device_token'],
                    'device_type' => $data['device_type']
                );

                $updateUser = User::where(['id' => Auth::guard('admin')->user()->id, 'email' => $data['email']])->update($login);
                return redirect('/admin/dashboard')->with('success', "You have been successfully logged in");
            } else {
                return redirect()->back()->with('error', "Invalid Credentials");
            }
        }
        return view('auth.login');
    }

    public function email(Request $request)
    {
        Session::put('page', 'email');
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();

                $validation = [
                    'smtp_host' => ['required', 'string', 'nullable', 'max:255'],
                    'smtp_port' => ['required', 'numeric', 'nullable', 'digits_between:1,5'],
                    'smtp_user' => ['required', 'string', 'nullable', 'email'],
                    'smtp_password' => ['string', 'nullable'],
                    'smtp_encryption' => ['string', 'nullable'],
                    'from_mail' => ['required', 'string', 'nullable', 'email'],
                    'from_name' => ['required', 'string', 'nullable'],
                ];
                $validator = Validator::make($data, $validation);
                $input = $request->except(['_token']);
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
                }

                foreach ($input as $key => $value) {
                    $settings = new Setting();
                    $settings->setSetting($key, $value);
                }

                return redirect()->back()->with('success', 'Email Setting updated successfully');
            }

            $settings = new Setting();
            return view('admin.settings.email')->with(compact('settings'));
        } catch (Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $th->getMessage(),
                ],
                403
            );
        }
    }

    public function twillio(Request $request)
    {
        Session::put('page', 'twillio');
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();

                $validation = [
                    'twilio_account_sid' => ['string', 'nullable', 'max:255'],
                    'twilio_auth_token' => ['string', 'nullable', 'max:255'],
                    'twilio_number' => ['string', 'nullable', 'max:12'],
                ];
                $validator = Validator::make($request->all(), $validation);
                $input = $request->except(['_token']);
                if ($validator->fails()) {
                    return response()->json(
                        [
                            'success' => false,
                            'errors' => $validator->getMessageBag(),
                        ],
                        400
                    );
                }

                foreach ($input as $key => $value) {
                    $settings = new Setting();
                    $settings->setSetting($key, $value);
                }

                return redirect()->back()->with('success', 'Twillio Setting updated successfully');
            }

            $settings = new Setting();
            return view('admin.settings.twillio')->with(compact('settings'));
        } catch (Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $th->getMessage(),
                ],
                403
            );
        }
    }
    public function stripe(Request $request)
    {
        Session::put('page', 'stripe');
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();

                $validation = [
                    'stripe_secret_key' => ['string', 'nullable', 'max:255'],
                    'stripe_publishable_key' => ['string', 'nullable', 'max:255'],
                    'stripe_webhook_secret' => ['string', 'nullable', 'max:255'],
                ];
                $validator = Validator::make($request->all(), $validation);
                $input = $request->except(['_token']);
                if ($validator->fails()) {
                    return response()->json(
                        [
                            'success' => false,
                            'errors' => $validator->getMessageBag(),
                        ],
                        400
                    );
                }

                foreach ($input as $key => $value) {
                    $settings = new Setting();
                    $settings->setSetting($key, $value);
                }

                return redirect()->back()->with('success', 'Twillio Setting updated successfully');
            }

            $settings = new Setting();
            return view('admin.settings.stripe')->with(compact('settings'));
        } catch (Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $th->getMessage(),
                ],
                403
            );
        }
    }

    public function maintenance(Request $request)
    {
        Session::put('page', 'maintenanceMode');
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $input = $request->except(['_token']);

                foreach ($input as $key => $value) {
                    $settings = new Setting();
                    $settings->setSetting($key, $value);
                }
                return redirect()->back()->with('success', 'Maintenance Setting updated successfully');
            }

            $settings = new Setting();
            return view('admin.settings.maintenance.index')->with(compact('settings'));
        } catch (Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $th->getMessage(),
                ],
                403
            );
        }
    }

    public function preScreenQuiz(Request $request)
    {
        Session::put('page', 'pre_screen_quiz');
        try {
            if ($request->isMethod('post')) {
                $data = $request->all();
                $input = $request->except(['_token']);

                foreach ($input as $key => $value) {
                    $settings = new Setting();
                    $settings->setSetting($key, $value);
                }
                return redirect()->back()->with('success', 'Pre-Screen Quiz Setting updated successfully');
            }

            $settings = new Setting();
            return view('admin.settings.preScreenQuiz')->with(compact('settings'));
        } catch (Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $th->getMessage(),
                ],
                403
            );
        }
    }

    public function getLoginKey($user_id)
    {
        $salt = "23df$#%%^66sd$^%fg%^sjgdk90fdklndg099ndfg09LKJDJ*@##lkhlkhlsa#$%";
        $login_key = hash('sha1', $salt . $user_id . time());
        return $login_key;
    }

    public function logout(Request $request)
    {
        // Check if the user is logged in with the 'user' guard
        if (Auth::guard('admin')->check()) {
            // Log the user out
            Auth::guard('admin')->logout();
        }

        // Invalidate the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to the login page
        return redirect('/admin');
    }

    public function profile(Request $request)
    {
        Session::put('page', 'admin-profile');
        if ($request->isMethod('post')) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;

            if (!isset($data['avatar']) || empty($data['avatar'])) {
                $profilePic = Auth::guard('admin')->user()->avatar;
            } else {
                $profilePic = time() . '.' . $data['avatar']->extension();
                if (!Storage::disk('public')->exists("/users/avatars")) {
                    Storage::disk('public')->makeDirectory("/users/avatars"); //creates directory
                }
                if (Storage::disk('public')->exists("/users/" . Auth::guard('admin')->user()->avatar)) {
                    Storage::disk('public')->delete("/users/" . Auth::guard('admin')->user()->avatar);
                }
                $request->avatar->storeAs("users/avatars", $profilePic, 'public');

                $profilePic = "users/avatars/$profilePic";
            }
            $user = User::where(['id' => Auth::guard('admin')->user()->id])->update(['name' => $data['name'], 'mobile' => $data['mobile'], 'avatar' => $profilePic]);
            return redirect()->back()->with('success', 'User details updated successfully.');
        }
        return view('admin.adminProfile');
    }

    public function googleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleHandle()
    {
        try {
            $user = Socialite::driver('google')->user();
            $findUser = User::where(['email' => $user->email])->first();
            if (!$findUser) {
                $findUser = new User;
                $findUser->name = $user->name;
                $findUser->email = $user->email;
                $findUser->password = bcrypt("Test@12345");
                $findUser->avatar = $user->avatar;
                $findUser->type = 2;
                $findUser->save();
            }

            if (Auth::attempt(['email' => $findUser->email, 'password' => "Test@12345", 'type' => $findUser->type])) {
                if (!isset($data['device_token']) || empty($data['device_token'])) {
                    $data['device_token'] = "";
                }

                if (!isset($data['device_type']) || empty($data['device_type'])) {
                    $data['device_type'] = "";
                }

                $login = array(
                    'login_key' => $this->getLoginKey(Auth::user()->id),
                    'device_token' => $data['device_token'],
                    'device_type' => $data['device_type']
                );

                $updateUser = User::where(['id' => $findUser->id, 'email' => $findUser->email])->update($login);
            }
            return redirect('user-home');
        } catch (Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $th->getMessage(),
                ],
                403
            );
        }
    }

    public function userHome()
    {
        return view('home');
    }

    public function facebookLogin()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookHandle()
    {
        try {
            $user = Socialite::driver('facebook')->user();
            $findUser = User::where(['email' => $user->email])->first();
            if (!$findUser) {
                $findUser = new User;
                $findUser->name = $user->name;
                $findUser->email = $user->email;
                $findUser->password = bcrypt("Test@12345");
                $findUser->avatar = $user->avatar;
                $findUser->type = 2;
                $findUser->save();
            }

            if (Auth::attempt(['email' => $findUser->email, 'password' => "Test@12345", 'type' => $findUser->type])) {
                if (!isset($data['device_token']) || empty($data['device_token'])) {
                    $data['device_token'] = "";
                }

                if (!isset($data['device_type']) || empty($data['device_type'])) {
                    $data['device_type'] = "";
                }

                $login = array(
                    'login_key' => $this->getLoginKey(Auth::user()->id),
                    'device_token' => $data['device_token'],
                    'device_type' => $data['device_type']
                );

                $updateUser = User::where(['id' => $findUser->id, 'email' => $findUser->email])->update($login);
            }
            return redirect('user-home');
        } catch (Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $th->getMessage(),
                ],
                403
            );
        }
    }

    public function changePassword(Request $request)
    {
        $data = $request->all();
        $validation = [
            'old_password' => ['required'],
            'new_password' => [
                'required',
                'min:6',
                'regex:/[a-z]/',     // must contain at least one lowercase letter
                'regex:/[A-Z]/',     // must contain at least one uppercase letter
                'regex:/[0-9]/',     // must contain at least one digit
                'regex:/[@$!%*#?&]/' // must contain a special character
            ],
            'confirm_password' => ['required', 'same:new_password'],
        ];
        $validator = Validator::make($data, $validation);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
        }
        $currentPasswordStatus = Hash::check($data['old_password'], Auth::guard('admin')->user()->password);
        if ($currentPasswordStatus) {
            $password = Hash::make($data['new_password']);
            User::where(['id' => Auth::guard('admin')->user()->id])->update(['password' => $password]);

            return redirect()->back()->with('success', "Your Password Changed Successfully.");
        } else {
            return redirect()->back()->with('error', "Your current password is wrong.");
        }
    }
}
