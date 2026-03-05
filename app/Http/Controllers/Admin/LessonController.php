<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonTip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    public function index()
    {
        Session::put('page', 'lessons');
        $lessonsData = Lesson::with('lessonTips')->get()->toArray();
        // echo "<pre>"; print_r($lessonsData); die;
        return view('admin.lesson.index', ['lessonsData' => $lessonsData]);
    }

    public function create(Request $request)
    {
        Session::put('page', 'lessons');
        if ($request->isMethod('POST')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $validation = [
                'title' => ['required', 'string', 'max:255'],
                'lesson_question' => ['required', 'string'],
                'description' => ['required', 'string'],
                'video_link' => ['nullable', 'string'],
            ];

            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (!isset($data['isFeature'])) {
                $data['isFeature'] = 0;
                $data['feature_name'] = '';
                $data['feature_title'] = '';
                $data['feature_desc'] = '';
                $data['feature_image'] = '';
            } else {
                $rules = [
                    'feature_name' => ['required', 'string'],
                    'feature_title' => ['required', 'string'],
                    'feature_desc' => ['required', 'string'],
                    'feature_image' => ['required', 'image'],
                ];
                $message = [
                    'feature_name.required' => "Please choose Feature Name.",
                    'feature_title.required' => "Please enter Feature Title.",
                    'feature_desc.required' => "Please enter Feature Description.",
                    'feature_image.required' => "Please upload Feature Image.",
                ];

                $validator = Validator::make($request->all(), $rules, $message);
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
                }

                $data['isFeature'] = 1;

                // Handle the feature image upload
                if ($request->hasFile('feature_image')) {
                    $featureImage = time() . '.' . $data['feature_image']->extension();

                    if (!Storage::disk('public')->exists("/lesson/featureImage")) {
                        Storage::disk('public')->makeDirectory("/lesson/featureImage"); // creates directory
                    }

                    $request->feature_image->storeAs("/lesson/featureImage", $featureImage, 'public');
                    $data['feature_image'] = "/lesson/featureImage/$featureImage";
                }
            }

            // Handle other fields
            $status = !empty($data['status']) ? 1 : 0;

            $answers = isset($data['answers']) ? json_encode($data['answers']) : null;

            $slug = Str::slug($data['title']);

            $lesson = new Lesson;
            $lesson->title = $data['title'];
            $lesson->description = $data['description'];
            $lesson->slug = $slug;
            $lesson->lesson_question = $data['lesson_question'];
            $lesson->answers = $answers;
            $lesson->task_title = $data['task_title'] ?? null;
            $lesson->task_content = $data['task_content'] ?? null;
            $lesson->status = $status;
            $lesson->video_title = $data['video_title'] ?? null;
            $lesson->video_description = $data['video_description'] ?? null;
            $lesson->video_link = $data['video_link'] ?? null;
            $lesson->isFeature = $data['isFeature'];
            $lesson->feature_name = $data['feature_name'];
            $lesson->feature_title = $data['feature_title'];
            $lesson->feature_desc = $data['feature_desc'];
            $lesson->feature_image = $data['feature_image'];

            // Save the lesson
            if ($lesson->save()) {
                foreach ($data['tips'] as $lessonTip) {
                    $tips = new LessonTip;
                    $tips->lesson_id = $lesson->id;
                    $tips->tip_title = $lessonTip['tip_title'];
                    $tips->tip_content = $lessonTip['tip_content'];
                    $tips->save();
                }
            }
            return redirect('/admin/lesson-index')->with('success', 'Lesson created successfully!');
        }
        return view('admin.lesson.create');
    }

    public function update(Request $request, $slug, $id)
    {
        Session::put('page', 'lessons');
        $lesson = Lesson::with('lessonTips')->where(['id' => $id])->first();
        if ($request->isMethod('POST')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $validation = [
                'title' => ['required', 'string', 'max:255'],
                'lesson_question' => ['required', 'string'],
                'description' => ['required', 'string'],
                'video_link' => ['nullable', 'string'],
            ];
            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }
            if (!isset($data['isFeature'])) {
                $data['isFeature'] = 0;
                $data['feature_name'] = '';
                $data['feature_title'] = '';
                $data['feature_desc'] = '';
                $data['feature_image'] = '';
            } else {
                $rules = [
                    'feature_name' => ['required', 'string'],
                    'feature_title' => ['required', 'string'],
                    'feature_desc' => ['required', 'string'],
                ];

                if ($request->hasFile('feature_image')) {
                    $rules['feature_image'] = ['required', 'image'];
                }

                $message = [
                    'feature_name.required' => "Please choose Feature Name.",
                    'feature_title.required' => "Please enter Feature Title.",
                    'feature_desc.required' => "Please enter Feature Description.",
                    'feature_image.required' => "Please upload Feature Image.",
                ];

                $validator = Validator::make($request->all(), $rules, $message);
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
                }

                $data['isFeature'] = 1;

                // Handle feature image upload
                if ($request->hasFile('feature_image')) {
                    $featureImage = time() . '.' . $data['feature_image']->extension();

                    if (!Storage::disk('public')->exists("/lesson/featureImage")) {
                        Storage::disk('public')->makeDirectory("/lesson/featureImage"); // creates directory
                    }

                    $request->feature_image->storeAs("/lesson/featureImage", $featureImage, 'public');
                    $data['feature_image'] = "/lesson/featureImage/$featureImage";
                }
            }

            $status = !empty($data['status']) ? 1 : 0;

            $answers = isset($data['answers']) ? json_encode($data['answers']) : null;

            $slug = Str::slug($data['title']);

            // Update the lesson
            $lesson->title = $data['title'];
            $lesson->description = $data['description'];
            $lesson->slug = $slug;
            $lesson->lesson_question = $data['lesson_question'];
            $lesson->answers = $answers;
            $lesson->task_title = $data['task_title'] ?? null;
            $lesson->task_content = $data['task_content'] ?? null;
            $lesson->status = $status;
            $lesson->video_title = $data['video_title'] ?? null;
            $lesson->video_description = $data['video_description'] ?? null;
            $lesson->video_link = $data['video_link'] ?? null;
            $lesson->isFeature = $data['isFeature'];
            $lesson->feature_name = $data['feature_name'];
            $lesson->feature_title = $data['feature_title'];
            $lesson->feature_desc = $data['feature_desc'];
            $lesson->feature_image = $data['feature_image'] ?? $lesson->feature_image; // Keep old image if not updated

            if ($lesson->save()) {
                // Update or add tips
                $tipId = [];
                foreach ($data['tips'] as $key => $tip) {
                    $tipId[] = $tip['tipId'];

                    if (!empty($tip['tipId'])) {
                        LessonTip::where(['id' => $tip['tipId'], 'lesson_id' => $id])->update(['tip_title' => $tip['tip_title'], 'tip_content' => $tip['tip_content']]);
                    } else {
                        $tipLesson = new LessonTip;
                        $tipLesson->lesson_id = $id;
                        $tipLesson->tip_title = $tip['tip_title'];
                        $tipLesson->tip_content = $tip['tip_content'];
                        $tipLesson->save();
                    }
                }

                // Delete any tips that are no longer in the updated list
                LessonTip::whereNotIn('id', $tipId)->where(['lesson_id' => $id])->delete();
            }

            return redirect('/admin/lesson-index')->with('success', 'Lesson updated successfully!');
        }

        return view('admin.lesson.edit', ['lesson' => $lesson]);
    }

    private function handleTipImage($tip, $lesson)
    {
        if (empty($tip['tip_image']) || !isset($tip['tip_image'])) {
            foreach ($lesson->lessonTips as $lessonTip) {
                if($tip['tipId'] == $lessonTip['id']){
                    $tip['tip_image'] = $lessonTip->tip_image;
                }
            }
        } else {
            if (!Storage::disk('public')->exists("/lesson/tip")) {
                Storage::disk('public')->makeDirectory("/lesson/tip");
            }
            if (!empty($tip['tip_image'])) {
                Storage::disk('public')->delete($tip['tip_image']);
            }
            $tipImage = time() . md5(rand(1000, 10000)) . '.' . $tip['tip_image']->extension();
            $tip['tip_image']->storeAs("lesson/tip", $tipImage, 'public');
            $tip['tip_image'] = "lesson/tip/$tipImage";
        }
        return $tip['tip_image'];
    }

    public function updateLessonStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            Lesson::where(['id' => $data['lesson_id']])->update(['status' => $status]);

            return response()->json(['status' => $status, 'lesson_id' => $data['lesson_id']]);
        }
    }

    public function destroy($slug, $id)
    {
        Session::put('page', 'lessons');
        Lesson::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(1);
        return redirect()->back();
    }
}
