<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\CompleteRegistration;
use App\Mail\FinishRegistration;
use App\Models\Answer;
use App\Models\AppPurchase;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Promocode;
use App\Models\PromoCodeUsage;
use App\Models\Quiz;
use App\Models\QuizGroup;
use App\Models\Setting;
use App\Models\StepsGoal;
use App\Models\StripeCustomer;
use App\Models\SubscriptionHistories;
use App\Models\Transition;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserProgram;
use App\Models\UserReferenceAnswer;
use App\Models\UsersInitialMeasurement;
use App\Models\UsersTargetMeasurement;
use App\Models\UsersSubscriptions;
use App\Services\CustomerIoService;
use Carbon\Carbon;
use DateTime;
use DOMDocument;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QuizController extends Controller
{
    public function preScreenQuiz(Request $request)
    {
        Session::put(['quiz_started' => true]);

        $setting = Cache::remember('settings_all', 300, function () {
            return Setting::get()->toArray();
        });
        $showPreQuizScreen = array_filter($setting, function ($item) {
            return $item['key'] === 'pre_screen_quiz';
        });
        $showPreQuizScreen = array_values($showPreQuizScreen);
        if (!empty($showPreQuizScreen) && $showPreQuizScreen[0]['value'] == 1 && $showPreQuizScreen[0]['key'] == 'pre_screen_quiz') {
            return view('front.quiz.preScreenQuiz');
        }
        return redirect()->route('questions');
    }

    public function getQuesAns(Request $request)
    {
        $setting = Setting::get()->toArray();
        $maintenanceMode = array_filter($setting, function ($item) {
            return $item['key'] === 'maintenance_mode';
        });
        $maintenanceMode = array_values($maintenanceMode);

        $showPreQuizScreen = array_filter($setting, function ($item) {
            return $item['key'] === 'pre_screen_quiz';
        });
        $showPreQuizScreen = array_values($showPreQuizScreen);

        if (!empty($maintenanceMode) && $maintenanceMode[0]['value'] == 1 && $maintenanceMode[0]['key'] == 'maintenance_mode') {
            return view('front.quiz.blank');
        }
        $data = $request->all();
        $previousQuesId = $request->input('quesId');
        if (!session()->get('quiz_started')) {
            if (isset($data['isWebView']) && $data['isWebView'] == 1) {
                Session::put(['isWebView' => 1]);
            }

            if (isset($data['userId']) && isset($data['isWebView']) && $data['isWebView'] == 1) {
                Auth::guard('user')->loginUsingId($data['userId']);
                Session::put(['isWebView' => 1]);
            }

            if (isset($data['userType']) && isset($data['isWebView']) && $data['isWebView'] == 1) {
                Session::put(['isWebView' => 1, 'userType' => 1]);
            }

            if (isset($data['sessionId']) && !empty($data['sessionId'])) {
                Session::put(['sessionId' => $data['sessionId']]);
            }
            // Redirect to preScreenQuiz if the session variable is not set

            if (!empty($showPreQuizScreen) && $showPreQuizScreen[0]['value'] == 1 && $showPreQuizScreen[0]['key'] == 'pre_screen_quiz') {
                return redirect()->route('preScreenQuiz');
            }
        }

        // $request->session()->flush();
        // $isWebView = 1;

        $gender = Session::get('gender', 'all');

        $getQuesWithAns = Cache::remember("quiz_active_with_ans_{$gender}", 120, function () use ($gender) {
            $questions = Quiz::where('is_active', 1)
                ->orderBy('quiz_position', 'asc')
                ->with('answers', 'quiz_group')
                ->get()
                ->toArray();

            usort($questions, function ($a, $b) {
                $orderA = isset($a['quiz_group']['quiz_group_order']) ? $a['quiz_group']['quiz_group_order'] : PHP_INT_MAX;
                $orderB = isset($b['quiz_group']['quiz_group_order']) ? $b['quiz_group']['quiz_group_order'] : PHP_INT_MAX;
                return $orderA <=> $orderB;
            });

            // Filter questions based on gender
            $questions = array_filter($questions, function ($question) use ($gender) {
                return $question['ques_for_gender'] == $gender || $question['ques_for_gender'] == 'all';
            });

            // Re-index array to maintain numeric keys
            return array_values($questions);
        });

        $answeredQuestions = session('answered_questions', []);
        $groupedQuestions = $this->groupQuestionsByQuizGroup($getQuesWithAns);

        list($nextQuestion, $currentQuizGroupId, $currentQuizGroupTitle, $currentQuizGroupColor) = $this->findNextQuestion($groupedQuestions, $answeredQuestions, $gender, $previousQuesId);

        if (!empty($nextQuestion)) {
            $anotherQuestionAnswer = Quiz::where(['id' => $nextQuestion['ques_id'], 'is_active' => 1])->first();

            // dd($anotherQuestionAnswer);
            if (!empty($anotherQuestionAnswer)  && !is_null($anotherQuestionAnswer)) {
                $sessionId = Session::get('quiz_session_id');
                $userAnswerForMainQuestion = UserAnswer::where(['session_id' => $sessionId, 'question_id' => $anotherQuestionAnswer['id']])->first();
                if ($userAnswerForMainQuestion) {
                    if ($userAnswerForMainQuestion['answer_type'] === 'userInput') {
                        $answerForMainQuestion = UserReferenceAnswer::where(['question_id' => $userAnswerForMainQuestion['question_id'], 'user_answer_id' => $userAnswerForMainQuestion['answer_id'], 'session_id' => $userAnswerForMainQuestion['session_id']])->first();
                    } else {
                        $answerForMainQuestion = Answer::where(['question_id' => $userAnswerForMainQuestion['question_id'], 'id' => $userAnswerForMainQuestion['answer_id']])->first();
                    }
                    if ($answerForMainQuestion) {
                        if ($answerForMainQuestion['answer_type'] === 'userInput') {
                            $nextQuestion['ques_title'] = str_replace('{use_in_question}', $answerForMainQuestion['value'], $nextQuestion['ques_title']);
                        } else {
                            $nextQuestion['ques_title'] = str_replace('{use_in_question}', $answerForMainQuestion['ques_answers'], $nextQuestion['ques_title']);
                        }
                    }
                }
            }
        }

        if (is_null($currentQuizGroupId) && !empty($groupedQuestions)) {
            reset($groupedQuestions);
            $currentQuizGroupId = key($groupedQuestions);
            $currentQuizGroupTitle = $groupedQuestions[$currentQuizGroupId][0]['quiz_group']['title'];
        }

        // Get total number of quiz groups (cached)
        $totalGroupsCount = Cache::remember('quiz_groups_count', 300, function () {
            return QuizGroup::count();
        });
        $quizGroup = Cache::remember("quiz_group_{$currentQuizGroupId}", 300, function () use ($currentQuizGroupId) {
            return QuizGroup::where('id', '=', $currentQuizGroupId)->first();
        });

        // Initialize arrays for progress data
        $quizProgress = [];
        $totalQuestionsInGroup = 0;
        $answeredQuestionsInGroup = 0;

        foreach ($groupedQuestions as $groupId => $questions) {
            $totalQuestionsInGroup = count($questions);
            $answeredQuestionsInGroup = $this->countAnsweredQuestionsInGroup($groupedQuestions, $groupId, $answeredQuestions);
            $percentAnsweredInGroup = $totalQuestionsInGroup > 0 ? ($answeredQuestionsInGroup / $totalQuestionsInGroup) * 100 : 0;

            // Add the progress for the current quiz group
            $quizProgress[] = [
                'title' => $questions[0]['quiz_group']['title'],
                'color' => $questions[0]['quiz_group']['color'],
                'quiz_group_image' => $questions[0]['quiz_group']['quiz_group_image'],
                'percentage' => $percentAnsweredInGroup,
                'quiz_group_order' => $questions[0]['quiz_group']['quiz_group_order'], // Include the quiz group order
            ];
        }

        // Filter to show only the progress for quiz groups with order 1
        if ($quizGroup && $quizGroup->quiz_group_order == 1) {
            $quizProgress = array_filter($quizProgress, function ($progress) {
                return $progress['quiz_group_order'] == 1;
            });
        }

        if (is_null($nextQuestion) || empty($nextQuestion)) {
            // Session::forget('answered_questions');
            return redirect()->route('get-package');
        }

        $percentAnsweredInGroup = $totalQuestionsInGroup > 0 ? ($answeredQuestionsInGroup / $totalQuestionsInGroup) * 100 : 0;

        $userAnswerBySessionId = [];
        if (!is_null(Session::get('quiz_session_id')) && !empty(Session::get('quiz_session_id')) && !empty($nextQuestion)) {
            if ($nextQuestion['answer_type'] === 'multiple') {
                $userAnswerBySessionId = UserAnswer::with('userReferenceAnswer')->where(['session_id' => Session::get('quiz_session_id'), 'question_id' => $nextQuestion['id']])->get();
            } else {
                $userAnswerBySessionId = UserAnswer::with('userReferenceAnswer')->where(['session_id' => Session::get('quiz_session_id'), 'question_id' => $nextQuestion['id']])->first();
            }
        }

        $scriptTags = $nextQuestion['ques_title'];
        if (!empty($nextQuestion['google_analytic_script'])) {
            $scriptTags = $nextQuestion['google_analytic_script'];
            // $dom = new DOMDocument();
            // // Suppress errors due to HTML5 tags in DOMDocument
            // libxml_use_internal_errors(true);

            // // Decode HTML entities before loading into DOMDocument
            // $decodedScript = htmlspecialchars_decode($nextQuestion['google_analytic_script']);

            // // Wrap the decoded HTML in a div and load it
            // $dom->loadHTML('<div>' . $decodedScript . '</div>');
            // libxml_clear_errors();

            // // Extract all <script> tags
            // foreach ($dom->getElementsByTagName('script') as $script) {
            //     $scriptTags .= $dom->saveHTML($script);
            // }
        }

        return view('front.quiz.questions', [
            'currentQuestion' => $nextQuestion,
            'groupedQuestions' => $groupedQuestions[$currentQuizGroupId] ?? [],
            'totalQuestionsInGroup' => $totalQuestionsInGroup,
            'answeredQuestionsInGroup' => $answeredQuestionsInGroup,
            'percentAnsweredInGroup' => $percentAnsweredInGroup,
            'quizProgress' => $quizProgress,
            'totalGroupsCount' => $totalGroupsCount,
            'currentQuizGroupTitle' => $currentQuizGroupTitle,
            'currentQuizGroupColor' => $currentQuizGroupColor,
            'userAnswerBySessionId' => $userAnswerBySessionId,
            'googleAnalyticScript' => $scriptTags
        ]);
    }

    public function getPreviousQuesAns($questionId, $sessionId, $isBackFromTransition = false)
    {

        // Retrieve the answered questions from the session
        $answeredQuestions = session('answered_questions', []);
        // dd($answeredQuestions);
        // Check if there are no answered questions
        if (empty($answeredQuestions)) {
            // Redirect to the starting point of the quiz
            return redirect()->route('preScreenQuiz');
        }

        // Get the previous question's ID
        if ($isBackFromTransition) {
            $previousQuestionId = $questionId;
        } else {
            // If the current question is not in the answered questions, assume we're at the last answered question
            $currentIndex = array_search($questionId, array_reverse($answeredQuestions, true));

            if ($currentIndex === false) {
                $currentIndex = count($answeredQuestions); // Set it to "after the last index"
            }
            // dd($currentIndex);

            if ($currentIndex <= 0) {
                return redirect()->back()->with('error', 'No previous question found.');
            }
            $previousQuestionId = $answeredQuestions[$currentIndex - 1];
        }

        if (array_search($questionId, $answeredQuestions) != false) {
            $answeredQuestionIndex = array_search($questionId, $answeredQuestions);
            unset($answeredQuestions[$answeredQuestionIndex]);
            session(['answered_questions' => $answeredQuestions]);
        }

        // Fetch the previous question and its related data
        $previousQuestion = Quiz::where('id', $previousQuestionId)
            ->where('is_active', 1)
            ->with('answers', 'quiz_group')
            ->first();

        if (!$previousQuestion) {
            if (array_search($previousQuestionId, $answeredQuestions) != false) {
                $previousQuestionIndex = array_search($previousQuestionId, $answeredQuestions);
                if ($previousQuestionIndex <= 0) {
                    return redirect()->route('questions');
                }
                // $previousOfPreviousQuestion = $answeredQuestions[$previousQuestionIndex - 1];
                unset($answeredQuestions[$previousQuestionIndex]);
                session(['answered_questions' => $answeredQuestions]);

                return $this->getPreviousQuesAns($questionId, $sessionId, $isBackFromTransition);
            }
            // Handle case where the previous question is not found
            return redirect()->route('questions');
        }

        // Get user answers for the previous question

        if ($previousQuestion['answer_type'] === 'multiple') {
            $userAnswer = UserAnswer::with('userReferenceAnswer')
                ->where(['session_id' => $sessionId, 'question_id' => $previousQuestionId])
                ->get();
        } else {
            $userAnswer = UserAnswer::with('userReferenceAnswer')
                ->where(['session_id' => $sessionId, 'question_id' => $previousQuestionId])
                ->first();
        }

        $anotherQuestionAnswer = Quiz::where(['id' => $previousQuestion['ques_id'], 'is_active' => 1])->first();


        // Replace placeholders in the question title with user answers if applicable
        if ($anotherQuestionAnswer) {
            $anotherQuestionuserAnswer = UserAnswer::with('userReferenceAnswer')
                ->where(['session_id' => $sessionId, 'question_id' => $anotherQuestionAnswer['id']])
                ->first();
            if ($anotherQuestionAnswer['answer_type'] === 'userInput') {
                $answerDetail = UserReferenceAnswer::where([
                    'question_id' => $anotherQuestionuserAnswer['question_id'],
                    'user_answer_id' => $anotherQuestionuserAnswer['answer_id'],
                    'session_id' => $anotherQuestionuserAnswer['session_id']
                ])->first();
                if ($answerDetail) {
                    $previousQuestion->ques_title = str_replace(
                        '{use_in_question}',
                        $answerDetail->value,
                        $previousQuestion->ques_title
                    );
                }
            } else {
                $answerDetail = Answer::where([
                    'question_id' => $anotherQuestionuserAnswer['question_id'],
                    'id' => $anotherQuestionuserAnswer['answer_id']
                ])->first();
                if ($answerDetail) {
                    $previousQuestion->ques_title = str_replace(
                        '{use_in_question}',
                        $answerDetail->ques_answers,
                        $previousQuestion->ques_title
                    );
                }
            }
        }

        if (!$isBackFromTransition) {
            if ($previousQuestion['answer_type'] == 'multiple') {
                $selectedAnswer = Answer::where(['id' => $userAnswer->first()->answer_id])->first();
            } else {
                $selectedAnswer = Answer::where(['id' => $userAnswer->answer_id])->first();
            }

            if (isset($selectedAnswer) && $selectedAnswer->transition_id) {
                //$bgcolor = $selectedAnswer->question->quiz_group->color;
                //$bgimage = $selectedAnswer->question->quiz_group->quiz_group_image;



                $answerTransitionIds = $selectedAnswer->transition_id;
                $transitionIds = explode('|', $answerTransitionIds);

                // Fetch transitions based on the IDs
                $transitions = Transition::whereIn('id', $transitionIds)
                    ->where('status', 1)
                    ->orderByRaw("FIELD(id, " . implode(',', array_map('intval', $transitionIds)) . ")")
                    ->get()
                    ->toArray();

                $selectedAnswer['transitions'] = $transitions;
                $previousTransitionIndex = array_key_last($transitions);
                $previousTransition = Arr::last($transitions);
                if(isset($previousTransition)){
                    if ($previousTransition['is_chart'] == 1 && $previousTransition['is_paywall'] == 0) {
                        return $this->graphTransition($selectedAnswer['transitions'], $selectedAnswer['id']);
                    } elseif ($previousTransition['is_chart'] == 1 && $previousTransition['is_paywall'] == 1) {
                        return $this->paywallGraphTransition($selectedAnswer['transitions'], $selectedAnswer['id'], $previousTransition);
                    }
                    return view('front.quiz.user_transition', [
                        'transition' => $previousTransition,
                        'transitionDescription' => $previousTransition['trans_description'],
                        'allTransition' => $selectedAnswer['transitions'],
                        'selectedAnswerId' => $selectedAnswer['id'],
                        'prevIndex' => $previousTransitionIndex - 1,
                        'nextIndex' => $previousTransitionIndex + 1,
                        'backgroundColor' => $previousTransition['color'],
                        'backgroundImage' => $previousTransition['transition_image'],
                    ]);
                }
            }
        }


        $currentQuizGroupId = $previousQuestion->quiz_group->id ?? null;

        //copy of getQuesAns function -> start
        $gender = Session::get('gender', 'all');

        $getQuesWithAns = Quiz::where('is_active', 1)->orderBy('quiz_position', 'asc')->with('answers', 'quiz_group')->get()->toArray();
        usort($getQuesWithAns, function ($a, $b) {
            $orderA = isset($a['quiz_group']['quiz_group_order']) ? $a['quiz_group']['quiz_group_order'] : PHP_INT_MAX;
            $orderB = isset($b['quiz_group']['quiz_group_order']) ? $b['quiz_group']['quiz_group_order'] : PHP_INT_MAX;

            return $orderA <=> $orderB;
        });

        // Filter questions based on gender
        $getQuesWithAns = array_filter($getQuesWithAns, function ($question) use ($gender) {
            return $question['ques_for_gender'] == $gender || $question['ques_for_gender'] == 'all';
        });

        // Track skipped questions
        $skippedQuestions = session('skipped_questions', []);
        if (!in_array($previousQuestion->id, $skippedQuestions)) {
            $skippedQuestions[] = $previousQuestion->id;
        }
        session(['skipped_questions' => $skippedQuestions]);

        $answeredQuestions = session('answered_questions', []);
        $skippedQuestions = session('skipped_questions', []);

        $groupedQuestions = $this->groupQuestionsByQuizGroup($getQuesWithAns);


        // Get total number of quiz groups
        $totalGroupsCount = QuizGroup::count();
        $quizGroup = QuizGroup::where('id', '=', $currentQuizGroupId)->first();

        // Initialize arrays for progress data
        $quizProgress = [];
        $totalQuestionsInGroup = 0;
        $answeredQuestionsInGroup = 0;

        foreach ($groupedQuestions as $groupId => $questions) {
            $totalQuestionsInGroup = count($questions);

            $answeredQuestionsInGroup = $this->countAnsweredQuestionsInGroup($groupedQuestions, $groupId, $answeredQuestions,$skippedQuestions);
            $percentAnsweredInGroup = $totalQuestionsInGroup > 0 ? ($answeredQuestionsInGroup / $totalQuestionsInGroup) * 100 : 0;

            // Add the progress for the current quiz group
            $quizProgress[] = [
                'title' => $questions[0]['quiz_group']['title'],
                'color' => $questions[0]['quiz_group']['color'],
                'quiz_group_image' => $questions[0]['quiz_group']['quiz_group_image'],
                'percentage' => $percentAnsweredInGroup,
                'quiz_group_order' => $questions[0]['quiz_group']['quiz_group_order'], // Include the quiz group order
            ];
        }

        // Filter to show only the progress for quiz groups with order 1
        if ($quizGroup && $quizGroup->quiz_group_order == 1) {
            $quizProgress = array_filter($quizProgress, function ($progress) {
                return $progress['quiz_group_order'] == 1;
            });
        }
        //copy of getQuesAns function -> end



        // Return the view with the previous question data
        return view('front.quiz.questions', [
            'currentQuestion' => $previousQuestion,
            'groupedQuestions' => $groupedQuestions[$currentQuizGroupId] ?? [],
            'totalQuestionsInGroup' => $totalQuestionsInGroup,
            'answeredQuestionsInGroup' => $answeredQuestionsInGroup,
            'percentAnsweredInGroup' => $percentAnsweredInGroup,
            'quizProgress' => $quizProgress,
            'totalGroupsCount' => $totalGroupsCount,
            'currentQuizGroupTitle' => $previousQuestion->quiz_group->title ?? '',
            'currentQuizGroupColor' => $previousQuestion->quiz_group->color ?? '',
            'userAnswerBySessionId' => $userAnswer,
            'googleAnalyticScript' => $previousQuestion->google_analytic_script ?? $previousQuestion->ques_title,
        ]);
    }
    public function getPreviousTransition($selectedAnswerId, $index) {
        $selectedAnswer = Answer::where(['id' => $selectedAnswerId])->first()->toArray();

        $answer = Answer::find($selectedAnswerId);
        //$bgcolor = $answer->question->quiz_group->color;
        //$bgimage = $answer->question->quiz_group->quiz_group_image;

        $ids = explode('|', $selectedAnswer['transition_id']);

        // Fetch transitions based on the IDs
        $transitions = Transition::whereIn('id', $ids)
            ->where('status', 1)
            ->orderByRaw("FIELD(id, " . implode(',', array_map('intval', $ids)) . ")")
            ->get()
            ->toArray();

        // Add transitions to the answer
        $answer['transitions'] = $transitions;

        if ($index >= 0) {
            $transition = $answer['transitions'][$index];

            if ($transition['is_chart'] == 1 && $transition['is_paywall'] == 0) {
                return $this->graphTransition($answer['transitions'], $selectedAnswer['id']);
            } elseif ($transition['is_chart'] == 1 && $transition['is_paywall'] == 1) {
                return $this->paywallGraphTransition($answer['transitions'], $selectedAnswer['id'], $transition);
            }
            return view('front.quiz.user_transition', [
                'transition' => $transition,
                'transitionDescription' => $transition['trans_description'],
                'allTransition' => $answer['transitions'],
                'selectedAnswerId' => $selectedAnswerId,
                'prevIndex' => $index - 1,
                'nextIndex' => $index + 1,
                'backgroundColor' => $transition['color'],
                'backgroundImage' => $transition['transition_image'],
            ]);
        }

        // Retrieve the answered questions from the session
        $answeredQuestions = session('answered_questions', []);
        // Check if there are no answered questions
        if (empty($answeredQuestions)) {
            // Redirect to the starting point of the quiz
            return redirect()->route('preScreenQuiz');
        }

        $previousQuestionId = $answer['question_id'];
        $sessionId = Session::get('quiz_session_id');

        return redirect(route('quiz.previousQuestion', ['question_id' => $previousQuestionId, 'session_id' => $sessionId, 'is_back_from_transition' => true]));
    }
    private function groupQuestionsByQuizGroup($questions)
    {
        $groupedQuestions = [];
        foreach ($questions as $question) {
            $quizGroupId = $question['quiz_group_id'];
            $groupedQuestions[$quizGroupId][] = $question;
        }
        return $groupedQuestions;
    }

    private function findNextQuestion($groupedQuestions, $answeredQuestions, $gender, $previousQuesId = null)
    {
        $nextQuestion = null;
        $currentQuizGroupId = null;
        $currentQuizGroupTitle = '';
        $currentQuizGroupColor = '';

        // Flatten all questions from grouped questions into a single array for easy access by index
        $allQuestions = [];
        foreach ($groupedQuestions as $quizGroupId => $questions) {
            foreach ($questions as $question) {
                $allQuestions[] = $question;
            }
        }
        // dd($allQuestions);
        // If previousQuesId is provided, find its index in the answeredQuestions array
        if ($previousQuesId !== null) {
            // Find the index of the previous question in the answeredQuestions array
            $answeredQuestionIndex = array_search($previousQuesId, $answeredQuestions);

            if ($answeredQuestionIndex !== false) {
                // If the previous question exists in answeredQuestions, move to the next question
                $nextAnsweredQuestionIndex = $answeredQuestionIndex + 1;

                // Check if we have a next question in the answeredQuestions array
                if ($nextAnsweredQuestionIndex < count($answeredQuestions)) {
                    // Get the next question ID from answeredQuestions array
                    $nextQuestionId = $answeredQuestions[$nextAnsweredQuestionIndex];

                    // Find this question in the allQuestions array
                    foreach ($allQuestions as $question) {
                        if ($question['id'] == $nextQuestionId &&
                            ($question['ques_for_gender'] == $gender || $question['ques_for_gender'] == 'all')) {
                            $nextQuestion = $question;
                            $currentQuizGroupId = $question['quiz_group_id'];
                            $currentQuizGroupTitle = $question['quiz_group']['title'];
                            $currentQuizGroupColor = $question['quiz_group']['color'];
                            break;
                        }
                    }
                } else {
                    // If there is no next question in answeredQuestions, fall back to the original logic
                    foreach ($allQuestions as $quizGroupId => $question) {
                        if (($question['ques_for_gender'] == $gender || $question['ques_for_gender'] == 'all') &&
                            !in_array($question['id'], $answeredQuestions)) {
                            $nextQuestion = $question;
                            $currentQuizGroupId = $question['quiz_group_id'];
                            $currentQuizGroupTitle = $question['quiz_group']['title'];
                            $currentQuizGroupColor = $question['quiz_group']['color'];
                            break;
                        }
                    }
                }
            } else {
                // If previousQuesId is not found in answeredQuestions, fall back to original logic
                foreach ($allQuestions as $quizGroupId => $questions) {
                    foreach ($questions as $question) {
                        if (($question['ques_for_gender'] == $gender || $question['ques_for_gender'] == 'all') &&
                            !in_array($question['id'], $answeredQuestions)) {
                            $nextQuestion = $question;
                            $currentQuizGroupId = $question['quiz_group_id'];
                            $currentQuizGroupTitle = $question['quiz_group']['title'];
                            $currentQuizGroupColor = $question['quiz_group']['color'];
                            break 2;
                        }
                    }
                }
            }
        } else {
            // If no previous question ID is provided, fall back to original logic
            foreach ($allQuestions as $quizGroupId => $question) {
                if (($question['ques_for_gender'] == $gender || $question['ques_for_gender'] == 'all') &&
                    !in_array($question['id'], $answeredQuestions)) {
                    $nextQuestion = $question;
                    $currentQuizGroupId = $question['quiz_group_id'];
                    $currentQuizGroupTitle = $question['quiz_group']['title'];
                    $currentQuizGroupColor = $question['quiz_group']['color'];
                    break;
                }
            }
        }

        return [$nextQuestion, $currentQuizGroupId, $currentQuizGroupTitle, $currentQuizGroupColor];
    }

    private function countAnsweredQuestionsInGroup($groupedQuestions, $currentQuizGroupId, $answeredQuestions, $skippedQuestions = [])
    {
        $answeredQuestionsInGroup = 0;
        if (isset($groupedQuestions[$currentQuizGroupId])) {
            foreach ($groupedQuestions[$currentQuizGroupId] as $question) {
                // Count only questions that are answered and not skipped
                if (in_array($question['id'], $answeredQuestions) && !in_array($question['id'], $skippedQuestions)) {
                    $answeredQuestionsInGroup++;
                }
            }
        }
        return $answeredQuestionsInGroup;
    }

    public function saveAnswer(Request $request, $questionId)
    {
        $quesData = Quiz::find($questionId);
        if ($quesData && $quesData->is_turnstile_enabled == 1) {
            if (app()->isLocal()) {
                $request->merge(['cf-turnstile-response' => 'fake-token']);
            }
            $request->validate([
                'cf-turnstile-response' => 'required|turnstile',
            ]);
        }
        $request->validate([
            'answer_id' => 'required',
        ]);

        if (isset($request['answerType']) && $request['answerType'] == 'userInput' && !isset($request['isWhatsappQue'] )) {
            $request->validate([
                'answer' => 'required',
            ]);
        }
        $data = $request->all();
        if (isset($data['answer_for']) && ($data['answer_for'] === 'steps_goal' || $data['answer_for'] === 'activity_level' || $data['answer_for'] === 'trainer')) {
            $answerData = explode(",", $data['answer_id']); //100,0
            $data['answer_id'] = $answerData[0];
            $data['answer_index'] = $answerData[1];
        }
        // dd($data);
        $skippedQuestions = session('skipped_questions', []);
        if (($key = array_search($questionId, $skippedQuestions)) !== false) {
            unset($skippedQuestions[$key]);
            session(['skipped_questions' => $skippedQuestions]);
        }
        $selectedAnswerId = $request->input('answer_id');

        if (!Auth::guard('user')->check()) {
            if (isset($data['answer_for']) && $data['answer_for'] == 'email') {
                // Check if email exists
                $user = User::where('email', $data['answer'])->first();

                if ($user) {
                    // Case 1: User exists and isSubscribedUser != 2
                    if ($user->isSubscribedUser != 2) {
                        return redirect()->back()->with('error', "This Email already exists.");
                    }

                    // Case 2: User exists and isSubscribedUser == 2
                    if ($user->isSubscribedUser == 2) {
                        $refAnswer = UserReferenceAnswer::where('value', $user->email)->first();

                        if ($refAnswer) {
                            $sessionId = $refAnswer->session_id;

                            // Delete all UserReferenceAnswer records with the same session_id
                            UserReferenceAnswer::where('session_id', $sessionId)->delete();
                            UserAnswer::where('session_id', $sessionId)->delete();
                            // Delete the user
                            $user->delete();
                        }
                    }
                }
            }
        }

        // Generate or retrieve the session ID
        $userAnsweredSesionId = Session::get('sessionId');
        if (!empty($userAnsweredSesionId) && !is_null($userAnsweredSesionId)) {
            $sessionId = $userAnsweredSesionId;
        } else {
            $sessionId = session('quiz_session_id', Str::uuid());
        }
        session(['quiz_session_id' => $sessionId]);

        $quesId = $data['questionId'];

        if ($data['answerType'] === 'multiple') {
            $this->saveMultipleAnswers($data, $sessionId);
        } elseif ($data['answerType'] === 'userInput') {
            $this->saveUserInputAnswer($data, $sessionId);
        } else {
            $this->saveSingleAnswer($data, $sessionId);
        }

        $answeredQuestions = session('answered_questions', []);
        if (!in_array($questionId, $answeredQuestions)) {
            $answeredQuestions[] = $questionId;
        }
        session(['answered_questions' => $answeredQuestions]);

        $getQuesWithAns = Quiz::with('answers')->find($questionId);
        if ($getQuesWithAns) {
            $getQuesWithAns = $getQuesWithAns->toArray();
            foreach ($getQuesWithAns['answers'] as &$answer) {
                // Get transition IDs
                $ids = explode('|', $answer['transition_id']);

                // Fetch transitions based on the IDs in the specified order
                $transitions = Transition::whereIn('id', $ids)
                    ->where('status', 1)
                    ->orderByRaw("FIELD(id, " . implode(',', array_map('intval', $ids)) . ")")
                    ->get()
                    ->toArray();

                // Add transitions to the answer
                $answer['transitions'] = $transitions;
            }

            $selectedAnswer = $this->findSelectedAnswer($data['answerType'], $getQuesWithAns, $selectedAnswerId, $questionId);
            if ($selectedAnswer && isset($selectedAnswer['transitions']) && !empty($selectedAnswer['transitions'])) {
                $quiz = Quiz::find($questionId);
                //$bgcolor = $quiz->quiz_group->color ?? null;
                //$bgimage = $quiz->quiz_group->quiz_group_image ?? null;
                foreach ($selectedAnswer['transitions'] as $index => $transition) {
                    if ($transition['is_chart'] == 1 && $transition['is_paywall'] == 0) {
                        return $this->graphTransition($selectedAnswer['transitions'], $selectedAnswer['id']);
                    } elseif ($transition['is_chart'] == 1 && $transition['is_paywall'] == 1) {
                        return $this->paywallGraphTransition($selectedAnswer['transitions'], $selectedAnswer['id'], $transition);
                    }

                    return view('front.quiz.user_transition', [
                        'transition' => $transition,
                        'transitionDescription' => $transition['trans_description'],
                        'allTransition' => $selectedAnswer['transitions'],
                        'selectedAnswerId' => $selectedAnswer['id'],
                        'prevIndex' => $index - 1,
                        'nextIndex' => $index + 1,
                        'backgroundColor' => $transition['color'],
                        'backgroundImage' => $transition['transition_image'],
                    ]);
                }
            } else {
                return redirect()->route('questions', ['quesId' => $quesId]);
            }
        }
        return redirect()->route('questions');
    }

    private function graphTransition($allTransition, $selectedAnswerId)
    {
        $userAnsweredData = Session::get('userAnsweredData');
        $currentWeight = null;
        $goalWeight = null;
        $nextIndex = count($allTransition);
        $currentTransitionIndex = 0;
        $previousTransitionIndex = -1;
        foreach ($allTransition as $index => $item) {
            if ($item['is_chart'] == 1 && $item['is_paywall'] == 0) {
                $nextIndex = $index + 1; // Update lastIndex whenever is_chart is 1
            }
        }
        $answer = Answer::find($selectedAnswerId);
        //$bgcolor = $answer->question->quiz_group->color;
        //$bgimage = $answer->question->quiz_group->quiz_group_image;

        if (!is_null($userAnsweredData)) {
            foreach ($userAnsweredData as $userAnswer) {
                if ($userAnswer['key'] === 'current_weight') {
                    $currentWeight = (int) $userAnswer['value'];
                }
                if ($userAnswer['key'] === 'desire_weight') {
                    $goalWeight = (int) $userAnswer['value'];
                }
            }
        }

        $weightCategory = [];
        if ($currentWeight !== null && $goalWeight !== null) {
            $weightDifference = abs($goalWeight - $currentWeight) * 1000;

            if ($weightDifference <= 5000) { // less than 5 kg
                $weightCategory[] = 'Graph-1-5kg-Max';
                $weightCategory[] = 'Graph-2-5kg-Max';
            } elseif ($weightDifference > 5000 && $weightDifference <= 10000) { // between 5 kg and 10 kg
                $weightCategory[] = 'Graph-1-10kg-Max';
                $weightCategory[] = 'Graph-2-10kg-Max';
            } else { // more than 10 kg
                $weightCategory[] = 'Graph-1-10kg-Plus';
                $weightCategory[] = 'Graph-2-10kg-Plus';
            }
        }

        // Result array to store matched transitions
        $transition = "";

        // Loop through all transitions
        foreach ($allTransition as $index => $eachTransition) {
            if (in_array($eachTransition['chart_name'], $weightCategory)) {
                $transition = $eachTransition;
                $currentTransitionIndex = $index;
                break; // Stop loop after finding the first match
            }
        }
        //dd($allTransition);

        // Check if transition is empty
        if (empty($transition)) {
            return redirect()->route('questions');
        }


        $previousTransitions = array_slice($allTransition, 0, $currentTransitionIndex);
        $nonGraphTransitions = array_filter($previousTransitions, function ($nonGraphTransition) {
            return $nonGraphTransition['is_chart'] == 0;
        });
        if (!empty($nonGraphTransitions)) {
            $previousTransitionIndex = array_key_last($nonGraphTransitions);
        }

        if ($currentWeight !== null && $goalWeight !== null) {

            $actualWeightDiffrence = $goalWeight - $currentWeight; // to show negative value as well
            $weightDifference = ($goalWeight - $currentWeight) * 1000; // Convert kg to grams

            // Set the locale to French
            Carbon::setLocale('fr');
            $currentDate = Carbon::now();

            $weeksToGoal = abs($weightDifference) / 250;
            $fullWeeks = floor($weeksToGoal); // Full weeks
            $remainingDays = round(($weeksToGoal - $fullWeeks) * 7); // Remaining days
            $goalAchieveDate = $currentDate->copy()->addWeeks($fullWeeks)->addDays($remainingDays)->translatedFormat('j F Y');
            $fiveKgLossDate = $currentDate->copy()->addWeeks(20)->translatedFormat('j F');
            $tenKgLossDate = $currentDate->copy()->addWeeks(40)->format('Y-m-d'); // Remains in numeric format

            $newWeeksToGoal = abs($weightDifference) / 300;
            $newFullWeeks = floor($newWeeksToGoal); // Full weeks
            $newRemainingDays = round(($newWeeksToGoal - $newFullWeeks) * 7); // Remaining days
            $newGoalAchieveDate = $currentDate->copy()->addWeeks($newFullWeeks)->addDays($newRemainingDays)->translatedFormat('j F Y');
            $newFiveKgLossDate = $currentDate->copy()->addDays(117)->translatedFormat('j F');
            $newTenKgLossDate = $currentDate->copy()->addDays(234)->format('Y-m-d'); // Remains in numeric format



            $transition['trans_description'] = str_replace(
                ['{CurrentWeight}', '{CurrentDate}', '{FiveKgLossDate}', '{TenKgLossDate}', '{GoalWeight}', '{GoalAchieveDate}', '{NewGoalAchieveDate}', '{NewFiveKgLossDate}', '{NewTenKgLossDate}', '{ActualWeightDiffrence}'],
                [$currentWeight, $currentDate, $fiveKgLossDate, $tenKgLossDate, $goalWeight, $goalAchieveDate, $newGoalAchieveDate, $newFiveKgLossDate, $newTenKgLossDate, $actualWeightDiffrence],
                $transition['trans_description']
            );
        }

        return view('front.quiz.user_transition', [
            'transition' => $transition,
            'transitionDescription' => $transition['trans_description'],
            'allTransition' => $allTransition,
            'selectedAnswerId' => $selectedAnswerId,
            'prevIndex' => $previousTransitionIndex,
            'nextIndex' => $nextIndex,
            'backgroundColor' => $transition['color'],
            'backgroundImage' => $transition['transition_image'],
        ]);
    }

    private function paywallGraphTransition($allTransition, $selectedAnswerId, $transitions)
    {
        $userAnsweredData = Session::get('userAnsweredData');
        $currentWeight = null;
        $goalWeight = null;
        $nextIndex = count($allTransition);
        $currentTransitionIndex = 0;
        $previousTransitionIndex = -1;

        $answer = Answer::find($selectedAnswerId);
        //$bgcolor = $answer->question->quiz_group->color;
        //$bgimage = $answer->question->quiz_group->quiz_group_image;

        if (!is_null($userAnsweredData)) {
            foreach ($userAnsweredData as $userAnswer) {
                if ($userAnswer['key'] === 'current_weight') {
                    $currentWeight = (int) $userAnswer['value'];
                }
                if ($userAnswer['key'] === 'desire_weight') {
                    $goalWeight = (int) $userAnswer['value'];
                }
            }
        }

        $weightCategory = [];
        if ($currentWeight !== null && $goalWeight !== null) {
            $weightDifference = abs($goalWeight - $currentWeight) * 1000;

            if ($weightDifference <= 5000) { // less than 5 kg
                $weightCategory[] = 'Graph-1-5kg-Max';
                $weightCategory[] = 'Graph-2-5kg-Max';
            } elseif ($weightDifference > 5000 && $weightDifference <= 10000) { // between 5 kg and 10 kg
                $weightCategory[] = 'Graph-1-10kg-Max';
                $weightCategory[] = 'Graph-2-10kg-Max';
            } else { // more than 10 kg
                $weightCategory[] = 'Graph-1-10kg-Plus';
                $weightCategory[] = 'Graph-2-10kg-Plus';
            }
        }

        // Array to store items with is_paywall = 1
        $paywallItems = [];

        foreach ($allTransition as $index => $item) {
            if ($item['is_paywall'] == 1) {
                $paywallItems[] = $item; // Store items with is_paywall = 1
            }
        }

        // Result array to store matched transitions
        $transition = $transitions;

        // Loop through all paywall items
        foreach ($paywallItems as $paywallIndex => $eachPaywallItem) {
            if (in_array($eachPaywallItem['chart_name'], $weightCategory)) {
                $transition = $eachPaywallItem; // Store matching items
                $currentTransitionIndex = $paywallIndex;
            }
        }

        $previousTransitions = array_slice($allTransition, 0, $currentTransitionIndex);
        $nonGraphTransitions = array_filter($previousTransitions, function ($nonGraphTransition) {
            return $nonGraphTransition['is_chart'] == 0;
        });
        if (!empty($nonGraphTransitions)) {
            $previousTransitionIndex = array_key_last($nonGraphTransitions);
        }

        if ($currentWeight !== null && $goalWeight !== null) {

            $actualWeightDiffrence = $goalWeight - $currentWeight; // to show negative value as well
            $weightDifference = ($goalWeight - $currentWeight) * 1000; // Convert kg to grams

            // Set the locale to French
            Carbon::setLocale('fr');
            $currentDate = Carbon::now();

            $weeksToGoal = abs($weightDifference) / 250;
            $fullWeeks = floor($weeksToGoal); // Full weeks
            $remainingDays = round(($weeksToGoal - $fullWeeks) * 7); // Remaining days
            $goalAchieveDate = $currentDate->copy()->addWeeks($fullWeeks)->addDays($remainingDays)->translatedFormat('j F Y');
            $fiveKgLossDate = $currentDate->copy()->addWeeks(20)->translatedFormat('j F');
            $tenKgLossDate = $currentDate->copy()->addWeeks(40)->format('Y-m-d'); // Remains in numeric format

            $newWeeksToGoal = abs($weightDifference) / 300;
            $newFullWeeks = floor($newWeeksToGoal); // Full weeks
            $newRemainingDays = round(($newWeeksToGoal - $newFullWeeks) * 7); // Remaining days
            $newGoalAchieveDate = $currentDate->copy()->addWeeks($newFullWeeks)->addDays($newRemainingDays)->translatedFormat('j F Y');
            $newFiveKgLossDate = $currentDate->copy()->addDays(117)->translatedFormat('j F');
            $newTenKgLossDate = $currentDate->copy()->addDays(234)->format('Y-m-d'); // Remains in numeric format


            $transition['trans_description'] = str_replace(
                ['{CurrentWeight}', '{CurrentDate}', '{FiveKgLossDate}', '{TenKgLossDate}', '{GoalWeight}', '{GoalAchieveDate}', '{NewGoalAchieveDate}', '{NewFiveKgLossDate}', '{NewTenKgLossDate}', '{ActualWeightDiffrence}'],
                [$currentWeight, $currentDate, $fiveKgLossDate, $tenKgLossDate, $goalWeight, $goalAchieveDate, $newGoalAchieveDate, $newFiveKgLossDate, $newTenKgLossDate, $actualWeightDiffrence],
                $transition['trans_description']
            );
        }

        return view('front.quiz.user_transition', [
            'transition' => $transition,
            'transitionDescription' => $transition['trans_description'],
            'allTransition' => $allTransition,
            'selectedAnswerId' => $selectedAnswerId,
            'prevIndex' => $previousTransitionIndex,
            'nextIndex' => $nextIndex,
            'backgroundColor' => $transition['color'],
            'backgroundImage' => $transition['transition_image'],
        ]);
    }

    public function showTransition($selectedAnswerId, $nextIndex)
    {
        $selectedAnswer = Answer::where(['id' => $selectedAnswerId])->first()->toArray();

        $answer = Answer::find($selectedAnswerId);
        //$bgcolor = $answer->question->quiz_group->color;
        //$bgimage = $answer->question->quiz_group->quiz_group_image;

        $ids = explode('|', $selectedAnswer['transition_id']);

        // Fetch transitions based on the IDs
        $transitions = Transition::whereIn('id', $ids)
            ->where('status', 1)
            ->orderByRaw("FIELD(id, " . implode(',', array_map('intval', $ids)) . ")")
            ->get()
            ->toArray();

        // Add transitions to the answer
        $answer['transitions'] = $transitions;

        if ($nextIndex < 0 || $nextIndex >= count($answer['transitions'])) {
            abort(404); // Or handle the error as needed
        }

        // Get the transition for the given index
        $transition = $answer['transitions'][$nextIndex];

        if ($transition['is_chart'] == 1 && $transition['is_paywall'] == 0) {
            return $this->graphTransition($answer['transitions'], $selectedAnswer['id']);
        } elseif ($transition['is_chart'] == 1 && $transition['is_paywall'] == 1) {
            return $this->paywallGraphTransition($answer['transitions'], $selectedAnswer['id'], $transition);
        }
        return view('front.quiz.user_transition', [
            'transition' => $transition,
            'transitionDescription' => $transition['trans_description'],
            'allTransition' => $answer['transitions'],
            'selectedAnswerId' => $selectedAnswerId,
            'prevIndex' => $nextIndex - 1,
            'nextIndex' => $nextIndex + 1,
            'backgroundColor' => $transition['color'],
            'backgroundImage' => $transition['transition_image'],
        ]);
    }

    private function saveMultipleAnswers($data, $sessionId)
    {
        UserAnswer::where(['question_id' => $data['questionId'], 'session_id' => $sessionId])->delete();
        foreach ($data['answer_id'] as $answerId) {
            $this->createUserAnswer($data, $answerId, $sessionId);
        }
    }

    private function saveUserInputAnswer($data, $sessionId)
    {
        $this->createUserAnswer($data, $data['answer_id'], $sessionId);
    }

    private function saveSingleAnswer($data, $sessionId)
    {
        $this->createUserAnswer($data, $data['answer_id'], $sessionId);
    }

    private function createUserAnswer($data, $answerId, $sessionId)
    {
        if (isset($data['answer_for']) && ($data['answer_for'] === 'steps_goal' || $data['answer_for'] === 'activity_level'  || $data['answer_for'] === 'trainer')) {
            $answerId = $data['answer_id'];
        }

        $getQuestionType = Quiz::where(['id' => $data['questionId']])->first();
        if ($getQuestionType) {
            $quesType = $getQuestionType['ques_type'];

            if ($data['answerType'] === 'multiple') {

                $getQuestionType = Quiz::where(['id' => $data['questionId']])->first();
                $quesType = $getQuestionType['ques_type'];

                $userAnswer = new UserAnswer;
                $userAnswer->question_id = $data['questionId'];
                $userAnswer->quiz_group_id = $data['quizGroupId'];
                $userAnswer->question_type = $quesType;
                $userAnswer->answer_type = $data['answerType'];
                $userAnswer->answer_id = $answerId;
                $userAnswer->session_id = $sessionId; // Set the session ID
                $userAnswer->save();
            } else {
                $storedUserAnswer = UserAnswer::where(['question_id' => $data['questionId'], 'session_id' => $sessionId])->first();
                if (!empty($storedUserAnswer) && !is_null($storedUserAnswer)) {
                    $storedUserAnswer->update([
                        'question_id' => $data['questionId'],
                        'quiz_group_id' => $data['quizGroupId'],
                        'answer_type' => $data['answerType'],
                        'answer_id' => $answerId,
                    ]);
                    $userAnswer['id'] = $storedUserAnswer->id;
                } else {
                    $getQuestionType = Quiz::where(['id' => $data['questionId']])->first();
                    $quesType = $getQuestionType['ques_type'];

                    $userAnswer = new UserAnswer;
                    $userAnswer->question_id = $data['questionId'];
                    $userAnswer->quiz_group_id = $data['quizGroupId'];
                    $userAnswer->question_type = $quesType;
                    $userAnswer->answer_type = $data['answerType'];
                    $userAnswer->answer_id = $answerId;
                    $userAnswer->session_id = $sessionId; // Set the session ID
                    $userAnswer->save();
                }
            }

            if (!isset($data['email_marketing']) && empty($data['email_marketing'])) {
                $data['email_marketing'] = 0;
            }
            if (($data['answerType'] === 'userInput') || $getQuestionType['ques_for'] === 'trainer' || $getQuestionType['ques_for'] === 'steps_goal' || $getQuestionType['ques_for'] === 'activity_level') {
                $getKey = Answer::where(['question_id' => $data['questionId'], 'id' => $answerId])->first('ques_answers')->toArray();

                if ($getQuestionType['ques_for'] === 'steps_goal') {
                    $key = "steps_goal";
                    if ($data['answer_index'] === "0") {
                        $keyValue = 5000;
                    }
                    if ($data['answer_index'] === "1") {
                        $keyValue = 7500;
                    }
                    if ($data['answer_index'] == "2") {
                        $keyValue = 10000;
                    }
                    if ($data['answer_index'] === "3" || $data['answer_index'] >= "3") {
                        $keyValue = 12500;
                    }
                }

                if ($getQuestionType['ques_for'] === 'activity_level') {
                    $key = 'activity_level';
                    if ($data['answer_index'] === "0") {
                        $keyValue = 1.2375;
                    }
                    if ($data['answer_index'] === "1") {
                        $keyValue = 1.2375;
                    }
                    if ($data['answer_index'] == "2") {
                        $keyValue = 1.24;
                    }
                    if ($data['answer_index'] === "3" || $data['answer_index'] >= "3") {
                        $keyValue = 1.38;
                    }
                }
                if ($getQuestionType['ques_for'] === 'trainer') {
                    $key = 'trainer';
                    if ($data['answer_index'] === "0") {
                        $keyValue = 'BananaMo';
                        $trainerDiscountMsg = 'BananaGANG';
                    }
                    elseif ($data['answer_index'] === "1") {
                        $keyValue = 'Morgan';
                        $trainerDiscountMsg = 'TheSlowMethod';
                    }
                    elseif ($data['answer_index'] === "2") {
                        $keyValue = 'Both';
                        $trainerDiscountMsg = 'RevealClub';
                    }
                    elseif ((int)$data['answer_index'] > 2) {
                        $keyValue = 'unknown';
                        $trainerDiscountMsg = 'Offre spéciale';
                    }
                    Session::put('trainerDiscountMsg',$trainerDiscountMsg);
                }

                if ($getQuestionType['ques_for'] != 'activity_level' && $getQuestionType['ques_for'] != 'steps_goal' && $getQuestionType['ques_for'] != 'trainer') {
                    $key = $getKey['ques_answers'];
                    $keyValue = $data['answer'];
                }

                if (strtolower($key) === 'gender') {
                    Session::put('gender', strtolower($keyValue));
                }

                $userAnsweredData = session('userAnsweredData', []);
                $userAnsweredData[] = ['key' => $key, 'value' => $keyValue];
                session(['userAnsweredData' => $userAnsweredData]);

                $storedUserReferenceAnswer = UserReferenceAnswer::where(['user_answered_id' => $userAnswer['id'], 'question_id' => $data['questionId'], 'session_id' => $sessionId])->first();
                if (!empty($storedUserReferenceAnswer) && !is_null($storedUserReferenceAnswer)) {
                    if (isset($data['answer_for']) && ($data['answer_for'] === 'steps_goal' || $data['answer_for'] === 'activity_level' || $data['answer_for'] === 'trainer')) {
                        $answer = $getKey['ques_answers'];
                    } else {
                        $answer = $keyValue;
                    }
                    $storedUserReferenceAnswer->update([
                        'user_answered_id' => $userAnswer['id'],
                        'question_id' => $data['questionId'],
                        'user_answer_id' => $answerId,
                        'answer_type' => $data['answerType'],
                        'key' => strtolower($key),
                        'value' => $keyValue,
                        'answer' => $answer,
                        'email_marketing' => $data['email_marketing']
                    ]);
                } else {
                    $userInput = new UserReferenceAnswer;
                    // Fix: Handle $userAnswer as object or array
                    if (is_array($userAnswer)) {
                        $userInput->user_answered_id = $userAnswer['id'];
                    } elseif (is_object($userAnswer)) {
                        $userInput->user_answered_id = $userAnswer->id;
                    }
                    $userInput->question_id = $data['questionId'];
                    $userInput->quiz_group_id = $data['quizGroupId'];
                    $userInput->user_answer_id = $answerId;
                    $userInput->answer_type = $data['answerType'];
                    $userInput->key = strtolower($key);
                    $userInput->value = $keyValue;
                    if (isset($data['answer_for']) && ($data['answer_for'] === 'steps_goal' || $data['answer_for'] === 'activity_level' || $data['answer_for'] === 'trainer')) {
                        $userInput->answer = $getKey['ques_answers'];
                    } else {
                        $userInput->answer = $keyValue;
                    }
                    $userInput->email_marketing = $data['email_marketing'];
                    $userInput->session_id = $sessionId;
                    $userInput->save();
                }
            }
        }
    }

    private function findSelectedAnswer($answerType, $getQuesWithAns, $selectedAnswerId, $questionId)
    {
        if ($answerType === 'single' || $answerType === 'userInput') {

            foreach ($getQuesWithAns['answers'] as $answer) {
                if ($getQuesWithAns['ques_for'] === 'steps_goal' || $getQuesWithAns['ques_for'] === 'activity_level'|| $getQuesWithAns['ques_for'] === 'trainer') {
                    $rawAnswerId = explode(",", $selectedAnswerId);
                    $selectedAnswerId = $rawAnswerId[0];
                }
                if ($answer['id'] == $selectedAnswerId) {
                    return $answer;
                }
            }
        } else {
            $answer = Answer::where(['question_id' => $questionId])->first()->toArray();
            $ids = explode('|', $answer['transition_id']);

            // Fetch transitions based on the IDs in the specified order
            $transitions = Transition::whereIn('id', $ids)
                ->where('status', 1)
                ->orderByRaw("FIELD(id, " . implode(',', array_map('intval', $ids)) . ")")
                ->get()
                ->toArray();
            if ($transitions !== []) {
                $answer['transitions'] = $transitions;
            } else {
                $answer['transitions'] = [];
            }
            return $answer;
        }
        return null;
    }

    public function assignProgram()
    {
        $sessionId = Session::get('quiz_session_id');
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

        // Get level id based on points
        $levelId = $this->getLevelIdByPoints($totalPoints);

        // Assign cardio program
        $cardioProgram = null;
        if (!empty($cardioProgramIds)) {
            $cardioProgram = Program::whereIn('cardio_id', $cardioProgramIds)
                ->where('status', 1)
                ->where('program_type', 'cardio')
                ->where('level_id', $levelId)
                ->first();
        }
        if (!$cardioProgram) {
            $cardioProgram = Program::where('status', 1)
                ->where('program_type', 'cardio')
                ->where('level_id', $levelId)
                ->first();
        }

        // Assign muscle program
        $muscleProgram = null;
        if (!empty($muscleProgramIds)) {
            $muscleProgram = Program::whereIn('muscle_strength_id', $muscleProgramIds)
                ->where('status', 1)
                ->where('program_type', 'muscle')
                ->where('level_id', $levelId)
                ->first();
        }
        if (!$muscleProgram) {
            $muscleProgram = Program::where('status', 1)
                ->where('program_type', 'muscle')
                ->where('level_id', $levelId)
                ->first();
        }

        // Return both programs as array
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

    public function getPackage()
    {
        $getPackages = Plan::where('status', 1)->get();

        $quizSessionId = Session::get('quiz_session_id');

        $answers = UserReferenceAnswer::where('session_id', $quizSessionId)
            ->whereIn('key', ['current_weight', 'desire_weight'])
            ->pluck('value', 'key');

        $currentWeight = isset($answers['current_weight']) ? $answers['current_weight'] : 0;
        $desiredWeight = isset($answers['desire_weight']) ? $answers['desire_weight'] : 0;
        $differenceWeight = abs($currentWeight - $desiredWeight);

        $newWeeksToGoal = abs($differenceWeight) / 300;
        // Set the locale to French
        Carbon::setLocale('fr');
        $currentDate = Carbon::now();
        $newGoalAchieveDate = $currentDate->copy()->addWeeks($newWeeksToGoal)->translatedFormat('j F Y');
        $newFiveKgLossDate = $currentDate->copy()->addDays(117)->translatedFormat('j F');
        $newTenKgLossDate = $currentDate->copy()->addDays(234)->format('Y-m-d'); // Remains in numeric format

        $graphData = $this->generateWeightData($currentWeight, $desiredWeight);

        $lastDate = $this->calculateAchievementDate($currentWeight, $desiredWeight);

        return view('front.quiz.packages', [
            'getPackages' => $getPackages,
            'graphData' => $graphData,
            'lastDate' => $lastDate,
            'desiredWeight' => $desiredWeight,
            'differenceWeight' => $differenceWeight,
            'newGoalAchieveDate' => $newGoalAchieveDate
        ]);
    }

    private function generateWeightData($currentWeight, $desiredWeight)
    {
        $weightChangeRate = 1; // kg per 5 days
        $daysPerChange = 5;
        $averageDaysPerMonth = 30; // Approximate average days in a month
        $totalWeightChange = $currentWeight - $desiredWeight;
        $totalDays = $totalWeightChange * $daysPerChange;
        $totalMonths = ceil($totalDays / $averageDaysPerMonth);
        $weightChangePerMonth = ($weightChangeRate * $averageDaysPerMonth) / $daysPerChange;

        $labels = [];
        $data = [];
        $current = $currentWeight;
        $currentDate = Carbon::now(); // Current date
        $endDate = $currentDate->copy()->addDays($totalDays);
        $interval = $currentDate->copy()->startOfMonth(); // Start from the current month

        while ($interval->lessThanOrEqualTo($endDate)) {
            $labels[] = $interval->format('M Y'); // Label in "Month Year" format
            $data[] = max($current, $desiredWeight); // Ensure the weight does not go below the desired weight

            // Move to the next month
            $interval->addMonth();

            // Calculate weight at the end of this month
            $daysInMonth = $interval->copy()->daysInMonth;
            $weightLostThisMonth = ($daysInMonth / $daysPerChange) * $weightChangeRate;

            $current -= $weightLostThisMonth;

            // Ensure weight does not go below the desired weight
            if ($current < $desiredWeight) {
                $current = $desiredWeight;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function calculateAchievementDate($currentWeight, $desiredWeight)
    {
        $weightChangeRate = 1; // kg per 5 days
        $daysPerChange = 5;

        // Calculate the total weight to lose
        $totalWeightChange = $currentWeight - $desiredWeight;

        // Calculate total days required
        $totalDays = $totalWeightChange * $daysPerChange;

        // Current date
        $currentDate = Carbon::now();

        // Calculate the exact date when the desired weight will be achieved
        $achievementDate = $currentDate->addDays($totalDays);

        // Format the date as "Month Day, Year"
        return $achievementDate->format('F j, Y'); // e.g., "March 11, 2025"
    }

    public function payment(Request $request, $id = null)
    {
        Session::put(['planId' => $id]);
        return response()->json(['success' => true]);
    }

    public function getPackageDetails($id)
    {
        $package = Plan::find($id);
        if ($package) {

            // If this is the yearly commitment plan (monthly payments for 12 months)
            if ((int)$package->is_yearly_commitment === 1) {

                $monthlyPrice = (float) $package->discprice;          // User pays this every month
                $totalCommitment = (float) $package->total_disc_price; // Total yearly amount (12 × monthly)
                $originalTotal = (float) $package->total_price;        // UI-only display

                // Discount calculation for UI
                $priceDifference = round($originalTotal - $totalCommitment, 2);
                $dicsPercent = $originalTotal > 0
                    ? (int)(($priceDifference * 100) / $originalTotal)
                    : 0;

                // Klarna commission logic
                $klarnaCommision = 0;
                if ($package->for_klarna == 1) {
                    $klarnaCommision = round($totalCommitment * 0.05, 2);
                }

                // User pays only the monthly amount today
                $finalPrice = $monthlyPrice;

                // Message for UI (special UI text)
                $message = "Plan annuel payé mensuellement : " . $monthlyPrice . "€/mois. Total sur 12 mois : " . $totalCommitment . "€";
                $trainerDiscountMsg = Session::get('trainerDiscountMsg');
                $discMsg1 = $trainerDiscountMsg . '-(' . $dicsPercent . '%)';
                return response()->json([
                    'success' => true,
                    'package' => [
                        'name' => $package->name,
                        'is_yearly_commitment' => 1,
                        'monthly_price' => $monthlyPrice,
                        'total_price' => $originalTotal,
                        'total_disc_price' => $totalCommitment,
                        'discount_amount' => $priceDifference,
                        'discount_percentage' => $discMsg1,
                        'final_price' => $finalPrice, // amount charged today
                        'klarnaCommision' => $klarnaCommision,
                        'message' => $message
                    ]
                ]);
            }

            // -------------------------------------------
            // NORMAL PACKAGES BELOW (unchanged logic)
            // -------------------------------------------

            $priceDifference = round((float) $package['total_price'] - (float) $package['total_disc_price'], 2);
            $dicsPercent = (int)(($priceDifference * 100) / (float) $package['total_price']);

            if ($package->plan_type == 0) {
                $message = "Économise -" . $package->total_disc_price . "€ sur ton 1 mois (-" . $dicsPercent . "% off)";
            }
            if ($package->plan_type == 1) {
                $message = "Économise -" . $package->total_disc_price . "€ sur ton 3 mois (-" . $dicsPercent . "% off)";
            }
            if ($package->plan_type == 2) {
                $message = "Économise -" . $package->total_disc_price . "€ sur ton plan annuel (-" . $dicsPercent . "% off)";
            }
            if ($package->plan_type == 3) {
                $message = "Économise -" . $package->total_disc_price . "€ sur ton 24 mois (-" . $dicsPercent . "% off)";
            }
            if ($package->plan_type == 4) {
                $message = "Économise -" . $package->total_disc_price . "€ sur ton 6 mois (-" . $dicsPercent . "% off)";
            }

            $klarnaCommision = 0;
            $finalPrice = $package->total_disc_price;
            if($package->for_klarna == 1){
                $klarnaCommision = round($package->total_disc_price * 0.05, 2);
                $finalPrice = $package->total_disc_price + $klarnaCommision;
            }
            $trainerDiscountMsg = Session::get('trainerDiscountMsg');
            $discMsg = $trainerDiscountMsg . '-(' . $dicsPercent . '%)';

            return response()->json([
                'success' => true,
                'package' => [
                    'name' => $package->name,
                    'total_price' => $package->total_price,
                    'discount_amount' => $priceDifference,
                    'discount_percentage' => $discMsg,
                    'klarnaCommision' => $klarnaCommision,
                    'final_price' => $finalPrice,
                    'message' => $message
                ]
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function checkout(Request $request)
    {
        Session::forget('answered_questions');
        try {
            Log::info('Checkout process started', ['payment_method' => $request->payment_method, 'plan_id' => $request->planId]);

            // Handle Klarna payment
            if ($request->payment_method === 'klarna') {
                Log::info('Processing Klarna payment', ['plan_id' => $request->planId]);
                $plan = Plan::findOrFail($request->planId);
                if($plan->for_klarna == 0){
                    Log::warning('Plan not available for Klarna', ['plan_id' => $request->planId]);
                    return redirect()->back()->with('error', 'Ce plan n\'est pas disponible pour Klarna.');
                }
                return $this->redirectToKlarna($request);
            }

            $sessionPlanId = Session::get('planId');
            $sessionId = Session::get('quiz_session_id');

            Log::info('Processing non-Klarna payment', ['session_plan_id' => $sessionPlanId]);
            // $getPackage = Plan::find($sessionPlanId);
            $getPackage = Plan::find($request->planId);
            $getEmail = UserReferenceAnswer::where(['session_id' => $sessionId, 'key' => 'email'])->first();
            $email = $getEmail['value'];
            Log::info('Email retrieved for checkout', ['email' => $email]);

            if (!Auth::guard('user')->check()) {
                Log::info('Creating new user for email: ' . $email);
                $user = User::where('email', $email)->first();
                if ($user) {
                    if ($user->isSubscribedUser != 2) {
                        Log::warning('Email already exists with another account', ['email' => $email]);
                        return redirect()->back()->with('error', 'Cet email existe déjà avec un autre compte.');
                    }
                    // Case 2: User exists and isSubscribedUser == 2
                    if ($user->isSubscribedUser == 2) {
                        $user->delete();
                    }
                }
                $user = $this->createNewUser($email,1);
                Log::info('New user created', ['user_id' => $user->id, 'email' => $email]);
            } else {
                $user = Auth::guard('user')->user();
                Log::info('Using existing user', ['user_id' => $user->id, 'email' => $user->email]);
            }


            if (isset($request->stripeToken)) {
                Log::info('Processing Stripe payment', ['user_id' => $user->id, 'stripe_token' => $request->stripeToken]);
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

                try {
                    Log::info('Creating Stripe customer', ['user_email' => $user->email]);
                    $stripeCustomer = $this->createStripeCustomer($stripe, $user->email, $request->stripeToken);
                    $card = $this->getStripeCard($stripeCustomer->id, $stripeCustomer->default_source);

                    $this->saveStripeCustomer($user->id, $stripeCustomer, $card);
                    Log::info('Stripe customer created', ['stripe_customer_id' => $stripeCustomer->id]);

                    if ((int)$getPackage->is_yearly_commitment === 1) {
                        $amount = (float) $getPackage->discprice ?? $getPackage->price;
                    } else {
                        $amount = $getPackage->total_disc_price ?? $getPackage->total_price;
                    }
                    Log::info('Creating Stripe plan', ['amount' => $amount]);
                    $plan = $this->createStripePlan($stripe, $getPackage, $amount);

                    // Process promo code if any
                    $couponId = null;
                    if ($request->promocode_discount != 0 && $request->promoCodeId != 0 && $request->promoCodeId != null) {
                        Log::info('Applying promocode discount', ['discount' => $request->promocode_discount, 'promo_code_id' => $request->promoCodeId]);
                        // Create the coupon
                        $discountAmount = abs(floatval($request->promocode_discount));
                        $percentOff = ($discountAmount / $amount) * 100; // Calculate percent off

                        $coupon = $stripe->coupons->create([
                            'percent_off' => round($percentOff),
                            'duration' => 'once',
                        ]);
                        $couponId = $coupon->id;
                        $promoUsage = PromoCodeUsage::create([
                            'user_id' => $user->id,
                            'promo_code_id' => $request->promoCodeId,
                            'applied_date' => now()->format('Y-m-d'),
                            'total_discount_price' => $discountAmount,
                        ]);
                    }

                    $isTrial = $getPackage->freetrial; // 1 if freetrial is enabled, 0 if not
                    $trialDays = $getPackage->trialdays; // Get the dynamic trial days value

                    // Log subscription creation
                    Log::info('Creating Stripe subscription', ['user_id' => $user->id, 'plan_id' => $plan->id]);
                    $subscription = $this->createStripeSubscription($stripe, $stripeCustomer->id, $plan->id, $isTrial, $trialDays, $couponId);
                    if ($subscription->latest_invoice) {
                        $latestInvoice = $stripe->invoices->retrieve($subscription->latest_invoice, ['expand' => ['payment_intent']]);
                        $paymentIntent = $latestInvoice->payment_intent ?? null;

                        if ($paymentIntent && in_array($paymentIntent->status, ['requires_action', 'requires_payment_method'])) {

                            return redirect()->route('stripe.auth', [
                                'user_id' => $user->id,
                                'invoiceID' => $subscription->latest_invoice,
                                'plan_id' => $getPackage->id,
                                'payment_intent' => $paymentIntent->id,
                                'payment_intent_client_secret' => $paymentIntent->client_secret,
                            ])->with('message', '3D Secure authentication is required.');
                        }
                    }

                    if ($subscription->status == 'active' || $subscription->status == 'trialing' || ($subscription->status == 'incomplete' && $latestInvoice && $latestInvoice->status == 'paid')) {
                        Log::info('Subscription is active or trialing', ['subscription_id' => $subscription->id]);
                        $this->saveSubscriptionData($user->id, $subscription, $getPackage->id);
                        // User::where(['id' => $user->id, 'email' => $user->email])->update(['isQuestionsAttempted' => 1]);
                        Log::info('Subscription data saved', ['user_id' => $user->id, 'subscription_id' => $subscription->id]);
                        // More logic for user and email sending
                        $subData = [
                            'name' => 'Subscriber',
                            'subscriptionId' => $subscription->id,
                            'startDate' => date("Y-m-d", $subscription->current_period_start),
                            'planName' => $getPackage->name,
                            'billingDate' => Carbon::now()->format('Y-m-d'),
                            'nextBillingDate' => date("Y-m-d", $subscription->current_period_end),
                            'totalAmountCharged' => $subscription->plan['amount'] / 100,
                        ];

                        Log::info('Sending email for finish registration', ['user_email' => $user->email]);
                        $mailStatus = $this->sendMailForFinishRegistration($user->email);
                        $customerIo = new CustomerIoService();
                        $customerIo->sendTransactionalEmail($user->email, '8', $subData);

                        if (!$mailStatus) {
                            Log::error('Email sending failed for user: ' . $user->email);
                        }
                            Log::info('Assigning program to user', ['user_id' => $user->id]);
                            $assignedProgram = $this->assignProgram();
                            $this->saveUserProgram($user->id, $assignedProgram);

                            Log::info('Saving user evolution data');
                            $userEvolutionData = UserReferenceAnswer::where(['session_id' => $sessionId])->get();
                            $this->saveUserEvolutionData($user->id, $userEvolutionData);

                            Log::info('Checking if WebView flag is set');
                            $token = session()->get('resetToken');
                            $isWebView = Session::get('isWebView');

                            if (!is_null($isWebView) && $isWebView == 1) {
                                // Redirect to a view with an ID in the query string for mobile web view
                                Log::info('WebView is enabled, redirecting accordingly');
                                Session::forget('quiz_session_id');
                                session(['userId' => $user->id]);
                            }
                            Auth::guard('user')->login($user);
                            Log::info('Redirecting user to address page', ['user_id' => $user->id]);
                            return redirect()->to('/address');
                            // return redirect()->to('/payment-success');

                    } else {
                        Log::error('Subscription failed', ['subscription_id' => $subscription->id]);
                        User::find($user->id)->delete();
                    }
                } catch (Exception $e) {
                    Log::error('Stripe payment failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                    $customerIo = new CustomerIoService();
                    $customerIo->sendTransactionalEmail($user->email, '10', ['name' => 'Valued Customer']);
                    User::find($user->id)->delete();
                    return back()->with('error',  $e->getMessage());
                }
            }

            Log::info('Checkout process completed');
        } catch (Exception $e) {
            Log::error('Checkout process failed', ['error' => $e->getMessage(), 'user' => isset($user) ? $user->id : 'N/A']);
            if(isset($user)) {
                $customerIo = new CustomerIoService();
                $customerIo->sendTransactionalEmail($user->email, '10', ['name' => 'Valued Customer']);
                User::find($user->id)->delete();
            }
            return view('page500');
        }
    }

    private function createNewUser($email, $incompletePayment = 0)
    {
        $subscribed = 0;
        if($incompletePayment == 1){ // incomplete payment
            $subscribed = 2;
        }

        $user = new User();
        $user->name = "";
        $user->email = $email;
        $user->password = Hash::make('password');
        $user->type = 2;
        $user->mobile = "";
        $user->isQuestionsAttempted = 0;
        $user->isSubscribedUser = $subscribed;
        $user->status = 1;
        $user->save();

        return $user;
    }

    private function createStripeCustomer($stripe, $email, $token = null)
    {
        $data = [
            "name" => $email,
            "email" => $email,
        ];

        if ($token) {
            $data['source'] = $token;
        }

        return $stripe->customers->create($data);
    }

    private function getStripeCard($customerId, $defaultSource)
    {
        if (empty($defaultSource)) {
            return null; // No card to retrieve
        }
        $response = Http::withBasicAuth(env('STRIPE_SECRET'), '')
            ->get("https://api.stripe.com/v1/customers/{$customerId}/cards/{$defaultSource}");
        return json_decode($response);
    }

    private function saveStripeCustomer($userId, $stripeCustomer, $card)
    {
        $customer = new StripeCustomer;
        $customer->user_id = $userId;
        $customer->card_details = json_encode($stripeCustomer);
        $customer->card = json_encode($card);
        $customer->save();
    }

    private function createStripePlan($stripe, $package, $amount)
    {
        $interval = 'month';
        if ($package->plan_type == '0') {
            $interval_count = 1;
        } elseif ($package->plan_type == '1') {
            $interval_count = 3;
        } elseif ($package->plan_type == '2') {
            $interval_count = 12;
        } elseif ($package->plan_type == '3') {
            $interval_count = 24;
        } elseif ($package->plan_type == '4') {
            $interval_count = 6;
        }

        return $stripe->plans->create([
            "product" => ["name" => $package->name],
            "amount" => $amount * 100,
            "currency" => 'EUR',
            "interval" => $interval,
            "interval_count" => $interval_count,
            "metadata" => [
                "package_id" => $package->id,
                "package_name" => $package->name,
                "is_yearly_commitment" => isset($package->is_yearly_commitment) ? $package->is_yearly_commitment : 0
            ],
        ]);
    }

    private function createStripeSubscription($stripe, $customerId, $planId, $isTrial, $trialDays = null, $couponId = null)
    {
        $subscriptionParams = [
            "customer" => $customerId,
            "items" => [["plan" => $planId]],
        ];

        // Add trial period if applicable
        if ($isTrial == 1 && $trialDays > 0) {
            $subscriptionParams['trial_period_days'] = $trialDays;
        }
        // Add coupon if provided
        if ($couponId) {
            $subscriptionParams['coupon'] = $couponId;
        }

        return $stripe->subscriptions->create($subscriptionParams);
    }

    private function saveSubscriptionData($userId, $subscriptionDetail, $planId, $withKlarna = null)
    {
        $startDate = date("Y-m-d", $subscriptionDetail->current_period_start);
        $endDate = date("Y-m-d", $subscriptionDetail->current_period_end);
        $customer_id = (is_object($subscriptionDetail->customer))?$subscriptionDetail->customer->id:$subscriptionDetail->customer;
        // $customer_id = $subscriptionDetail->customer;
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
        $user = User::where('id', $userId)->update(['isQuestionsAttempted' => 1, 'isSubscribedUser' => 1]);
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

            Session::put(['resetToken' => $token]);
            return true;
            // Send the email
            // if (Mail::to($email)->send(new FinishRegistration($user, $token))) {
            // } else {
            //     return false;
            // }
        }
    }

    public function finishRegistration(Request $request, $token)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            // echo "<pre>"; print_r($data); die;

            $rules = [
                'email' => 'required|email',
                'password' => [
                    'required',
                    'min:6',
                    // 'regex:/[a-z]/',     // must contain at least one lowercase letter
                    // 'regex:/[A-Z]/',     // must contain at least one uppercase letter
                    // 'regex:/[0-9]/',     // must contain at least one digit
                    // 'regex:/[@$!%*#?&]/' // must contain a special character
                ],
                'cnf_password' => 'required_with:password|same:password'
            ];

            $messages = [
                'email.requierd' => 'Please enter your email',
                'email.email' => 'Please enter valid email',
                'password.required' => 'Please enter password',
                'password.min' => 'Password should be six character long',
                // 'password.regex' => 'Password must contains at least one lowercase letter, one uppercase letter, one digit, and a special character.',
                'cnf_password.same' => 'Password and Confirm Password must be same'
            ];

            $validation = Validator::make($data, $rules, $messages);

            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation->messages());
            }

            $resetPassData = DB::table('password_resets')->where(['token' => $token])->first();
            if (!$resetPassData) {
                return back()->withInput()->with('error', 'Your registration link is expired Or You already registered with this link. Please login OR contact Administrator.');
            }

            if ($resetPassData->email === $data['email']) {
                $user = User::where('email', $data['email'])->update(['password' => Hash::make($request->password), 'isQuestionsAttempted' => 1, 'isSubscribedUser' => 1, 'iosSubscribedUser' => 1]);

                $userData = User::where('email', $data['email'])->first();

                $resetPassData = DB::table('password_resets')->where(['token' => $token])->delete();

                Auth::guard('user')->loginUsingId($userData->id);

                $mailRegisteration = $this->sendMailForCompleteRegistration($data['email']);
                return redirect('/referral-source')->with('success', "You have been successfully logged in");
                // if(Auth::guard('user')->user()->isQuestionsAttempted === 0){
                //     return redirect('/process-quiz')->with('success', "Please attempt all Questions.");
                // }else{
                //     return redirect('/dashboard')->with('success', "You have been successfully logged in");
                // }
            } else {
                return back()->with('error', 'Invalid Email!');
            }
        }
        $resetPassData = DB::table('password_resets')->where(['token' => $token])->first();
        if (!$resetPassData) {
            return back()->with('error', 'Your registration link is expired Or You already registered with this link. Please login OR contact Administrator.');
        }

        $user = User::where(['email' => $resetPassData->email])->first();
        if ($user) {
            $email = $user->email;
        }
        return view('front.auth.finishRegistration', ['email' => $email, 'token' => $token]);
    }

    private function sendMailForCompleteRegistration($email)
    {
        $user = User::where(['email' => $email])->first();
        if ($user) {
            if (Mail::to($email)->send(new CompleteRegistration($user))) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function referralSource(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $userId = Auth::guard('user')->id();

            $user = User::where('id', $userId)->first();
            // echo "<pre>";print_r($data);die;
            $user->referral_source = $data['source'];
            $user->update();

            return response()->json(['success' => true]);
        }

        return view('front.auth.referralSource');
    }

    public function address(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'country' => 'required',
                'city' => 'required',
                'address' => 'required',
                'postal_code' => 'required',
            ];

            $messages = [
                'first_name.required' => 'Please enter your name',
                'last_name.required' => 'Please enter your name',
                'country.required' => 'Please select a country',
                'city.required' => 'Please enter your city',
                'address.required' => 'Please enter your address',
                'postal_code.required' => 'Please enter your postal code',
                'postal_code.numeric' => 'Postal code must be a valid number',
            ];

            $validation = Validator::make($data, $rules, $messages);

            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation->messages());
            }

            $userId = Auth::guard('user')->id();

            $user = User::where('id', $userId)->first();

            $user->first_name = $data['first_name'] ?? $user->first_name;
            $user->last_name = $data['last_name'] ?? $user->last_name;
            $user->country = $data['country'] ?? $user->country;
            $user->city = $data['city'] ?? $user->city;
            $user->address = $data['address'] ?? $user->address;
            $user->postal_code = $data['postal_code'] ?? $user->postal_code;
            $user->company = $data['company'] ?? $user->company;
            $user->mobile = $data['Phone'] ?? $user->mobile;
            $user->update();

            return redirect('/payment-success');
            // return redirect('/download')->with('success', "You have been successfully logged in");
        }
        return view('front.auth.address');
    }

    public function download(Request $request)
    {
        return view('front.auth.download');
    }

    public function countries(Request $request)
    {
        $path = public_path('webAssets/json/countries.json');

        if (file_exists($path)) {
            $data = File::get($path);
            return response()->json(json_decode($data, true), 200);
        }

        return response()->json(['error' => 'Countries data not found.'], 404);
    }

    public function checkDiscountCode(Request $request, $planId = null)
    {
        // Get the code from the request
        $code = $request->input('code');

        $promocode = Promocode::where(['promocode' => $code])->where('start_date', '<=', Carbon::now()->format('Y-m-d'))->where('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();

        if (!empty($promocode)) {
            if (is_null($planId)) {
                $planId = Session::get('planId');
            }
            $package = Plan::find($planId);
            if ($package) {
                // echo "<pre>"; print_r($planId); die;
                // Calculate the required details
                $priceDifference = ((float) $package['total_price'] - (float) $package['total_disc_price']);
                $dicsPercent = (int)(($priceDifference * 100) / (float) $package['total_price']);

                if ($promocode['discount_type'] === 'percentage') {
                    $promocodeDiscountValue = (int)$promocode['discount_value'];
                    $promocodeDisc = (int)(($package->total_disc_price * $promocode['discount_value']) / 100);
                    $finalPrice = round($package->total_disc_price - $promocodeDisc, 2);
                } else {
                    $promocodeDiscountValue = $promocode['discount_value'];
                    $promocodeDisc = $promocode['discount_value'];
                    $finalPrice = round($package->total_disc_price - $promocodeDisc, 2);
                }

                if ($package->plan_type == 0 && $promocode['discount_type'] === 'percentage') {
                    $message = "Économise -" . $finalPrice . "€ sur ton 1 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "% supplémentaires";
                }
                if ($package->plan_type == 0 && $promocode['discount_type'] === 'amount') {
                    $message = "Économise -" . $finalPrice . "€ sur ton 1 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "€ supplémentaires";
                }

                if ($package->plan_type == 1 && $promocode['discount_type'] === 'percentage') {
                    $message = "Économise -" . $finalPrice . "€ sur ton 6 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "% supplémentaires";
                }
                if ($package->plan_type == 1 && $promocode['discount_type'] === 'amount') {
                    $message = "Économise -" . $finalPrice . "€ sur ton 6 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "€ supplémentaires";
                }

                if ($package->plan_type == 2 && $promocode['discount_type'] === 'percentage') {
                    $message = "Économise -" . $package->total_disc_price . "€ sur ton plan annuel (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "% supplémentaires";
                }
                if ($package->plan_type == 2 && $promocode['discount_type'] === 'amount') {
                    $message = "Économise -" . $package->total_disc_price . "€ sur ton plan annuel (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "€ supplémentaires";
                }

                if ($package->plan_type == 3 && $promocode['discount_type'] === 'percentage') {
                    $message = "Économise -" . $package->total_disc_price . "€ sur ton 24 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "% supplémentaires";
                }
                if ($package->plan_type == 3 && $promocode['discount_type'] === 'amount') {
                    $message = "Économise -" . $package->total_disc_price . "€ sur ton 24 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "€ supplémentaires";
                }
                if ($package->plan_type == 4 && $promocode['discount_type'] === 'percentage') {
                    $message = "Économise -" . $package->total_disc_price . "€ sur ton 6 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "% supplémentaires";
                }
                if ($package->plan_type == 4 && $promocode['discount_type'] === 'amount') {
                    $message = "Économise -" . $package->total_disc_price . "€ sur ton 6 mois (-" . $dicsPercent . "% off) avec " . $promocodeDiscountValue . "€ supplémentaires";
                }
                return response()->json([
                    'isValid' => true,
                    'promocode' => [
                        'promocodeId' => $promocode['id'],
                        'promocode_discount_value' => $promocodeDiscountValue,
                        'promocode_discount' => $promocodeDisc,
                        'final_price' => $finalPrice,
                        'message' => $message
                    ]
                ]);
            }
            // return response()->json(['isValid' => true, 'promocode' => $promocode]);
        } else {
            return response()->json(['isValid' => false]);
        }
    }

    public function validateMerchant(Request $request)
    {
        $validationUrl = $request->input('validationUrl');

        $client = new Client();
        $response = $client->post($validationUrl, [
            'json' => [
                'merchantIdentifier' => 'merchant.revealApp',
                'displayName' => 'Your Store Name',
                'initiative' => 'web',
                'initiativeContext' => 'https://8670-43-251-73-77.ngrok-free.app/'
            ],
            'cert' => 'public\RevealPem.pem', // Apple Pay certificate
            // 'ssl_key' => 'path/to/your/private-key.pem'
        ]);

        return $response->getBody()->getContents();
    }

    public function processPayment(Request $request)
    {
        $paymentData = $request->input('paymentData');

        // Process paymentData with a Payment Processor (e.g., Stripe)

        return response()->json(['success' => true]);
    }

    public function threeDSuccessPayment(Request $request){

        Log::info('3D Success Payment started.');
        $u = $request->u;
        Log::info('Decoded base64 string for u: ' . $u);

        $u = base64_decode($u);
        parse_str($u, $output);

        Log::info('Parsed output: ', $output);
        $invoiceId = $output['invoiceID'];
        $user_id = $output['user_id'];
        $plan_id = $output['plan_id'];

        $sessionId = Session::get('quiz_session_id');
        Log::info('Session ID retrieved: ' . $sessionId);

        $getPackage = Plan::find($plan_id);
        Log::info('Package found: ', ['plan_id' => $plan_id, 'package_name' => $getPackage ? $getPackage->name : 'not found']);

        $user = User::find($user_id);
        Log::info('User found: ', ['user_id' => $user_id, 'user_email' => $user ? $user->email : 'not found']);

        // Stripe::setApiKey(config('services.stripe.secret'));
        try {
            Log::info('Initializing Stripe client.');
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

            Log::info('Retrieving invoice with invoiceId: ' . $invoiceId);
            // Retrieve the invoice
            $invoice = $stripe->invoices->retrieve($invoiceId, ['expand' => ['payment_intent']]);

            $subscriptionId = $invoice->subscription;
            Log::info('Subscription retrieved from invoice: ' . $subscriptionId);

            // Extract relevant statuses
            $invoiceStatus = $invoice->status;  // e.g., 'paid', 'open', 'void'
            $paymentIntentStatus = $invoice->payment_intent->status ?? null;  // e.g., 'succeeded', 'requires_action'

            Log::info('Invoice status: ' . $invoiceStatus);
            Log::info('Payment Intent status: ' . $paymentIntentStatus);

            if ($invoice->status == 'paid') {
                Log::info('Invoice is paid. Proceeding to retrieve subscription.');

                // Retrieve the subscription
                $subscription = $stripe->subscriptions->retrieve($subscriptionId, [
                    'expand' => ['customer', 'latest_invoice.payment_intent'] // Expanding useful related data
                ]);

                Log::info('Subscription retrieved: ', ['subscription_id' => $subscription->id]);

                $this->saveSubscriptionData($user_id, $subscription, $getPackage->id);
                Log::info('Subscription data saved for user_id: ' . $user_id);

                $subData = [
                    'name' => 'Subscriber',
                    'subscriptionId' => $subscription->id,
                    'startDate' => date("Y-m-d", $subscription->current_period_start),
                    'planName' => $getPackage->name,
                    'billingDate' => Carbon::now()->format('Y-m-d'),
                    'nextBillingDate' => date("Y-m-d", $subscription->current_period_end),
                    'totalAmountCharged' => $subscription->plan['amount'] / 100,
                ];

                Log::info('Prepared subscription data for email and Customer.io.', $subData);

                $mailStatus = $this->sendMailForFinishRegistration($user->email);
                Log::info('Email sending status: ' . ($mailStatus ? 'success' : 'failure'));

                $customerIo = new CustomerIoService();
                $customerIo->sendTransactionalEmail($user->email, '8', $subData);
                Log::info('Transactional email sent to user: ' . $user->email);

                if (!$mailStatus) {
                    Log::error('Email sending failed for user: ' . $user->email);
                }
                    $assignedProgram = $this->assignProgram();
                    Log::info('Assigned program: ', $assignedProgram);

                    $this->saveUserProgram($user->id, $assignedProgram);
                    Log::info('User program saved for user_id: ' . $user->id);

                    $userEvolutionData = UserReferenceAnswer::where(['session_id' => $sessionId])->get();
                    Log::info('Retrieved user evolution data: ', ['user_id' => $user->id, 'data_count' => count($userEvolutionData)]);

                    $this->saveUserEvolutionData($user->id, $userEvolutionData);
                    Log::info('User evolution data saved for user_id: ' . $user->id);

                    $token = session()->get('resetToken');
                    Log::info('Reset token retrieved from session: ' . ($token ? 'present' : 'not present'));

                    $isWebView = Session::get('isWebView');
                    Log::info('WebView session status: ' . ($isWebView ? 'true' : 'false'));

                    if (!is_null($isWebView) && $isWebView == 1) {
                        Log::info('Redirecting to mobile web view.');

                        // Redirect to a view with an ID in the query string for mobile web view
                        Session::forget('quiz_session_id');
                        session(['userId' => $user->id]);
                    }
                    Auth::guard('user')->login($user);
                    Log::info('User logged in: ' . $user->email);

                    return redirect()->to('/address');
                // return redirect()->route('questions')->with('error', "Quelque chose s'est mal passé. Veuillez répondre à toutes les questions.");
            }else{
                Log::info('Invoice not paid. Deleting user with ID: ' . $user->id);
                User::find($user->id)->delete();
            }

        } catch (\Exception $e) {
            Log::error('Exception occurred: ' . $e->getMessage());
            User::find($user->id)->delete();
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }

        Log::info('3D Success Payment process completed.');
    }

    public function deleteUserAfterFailedAttempt(Request $request){
        $request->validate([
            'userId' => 'required|exists:users,id',
        ]);

        $userId = $request->input('userId');

        try {
            User::findOrFail($userId)->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting user'], 500);
        }
    }

    public function redirectToKlarna($request)
    {
        try {
            Log::info('Redirecting to Klarna', ['plan_id' => $request->planId, 'session_id' => Session::get('quiz_session_id')]);

            $sessionPlanId = Session::get('planId');
            $sessionId = Session::get('quiz_session_id');

            // $getPackage = Plan::find($sessionPlanId);
            $getPackage = Plan::find($request->planId);
            Log::info('Fetched plan', ['plan_id' => $request->planId, 'plan_name' => $getPackage->name]);

            $getEmail = UserReferenceAnswer::where(['session_id' => $sessionId, 'key' => 'email'])->first();
            $email = $getEmail['value'];
            Log::info('Email fetched for Klarna checkout', ['email' => $email]);

            if (!Auth::guard('user')->check()) {

                Log::info('User not authenticated, checking if email already exists', ['email' => $email]);
                $user = User::where('email', $email)->first();

                if ($user) {
                    // User found, now check subscription
                    Log::info('User found with email', ['user_id' => $user->id]);
                    $hasSubscription = UsersSubscriptions::where('user_id', $user->id)->exists();
                    $hasAppPurchase = AppPurchase::where('user_id', $user->id)->exists();

                    if ($hasSubscription || $hasAppPurchase) {
                        Log::warning('User already has a subscription or app purchase', ['user_id' => $user->id]);
                        return redirect()->back()->with('error', 'Cet email existe déjà avec un autre compte.');
                    } else {
                        Log::info('Deleting existing user and retrying process', ['user_id' => $user->id]);
                        User::find($user->id)->delete();
                        return redirect()->back()->with('error', "quelque chose s'est mal passé, veuillez réessayer");
                    }
                }

                Log::info('No existing user, creating new user');
                $user = $this->createNewUser($email , 1);
                Log::info('New user created', ['user_id' => $user->id, 'email' => $email]);
            } else {
                $user = Auth::guard('user')->user();
                Log::info('Using authenticated user', ['user_id' => $user->id, 'email' => $user->email]);
            }

            $plan = Plan::findOrFail($request->planId);
            Log::info('Fetched plan for Klarna payment', ['plan_id' => $plan->id, 'plan_name' => $plan->name]);

            $amount = $plan->total_disc_price ?? $plan->total_price;
            if($request->final_price){
                $amount = $request->final_price;
            }
            $discountAmount = 0;
            $finalPrice = $amount;

            Log::info('Final price calculated', ['final_price' => $finalPrice]);
            // if ($request->promocode_discount && $request->promoCodeId) {
            //     $discountAmount = floatval($request->promocode_discount);
            //     $finalPrice = $amount - $discountAmount;

            //     PromoCodeUsage::create([
            //         'user_id' => $user->id,
            //         'promo_code_id' => $request->promoCodeId,
            //         'applied_date' => now()->format('Y-m-d'),
            //         'total_discount_price' => $discountAmount,
            //     ]);
            // }

            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            Log::info('Stripe client initialized for RECURRING subscription');

            $customer = $this->getOrCreateStripeCustomer($user);
            Log::info('Stripe customer retrieved or created', ['stripe_customer_id' => $customer->id]);

            /**
             * =====================================================
             * CREATE STRIPE PRICE FOR RECURRING BILLING
             * =====================================================
             * CRITICAL: Use Price API for subscriptions
             */
            $interval = 'month';
            $intervalCount = 1;

            if ($plan->plan_type == '0') {
                $intervalCount = 1; // Monthly
            } elseif ($plan->plan_type == '1') {
                $intervalCount = 3; // Quarterly
            } elseif ($plan->plan_type == '2') {
                $intervalCount = 12; // Yearly
            } elseif ($plan->plan_type == '3') {
                $intervalCount = 24; // 2 years
            } elseif ($plan->plan_type == '4') {
                $intervalCount = 6; // Semi-annual
            }

            Log::info('Creating Stripe Price for Klarna recurring', [
                'interval' => $interval,
                'interval_count' => $intervalCount,
                'amount' => $finalPrice
            ]);

            $price = $stripe->prices->create([
                'unit_amount' => intval($finalPrice * 100),
                'currency' => 'eur',
                'recurring' => [
                    'interval' => $interval,
                    'interval_count' => $intervalCount,
                ],
                'product_data' => [
                    'name' => $plan->name,
                    'metadata' => [
                        'package_id' => $plan->id,
                    ],
                ],
            ]);

            Log::info('Stripe Price created', ['price_id' => $price->id]);

            /**
             * =====================================================
             * CREATE SUBSCRIPTION (NOT PAYMENTINTENT!)
             * =====================================================
             * KEY: payment_behavior='default_incomplete'
             * This creates subscription in 'incomplete' status
             * and generates PaymentIntent for first payment
             */
            $subscriptionData = [
                'customer' => $customer->id,
                'items' => [['price' => $price->id]],
                'payment_behavior' => 'default_incomplete', // ← CRITICAL
                'payment_settings' => [
                    'payment_method_types' => ['klarna'],
                    'save_default_payment_method' => 'on_subscription', // ← Auto-saves PM
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_method' => 'klarna'
                ],
            ];

            // Add trial if applicable
            if ($plan->freetrial == 1 && $plan->trialdays > 0) {
                $subscriptionData['trial_period_days'] = $plan->trialdays;
                Log::info('Adding trial period', ['trial_days' => $plan->trialdays]);
            }

            Log::info('Creating Klarna recurring subscription', ['user_id' => $user->id]);
            $subscription = $stripe->subscriptions->create($subscriptionData);

            Log::info('Klarna subscription created', [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ]);

            /**
             * Get client_secret from PaymentIntent
             * This is what the frontend needs to confirm payment
             */
            $latestInvoice = $subscription->latest_invoice;
            $paymentIntent = $latestInvoice->payment_intent;

            if (!$paymentIntent) {
                throw new \Exception('PaymentIntent not found on first invoice');
            }

            $clientSecret = $paymentIntent->client_secret;

            Log::info('Client secret obtained', [
                'payment_intent_id' => $paymentIntent->id,
                'subscription_id' => $subscription->id
            ]);

            // Returning Klarna checkout view with SUBSCRIPTION data
            return view('front.klarna.checkout', [
                'payment_intent_id' => $paymentIntent->id,
                'clientSecret' => $clientSecret,
                'subscription_id' => $subscription->id, // ← NEW: Pass subscription ID
                'plan' => $plan,
                'user' => $user,
                'finalAmount' => $finalPrice,
                'is_recurring' => true, // ← NEW: Flag for frontend
            ]);

        } catch (Exception $e) {
            Log::error('Error in Klarna Checkout', [
                'error' => $e->getMessage(),
                'user_id' => isset($user) ? $user->id : 'N/A',
                'plan_id' => isset($plan) ? $plan->id : 'N/A'
            ]);

            if (isset($user)) {
            User::find($user->id)->delete();
            }

            return back()->with('error', 'Klarna Checkout failed: ' . $e->getMessage());
        }
    }

    private function getOrCreateStripeCustomer($user, $token = null)
    {
        $existing = StripeCustomer::where('user_id', $user->id)->first();

        if ($existing) {
            return json_decode($existing->card_details);
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $customer = $this->createStripeCustomer($stripe, $user->email, $token);

        $card = $this->getStripeCard($customer->id, $customer->default_source);
        $this->saveStripeCustomer($user->id, $customer, $card);

        return $customer;
    }

    public function confirmKlarna(Request $request)
    {
        $paymentIntentId = $request->get('payment_intent');

        // Fetch the PaymentIntent from Stripe
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        try {
            // Retrieve PaymentIntent with invoice and expanded subscription
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, [
                'expand' => ['invoice.subscription']
            ]);

            $status = $request->get('redirect_status');

            // Get metadata from the subscription object inside the invoice
            $subscription = $paymentIntent->invoice->subscription;
            $metadata = $subscription->metadata ?? null;

            // Extract user_id and plan_id from the subscription metadata
            $userId = $metadata->user_id ?? null;
            $planId = $metadata->plan_id ?? null;

            if ($status === 'succeeded') {
                // Log successful payment details
                Log::info('Klarna payment succeeded', [
                    'payment_intent_id' => $paymentIntentId,
                    'subscription_id' => $subscription->id,
                    'user_id' => $userId
                ]);

                // Save subscription info (now with real Stripe subscription)
                $this->finalizeKlarnaSubscription($paymentIntent, $userId, $planId, $subscription);

                // Log the user in
                Auth::guard('user')->login(User::find($userId));
                return redirect('/address');
            } else {
                // If payment failed, delete the user if created
                if (isset($userId)) {
                    User::find($userId)->delete();
                }
                return redirect()->route('get-package')->with('error', 'Le paiement Klarna a échoué. Veuillez réessayer.');
            }

        } catch (\Exception $e) {
            Log::error('Klarna confirmation error', ['error' => $e->getMessage()]);

            // If an error occurs, delete the user if it was created
            if (isset($userId)) {
                User::find($userId)->delete();
            }
            return redirect()->route('get-package')->with('error', 'Erreur Klarna : ' . $e->getMessage());
        }
    }

    private function finalizeKlarnaSubscription($paymentIntent, $userId, $planId, $subscription)
    {
        $user = User::find($userId);
        $plan = Plan::find($planId);
        $sessionId = Session::get('quiz_session_id');

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $withKlarna = 'klarna';
        /**
         * Retrieve the REAL subscription from Stripe
         * We created it in redirectToKlarna(), now we fetch it
         */
        if ($subscription) {

            Log::info('Real subscription retrieved', [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end' => $subscription->current_period_end
            ]);
        } else {
            // Fallback: If subscription ID not passed, log warning
            Log::warning('No subscription ID provided, webhook will handle subscription creation');

            // Create minimal subscription data (webhook will update it properly)
            $totalPlanDays = $plan->dayscount;
            $subscription = (object)[
                'id' => 'klarna_pending_' . $paymentIntent->id,
                'status' => 'pending',
                'customer' => $paymentIntent->customer,
                'plan' => [
                    'amount' => $paymentIntent->amount,
                    'interval_count' => 1,
                ],
                'current_period_start' => now()->timestamp,
                'current_period_end' => now()->addDays($totalPlanDays)->timestamp,
            ];
        }

        // Store Stripe Customer if not already
        if (!StripeCustomer::where('user_id', $user->id)->exists()) {
            $customer = $stripe->customers->retrieve($paymentIntent->customer);
            $this->saveStripeCustomer($user->id, $customer, []);
            Log::info('Stripe customer saved', ['customer_id' => $customer->id]);
        }

        /**
         * Save subscription data (now with REAL Stripe subscription)
         * The webhook will also update this, but we save it now for immediate access
         */
        Log::info('Saving subscription data', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id
        ]);

        $this->saveSubscriptionData($user->id, $subscription, $plan->id, $withKlarna);

        // Send registration email
        $subData = [
            'name' => 'Subscriber',
            'subscriptionId' => $subscription->id,
            'startDate' => is_object($subscription) && isset($subscription->current_period_start)
                ? date("Y-m-d", $subscription->current_period_start)
                : now()->format("Y-m-d"),
            'planName' => $plan->name,
            'billingDate' => now()->format('Y-m-d'),
            'nextBillingDate' => is_object($subscription) && isset($subscription->current_period_end)
                ? date("Y-m-d", $subscription->current_period_end)
                : now()->addDays($plan->dayscount)->format("Y-m-d"),
            'totalAmountCharged' => $paymentIntent->amount / 100,
        ];

        $mailStatus = $this->sendMailForFinishRegistration($user->email);
        $customerIo = new CustomerIoService();
        $customerIo->sendTransactionalEmail($user->email, '8', $subData);

        // Assign program and evolution data
        if (!$mailStatus) {
            Log::error('Email sending failed for user: ' . $user->email);
        }
            $assignedProgram = $this->assignProgram();
            $this->saveUserProgram($user->id, $assignedProgram);

            $userEvolutionData = UserReferenceAnswer::where(['session_id' => $sessionId])->get();
            $this->saveUserEvolutionData($user->id, $userEvolutionData);

        // Clean up session and login
        Session::forget('quiz_session_id');
        session(['userId' => $user->id]);

        Log::info('Klarna subscription finalized successfully', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id
        ]);
    }

}
