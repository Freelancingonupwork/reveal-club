<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Models\Answer;
use App\Models\Cardio;
use App\Models\MuscleStrength;
use App\Models\Quiz;
use App\Models\QuizGroup;
use App\Models\Transition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index()
    {
        Session::put('page', "quiz");
        $quizzes = Quiz::orderBy('quiz_position')->with('quiz_group')->get()->toArray();
        $quizGroupData = QuizGroup::get()->toArray();
        $transitions = Transition::where('status', 1)->get();
        $quizGroups = QuizGroup::all();
        $quizes = Quiz::all();
        return view('admin.quiz.questions.index', ['quizzes' => $quizzes, 'quizGroupData' => $quizGroupData, 'transitions' => $transitions, 'quizGroups' => $quizGroups, 'quizes' => $quizes]);
    }

    public function setQuizPosition(Request $request)
    {
        $positions = Quiz::all();
        foreach ($positions as $position) {
            foreach ($request->order as $order) {
                if ($order['id'] == $position->id) {
                    $position->update(['quiz_position' => $order['position']]);
                }
            }
        }

        return response('Update Successfully.', 200);
    }

    public function addQuiz(Request $request)
    {
        Session::put('page', "quiz");
        $transitions = Transition::where('status', 1)->get();
        $quizGroups = QuizGroup::all();
        $quizes = Quiz::all();

        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $validator = $this->validateQuizData($data);
            if ($validator->fails()) {
                return $this->handleValidationFailure($validator);
            }

            // Additional validations
            $this->additionalValidations($data);

            // Prepare data
            $this->prepareQuizData($data);
            $data['ques_image'] = $this->handleQuizImage($request, $data);

            DB::transaction(function () use ($data) {
                $quiz = $this->createQuiz($data);
                $answers = $this->prepareAnswers($data, $quiz->id);
                Answer::insert($answers);
            });

            return redirect('/admin/quiz-index')->with('success', 'Question Inserted Successfully !!!');
        }

        return view('admin.quiz.questions.create', compact('transitions', 'quizGroups', 'quizes'));
    }

    private function validateQuizData(array $data)
    {
        $rules = [
            'title' => ['required', 'string'],
            'ques_type' => ['required', 'integer'],
            'quiz_group_id' => ['required'],
            'answer_type' => ['required'],
            'ques_image' => ['sometimes', 'required', 'mimes:jpeg,jpg,png,gif'],
            'ques_for_gender' => ['required', 'string'],
        ];

        $messages = [
            'title.required' => "Please enter Question Title.",
            'ques_type.required' => "Please select type of question.",
            'quiz_group_id.required' => "Please Select Quiz Group.",
            'answer_type.required' => "Please select answer type.",
            'ques_image.required' => "Please select an image for this question.",
            'ques_image.mimes' => "Please select an image with jpeg, jpg, png, or gif extensions.",
            'ques_for_gender.required' => "Please select one gender from the 'This Question is for' section."
        ];

        return Validator::make($data, $rules, $messages);
    }

    private function handleValidationFailure($validator)
    {
        return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
    }

    private function additionalValidations(array $data)
    {
        if ($data['ques_type'] == 0 && empty($data['ques_for'])) {
            throw new \Exception("Please select an option in 'Use in Profile Section'.");
        }

        if ($data['ques_type'] == 1 && !isset($data['answer_type'])) {
            throw new \Exception("Please select an answer type.");
        }

        if (isset($data['have_transition']) && !isset($data['trans_logic'])) {
            throw new \Exception("Please select transition logic.");
        }

        if (isset($data['have_transition']) && $data['trans_logic'] === "No" && empty($data['common_transition_id'])) {
            throw new \Exception("Please select a transition.");
        }
    }

    private function prepareQuizData(array &$data)
    {
        $data['ques_for'] = $data['ques_for'] ?? ($data['ques_type'] == 1 ? "cardio" : "none");
        $data['sales_page'] = isset($data['sales_page']) ? 1 : 0;
        $data['is_turnstile_enabled'] = isset($data['is_turnstile_enabled']) ? 1 : 0;
        $data['calory_calc'] = isset($data['calory_calc']) ? 1 : 0;
        $data['another_ques'] = isset($data['another_ques']) ? 1 : 0;
        $data['have_transition'] = isset($data['have_transition']) ? 1 : 0;
        $data['trans_logic'] = $data['trans_logic'] ?? null;

        // Set instruction details
        $data['haveInstruction'] = isset($data['haveInstruction']) ? 1 : 0;
        $data['instructionMessage'] = $data['haveInstruction'] ? $data['instructionMessage'] : '';
        $data['answer_format'] = $data['answer_format'] ?? null;

        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
    }

    private function handleQuizImage(Request $request, array &$data)
    {
        if (isset($data['isQuesImage']) && $request->has('ques_image')) {
            $quizImage = time() . '.' . $data['ques_image']->extension();
            $data['ques_image']->storeAs("quiz/image", $quizImage, 'public');
            return "quiz/image/$quizImage";
        }
        return null;
    }

    private function createQuiz(array $data)
    {
        $slug = Str::slug($data['title']);
        return Quiz::create([
            'ques_title' => $data['title'],
            'quiz_group_id' => $data['quiz_group_id'],
            'slug' => $slug,
            'ques_description' => $data['quesDescription'] ?? '',
            'is_ques_image' => isset($data['isQuesImage']) ? 1 : 0,
            'ques_image' => $data['ques_image'],
            'ques_type' => $data['ques_type'],
            'ques_for' => $data['ques_for'],
            'is_sales_page' => $data['sales_page'],
            'is_calory_calc' => $data['calory_calc'],
            'is_another_ques' => $data['another_ques'],
            'ques_id' => $data['ques_id'] ?? null,
            'is_have_transition' => $data['have_transition'],
            'transition_logic' => $data['trans_logic'],
            'answer_type' => $data['answer_type'],
            'answer_format' => $data['answer_format'] ?? null,
            'have_instruction' => $data['haveInstruction'],
            'instruction_message' => $data['instructionMessage'],
            'ques_for_gender' => $data['ques_for_gender'] ?? "all",
            'is_turnstile_enabled' => $data['is_turnstile_enabled'],
            'is_google_analytics' => isset($data['is_google_analytics']) ? 1 : 0,
            'google_analytic_script' => isset($data['google_analytic_script']) ? $data['google_analytic_script'] : null,
            'is_active' => $data['is_active'],
        ]);
    }

    private function prepareAnswers(array $data, $quizId)
    {
        $answers = [];
        $transitionLogicNo = $data['trans_logic'] === "No";

        $answerFormat = $data['answer_format'] ?? null;

        // Handling single answers
        if ($data['answer_type'] == "single") {
            $this->handleSingleAnswers($data, $quizId, $answers, $transitionLogicNo);
        }

        // Handling multiple answers
        if ($data['answer_type'] == "multiple") {
            $this->handleMultipleAnswers($data, $quizId, $answers);
        }

        // Handling user input answers
        if (($data['ques_type'] == 0) && ($data['answer_type'] == "userInput")) {
            $this->handleUserInputAnswers($data, $answers, $quizId, $transitionLogicNo);
        }

        return $answers;
    }

    private function handleSingleAnswers(array $data, $quizId, array &$answers, $transitionLogicNo)
    {
        if (!$data['have_transition'] && $data['ques_type'] != 1) {
            foreach ($data['singleAnswers'] as $singleAnswer) {
                $answers[] = $this->buildAnswerArray($singleAnswer, $quizId, $data);
            }
        } else {
            if ($transitionLogicNo) {
                if (isset($data['singleAnswers']) && is_array($data['singleAnswers'])) {
                    foreach ($data['singleAnswers'] as $answerWithCommonTransition) {
                        $answers[] = $this->buildAnswerArray($answerWithCommonTransition, $quizId, $data, true);
                    }
                }
            } else {
                if (isset($data['singleAnswersWithTransition']) && is_array($data['singleAnswersWithTransition'])) {
                    foreach ($data['singleAnswersWithTransition'] as $singleAnswersWithTransition) {
                        $answers[] = $this->buildAnswerArray($singleAnswersWithTransition, $quizId, $data, true);
                    }
                }
            }
        }

        if ($data['ques_type'] == 1) {
            $this->handleCardioAndMuscleAnswers($data, $answers, $quizId);
        }
    }

    private function handleMultipleAnswers(array $data, $quizId, array &$answers)
    {
        if ($data['ques_type'] == 1) {
            $this->handleCardioAndMuscleAnswers($data, $answers, $quizId, true);
        } else {
            foreach ($data['multipleAnswers'] as $multipleAnswer) {
                $answers[] = $this->buildAnswerArray($multipleAnswer, $quizId, $data);
            }
        }
    }

    private function buildAnswerArray($answer, $quizId, array $data, $hasTransition = false)
    {
        if ($hasTransition && $data['trans_logic'] == 'Yes') {
            $transition_id = isset($answer['transition_id']) ? implode("|", $answer['transition_id']) : null;
        } elseif (!$hasTransition && $data['trans_logic'] == 'No') {
            $transition_id = implode("|", $data['common_transition_id']) ?? [];
        } else {
            if (isset($data['common_transition_id']) && !empty($data['common_transition_id'])) {
                $transition_id = implode("|", $data['common_transition_id']);
            } else {
                $transition_id = null;
            }
        }

        $answerFormat = $data['answer_format'] ?? null;
        $answerImage = "";

        if (isset($answer['answer_img'])) {
            $answerImage = time() . '.' . md5(rand(1000, 10000)) . $answer['answer_img']->extension();
            $answer['answer_img']->storeAs("quiz/answer/image", $answerImage, 'public');
            $answerImage = "quiz/answer/image/$answerImage";
        }

        return [
            'question_id' => $quizId,
            'answer_type' => $data['answer_type'],
            'answer_format' => $answerFormat,
            'ques_answers' => $answer['answer'] ?? '',
            'answer_img' => $answerImage,
            'cardio_and_muscle_id' => 0,
            'have_transition' => $hasTransition ? 1 : 0,
            'transition_id' => $transition_id,
            'transition_logic' => $data['trans_logic'] ?? null,
            'ques_type' => $data['ques_type'] ?? null,
            'ans_points' => $answer['points'] ?? null,
        ];
    }

    // The handleCardioAndMuscleAnswers and handleUserInputAnswers methods remain unchanged.

    private function handleCardioAndMuscleAnswers(array $data, array &$answers, $quizId, $isMultiple = false)
    {
        if ($data['have_transition'] && $data['trans_logic'] == 'Yes') {
            $transition_id = implode("|", $data['transition_id']);
        } elseif ($data['have_transition'] && $data['trans_logic'] == 'No') {
            $transition_id = implode("|", $data['common_transition_id']) ?? [];
        } else {
            if (isset($data['common_transition_id']) && !empty($data['common_transition_id'])) {
                $transition_id = implode("|", $data['common_transition_id']);
            } else {
                $transition_id = null;
            }
        }
        $answerFormat = $data['answer_format'] ?? null;
        if ($data['ques_for'] === 'cardio') {
            foreach ($isMultiple ? $data['multipleAnsForCardio'] : $data['singleAnsForCardio'] as $answer) {
                $cardio = Cardio::find($answer['cardio_id']);
                if ($cardio) {
                    $answers[] = [
                        'question_id' => $quizId,
                        'answer_type' => $data['answer_type'],
                        'answer_format' => $answerFormat,
                        'ques_answers' => $cardio->title,
                        'cardio_and_muscle_id' => $answer['cardio_id'],
                        'have_transition' => $data['have_transition'] ? 1 : 0,
                        'transition_id' => $transition_id,
                        'transition_logic' => $data['trans_logic'],
                        'ques_type' => $data['ques_type'] ?? null,
                        'ans_points' => 0,
                    ];
                }
            }
        }

        if ($data['ques_for'] === 'musclestrengthening') {
            foreach ($isMultiple ? $data['multipleAnsForMuscle'] : $data['singleAnsForMuscle'] as $answer) {
                $muscleStrength = MuscleStrength::find($answer['muscle_id']);
                if ($muscleStrength) {
                    $answers[] = [
                        'question_id' => $quizId,
                        'answer_type' => $data['answer_type'],
                        'answer_format' => $answerFormat,
                        'ques_answers' => $muscleStrength->title,
                        'cardio_and_muscle_id' => $answer['muscle_id'],
                        'have_transition' => $data['have_transition'] ? 1 : 0,
                        'transition_id' => $transition_id,
                        'transition_logic' => $data['trans_logic'],
                        'ques_type' => $data['ques_type'] ?? null,
                        'ans_points' => 0,
                    ];
                }
            }
        }

        if ($data['ques_for'] === 'level') {
            foreach ($isMultiple ? $data['multipleAnsForLevel'] : $data['singleAnsForLevel'] as $answer) {
                $answers[] = [
                    'question_id' => $quizId,
                    'answer_type' => $data['answer_type'],
                    'answer_format' => $answerFormat,
                    'ques_answers' => $answer['level'] ?? '',
                    'cardio_and_muscle_id' => 0,
                    'have_transition' => $data['have_transition'] ? 1 : 0,
                    'transition_id' => $transition_id,
                    'transition_logic' => $data['trans_logic'],
                    'ques_type' => $data['ques_type'] ?? null,
                    'ans_points' => $answer['points'] ?? null,
                ];
            }
        }
    }

    private function handleUserInputAnswers(array $data, array &$answers, $quizId, $transitionLogicNo)
    {
        // Ensure you access the answer correctly
        $inputAnswer = $data['userQues'] ?? ''; // Get the user input answer or default to an empty string

        if (!empty($inputAnswer)) {
            $answers[] = [
                'question_id' => $quizId,
                'answer_type' => $data['answer_type'], // This will be 'userInput'
                'answer_format' => $data['answer_format'] ?? null, // Default to null if not set
                'ques_answers' => $inputAnswer, // The actual user input answer
                'label' => $data['answer'], // The actual user input answer
                'have_transition' => $transitionLogicNo ? 1 : 0,
                'is_numeric' => $data['isNumericAnswer'] ?? 0,
                'transition_id' => $transitionLogicNo ? implode("|", $data['common_transition_id'] ?? []) : null,
                'transition_logic' => $data['trans_logic'] ?? null,
            ];
        }
    }

    // Update Start

    public function updateQuiz(Request $request, $slug, $id)
    {
        Session::put('page', "quiz");
        $transitions = Transition::where('status', 1)->get();
        $quizGroups = QuizGroup::all();
        $quizes = Quiz::all();
        $quiz = Quiz::findOrFail($id); // Find the existing quiz by ID
        $quizData = Quiz::with('answers')->where(['id' => $id, 'slug' => $slug])->firstOrFail()->toArray();

        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $validator = $this->validateQuizDataUpdate($data, $id); // Pass ID for any specific validation
            if ($validator->fails()) {
                return $this->handleValidationFailureUpdate($validator);
            }

            // Additional validations
            $this->additionalValidationsUpdate($data);

            // Prepare data
            $this->prepareQuizDataUpdate($data);
            // Handle image update or removal
            // dd($data['isQuesImage']);
            if (!isset($data['isQuesImage']) || ($data['isQuesImage'] != 1 && $data['isQuesImage'] != 'on')) {
                // Delete the existing image from storage if it exists
                if (!empty($quiz->ques_image) && Storage::disk('public')->exists($quiz->ques_image)) {
                    Storage::disk('public')->delete($quiz->ques_image);
                }
                $data['ques_image'] = ''; // Set image field to blank
            } else {
                $data['ques_image'] = $this->handleQuizImageUpdate($request, $data, $quiz); // Handle existing image if no new one is uploaded
            }

            DB::transaction(function () use ($data, $quiz) {
                $quiz->update($this->prepareQuizUpdateDataUpdate($data)); // Update existing quiz
                $this->updateAnswers($data, $quiz->id); // Update answers
            });

            return redirect('/admin/quiz-index')->with('success', 'Question Updated Successfully !!!');
        }

        return view('admin.quiz.questions.edit', compact('transitions', 'quizGroups', 'quizes', 'quiz', 'quizData'));
    }

    private function validateQuizDataUpdate(array $data, $id = null)
    {
        $rules = [
            'title' => ['required', 'string'],
            'ques_type' => ['required', 'integer'],
            'quiz_group_id' => ['required'],
            'answer_type' => ['required'],
            'ques_image' => ['sometimes', 'required', 'mimes:jpeg,jpg,png,gif'],
            'ques_for_gender' => ['required', 'string'],
        ];

        $messages = [
            'title.required' => "Please enter Question Title.",
            'ques_type.required' => "Please select type of question.",
            'quiz_group_id.required' => "Please Select Quiz Group.",
            'answer_type.required' => "Please select answer type.",
            'ques_image.required' => "Please select an image for this question.",
            'ques_image.mimes' => "Please select an image with jpeg, jpg, png, or gif extensions.",
            'ques_for_gender.required' => "Please select one gender from the 'This Question is for' section."
        ];

        return Validator::make($data, $rules, $messages);
    }

    private function handleValidationFailureUpdate($validator)
    {
        return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
    }

    private function additionalValidationsUpdate(array $data)
    {
        if ($data['ques_type'] == 0 && empty($data['ques_for'])) {
            throw new \Exception("Please select an option in 'Use in Profile Section'.");
        }

        if ($data['ques_type'] == 1 && !isset($data['answer_type'])) {
            throw new \Exception("Please select an answer type.");
        }

        if (isset($data['have_transition']) && $data['have_transition'] === 1 && !isset($data['trans_logic'])) {
            throw new \Exception("Please select transition logic.");
        }

        if (isset($data['have_transition']) && $data['have_transition'] == 1 && isset($data['trans_logic']) && $data['trans_logic'] === "No" && empty($data['common_transition_id']) && empty($data['selected_transition_ids'])) {
            throw new \Exception("Please select a transition.");
        }

        if (isset($data['is_google_analytics']) && empty($data['google_analytic_script'])) {
            throw new \Exception("Please enter google analytic script");
        }
    }

    private function prepareQuizDataUpdate(array &$data)
    {
        $data['ques_for'] = $data['ques_for'] ?? ($data['ques_type'] == 1 ? "cardio" : "none");
        $data['sales_page'] = isset($data['sales_page']) ? 1 : 0;
        $data['is_turnstile_enabled'] = isset($data['is_turnstile_enabled']) ? 1 : 0;
        $data['calory_calc'] = isset($data['calory_calc']) ? 1 : 0;
        $data['another_ques'] = isset($data['another_ques']) ? 1 : 0;
        $data['have_transition'] = (isset($data['have_transition']) && ($data['have_transition'] == 1 || $data['have_transition'] == 'on')) ? 1 : 0;
        $data['trans_logic'] = $data['trans_logic'] ?? "No";

        // Set instruction details
        $data['haveInstruction'] = isset($data['haveInstruction']) ? 1 : 0;
        $data['instructionMessage'] = $data['haveInstruction'] ? $data['instructionMessage'] : '';
        $data['answer_format'] = $data['answer_format'] ?? null;

        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
    }

    private function handleQuizImageUpdate(Request $request, array &$data, Quiz $quiz)
    {
        if (isset($data['isQuesImage']) && $request->has('ques_image')) {
            $quizImage = time() . '.' . $data['ques_image']->extension();
            $data['ques_image']->storeAs("quiz/image", $quizImage, 'public');
            return "quiz/image/$quizImage";
        }

        // If no new image is uploaded, keep the existing one
        return $quiz->ques_image; // Return existing image path
    }

    private function prepareQuizUpdateDataUpdate(array &$data)
    {
        return [
            'ques_title' => $data['title'],
            'quiz_group_id' => $data['quiz_group_id'],
            'ques_description' => $data['quesDescription'] ?? '',
            'is_ques_image' => isset($data['isQuesImage']) ? 1 : 0,
            'ques_image' => $data['ques_image'],
            'ques_type' => $data['ques_type'],
            'ques_for' => $data['ques_for'],
            'is_sales_page' => $data['sales_page'],
            'is_calory_calc' => $data['calory_calc'],
            'is_another_ques' => $data['another_ques'],
            'ques_id' => $data['ques_id'] ?? null,
            'is_have_transition' => $data['have_transition'],
            'transition_logic' => $data['trans_logic'],
            'answer_type' => $data['answer_type'],
            'answer_format' => $data['answer_format'] ?? null,
            'have_instruction' => $data['haveInstruction'],
            'instruction_message' => $data['instructionMessage'],
            'ques_for_gender' => $data['ques_for_gender'] ?? "all",
            'is_turnstile_enabled' => $data['is_turnstile_enabled'],
            'is_google_analytics' => isset($data['is_google_analytics']) ? 1 : 0,
            'google_analytic_script' => isset($data['google_analytic_script']) ? $data['google_analytic_script'] : null,
            'is_active' => $data['is_active'],
        ];
    }

    private function updateAnswers(array $data, $quizId)
    {
        // Prepare new answers and insert them
        $answers = $this->prepareAnswersUpdate($data, $quizId);
        if (!empty($answers)) {
            Answer::insert($answers);
        }
    }

    private function prepareAnswersUpdate(array $data, $quizId)
    {
        $answers = [];
        $transitionLogicNo = $data['trans_logic'] === "No";

        // Handling single answers
        if ($data['answer_type'] == "single") {
            $this->handleSingleAnswersUpdate($data, $quizId, $answers, $transitionLogicNo);
        }

        // Handling multiple answers
        if ($data['answer_type'] == "multiple") {
            $this->handleMultipleAnswersUpdate($data, $quizId, $answers);
        }

        // Handling user input answers
        if (($data['ques_type'] == 0) && ($data['answer_type'] == "userInput")) {
            $this->handleUserInputAnswersUpdate($data, $answers, $quizId, $transitionLogicNo);
        }

        return $answers;
    }

    private function handleSingleAnswersUpdate(array $data, $quizId, array &$answers, $transitionLogicNo)
    {
        if (!$data['have_transition'] && $data['ques_type'] != 1) {
            if(isset($data['answer_format']) && $data['answer_format'] == 'image') {
                if (isset($data['singleAnswersImg']) && is_array($data['singleAnswersImg'])) {
                    $this->deleteQuestionAnswers($quizId, $data['singleAnswersImg']);
                    foreach ($data['singleAnswersImg'] as $singleAnswer) {
                        if (isset($singleAnswer['answer']) && $singleAnswer['answer'] != NULL) {
                            if (isset($singleAnswer['id']) && $singleAnswer['id'] != NULL) {
                                $this->updateQuestionAnswers($singleAnswer, $singleAnswer['answer'], $data);
                            } else {
                                $answers[] = $this->createQuestionAnswers($singleAnswer, $singleAnswer['answer'], $quizId, $data);
                            }
                        }
                    }
                } else {
                    $this->deleteQuestionAnswers($quizId);
                }
            } else {
                if (isset($data['singleAnswers']) && is_array($data['singleAnswers'])) {
                    $this->deleteQuestionAnswers($quizId, $data['singleAnswers']);
                    foreach ($data['singleAnswers'] as $singleAnswer) {
                        if (isset($singleAnswer['answer']) && $singleAnswer['answer'] != NULL) {
                            if (isset($singleAnswer['id']) && $singleAnswer['id'] != NULL) {
                                $this->updateQuestionAnswers($singleAnswer, $singleAnswer['answer'], $data);
                            } else {
                                $answers[] = $this->createQuestionAnswers($singleAnswer, $singleAnswer['answer'], $quizId, $data);
                            }
                        }
                    }
                } else {
                    $this->deleteQuestionAnswers($quizId);
                }
            }
        } else if ($data['ques_type'] == 1) {
            $this->handleCardioAndMuscleAnswersUpdate($data, $answers, $quizId);
        } else {
            if ($transitionLogicNo) {
                if(isset($data['answer_format']) && $data['answer_format'] == 'image') {
                    if (isset($data['singleAnswersImg']) && is_array($data['singleAnswersImg'])) {
                        $this->deleteQuestionAnswers($quizId, $data['singleAnswersImg']);
                        foreach ($data['singleAnswersImg'] as $answerWithCommonTransition) {
                            if (isset($answerWithCommonTransition['answer']) && $answerWithCommonTransition['answer'] != NULL) {
                                if (isset($answerWithCommonTransition['id']) && $answerWithCommonTransition['id'] != NULL) {
                                    $this->updateQuestionAnswers($answerWithCommonTransition, $answerWithCommonTransition['answer'], $data);
                                } else {
                                    $answers[] = $this->createQuestionAnswers($answerWithCommonTransition, $answerWithCommonTransition['answer'], $quizId, $data, 0, true);
                                }
                            }
                        }
                    } else {
                        $this->deleteQuestionAnswers($quizId);
                    }
                } else{
                    if (isset($data['singleAnswers']) && is_array($data['singleAnswers'])) {
                        $this->deleteQuestionAnswers($quizId, $data['singleAnswers']);
                        foreach ($data['singleAnswers'] as $answerWithCommonTransition) {
                            if (isset($answerWithCommonTransition['answer']) && $answerWithCommonTransition['answer'] != NULL) {
                                if (isset($answerWithCommonTransition['id']) && $answerWithCommonTransition['id'] != NULL) {
                                    $this->updateQuestionAnswers($answerWithCommonTransition, $answerWithCommonTransition['answer'], $data);
                                } else {
                                    $answers[] = $this->createQuestionAnswers($answerWithCommonTransition, $answerWithCommonTransition['answer'], $quizId, $data, 0, true);
                                }
                            }
                        }
                    } else {
                        $this->deleteQuestionAnswers($quizId);
                    }
                }
            } else {
                if(isset($data['answer_format']) && $data['answer_format'] == 'image') {
                    if (isset($data['singleAnswersWithTransitionImg']) && is_array($data['singleAnswersWithTransitionImg'])) {
                        $this->deleteQuestionAnswers($quizId, $data['singleAnswersWithTransitionImg']);
                        foreach ($data['singleAnswersWithTransitionImg'] as $singleAnswersWithTransition) {
                            if (isset($singleAnswersWithTransition['answer']) && $singleAnswersWithTransition['answer'] != NULL) {
                                if (isset($singleAnswersWithTransition['id']) && $singleAnswersWithTransition['id'] != NULL) {
                                    $this->updateQuestionAnswers($singleAnswersWithTransition, $singleAnswersWithTransition['answer'], $data, 0, true);
                                } else {
                                    $answers[] = $this->createQuestionAnswers($singleAnswersWithTransition, $singleAnswersWithTransition['answer'], $quizId, $data, 0, true);
                                }
                            }
                        }
                    } else {
                        $this->deleteQuestionAnswers($quizId);
                    }
                } else {
                    if (isset($data['singleAnswersWithTransition']) && is_array($data['singleAnswersWithTransition'])) {
                        $this->deleteQuestionAnswers($quizId, $data['singleAnswersWithTransition']);
                        foreach ($data['singleAnswersWithTransition'] as $singleAnswersWithTransition) {
                            if (isset($singleAnswersWithTransition['answer']) && $singleAnswersWithTransition['answer'] != NULL) {
                                if (isset($singleAnswersWithTransition['id']) && $singleAnswersWithTransition['id'] != NULL) {
                                    $this->updateQuestionAnswers($singleAnswersWithTransition, $singleAnswersWithTransition['answer'], $data, 0, true);
                                } else {
                                    $answers[] = $this->createQuestionAnswers($singleAnswersWithTransition, $singleAnswersWithTransition['answer'], $quizId, $data, 0, true);
                                }
                            }
                        }
                    } else {
                        $this->deleteQuestionAnswers($quizId);
                    }
                }
            }
        }
    }

    private function handleMultipleAnswersUpdate(array $data, $quizId, array &$answers)
    {
        if ($data['ques_type'] == 1) {
            $this->handleCardioAndMuscleAnswersUpdate($data, $answers, $quizId, true);
        } else {
            if (isset($data['answer_format']) && $data['answer_format'] == 'image') {
                if (isset($data['multipleAnswersImg']) && is_array($data['multipleAnswersImg'])) {
                    $this->deleteQuestionAnswers($quizId, $data['multipleAnswersImg']);
                    foreach ($data['multipleAnswersImg'] as $multipleAnswer) {
                        if (isset($multipleAnswer['answer']) && $multipleAnswer['answer'] != NULL) {
                            if (isset($multipleAnswer['id']) && $multipleAnswer['id'] != NULL) {
                                $this->updateQuestionAnswers($multipleAnswer, $multipleAnswer['answer'], $data);
                            } else {
                                $answers[] = $this->createQuestionAnswers($multipleAnswer, $multipleAnswer['answer'], $quizId, $data);
                            }
                        }
                    }
                } else {
                    $this->deleteQuestionAnswers($quizId);
                }
            } else {
                if (isset($data['multipleAnswers']) && is_array($data['multipleAnswers'])) {
                    $this->deleteQuestionAnswers($quizId, $data['multipleAnswers']);
                    foreach ($data['multipleAnswers'] as $multipleAnswer) {
                        if (isset($multipleAnswer['answer']) && $multipleAnswer['answer'] != NULL) {
                            if (isset($multipleAnswer['id']) && $multipleAnswer['id'] != NULL) {
                                $this->updateQuestionAnswers($multipleAnswer, $multipleAnswer['answer'], $data);
                            } else {
                                $answers[] = $this->createQuestionAnswers($multipleAnswer, $multipleAnswer['answer'], $quizId, $data);
                            }
                        }
                    }
                } else {
                    $this->deleteQuestionAnswers($quizId);
                }
            }
        }
    }

    private function buildAnswerArrayUpdate($answer, $quizId, array $data, $hasTransition = false)
    {
        $transition_id = null;
        if ($hasTransition && $data['trans_logic'] == 'Yes') {
            $transition_id = implode("|", $answer['transition_id']);
        } elseif (!$hasTransition && $data['trans_logic'] == 'No') {
            // $transition_id = implode("|", $data['common_transition_id']) ?? [];
            $transition_id = $data['selected_transition_ids'] ?? "";
        } else {
            if (isset($data['selected_transition_ids']) && !empty($data['selected_transition_ids'])) {
                // $transition_id = implode("|", $data['common_transition_id']);
                $transition_id =  $data['selected_transition_ids'];
            }
        }

        $answerImage = "";
        if (isset($answer['answer_img'])) {
            $answerImage = time() . '.' . md5(rand(1000, 10000)) . $answer['answer_img']->extension();
            $answer['answer_img']->storeAs("quiz/answer/image", $answerImage, 'public');
            $answerImage = "quiz/answer/image/$answerImage";
        }

        return [
            'question_id' => $quizId,
            'answer_type' => $data['answer_type'],
            'answer_format' => $data['answer_format'] ?? null,
            'ques_answers' => $answer['answer'] ?? '',
            'answer_img' => $answerImage,
            'cardio_and_muscle_id' => 0,
            'have_transition' => $hasTransition ? 1 : 0,
            'transition_id' => $transition_id,
            'transition_logic' => $data['trans_logic'] ?? null,
            'ques_type' => $data['ques_type'] ?? null,
            'ans_points' => $answer['points'] ?? null,
        ];
    }

    private function handleCardioAndMuscleAnswersUpdate(array $data, array &$answers, $quizId, $isMultiple = false)
    {
        // Logic remains unchanged
        $answerFormat = $data['answer_format'] ?? null;

        if ($data['ques_for'] === 'cardio') {
            $cardioAnswers = $isMultiple ? (isset($data['multipleAnsForCardio']) ? $data['multipleAnsForCardio'] : []) : (isset($data['singleAnsForCardio']) ? $data['singleAnsForCardio'] : []);
            if (!empty($cardioAnswers)) {
                $this->deleteQuestionAnswers($quizId, $cardioAnswers);
                foreach ($cardioAnswers as $answerData) {
                    $cardio = Cardio::find($answerData['cardio_id']);
                    if ($cardio) {
                        if (isset($answerData['id']) && $answerData['id'] != NULL) {
                            $this->updateQuestionAnswers($answerData, $cardio->title, $data, $answerData['cardio_id']);
                        } else {
                            $answers[] = $this->createQuestionAnswers($answerData, $cardio->title, $quizId, $data, $answerData['cardio_id']);
                        }
                    }
                }
            } else {
                $this->deleteQuestionAnswers($quizId);
            }
        }

        if ($data['ques_for'] === 'musclestrengthening') {
            $muscleStregtheningAnswers = $isMultiple ? (isset($data['multipleAnsForMuscle']) ? $data['multipleAnsForMuscle'] : []) : (isset($data['singleAnsForMuscle']) ? $data['singleAnsForMuscle'] : []);
            if (!empty($muscleStregtheningAnswers)) {
                $this->deleteQuestionAnswers($quizId, $muscleStregtheningAnswers);
                foreach ($muscleStregtheningAnswers as $answerData) {
                    $muscleStrength = MuscleStrength::find($answerData['muscle_id']);
                    if ($muscleStrength) {
                        if (isset($answerData['id']) && $answerData['id'] != NULL) {
                            $this->updateQuestionAnswers($answerData, $muscleStrength->title, $data, $answerData['muscle_id']);
                        } else {
                            $answers[] = $this->createQuestionAnswers($answerData, $muscleStrength->title, $quizId, $data, $answerData['muscle_id']);
                        }
                    }
                }
            } else {
                $this->deleteQuestionAnswers($quizId);
            }
        }

        if ($data['ques_for'] === 'level') {
            $levelAnswers = $isMultiple ? (isset($data['multipleAnsForLevel']) ? $data['multipleAnsForLevel'] : []) : (isset($data['singleAnsForLevel']) ? $data['singleAnsForLevel'] : []);
            if (!empty($levelAnswers)) {
                $this->deleteQuestionAnswers($quizId, $levelAnswers);
                foreach ($levelAnswers as $answerData) {
                    if (isset($answerData['level']) && $answerData['level'] != NULL) {
                        if (isset($answerData['id']) && $answerData['id'] != NULL) {
                            $this->updateQuestionAnswers($answerData, $answerData['level'], $data);
                        } else {
                            $answers[] = $this->createQuestionAnswers($answerData, ($answerData['level'] ?? ''), $quizId, $data);
                        }
                    }
                }
            } else {
                $this->deleteQuestionAnswers($quizId);
            }
        }
    }

    private function handleUserInputAnswersUpdate(array $data, array &$answers, $quizId, $transitionLogicNo)
    {
        // Ensure you access the answer correctly
        $inputAnswer = $data['userQues'] ?? ''; // Get the user input answer or default to an empty string

        if (!empty($inputAnswer)) {
            if (isset($data['ans_id']) && $data['ans_id'] != NULL) {
                $answer = Answer::find($data['ans_id']);
                if ($answer) {
                    $answer->answer_type = $data['answer_type'];
                    $answer->answer_format = $data['answer_format'] ?? NULL;
                    $answer->ques_answers = $inputAnswer;
                    $answer->label = $data['answer'];
                    $answer->have_transition = $transitionLogicNo ? 1 : 0;
                    $answer->is_numeric = $data['isNumericAnswer'] ?? 0;
                    $answer->transition_id = $transitionLogicNo ? $data['selected_transition_ids'] : NULL;
                    $answer->transition_logic = $data['trans_logic'] ?? NULL;
                    $answer->save();
                }
            } else {
                $answers[] = [
                    'question_id' => $quizId,
                    'answer_type' => $data['answer_type'], // This will be 'userInput'
                    'answer_format' => $data['answer_format'] ?? null, // Default to null if not set
                    'ques_answers' => $inputAnswer, // The actual user input answer
                    'label' => $data['answer'], // The actual user input answer
                    'have_transition' => $transitionLogicNo ? 1 : 0,
                    'is_numeric' => $data['isNumericAnswer'] ?? 0,
                    'transition_id' => $transitionLogicNo ?  $data['selected_transition_ids'] : null,
                    'transition_logic' => $data['trans_logic'] ?? null,
                ];
            }
        }
    }

    private function createQuestionAnswers($answerData, $ques_answers, $quizId, array $data, $cardio_and_muscle_id = 0, $hasTransition = false) {
        $answer = array();
        $answer['question_id'] = $quizId;
        $answer['answer_type'] = $data['answer_type'];
        $answer['answer_format'] = $data['answer_format'] ?? NULL;
        $answer['ques_answers'] = $ques_answers;
        if (isset($answerData['answer_img']) && $answerData['answer_img'] != NULL) {
            $answerImage = time() . '.' . md5(rand(1000, 10000)) . $answerData['answer_img']->extension();
            $answerData['answer_img']->storeAs("quiz/answer/image", $answerImage, 'public');
            $answer['answer_img'] = "quiz/answer/image/$answerImage";
        }
        $answer['cardio_and_muscle_id'] = $cardio_and_muscle_id;
        $answer['have_transition'] = $hasTransition ? 1 : 0;
        if ($hasTransition && $data['trans_logic'] == 'Yes') {
            $answer['transition_id'] = isset($answerData['transition_id']) ? implode("|", $answerData['transition_id']) : NULL;
        } else if (!$hasTransition && $data['trans_logic'] == 'No') {
            $answer['transition_id'] = $data['selected_transition_ids'] ?? "";
        } else {
            if (isset($data['selected_transition_ids']) && !empty($data['selected_transition_ids'])) {
                $answer['transition_id'] =  $data['selected_transition_ids'];
            }
        }
        $answer['transition_logic'] = $data['trans_logic'] ?? NULL;
        $answer['ques_type'] = $data['ques_type'] ?? NULL;
        $answer['ans_points'] = $answerData['points'] ?? NULL;

        return $answer;
    }

    private function updateQuestionAnswers(array $answerData, $ques_answers, array $data, $cardio_and_muscle_id = 0, $hasTransition = false) {
        $answer = Answer::find($answerData['id']);
        if ($answer) {
            $answer->answer_type = $data['answer_type'];
            $answer->answer_format = $data['answer_format'] ?? NULL;
            $answer->ques_answers = $ques_answers;
            if (isset($answerData['answer_img']) && $answerData['answer_img'] != NULL) {
                $answerImage = time() . '.' . md5(rand(1000, 10000)) . $answerData['answer_img']->extension();
                $answerData['answer_img']->storeAs("quiz/answer/image", $answerImage, 'public');
                $answer->answer_img = "quiz/answer/image/$answerImage";
            }
            $answer->cardio_and_muscle_id = $cardio_and_muscle_id;
            $answer->have_transition = $hasTransition ? 1 : 0;
            if ($hasTransition && $data['trans_logic'] == 'Yes') {
                $answer->transition_id = isset($answerData['transition_id']) ? implode("|", $answerData['transition_id']) : NULL;
            } else if (!$hasTransition && $data['trans_logic'] == 'No') {
                $answer->transition_id = $data['selected_transition_ids'] ?? "";
            } else {
                if (isset($data['selected_transition_ids']) && !empty($data['selected_transition_ids'])) {
                    $answer->transition_id =  $data['selected_transition_ids'];
                }
            }
            $answer->transition_logic = $data['trans_logic'] ?? NULL;
            $answer->ques_type = $data['ques_type'] ?? NULL;
            $answer->ans_points = $answerData['points'] ?? NULL;

            if ($answer->save()) {
                return true;
            }
            return false;
        }
        return false;
    }

    private function deleteQuestionAnswers($question_id, array $answers = []) {
        if (empty($answers)) {
            if (Answer::where(['question_id' => $question_id])->delete()) {
                return true;
            }
            return false;
        }
        $questionAnswers = Answer::where(['question_id' => $question_id])->get();
        if ($questionAnswers->isNotEmpty()) {
            $questionAnswers = $questionAnswers->toArray();
            $questionAnswerIds = array_column($questionAnswers, 'id');
            $answerIds = array_column($answers, 'id');
            $deletedAnswerIds = array_diff($questionAnswerIds, $answerIds);
            if (!empty($deletedAnswerIds)) {
                if (Answer::whereIn('id', $deletedAnswerIds)->delete()) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return true;
    }

    // Update End

    public function getGroupWiseQuiz(Request $request)
    {
        $data = $request->all();
        if ($data['quiz_group_id'] === 'All') {
            $quizzes = Quiz::orderBy('quiz_position', 'asc')->with('quiz_group')->get();
        } else {
            $quizzes = Quiz::orderBy('quiz_position', 'asc')->with('quiz_group')->where(['quiz_group_id' => $data['quiz_group_id']])->get();
        }
        return response()->json($quizzes);
    }

    public function destroyQuiz($slug, $id)
    {
        Session::put('page', 'quiz');
        Quiz::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(1);
        return redirect()->back();
    }

    public function quizGroupIndex()
    {
        Session::put('page', 'quizGroup');
        $quizGroups = QuizGroup::orderBy('quiz_group_order', 'asc')->get()->toArray();

        $getQuizGroup = QuizGroup::whereNotNull('quiz_group_order')->get('quiz_group_order');
        $quizGroupOrder = [];
        foreach ($getQuizGroup as $order) {
            $quizGroupOrder[] = $order['quiz_group_order'];
        }
        return view('admin.quiz.quiz_group.index', ['quizGroups' => $quizGroups, 'quizGroupOrder' => $quizGroupOrder]);
    }

    public function addQuizGroup(Request $request)
    {
        Session::put('page', 'quizGroup');
        $getQuizGroup = QuizGroup::whereNotNull('quiz_group_order')->get('quiz_group_order');
        $quizGroupOrder = $getQuizGroup->pluck('quiz_group_order')->toArray();

        // Check if the table is empty
        if ($getQuizGroup->isEmpty()) {
            $getLastQuizGroupOrder = null;
        } else {
            $getLastQuizGroupOrder = $getQuizGroup->last()->toArray();
        }

        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'title' => ['required', 'string'],
                'color' => ['required', 'string'],
                // 'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                'quiz_group_order' => ['required', 'numeric']
            ];

            $message = [
                'title.required' => "Please enter title.",
                'quiz_group_order.required' => "Please enter quiz group order.",
                'quiz_group_order.numeric' => "Quiz group order should be a numeric value.",
                // 'image.required' => "Please select an image for this quiz group.",
                // 'image.max' => "The image must not be greater than 2 MB.",
                // 'image.image' => "The image must be an image file.",
                // 'image.mimes' => "The image must be a file of type: jpeg, png, jpg, gif, svg.",
            ];

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            $slug = Str::slug($data['title']);
            $existingQuizGroupOrder = QuizGroup::where(['quiz_group_order' => $data['quiz_group_order']])->first();

            // If there are no quiz groups, start from order 1; otherwise, increment the last order
            $updatedQuizGroupOrder = $getLastQuizGroupOrder ? $getLastQuizGroupOrder['quiz_group_order'] + 1 : 1;

            if (!empty($existingQuizGroupOrder)) {
                QuizGroup::where(['id' => $existingQuizGroupOrder['id'], 'quiz_group_order' => $data['quiz_group_order']])
                    ->update(['quiz_group_order' => $updatedQuizGroupOrder]);
            }

            if ($request->has('quiz_group_image')) {
                $quiImage = time() . '.' . $data['quiz_group_image']->extension();
                if (!Storage::disk('public')->exists("/quiz_group/images")) {
                    Storage::disk('public')->makeDirectory("/quiz_group/images"); //creates directory
                }

                $request->quiz_group_image->storeAs("quiz_group/images", $quiImage, 'public');
                $data['quiz_group_image'] = "quiz_group/images/$quiImage";
            }

            $quizGroup = new QuizGroup;
            $quizGroup->title = $data['title'];
            $quizGroup->slug = $slug;
            $quizGroup->quiz_group_order = $data['quiz_group_order'];
            $quizGroup->color = $data['color'];
            $quizGroup->quiz_group_image = $data['quiz_group_image'];
            $quizGroup->save();

            return redirect('admin/quiz-group-index')->with('success', "Quiz Group Inserted successfully");
        }

        return view('admin.quiz.quiz_group.create', ['quizGroupOrder' => $quizGroupOrder, 'getLastQuizGroupOrder' => $getLastQuizGroupOrder]);
    }

    public function updateQuizGroup(Request $request, $slug, $id)
    {
        Session::put('page', 'quizGroup');
        $quizGroupData = QuizGroup::where(['slug' => $slug, 'id' => $id])->first()->toArray();
        $getQuizGroup = QuizGroup::where('quiz_group_order', '!=', $quizGroupData['quiz_group_order'])->get('quiz_group_order');
        $quizGroupOrder = [];
        foreach ($getQuizGroup as $order) {
            $quizGroupOrder[] = $order['quiz_group_order'];
        }
        // echo "<pre>"; print_r($quizGroupOrder); die;
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'color' => ['required', 'string'],
                'title' => ['required', 'string']
            ];

            $message = [
                'title.required' => "Please enter title."
            ];

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->getMessageBag());
            }

            $slug = Str::slug($data['title']);

            if (!isset($data['quiz_group_image']) || empty($data['quiz_group_image'])) {
                $data['quiz_group_image'] = $quizGroupData->quiz_group_image;
            } else {
                if (!Storage::disk('public')->exists("/program/images")) {
                    Storage::disk('public')->makeDirectory("/program/images"); //creates directory
                }
                if (empty($quizGroupData->quiz_group_image)) {
                    $quiImage = time() . '.' . $data['quiz_group_image']->extension();
                } else {
                    Storage::disk('public')->delete($quizGroupData['quiz_group_image']);
                    $quiImage = time() . '.' . $data['quiz_group_image']->extension();
                }
                $request->quiz_group_image->storeAs("quiz_group/images", $quiImage, 'public');
                $data['quiz_group_image'] = "quiz_group/images/$quiImage";
            }

            $existingQuizGroupOrder = QuizGroup::where(['quiz_group_order' => $data['quiz_group_order']])->first();
            if (!empty($existingQuizGroupOrder['quiz_group_order'])) {
                QuizGroup::where(['id' => $existingQuizGroupOrder['id'], 'quiz_group_order' => $data['quiz_group_order']])->update(['quiz_group_order' => $quizGroupData['quiz_group_order']]);
                // Update in Current Quiz Group
                QuizGroup::where(['id' => $id])->update(['title' => $data['title'], 'slug' => $slug, 'quiz_group_order' => $existingQuizGroupOrder['quiz_group_order'], 'color' => $data['color'], 'quiz_group_image' => $data['quiz_group_image']]);
            } else {
                QuizGroup::where(['id' => $id])->update(['title' => $data['title'], 'slug' => $slug, 'quiz_group_order' => $data['quiz_group_order'], 'color' => $data['color'], 'quiz_group_image' => $data['quiz_group_image']]);
            }

            return redirect('admin/quiz-group-index')->with('success', "Quiz Group Updated successfully");
        }
        return view('admin.quiz.quiz_group.edit', ['quizGroupData' => $quizGroupData, 'quizGroupOrder' => $quizGroupOrder]);
    }

    public function destroyQuizGroup($slug, $id)
    {
        Session::put('page', 'quizGroup');
        QuizGroup::where(['slug' => $slug, 'id' => $id])->delete();
        sleep(1);
        return redirect()->back();
    }

    public function setQuizOrderPosition(Request $request)
    {
        $positions = QuizGroup::all();
        foreach ($positions as $position) {
            foreach ($request->order as $order) {
                if ($order['id'] == $position->id) {
                    $position->update(['quiz_group_order' => $order['position']]);
                }
            }
        }

        return response('Update Successfully.', 200);
    }
}
