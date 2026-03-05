<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\feedback_question;
use App\Models\UnsubscriptionFlowQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index()
    {
        Session::put('page', "unsubscription");
        $screen = UnsubscriptionFlowQuestion::orderBy('screen_position', 'ASC')->get()->toArray();
        return view('admin.cancel_subscription.index', ['screen' => $screen,]);
    }

    public function addUnsubsflow(Request $request)
    {
        Session::put('page', 'unsubscription');
        $screens = UnsubscriptionFlowQuestion::all();
        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;

            $rules = [
                'unsubs_title' => ['required', 'string'],
                'unsubs_description' => ['required', 'string'],
            ];
            $message = [
                'unsubs_title.required' => "Please enter Title.",
                'unsubs_description.required' => "Please enter description.",
            ];

            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }
            $slug = Str::slug($data['unsubs_title']);

            $newUnSubs = new UnsubscriptionFlowQuestion();
            $newUnSubs->screen_title = $data['unsubs_title'];
            $newUnSubs->slug = $slug;
            $newUnSubs->screen_description = $data['unsubs_description'];
            $newUnSubs->button_text = $data['button_text'];

            if (isset($data['is_multiple_buttons']) && $data['is_multiple_buttons'] == 'on') {
                $newUnSubs->is_multiple_button = 1;
                $json_array = json_encode($data['multipleButtons'], JSON_PRETTY_PRINT);
                $newUnSubs->multiple_buttons_value = $json_array;
            } else {
                $newUnSubs->is_multiple_button = 0;
            }

            if (isset($data['show_title']) && $data['show_title'] == 'on') {
                $newUnSubs->show_title = 1;
            }else{
                $newUnSubs->show_title = 0;
            }
            if (isset($data['show_default_button']) && $data['show_default_button'] == 'on') {
                $newUnSubs->show_default_button = 1;
            }else{
                $newUnSubs->show_default_button = 0;
            }
            if (isset($data['is_unsubs_image']) && $data['is_unsubs_image'] == 'on') {
                $newUnSubs->is_screen_image = 1;

                $unsubs_image = time() . '.' . $data['unsubs_image']->extension();
                if (!Storage::disk('public')->exists("/unsubscription/image")) {
                    Storage::disk('public')->makeDirectory("/unsubscription/image"); //creates directory
                }
                $request->unsubs_image->storeAs("unsubscription/image", $unsubs_image, 'public');
                $unsubs_image = "unsubscription/image/$unsubs_image";

                $newUnSubs->screen_image = $unsubs_image;
            }
            $newUnSubs->screen_type = $data['is_transition'];

            if (isset($data['feedback_type'])) {
                $newUnSubs->feedback_type = $data['feedback_type'];
            }
            if (isset($data['is_transition']) && $data['is_transition'] == '2') {
                $newUnSubs->have_offer = $data['have_offer'];
                $newUnSubs->offer_in_per = $data['offer_in_per'];
            }

            $newUnSubs->save();
            if (isset($data['feedback_type'])) {
                $feedback_options = [];
                $feedbackType = $data['feedback_type'];

                if ($feedbackType == "single") {
                    foreach ($data['singleAnswers'] as $singleAnswers) {
                        $feedback_options[] = [
                            'screen_id' => $newUnSubs->id,
                            'feedback_type' => $data['feedback_type'],
                            'feedback_options' => $singleAnswers['single_Answer'],
                        ];
                    }

                }elseif($feedbackType == "multiple"){
                    foreach ($data['multipleAnswers'] as $multipleAnswers) {
                        $feedback_options[] = [
                            'screen_id' => $newUnSubs->id,
                            'feedback_type' => $data['feedback_type'],
                            'feedback_options' => $multipleAnswers['multiple_Answer'],
                        ];
                    }
                }elseif($feedbackType == "userInput"){
                    $feedback_options[] = [
                        'screen_id' => $newUnSubs->id,
                        'feedback_type' => $data['feedback_type'],
                        'feedback_options' => $data['input_Answer'],
                    ];
                }

                feedback_question::insert($feedback_options);
            }
            return redirect('/admin/unsubscription-index')->with('success', 'Cancel Subscription Screen Inserted Successfully !!!');
        }
        return view('admin.cancel_subscription.create', compact('screens'));
    }

    public function updateUnsubsflow(Request $request, $slug, $id){
        Session::put('page', 'unsubscription');
        $screens = UnsubscriptionFlowQuestion::all();
        $unsubsData = UnsubscriptionFlowQuestion::with('feedback')->where(['id' => $id, 'slug' => $slug])->firstOrFail()->toArray();

        if ($request->isMethod('post')) {
            $data = $request->all();
            // Validate request data
            $validator = Validator::make($data, [
                'unsubs_title' => ['required', 'string'],
                'unsubs_description' => ['required', 'string'],
            ], [
                'unsubs_title.required' => "Please enter Question Title.",
                'unsubs_description.required' => "Please enter description.",
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            // Generate slug and handle image upload
            $slug = Str::slug($data['unsubs_title']);

            if(isset($data['is_transition']) && ($data['is_transition'] == 1 || $data['is_transition'] == 2 )){
                $data['feedback_type'] = null;
            }

            if (isset($data['is_unsubs_image']) || !empty($data['is_unsubs_image'])) {
                $data['is_unsubs_image'] = 1;
                if (!isset($data['unsubs_image']) || empty($data['unsubs_image'])) {
                    $data['unsubs_image'] = $unsubsData['screen_image'];
                } else {
                    if (!Storage::disk('public')->exists("/unsubscription/image")) {
                        Storage::disk('public')->makeDirectory("/unsubscription/image"); //creates directory
                    }
                    if($unsubsData['screen_image'] != null){
                        Storage::disk('public')->delete($unsubsData['screen_image']);
                    }
                    $unsubsImage = time() . '.' . $data['unsubs_image']->extension();

                    $request->unsubs_image->storeAs("unsubscription/image", $unsubsImage, 'public');
                    $data['unsubs_image'] = "unsubscription/image/$unsubsImage";
                }
            } else {
                $data['is_unsubs_image'] = 0;
                if($unsubsData['screen_image'] != null || !empty($unsubsData['screen_image'])){
                    storage::disk('public')->delete($unsubsData['screen_image']);
                }

                $data['unsubs_image'] = null;
            }

            if (isset($data['is_multiple_buttons']) && $data['is_multiple_buttons'] == 'on') {
                $is_multiple_button = 1;
                $json_array = json_encode($data['multipleButtons'], JSON_PRETTY_PRINT);
            } else {
                $is_multiple_button = 0;
                $json_array = null;
            }
            if (isset($data['show_title']) && $data['show_title'] == 'on') {
                $show_title = 1;
            }else{
                $show_title = 0;
            }
            if (isset($data['show_default_button']) && $data['show_default_button'] == 'on') {
                $show_default_button = 1;
            }else{
                $show_default_button = 0;
            }
            if (isset($data['have_offer']) && !empty($data['have_offer'])) {
                $have_offer = $data['have_offer'];
                $offer_in_per = $data['offer_in_per'];
            }else{
                $have_offer = 0;
                $offer_in_per = null;

            }
            // dd($serialized_array);

            UnsubscriptionFlowQuestion::where(['id' => $id])
                ->update([
                    'screen_title'          => $data['unsubs_title'],
                    'show_title'            => $show_title,
                    'slug'                  => $slug,
                    'screen_description'    => $data['unsubs_description'],
                    'is_screen_image'       => $data['is_unsubs_image'],
                    'button_text'           => $data['button_text'],
                    'show_default_button'   => $show_default_button,
                    'is_multiple_button'    => $is_multiple_button,
                    'multiple_buttons_value'=> $json_array,
                    'have_offer'            => $have_offer,
                    'offer_in_per'          => $offer_in_per,
                    'screen_image'          => $data['unsubs_image'],
                    'screen_type'           => $data['is_transition'],
                    'feedback_type'         => $data['feedback_type']
                ]);

            feedback_question::where(['screen_id' => $id])->delete();

            if (isset($data['feedback_type'])) {
                $feedback_options = [];
                $feedbackType = $data['feedback_type'];

                if ($feedbackType == "single") {
                    foreach ($data['singleAnswers'] as $singleAnswers) {
                        $feedback_options[] = [
                            'screen_id' => $id,
                            'feedback_type' => $data['feedback_type'],
                            'feedback_options' => $singleAnswers['single_Answer'],
                        ];
                    }

                }elseif($feedbackType == "multiple"){
                    foreach ($data['multipleAnswers'] as $multipleAnswers) {
                        $feedback_options[] = [
                            'screen_id' => $id,
                            'feedback_type' => $data['feedback_type'],
                            'feedback_options' => $multipleAnswers['multiple_Answer'],
                        ];
                    }
                }elseif($feedbackType == "userInput"){
                    $feedback_options[] = [
                        'screen_id' => $id,
                        'feedback_type' => $data['feedback_type'],
                        'feedback_options' => $data['input_Answer'],
                    ];
                }

                feedback_question::insert($feedback_options);
            }

            return redirect('admin/unsubscription-index')->with('success', "Cancel Subscription Screen Updated Successfully");
        }

        return view('admin.cancel_subscription.edit', ['unsubsData' => $unsubsData, 'screens' => $screens]);

    }

    public function destroyUnsubsflow($slug, $id)
    {
        Session::put('page', 'unsubscription');
        UnsubscriptionFlowQuestion::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(1);
        return redirect()->back();
    }

    public function reorderUnsubsflow(Request $request){
        $screen = UnsubscriptionFlowQuestion::all();

        foreach ($screen as $page) {
            foreach ($request->order as $order) {
                if ($order['id'] == $page->id) {
                    $page->update(['screen_position' => $order['position']]);
                }
            }
        }

        return response(['status' => 'success',
        'message' => 'Update Successfully',
        'code' => 200]);
    }
}
