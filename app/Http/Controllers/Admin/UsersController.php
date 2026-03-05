<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Writer;

class UsersController extends Controller
{
    public function index()
    {
        Session::put('page', 'users');
        $users = User::where(['type' => 2])->get()->toArray();
        // dd($users);
        return view('admin.users.index')->with(compact('users'));
    }

    public function create(Request $request)
    {
        Session::put('page', 'users');
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'name' => 'required',
                'type' =>  'required|numeric',
                'email' => 'required|email',
                'mobile' => 'required|numeric|min:10'
            ];

            $messages = [
                'name.required' => "Please enter user name",
                'type.required' => "Please enter user type",
                'email.required' => 'Please enter user email',
                'email.email' => 'Please enter valid email',
                'mobile.required' => 'Please enter user mobile',
            ];

            $validation = Validator::make($data, $rules, $messages);

            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation->messages());
            }

            $password = Hash::make('test123');
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $password;
            $user->type = $data['type'];
            $user->mobile = $data['mobile'];
            $user->save();

            return redirect()->route('admin.users')->withSuccess(__('User created successfully.'));
        }

        return view('admin.users.create');
    }

    public function updateUserStatus(Request $request)
    {
        Session::put('page', 'users');
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            User::where(['id' => $data['user_id']])->update(['status' => $status]);

            return response()->json(['status' => $status, 'user_id' => $data['user_id']]);
        }
    }

    public function userProfile(Request $request, $id)
    {
        Session::put('page', 'users');
        $user = User::where(['id' => $id])->first();
        if ($request->isMethod('post')) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;


            if (!isset($data['avatar']) || empty($data['avatar'])) {
                $profilePic = $user->avatar;
            } else {
                $profilePic = time() . '.' . $data['avatar']->extension();
                if (!Storage::disk('public')->exists("/users/avatars")) {
                    Storage::disk('public')->makeDirectory("/users/avatars"); //creates directory
                }
                if (Storage::disk('public')->exists("/users/" . $user->avatar)) {
                    Storage::disk('public')->delete("/users/" . $user->avatar);
                }
                $request->avatar->storeAs("users/avatars", $profilePic, 'public');

                $profilePic = "users/avatars/$profilePic";
            }
            User::where(['id' => $user->id])->update([
                    'name' => $data['name'],
                    'mobile' => $data['mobile'], 
                    'avatar' => $profilePic,
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'country' => $data['country'],
                    'postal_code' => $data['postal_code'],
                ]);
            return redirect()->back()->with('success', 'User details updated successfully.');
        }
        return view('admin.users.profile')->with(compact('user'));
    }

    public function destroy($id)
    {
        Session::put('page', 'users');

        // List of all tables that reference the 'users' table through foreign keys
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
    
        // Finally, delete the user
        User::where('id', $id)->delete();

        return redirect()->back()->with('success', 'User Deleted successfully.');
    }

    public function exportNewUsers(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Handle date range export
        if ($startDate && $endDate) {
            $newUsers = User::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->where(function ($query) {
                    $query->where('isSubscribedUser', 1)
                        ->orWhere('iosSubscribedUser', 1);
                })
                ->get();
        } else {
            // Default behavior for exporting users without a date range
            $lastExportTimestamp = Storage::exists('last_export_timestamp.txt') 
                ? (int)Storage::get('last_export_timestamp.txt') 
                : 0;

            $newUsers = User::where('created_at', '>', date('Y-m-d H:i:s', $lastExportTimestamp))
                ->where(function ($query) {
                    $query->where('isSubscribedUser', 1)
                        ->orWhere('iosSubscribedUser', 1);
                })
                ->get();

            // Update the timestamp of the last export
            Storage::put('last_export_timestamp.txt', time());
        }

        if ($newUsers->isEmpty()) {
            return response()->json(['message' => 'No new users to export.'], 200);
        }

        $csvFilePath = 'exports/new_users_' . date('Y_m_d_H_i_s') . '.csv';
        $csv = Writer::createFromString('');

        // Add header
        $csv->insertOne(['First Name', 'Last Name', 'Email', 'Address', 'Country', 'City', 'Postal Code']);

        // Add new users' data
        foreach ($newUsers as $user) {
            $csv->insertOne([
                $user->first_name ?? $user->name,
                $user->last_name ?? $user->name,
                $user->email,
                $user->address,
                $user->country,
                $user->city,
                $user->postal_code,
            ]);
        }

        // Save CSV in storage
        Storage::put($csvFilePath, $csv->toString());
    
        try {
            $downloadPath = public_path('storage/' . $csvFilePath); // Assuming symbolic link is in place
            if (!file_exists($downloadPath)) {
                return response()->json(['message' => 'File does not exist for download.'], 404);
            }
            return response()->download($downloadPath)->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error("Error during file download: " . $e->getMessage());
            return response()->json(['message' => 'Failed to download the CSV file.'], 500);
        }
    }

    public function userResetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password reset successfully.');
    }

}
