<?php

namespace App\Http\Controllers\Admin\Program;

use App\Http\Controllers\Controller;
use App\Models\Cardio;
use App\Models\Category;
use App\Models\MuscleStrength;
use App\Models\Program;
use App\Models\ProgramLevel;
use App\Models\ProgramSession;
use App\Models\ProgramTag;
use App\Models\Session as ModelsSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class ProgramController extends Controller
{
    public function index()
    {
        Session::put('page', 'programs');
        $programData = Program::with('category')->get()->toArray();
        // dd($programData);
        DB::table('user_session_statuses')
            ->join('program_sessions', 'user_session_statuses.program_session_id', '=', 'program_sessions.id')
            ->whereColumn('user_session_statuses.program_id', 'program_sessions.program_id') // Ensure program_id matches
            ->update([
                'user_session_statuses.session_id' => DB::raw('program_sessions.session_id'),
                'user_session_statuses.session_week' => DB::raw('program_sessions.session_week')
            ]);
        return view('admin.programs.program.index', ['programsData' => $programData]);
    }

    public function create(Request $request)
    {
        Session::put('page', 'programs');
        $categories = Category::where(['status' => 1])->get()->toArray();
        $sessionData = ModelsSession::where(['status' => 1])->get()->toArray();
        $programLevels = ProgramLevel::get()->toArray();
        // echo "<pre>"; print_r($sessionData); die;
        $cardio = Cardio::where(['status' => 1])->get()->toArray();
        $tags = ProgramTag::where(['status' => 1])->get()->toArray();
        $muscleData = MuscleStrength::where(['status' => 1])->get()->toArray();
        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;

            $rules = [
                'title' => ['required', 'string', 'max:255'],
                'category_id' => ['required'],
                'program_type' => ['required','in:cardio,muscle'],
                'cardio_id' => ['required'],
                'muscle_strength_id' => ['required'],
                'level' => ['required'],
                'body_area' => ['required'],
                'duration' => ['required'],
                'frequency' => ['required', 'numeric'],
                'description' => ['required'],
                'objective' => ['required'],
                'program_tag' => ['nullable'], // Changed from required to nullable
                'program_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                'videos' => ['required']
            ];

            $message = [
                'title.required' => "Title is required.",
                'category_id.required' => "Please select category.",
                'program_type.required' => "Please select program type.",
                'cardio_id.required' => "Please select cardio type.",
                'muscle_strength_id.required' => "Please select muscle strengthening type.",
                'level.required' => "Please select level",
                'body_area.required' => "Please enter body area.",
                'duration.required' => "Please enter program duration.",
                'frequency.required' => "Please enter program frequency.",
                'frequency.numeric' => "Program frequency should be in numbers.",
                'description.required' => "Please write something about this program.",
                'objective.required' => "Please write purpose of this program.",
                'program_tag.required' => "Please select tags for this program.",
                'program_image.required' => "Please select an image for this program.",
                'program_image.max' => "The program image must not be greater than 2 MB.", // Custom message for image size validation
                'program_image.image' => "The program image must be an image file.",
                'program_image.mimes' => "The program image must be a file of type: jpeg, png, jpg, gif, svg.",
                'videos.required' => "Please enter video link."
            ];

            $validator = Validator::make($data, $rules, $message);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
                // return redirect()->back()->withInput('error',$validator->getMessageBag());
            }
            // Handle optional program_tag
            if (!isset($data['program_tag']) || empty($data['program_tag'])) {
                $data['program_tag'] = [];
            }
            // Convert program_tag to a string for storage
            $data['program_tag'] = implode("|", $data['program_tag']);

            if (!isset($data['videos']) && empty($data['videos'])) {
                return redirect()->back()->withInput()->with('error', "Please select program videos");
            }
            if (!isset($data['selectedOptions']) || is_null($data['selectedOptions'])) {
                // dd("Inside Isset: ",$data['selectedOptions']);
                return redirect()->back()->withInput()->with('error', "Please select session for this program");
            }

            $sessionWeekCount = count($data['selectedOptions']);

            if ($sessionWeekCount < $data['duration']) {
                // dd("Inside Session Week Count : ",$sessionWeekCount);
                return redirect()->back()->withInput()->with('error', "Please select session in every session weeks");
            }

            $sessionDetail = [];
            foreach ($data['selectedOptions'] as $key => $session) {
                $sessionInfo = [];
                $sessionInfo = $session;

                $sessionDetail['Semaine ' . $key + 1] = $sessionInfo;
            }
            $serializedData = json_encode($sessionDetail);
            // dd($serializedData);

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            $slug = Str::slug($data['title']);

            if (!isset($data['description']) || empty($data['description'])) {
                $data['description'] = "";
            }

            if (!isset($data['objective']) || empty($data['objective'])) {
                $data['objective'] = "";
            }

            if (empty($data['free_access'])) {
                $free_access = 0;
            } else {
                $free_access = 1;
            }

            // program image
            if ($request->has('program_image')) {
                $programImage = time() . '.' . $data['program_image']->extension();
                if (!Storage::disk('public')->exists("/program/images")) {
                    Storage::disk('public')->makeDirectory("/program/images"); //creates directory
                }

                $request->program_image->storeAs("program/program_images", $programImage, 'public');
                $data['program_image'] = "program/program_images/$programImage";
            }

            $program = new Program;
            $program->title = $data['title'];
            $program->category_id = $data['category_id'];
            $program->program_tag = $data['program_tag'];
            $program->program_type = $data['program_type'];
            $program->cardio_id = $data['cardio_id'];
            $program->muscle_strength_id = $data['muscle_strength_id'];
            $program->description = $data['description'];
            $program->objective = $data['objective'];
            $program->program_image = $data['program_image'];
            $program->level_id = $data['level'];
            $program->body_area = $data['body_area'];
            $program->duration = $data['duration'];
            $program->frequency = $data['frequency'];
            $program->video = $data['videos'];
            $program->slug = $slug;
            $program->free_access = $free_access;
            $program->status = $status;
            if ($program->save()) {
                foreach ($data['selectedOptions'] as $key => $session) {
                    foreach ($session as $sessionid) {
                        $sessionDetail = ModelsSession::where(['id' => $sessionid])->first();
                        $weekKey = 'Semaine ' . ($key + 1);

                        $programSession = new ProgramSession;
                        $programSession->program_id = $program->id;
                        $programSession->session_id = $sessionid;
                        $programSession->session_week = $weekKey;
                        $programSession->save();
                    }
                }
            }
            return redirect('admin/program-index')->with('success', 'Program Inserted Successfully !!!');
        }

        return view('admin.programs.program.create', ['categories' => $categories, 'cardioData' => $cardio, 'tags' => $tags, 'muscleData' => $muscleData, 'sessionData' => $sessionData, 'programLevels' => $programLevels]);
    }

    public function update(Request $request, $slug, $id)
    {
        Session::put('page', 'programs');
        $program = Program::where(['id' => $id])->first();
        $programLevels = ProgramLevel::get()->toArray();

        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>";print_r($data);die;
            $rules = [
                'title' => ['required', 'string', 'max:255'],
                'category_id' => ['required'],
                'program_type' => ['required','in:cardio,muscle'],
                'cardio_id' => ['required'],
                'muscle_strength_id' => ['required'],
                'level' => ['required'],
                'body_area' => ['required'],
                'duration' => ['required', 'numeric'],
                'frequency' => ['required', 'numeric'],
                'program_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                'videos' => ['required']
            ];

            $message = [
                'title.required' => "Title is required.",
                'category_id.required' => "Please select category.",
                'program_type.required' => "Please select program type.",
                'cardio_id.required' => "Please select cardio type.",
                'muscle_strength_id.required' => "Please select muscle strengthening type.",
                'level.required' => "Please select level",
                'body_area.required' => "Please enter body area.",
                'duration.required' => "Please enter program duration.",
                'duration.numeric' => "Program duration should be a number.",
                'frequency.required' => "Please enter program frequency.",
                'frequency.numeric' => "Program frequency should be a number.",
                'program_image.max' => "The program image field must not be greater than 2048 kilobytes.",
                'videos.required' => "Please enter video link."
            ];

            $validator = Validator::make($data, $rules, $message);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            // Handle program status and free access
            $status = !empty($data['status']) ? 1 : 0;
            $free_access = !empty($data['free_access']) ? 1 : 0;

            $slug = Str::slug($data['title']);

            // Handle program image upload
            if (!empty($data['program_image'])) {
                if (!Storage::disk('public')->exists("/program/images")) {
                    Storage::disk('public')->makeDirectory("/program/images");
                }
                if (!empty($program->program_image)) {
                    Storage::disk('public')->delete($program->program_image);
                }
                $programImage = time() . '.' . $data['program_image']->extension();
                $request->program_image->storeAs("program/images", $programImage, 'public');
                $data['program_image'] = "program/images/$programImage";
            } else {
                $data['program_image'] = $program->program_image;
            }

            // Update program details
            $updateProgram = Program::where(['id' => $id])->update([
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'program_tag' => implode("|", $data['program_tag'] ?? []),
                'muscle_strength_id' => $data['muscle_strength_id'],
                'description' => $data['description'] ?? '',
                'objective' => $data['objective'] ?? '',
                'level_id' => $data['level'],
                'body_area' => $data['body_area'],
                'duration' => $data['duration'],
                'frequency' => $data['frequency'],
                'program_image' => $data['program_image'],
                'video' => $data['videos'],
                'slug' => $slug,
                'cardio_id' => $data['cardio_id'],
                'program_type' => $data['program_type'],
                'free_access' => $free_access,
                'status' => $status
            ]);

           // Delete all existing program sessions for the given program
            ProgramSession::where('program_id', $id)->delete();

            // Insert new sessions
            foreach ($data['selectedOptions'] as $weekIndex => $sessionIds) {
                $weekKey = 'Semaine ' . ($weekIndex + 1);

                foreach ($sessionIds as $sessionId) {
                    ProgramSession::create([
                        'program_id' => $id,
                        'session_id' => $sessionId,
                        'session_week' => $weekKey
                    ]);
                }
            }

            return redirect('/admin/program-index')->with('success', 'Program updated successfully.');
        }

        $categories = Category::get()->toArray();
        $cardioData = Cardio::get()->toArray();
        $tagData = ProgramTag::get()->toArray();
        $muscleData = MuscleStrength::get()->toArray();
        $sessionData = ModelsSession::where(['status' => 1])->get();
        $programSessionData = ProgramSession::where(['program_id' => $id])->get();
        $prevSession = $programSessionData->groupBy('session_week');

        return view('admin.programs.program.edit', compact('program', 'categories', 'cardioData', 'tagData', 'muscleData', 'sessionData', 'programLevels', 'prevSession'));
    }

    public function updateProgramStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            Program::where(['id' => $data['program_id']])->update(['status' => $status]);

            return response()->json(['status' => $status, 'program_id' => $data['program_id']]);
        }
    }

    public function destroy($slug, $id)
    {
        Session::put('page', 'programs');
        Program::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(2);
        return redirect()->back();
    }

    public function cardioIndex()
    {
        Session::put('page', 'cardio');
        $cardioData = Cardio::get()->toArray();
        return view('admin.programs.cardio.index', ['cardioData' => $cardioData]);
    }

    public function createCardio(Request $request)
    {
        Session::put('page', 'cardio');

        if ($request->isMethod('post')) {
            $data = $request->all();

            $validation = [
                'title' => ['required', 'string', 'nullable', 'max:255'],
            ];
            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            $slug = Str::slug($data['title']);

            $cardio = new Cardio;
            $cardio->title = $data['title'];
            $cardio->description = $data['description'] ?? "";
            $cardio->slug = $slug;
            $cardio->status = $status;
            $cardio->save();
            return redirect('/admin/cardio-index')->with('success', 'Cardio Inserted Successfully !!!');
        }

        return view('admin.programs.cardio.create');
    }

    public function updateCardio(Request $request, $slug, $id)
    {
        Session::put('page', 'cardio');
        $cardio = Cardio::where(['slug' => $slug, 'id' => $id])->first();

        if ($request->isMethod('post')) {
            $data = $request->all();
            $validation = [
                'title' => ['required', 'string', 'nullable', 'max:255'],
            ];
            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            $slug = Str::slug($data['title']);

            Cardio::where(['id' => $id])->update(['title' => $data['title'], 'description' => $data['description'] ?? "", 'slug' => $slug, 'status' => $status]);

            return redirect('/admin/cardio-index')->with('success', "Cardio Updated Successfully");
        }
        return view('admin.programs.cardio.edit')->with(compact('cardio'));
    }

    public function updateCardioStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            Cardio::where(['id' => $data['cardio_id']])->update(['status' => $status]);

            return response()->json(['status' => $status, 'cardio_id' => $data['cardio_id']]);
        }
    }

    public function destroyCardio($slug, $id)
    {
        Session::put('page', 'cardio');
        Cardio::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(2);
        return redirect()->back();
    }

    public function muscleStrengthIndex()
    {
        Session::put('page', 'muscleStrengths');
        $muscleData = MuscleStrength::get()->toArray();
        return view('admin.programs.muscles_strength.index', ['muscleData' => $muscleData]);
    }

    public function createMuscleStrength(Request $request)
    {
        Session::put('page', 'muscleStrengths');

        if ($request->isMethod('post')) {
            $data = $request->all();

            $validation = [
                'title' => ['required', 'string', 'nullable', 'max:255'],
            ];
            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            $slug = Str::slug($data['title']);

            $muscleStrength = new MuscleStrength();
            $muscleStrength->title = $data['title'];
            $muscleStrength->description = $data['description'] ?? "";
            $muscleStrength->slug = $slug;
            $muscleStrength->status = $status;
            $muscleStrength->save();
            return redirect('/admin/muscles-strength-index')->with('success', 'Added Muscle Strengthening Type Successfully !!!');
        }

        return view('admin.programs.muscles_strength.create');
    }

    public function updateMuscleStrength(Request $request, $slug, $id)
    {
        Session::put('page', 'muscleStrengths');
        $muscleStrength = MuscleStrength::where(['slug' => $slug, 'id' => $id])->first();

        if ($request->isMethod('post')) {
            $data = $request->all();
            $validation = [
                'title' => ['required', 'string', 'nullable', 'max:255'],
            ];
            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            $slug = Str::slug($data['title']);

            MuscleStrength::where(['id' => $id])->update(['title' => $data['title'], 'description' => $data['description'] ?? "", 'slug' => $slug, 'status' => $status]);

            return redirect('/admin/muscles-strength-index')->with('success', "Updated Muscle Strengthening Type Successfully");
        }
        return view('admin.programs.muscles_strength.edit')->with(compact('muscleStrength'));
    }

    public function updateMuscleStrengthStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            MuscleStrength::where(['id' => $data['muscle_id']])->update(['status' => $status]);

            return response()->json(['status' => $status, 'muscle_id' => $data['muscle_id']]);
        }
    }

    public function destroyMuscleStrength($slug, $id)
    {
        Session::put('page', 'muscleStrengths');
        MuscleStrength::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(2);
        return redirect()->back();
    }

    public function addProgramSession(Request $request)
    {
        $data = $request->all();
        echo "<pre>";
        print_r($data);
    }

    public function tagIndex()
    {
        Session::put('page', 'tag');
        $tagData = ProgramTag::get()->toArray();
        return view('admin.programs.tags.index', ['tagData' => $tagData]);
    }

    public function createTag(Request $request)
    {
        Session::put('page', 'tag');
        if ($request->isMethod('post')) {
            $data = $request->all();

            $validation = [
                'tag_name' => ['required', 'string', 'nullable', 'max:255'],
            ];
            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            $slug = Str::slug($data['tag_name']);

            $tag = new ProgramTag;
            $tag->tag_name = $data['tag_name'];
            $tag->slug = $slug;
            $tag->status = $status;
            $tag->save();
            return redirect('/admin/tag-index')->with('success', 'Tag Inserted Successfully !!!');
        }

        return view('admin.programs.tags.create');
    }

    public function updateTag(Request $request, $slug, $id)
    {
        Session::put('page', 'tag');
        $tag = ProgramTag::where(['slug' => $slug, 'id' => $id])->first();

        if ($request->isMethod('post')) {
            $data = $request->all();
            $validation = [
                'tag_name' => ['required', 'string', 'nullable', 'max:255'],
            ];
            $validator = Validator::make($data, $validation);
            $input = $request->except(['_token']);
            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            if (empty($data['status'])) {
                $status = 0;
            } else {
                $status = 1;
            }

            $slug = Str::slug($data['tag_name']);

            ProgramTag::where(['id' => $id])->update(['tag_name' => $data['tag_name'], 'slug' => $slug, 'status' => $status]);
            return redirect('/admin/tag-index')->with('success', "Tag Updated Successfully");
        }
        return view('admin.programs.tags.edit', ['tag' => $tag]);
    }

    public function updateTagStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            ProgramTag::where(['id' => $data['tag_id']])->update(['status' => $status]);
            return response()->json(['status' => $status, 'tag_id' => $data['tag_id']]);
        }
    }

    public function destroyTag($slug, $id)
    {
        Session::put('page', 'tag');
        ProgramTag::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(2);
        return redirect()->back();
    }

    public function levelIndex()
    {
        Session::put('page', 'levels');
        $levelData = ProgramLevel::get()->toArray();
        return view('admin.programs.level_settings.index', ['levelData' => $levelData]);
    }

    public function createLevel(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'level_title' => 'required',
                'start_range' => 'required|numeric|min:0',
                'end_range' => 'required|numeric|min:0|gte:start_range',
            ]);
            try {
                $data['slug'] = Str::slug($data['level_title']);
                $level = ProgramLevel::create([
                    'level_title' => $data['level_title'],
                    'slug' => $data['slug'],
                    'start_range' => $data['start_range'],
                    'end_range' => $data['end_range'],
                ]);
                return redirect('admin/level-index')->with('success', 'Record created successfully');
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        }
        return view('admin.programs.level_settings.create');
    }

    public function updateLevel(Request $request, $slug, $id, ProgramLevel $levelRecord)
    {
        Session::put('page', 'levels');
        $level = ProgramLevel::where(['slug' => $slug, 'id' => $id])->first();

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'level_title' => 'required',
                'start_range' => 'required|numeric|min:0',
                'end_range' => 'required|numeric|min:0|gte:start_range',
            ]);

            // echo "<pre>"; print_r($data); die;
            try {
                $data['slug'] = Str::slug($data['level_title']);

                $level->update([
                    'level_title' => $data['level_title'],
                    'slug' => $data['slug'],
                    'start_range' => $data['start_range'],
                    'end_range' => $data['end_range'],
                ]);

                return redirect('admin/level-index')->with('success', 'Record updated successfully');
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        }
        return view('admin.programs.level_settings.edit', ['level' => $level]);
    }

    public function updateLevelStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            ProgramLevel::where(['id' => $data['level_id']])->update(['status' => $status]);
            return response()->json(['status' => $status, 'level_id' => $data['level_id']]);
        }
    }

    public function destroyLevel($slug, $id)
    {
        Session::put('page', 'levels');
        ProgramLevel::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(2);
        return redirect()->back();
    }

    // Extra Not in use
    public function uploadLargeFiles(Request $request)
    {
        if ($request->isMethod('post')) {
            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

            if (!$receiver->isUploaded()) {
                // file not uploaded
            }

            $fileReceived = $receiver->receive(); // receive file
            if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
                $file = $fileReceived->getFile(); // get file
                $extension = $file->getClientOriginalExtension();
                $fileName = str_replace('.' . $extension, '', $file->getClientOriginalName()); //file name without extenstion
                $fileName .= '_' . md5(time()) . '.' . $extension; // a unique file name

                $disk = Storage::disk(config('filesystems.default'));
                $path = $disk->putFileAs('videos', $file, $fileName);

                // delete chunked file
                unlink($file->getPathname());
                return [
                    'path' => asset('storage/' . $path),
                    'filename' => $fileName
                ];
            }

            // otherwise return percentage information
            $handler = $fileReceived->handler();
            return [
                'done' => $handler->getPercentageDone(),
                'status' => true
            ];
        }

        return view('admin.programs.program.fileUpload');
    }

    public function updateSession(Request $request)
    {
        $data = $request->all();

        try {
            $session = ProgramSession::where('id', $data['sessionId'])->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found',
                ], 404);
            }

            $duplicateSession = ProgramSession::where('program_id', $session['program_id'])->where('session_week', $session['session_week'])->where('session_id', $data['title'])->where('id', '!=', $session['id'])->exists();

            if ($duplicateSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'This session is already in the list.',
                ], 400);
            }

            $session->session_id = $data['title'];
            $session->update();

            return response()->json([
                'success' => true,
                'message' => 'Session updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
