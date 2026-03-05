<?php

namespace App\Http\Controllers\API\V2;

use App\Models\Exercise;
use App\Models\UserProgram;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Challenge;
use App\Models\UserLevel;
use App\Models\FoodPlanner;
use Illuminate\Http\Request;
use App\Models\ChallengeUser;
use App\Models\TaskMilestone;
use App\Models\UserLevelTask;
use App\Models\userMilestone;
use App\Models\ChallengeLevel;
use App\Models\CommunityPosts;
use App\Models\LessonsPlanner;
use App\Models\UserAppFeedback;
use App\Models\UserLevelTaskLog;
use App\Models\UserSessionStatus;
use App\Models\OnboardingVisitLog;
use App\Models\ChallengeUserStatus;
use App\Models\UsersAppearanceInfo;
use App\Models\UserProgressImage;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\DashboardController as APIDashboardControllerV1;
use App\Models\Session;

class DashboardController extends APIDashboardControllerV1
{
    public function dashboard(Request $request)
    {
        try {
            $userAssignedProgram = $this->getUserAssignedProgram();
            $stepsTracking = $this->stepsTracking();
            $currentWeight = $this->currentWeight();
            $isMealLogged = $this->isLoggedMeal();
            $lessons = $this->lessons();
            $userData = $this->userData($request);
            $latestImageData = $this->latestImageData();
            $maintainanceMode = $this->maintainanceMode();
            $isWeek1Completed = $this->isWeek1Completed();
            $isChallengePicked = $this->onBoardingChallengeStatusInDashBoard();
            $UserChallenge = $this->UserChallengeDetails();
            $userLevelDetails = $this->userLevelDetails();
            $userPreviousLevelDetails = $this->userPreviousLevelDetails();

            $response = [
                [
                    'name' => "TopRecipes",
                    'topRecipes' => []
                ],
                    $currentWeight,
                [
                    'name' => "UserAssignedProgram",
                    'assignedProgram' => $userAssignedProgram
                ],
                [
                    'name' => "StepsTracking",
                    'stepsTracking' => $stepsTracking
                ],
                [
                    'name' => "MealLoggedInfo",
                    'mealWiseLogDeatails' => $isMealLogged
                ],
                [
                    'name' => "Lessons",
                    'dailyLesson' => $lessons
                ],
                [
                    'name' => "UserData",
                    'userData' => $userData
                ],
                [
                    'name' => "Evolution",
                    'latestImageData' => $latestImageData
                ],
                [
                    'isWeek1Completed' => $isWeek1Completed
                ],
                [
                    'maintainanceMode' => $maintainanceMode
                ],
                [   'name' => 'onboarding-challenge-status',
                    'ChallengeStatus' => $isChallengePicked
                ],
                [
                    'name' => 'UserChallenge',
                    'currentChallenge' => $UserChallenge
                ],
                [
                    'name' => 'UserLevels',
                    'userLevelDetails' => $userLevelDetails
                ],
                [
                    'name' => 'UserPreviousLevels',
                    'userPreviousLevelDetails' => $userPreviousLevelDetails
                ]
            ];

            return $this->successResponse("Data Found", $response, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }
    protected function lessons()
    {
        try {
            $userTz = Auth::user()->timezone ?? "Europe/Paris";
            $currentDate = (new DateTime('now', new \DateTimeZone($userTz)))->format('Y-m-d');
            $userId = Auth::user()->id;

            $lessons = Lesson::where('status', 1)->orderBy('id')->get();
            $completedLessonIds = LessonsPlanner::where('user_id', $userId)
                ->where('is_logged', 1)
                ->pluck('lesson_id')->toArray();

            $response = [];

            $lessonPlannerRecord = LessonsPlanner::where('user_id', $userId)
                ->orderBy('date')
                ->first();

            // Check if the user has completed the previous lesson
            $nextLesson = null;

            if ($lessonPlannerRecord) {
                $nextLesson = $lessons->first(function($lesson) use ($completedLessonIds) {
                    return !in_array($lesson->id, $completedLessonIds);
                });
                if (!$nextLesson) {
                    $response = [
                        'id' => 0,
                        'title' => 'All lessons completed.',
                        'description' => 'All lessons completed.',
                        'video_title' => '',
                        'video_description' => '',
                        'video_link' => '',
                        'lesson_question' => '',
                        'answers' => [],
                        'task_title' => '',
                        'task_content' => '',
                        'lesson_tips' => [],
                        'isTaskCompleted' => 1,
                        'isFeature' => 0,
                        'feature_name' => '',
                        'feature_title' => '',
                        'feature_desc' => '',
                        'feature_image' => '',
                    ];
                }

            } else {
                // If it's the user's first day, show the first lesson
                $nextLesson = $lessons->first();
            }

            if ($nextLesson) {
                // Create a lesson planner record for the next lesson
                LessonsPlanner::create([
                    'user_id' => $userId,
                    'lesson_id' => $nextLesson->id,
                    'date' => $currentDate,
                    'is_logged' => 0, // Mark as not yet completed
                ]);

                $lesson_data = [
                    'id' => $nextLesson->id,
                    'title' => $nextLesson->title,
                    'description' => $nextLesson->description,
                    'video_title' => $nextLesson->video_title ?? '',
                    'video_description' => $nextLesson->video_description ?? '',
                    'video_link' => $nextLesson->video_link ?? '',
                    'lesson_question' => $nextLesson->lesson_question,
                    'answers' => json_decode($nextLesson->answers, true),
                    'task_title' => $nextLesson->task_title,
                    'task_content' => $nextLesson->task_content,
                    'lesson_tips' => [],
                    'isTaskCompleted' => 0, // Lesson is not yet completed
                ];

                foreach ($nextLesson->lessonTips as $tip) {
                    $tipImage = '';
                    if ($tip->tip_image != '') {
                        $tipImage = asset(Storage::url($tip->tip_image));
                    }
                    $lesson_data['lesson_tips'][] = [
                        'id' => $tip->id,
                        'tip_title' => $tip->tip_title,
                        'tip_content' => $tip->tip_content,
                        'tip_image' => $tipImage,
                    ];
                }

                // Add feature-related fields at the end of the response
                $lesson_data['isFeature'] = $nextLesson->isFeature;
                $lesson_data['feature_name'] = $nextLesson->feature_name ?? '';
                $lesson_data['feature_title'] = $nextLesson->feature_title ?? '';
                $lesson_data['feature_desc'] = $nextLesson->feature_desc ?? '';
                $lesson_data['feature_image'] = $nextLesson->feature_image ? asset(Storage::url($nextLesson->feature_image)) : '';

                $response = $lesson_data;
            }

            return $response;
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function dailyLessonsPlanner(Request $request)
    {
        try {
            $data = $request->all();
            $userId = Auth::user()->id;
            $validation = [
                'lesson_id' => ['required', 'numeric'],
            ];
            $validator = Validator::make($data, $validation);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            // Use the current date in YYYY-MM-DD format
            $userTz = Auth::user()->timezone ?? "Europe/Paris";
            $currentDate = (new DateTime('now', new \DateTimeZone($userTz)))->format('Y-m-d');

            // Find or create the LessonsPlanner entry
            // Try to find existing record
            $lessonPlanner = LessonsPlanner::where([
                'user_id' => $userId,
                'lesson_id' => $data['lesson_id'],
                'date' => $currentDate,
            ])->first();

            $shouldLogLevelTask = false;

            if (!$lessonPlanner) {
                // No record exists, create new
                $lessonPlanner = LessonsPlanner::create([
                    'user_id' => $userId,
                    'lesson_id' => $data['lesson_id'],
                    'date' => $currentDate,
                    'is_logged' => 1,
                ]);
                $shouldLogLevelTask = true;
            } elseif ($lessonPlanner->is_logged == 0) {
                // Existing record, but is_logged was 0, update to 1
                $lessonPlanner->is_logged = 1;
                $lessonPlanner->save();
                $shouldLogLevelTask = true;
            }
            $levelDetails = null;
            if ($shouldLogLevelTask) {
                $levelResponse = $this->logLevelTaskEntries($userId, 'lessons', 1, 0);

                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }
            }


            $milestoneCompletedArray = [];

            $lessonMilestones = TaskMilestone::where('milestone_type', 'lessons')->get();

            $userCurrentMilestones = userMilestone::where([
                'user_id' => $userId,
                'milestone_type' => 'lessons'
            ])->pluck('milestone_id')->toArray();

            $userlessonsCompleteCount = UserLevelTaskLog::where([
                'user_id' => $userId,
                'level_task_type' => 'lessons'
            ])->sum('completed_count');

            foreach ($lessonMilestones as $milestone) {
                if (
                    $userlessonsCompleteCount >= $milestone->milestone_count &&
                    !in_array($milestone->id, $userCurrentMilestones)
                ) {
                    $milestoneCompletedArray[] = [
                        'type' => 'lessons',
                        'color' => 'pink',
                        'title' => $milestone->milestone_title,
                        'description' => $milestone->milestone_description,
                        'count' => $milestone->milestone_count,
                    ];

                    // Save to user_milestones table
                    UserMilestone::create([
                        'user_id' => $userId,
                        'milestone_id' => $milestone->id,
                        'milestone_type' => 'lessons',
                    ]);
                }
            }

            $lessonPlanner['milestoneCompletedArray'] = $milestoneCompletedArray;

            $lessonPlanner['levelCompletedDetails'] = $levelDetails ?: (object)[];

            return $this->getSingleResponse("Lesson planner entry saved successfully.", $lessonPlanner, 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function latestImageData() {
        $response = [
            "latestimageRecordId" => 0,
            "latestimagePath" => "",
            "latestuploadDate" => "",
            "previousimageRecordId" => 0,
            "previousimagePath" => "",
            "previousuploadDate" => "",
            "isTaskCompleted" => 0,
        ];
        $userId = Auth::id();

        $progressImage = UserProgressImage::with(['beforeImage', 'afterImage'])
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if ($progressImage) {
            // Previous image
            if (!empty($progressImage->before_image) && is_array($progressImage->before_image)) {
                $before = $progressImage->before_image;
                $response["previousimageRecordId"] = $progressImage->before_image_id;
                $response["previousimagePath"] = $before['image'] ?? '';
                $response["previousuploadDate"] = $before['imageDate'] ?? '';
            }

            // Latest image
            if (!empty($progressImage->after_image) && is_array($progressImage->after_image)) {
                $after = $progressImage->after_image;
                $response["latestimageRecordId"] = $progressImage->after_image_id;
                $response["latestimagePath"] = $after['image'] ?? '';
                $response["latestuploadDate"] = $after['imageDate'] ?? '';
            }

            // Task completion based on UsersAppearanceInfo
            $latestUpload = UsersAppearanceInfo::where('user_id', $userId)
                ->latest('imageDate')
                ->first();

            if ($latestUpload && !empty($latestUpload->imageDate)) {
                $response["isTaskCompleted"] = Carbon::parse($latestUpload->imageDate)
                    ->diffInDays(now()) < 7 ? 1 : 0;
            }
        } else {
            // Fallback if no progress images
            $userAppearance = UsersAppearanceInfo::where('user_id', $userId)
                ->latest()
                ->get();

            if ($userAppearance->count() > 0) {
                $latestRecord = $userAppearance->first();
                $response["latestimageRecordId"] = $latestRecord->id;
                $response["latestimagePath"] = $latestRecord->image ? url('storage/' . $latestRecord->image) : '';
                $response["latestuploadDate"] = $latestRecord->imageDate ?? "";

                if (!empty($response["latestuploadDate"])) {
                    $response["isTaskCompleted"] = Carbon::parse($response["latestuploadDate"])
                        ->diffInDays(now()) < 7 ? 1 : 0;
                }

                if ($userAppearance->count() > 1) {
                    $previousRecord = $userAppearance->skip(1)->first();
                    $response["previousimageRecordId"] = $previousRecord->id;
                    $response["previousimagePath"] = $previousRecord->image ? url('storage/' . $previousRecord->image) : '';
                    $response["previousuploadDate"] = $previousRecord->imageDate ?? "";
                }
            }
        }

        return $response;
    }


    protected function onBoardingChallengeStatusInDashBoard() {
        $user = Auth::user();
        $isLogForFood = $isLogForSession = $isLogForLesson = $isLogForUserAppearenceInfo = $isLogForSession2 = $totalCompleted = $isChallengeTaken = $isLogForCommunityPost = 0;

        // Check Food Planner
        $foodPlanner = FoodPlanner::where(['user_id' => $user->id])->latest()->first();
        if ($foodPlanner) {
            $isLogForFood = 1;
            $totalCompleted++;
        }

        // Check User Session Status
        $userSessionStatus = UserSessionStatus::where(['user_id' => $user->id, 'user_session_status' => 1])->latest()->first();
        if ($userSessionStatus) {
            $isLogForSession = 1;
            $totalCompleted++;
        }

        // Check Lesson Planner
        $lessonPlanner = LessonsPlanner::where(['user_id' => $user->id, 'is_logged' => 1])->latest()->first();
        if ($lessonPlanner) {
            $isLogForLesson = 1;
            $totalCompleted++;
        }

        // Check User Appearence Info
        $userAppearanceInfo = UsersAppearanceInfo::where(['user_id' => $user->id])->latest()->first();
        if ($userAppearanceInfo) {
            $isLogForUserAppearenceInfo = 1;
            $totalCompleted++;
        }

        // Check if user is older than 2 days (today's date is 09-07-2025)
        $oldUser = 0;
        $userCreatedAt = Carbon::parse($user->created_at);
        $today = Carbon::createFromFormat('d-m-Y', '09-07-2025');
        if ($userCreatedAt->diffInDays($today) > 2) {
            $oldUser = 1;
        }

        $userVisitedCommunityScreen = OnboardingVisitLog::where('user_id', $user->id)
            ->where('source', 'communityscreen')
            ->exists() ? 1 : 0;

        // Check User posted
        $userCommunityPost = CommunityPosts::where(['user_id' => $user->id])->latest()->first();

        if ($userCommunityPost) {
            $isLogForCommunityPost = 1;
            $totalCompleted++;
        }elseif($userVisitedCommunityScreen == 1){
            $isLogForCommunityPost = 1;
            $totalCompleted++;
        }elseif($oldUser == 1){
            $isLogForCommunityPost = 1;
            $totalCompleted++;
        }

        // Check User Sessions
        $userSessions = UserSessionStatus::where(['user_id' => $user->id, 'user_session_status' => 1])->count();
        if ($userSessions > 1) {
            $isLogForSession2 = 1;
            $totalCompleted++;
        }
        // Check User Challenge
        $UserChallenge = ChallengeUser::where(['user_id' => $user->id, 'status' => 1])->count();
        if ($UserChallenge > 0) {
            $isLogForFood = 1;
            $isLogForSession = 1;
            $isLogForLesson = 1;
            $isLogForUserAppearenceInfo = 1;
            $isLogForCommunityPost = 1;
            $isLogForSession2 = 1;
            $totalCompleted = 6;
            $isChallengeTaken = 1;
        }

        $data = [
            'totalCompleted' => $totalCompleted,
            'isChallengeTaken' => $isChallengeTaken,
        ];

        return $data;
    }

    protected function UserChallengeDetails()
    {
        $userId = Auth::user()->id;
        $challengeUser = ChallengeUser::where(['user_id' => $userId, 'status' => 1])->first();

        if (!$challengeUser) {
            return (object)[];
        }

        $challenge = Challenge::select('id as challenge_id', 'title_for_frontend as challenge_title_for_frontend', 'description as challenge_description', 'gif_url as challenge_gif')
            ->where(['id' => $challengeUser->challenge_id])
            ->first();

        if (!$challenge) {
            return (object)[];
        }

        // Fetch challenge level details
        $challengeLevel = ChallengeLevel::select('id', 'title', 'description as sub_title')
            ->where('id', $challengeUser->challenge_level_id)
            ->first();

        $todayExercisesCompleted = 0;
        $todayDate = date('Y-m-d');
        if (ChallengeUserStatus::where('user_id', $userId)
            ->where('complete_date', $todayDate)
            ->where('challenge_id', $challenge->challenge_id)
            ->where('challenge_level_id', $challengeUser->challenge_level_id)
            ->exists()) {
            $todayExercisesCompleted = 1;
        }

        $responseData = [
            "challenge_id" => $challenge->challenge_id,
            "challenge_title_for_frontend" => $challenge->challenge_title_for_frontend,
            "challenge_description" => $challenge->challenge_description,
            "challenge_gif" => $challenge->challenge_gif ? asset(Storage::url($challenge->challenge_gif)) : '',
            "challenge_level" => $challengeLevel,
            "todayExercisesCompleted" => $todayExercisesCompleted
        ];

        return $responseData;
    }

    protected function userLevelDetails(){
        $user = Auth::user();
        $currentLevel = $user->level;
        $currentLevelRecord = UserLevel::where('level', $currentLevel)->first();
        $levelsCount = UserLevel::count();
        $currentLevelTasksList = [];

        if (!$currentLevelRecord) {
            return  $response = [
                        'currentLevelTasksList' => $currentLevelTasksList,
                        'currentLevelProgress' => 0,
                        'currentLevel' => $currentLevel,
                        'totalLevels' => $levelsCount,
                    ];
        }

        // Get all tasks for the current level
        $currentLevelTasks = UserLevelTask::where('user_level_id', $currentLevelRecord->id)
            ->select('id', 'task_type', 'total_count', 'duration')
            ->get();

        // Calculate progress
        $totalTasks = count($currentLevelTasks);
        $progress = 0;

        foreach ($currentLevelTasks as $task) {
            $completed_count = (int) UserLevelTaskLog::where([
                'user_id' => $user->id,
                'level_task_type' => $task->task_type,
            ])->sum('completed_count');
            $status = ($completed_count >= $task->total_count) ? 1 : 0;

            // Each task's share of 100%
            $taskShare = $totalTasks > 0 ? (100 / $totalTasks) : 0;
            // Progress for this task
            $taskProgress = $task->total_count > 0 ? min($completed_count / $task->total_count, 1) * $taskShare : 0;
            $progress += $taskProgress;

            // Determine task name based on task type
            switch ($task->task_type) {
                case 'lessons':
                    $taskName = 'Tutos des Mo’s';
                    break;
                case 'workouts':
                    $taskName = 'Séances';
                    break;
                case 'log-meals':
                    $taskName = 'Track tes repas';
                    break;
                case 'add-photos':
                    $taskName = 'Ajoute une photo';
                    break;
                case 'drink-water':
                    $taskName = 'Bois de l’eau';
                    break;
                case 'community-posts':
                    $taskName = 'Interagis avec la communauté';
                    break;
                case 'streaks':
                    $taskName = 'Streak';
                    break;
                case 'walks':
                    $taskName = 'Objectif de pas';
                    break;
                case 'feedback':
                    $taskName = 'Donne ton avis';
                    break;
                case 'protiens':
                    $taskName = 'Mange des protéines';
                    break;
                case 'challenge':
                    $taskName = 'Challenge';
                    break;
                default:
                    $taskName = '';
                    break;
            }

            $taskData = [
                'task_name' => $taskName,
                'task_type' => $task->task_type,
                'duration' => $task->duration,
                'completed_count' => $completed_count,
                'total_count' => $task->total_count,
                'status' => $status,
            ];

            // Add remaining_count only for 'walks'
            if ($task->task_type === 'walks') {
                $taskData['remaining_count'] = max($task->total_count - $completed_count, 0);
            }

            $currentLevelTasksList[] = $taskData;
        }

        // Round progress to 2 decimal places
        $currentLevelProgress = round($progress, 2);

        $response = [
            'currentLevelTasksList' => $currentLevelTasksList,
            'currentLevelProgress' => $currentLevelProgress,
            'currentLevel' => $currentLevel,
            'totalLevels' => $levelsCount,
        ];

        return $response;
    }
    protected function userPreviousLevelDetails(){
        $user = Auth::user();
        $previousLevel = $user->level -1 ;
        $previousLevelPopupShown = '1';
        if($previousLevel > 0){
            $previousLevelPopupShown = $user->previous_level_popup_shown;
        }

        // Check if the level exists
        $SelectedLevel = UserLevel::where('level', $previousLevel)->first();
        if (!$SelectedLevel) {
            return (object)[]; // Return null if level doesn't exist
        }

        $currentLevelTasks = UserLevelTask::where('user_level_id', $SelectedLevel->id)
            ->select('id', 'task_type', 'total_count')
            ->get();

        // Add task_name for each task
        $currentLevelTasks->transform(function ($task) {
            switch ($task->task_type) {
                case 'lessons':
                    $taskName = 'Tutos des Mo’s';
                    break;
                case 'workouts':
                    $taskName = 'Séances';
                    break;
                case 'log-meals':
                    $taskName = 'Track tes repas';
                    break;
                case 'add-photos':
                    $taskName = 'Ajoute une photo';
                    break;
                case 'drink-water':
                    $taskName = 'Bois de l’eau';
                    break;
                case 'community-posts':
                    $taskName = 'Interagis avec la communauté';
                    break;
                case 'streaks':
                    $taskName = 'Streak';
                    break;
                case 'walks':
                    $taskName = 'Objectif de pas';
                    break;
                case 'feedback':
                    $taskName = 'Donne ton avis';
                    break;
                case 'protiens':
                    $taskName = 'Mange des protéines';
                    break;
                case 'challenge':
                    $taskName = 'Challenge';
                    break;
                default:
                    $taskName = '';
                    break;
            }
            $task->task_name = $taskName;
            return $task;
        });

        // Prepare the level data
        $levelData = [
            'id' => $SelectedLevel->id,
            'level' => $SelectedLevel->level,
            'level_title' => $SelectedLevel->level_title ?? '',
            'description' => $SelectedLevel->description ?? '',
            'improvement_per' => $SelectedLevel->improvement_per ?? 0,
            'video_title' => $SelectedLevel->video_title ?? '',
            'video_description' => $SelectedLevel->video_description ?? '',
            'video_url' => $SelectedLevel->video_url ?? '',
            'task_list' => $currentLevelTasks,
            'previousLevelPopupShown' => $previousLevelPopupShown,
        ];

        return $levelData;
    }
    public function logUserStreak()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $milestoneCompletedArray = [];
        $levelDetails = (object)[];

        // If last_opened_at is today, do nothing
        if ($user->last_opened_at && Carbon::parse($user->last_opened_at)->isSameDay($today)) {
            return $this->successResponse("Streak already updated for today.", [
                'streak' => (int) $user->streak,
                'last_opened_at' => $user->last_opened_at,
                'milestoneCompletedArray' => $milestoneCompletedArray,
                'levelCompletedDetails' => $levelDetails
            ], 200);
        }

        // Check the gap between today and last_opened_at
        $lastOpenedDate = Carbon::parse($user->last_opened_at);
        $daysDiff = $lastOpenedDate->diffInDays($today);

        if ($daysDiff <= 2) {
            // If the gap is 2 days or less, still keep streak incrementing (no reset)
            $user->streak += 1;
        } else {
            // If the gap is more than 2 days, reset streak
            $user->streak = 1;
        }

        $user->last_opened_at = $today;
        $user->save();

        $levelDetails = null;
        $levelResponse = $this->logLevelTaskEntries($user->id, 'streaks',$user->streak,1);

        if ($levelResponse != 0) {
            $levelDetails = $this->getLevelDetails($levelResponse);
        }

        $streaksMilestones = TaskMilestone::where('milestone_type', 'streaks')->get();

        $userCurrentMilestones = userMilestone::where([
            'user_id' => $user->id,
            'milestone_type' => 'streaks'
        ])->pluck('milestone_id')->toArray();

        $userStreakCompleteCount = UserLevelTaskLog::where([
            'user_id' => $user->id,
            'level_task_type' => 'streaks'
        ])->sum('completed_count');

        foreach ($streaksMilestones as $milestone) {
            if (
                $userStreakCompleteCount >= $milestone->milestone_count &&
                !in_array($milestone->id, $userCurrentMilestones)
            ) {
                $milestoneCompletedArray[] = [
                    'type' => 'streaks',
                    'color' => 'red',
                    'title' => $milestone->milestone_title,
                    'description' => $milestone->milestone_description,
                    'count' => $milestone->milestone_count,
                ];

                // Save to user_milestones table
                UserMilestone::create([
                    'user_id' => $user->id,
                    'milestone_id' => $milestone->id,
                    'milestone_type' => 'streaks',
                ]);
            }
        }
        return $this->successResponse("Streak updated.", [
            'streak' => $user->streak,
            'last_opened_at' => $user->last_opened_at,
            'milestoneCompletedArray' => $milestoneCompletedArray,
            'levelCompletedDetails' => $levelDetails ?: (object)[]
        ], 200);
    }
    public function levelDetails(Request $request)
    {
        $level = $request->level;
        $userCurrentLevel = Auth::user()->level;

        // Check if the level exists
        $SelectedLevel = UserLevel::where('level', $level)->first();
        if (!$SelectedLevel) {
            return $this->validationError("Fail", "Level not found.", 404);
        }

        $isCompleted = ($userCurrentLevel > $level) ? 1 : 0;

        $currentLevelTasks = UserLevelTask::where('user_level_id', $SelectedLevel->id)
            ->select('id', 'task_type', 'total_count')
            ->get();

            // Add task_name for each task
            $currentLevelTasks->transform(function ($task) {
                switch ($task->task_type) {
                    case 'lessons':
                        $taskName = 'Lessons';
                        break;
                    case 'workouts':
                        $taskName = 'Quick Workouts';
                        break;
                    case 'log-meals':
                        $taskName = 'Log Meals';
                        break;
                    case 'add-photos':
                        $taskName = 'Add Photo Results';
                        break;
                    case 'drink-water':
                        $taskName = 'Drink Water';
                        break;
                    case 'community-posts':
                        $taskName = 'Interact with Community';
                        break;
                    case 'streaks':
                        $taskName = 'Streak';
                        break;
                    case 'walks':
                        $taskName = 'Walk';
                        break;
                    case 'feedback':
                        $taskName = 'Leave Feedback';
                        break;
                    case 'protiens':
                        $taskName = 'Eat Protein';
                        break;
                    default:
                        $taskName = '';
                        break;
                }
                $task->task_name = $taskName;
                return $task;
            });

        $levelData = [
            'id' => $SelectedLevel->id,
            'level' => $SelectedLevel->level,
            'level_title' => $SelectedLevel->level_title ?? '',
            'description' => $SelectedLevel->description ?? '',
            'improvement_per' => $SelectedLevel->improvement_per ?? 0,
            'video_title' => $SelectedLevel->video_title ?? '',
            'video_description' => $SelectedLevel->video_description ?? '',
            // 'video_url' => $SelectedLevel->video_url ? asset(Storage::url($SelectedLevel->video_url)) : '',
            'video_url' => $SelectedLevel->video_url ?? '',
            'task_list' => $currentLevelTasks,
            'isCompleted' => $isCompleted,
        ];

        return $this->successResponse("Level fetched successfully.", $levelData, 200);
    }

    public function userAppFeedback(Request $request){

        $validated = $request->validate([
            'rating' => 'required|integer|in:1,2,3,4,5',
            'description' => 'required|string',
        ]);

        // Get the authenticated user ID
        $userId = Auth::user()->id;

        // Create feedback
        $feedback = new UserAppFeedback();
        $feedback->user_id = $userId;
        $feedback->rating = $validated['rating'];
        $feedback->description = $validated['description'];
        $feedback->save();

        $levelDetails = null;
        $levelResponse =  $this->logLevelTaskEntries($userId, 'feedback',1,0);

        if ($levelResponse != 0) {
            $levelDetails = $this->getLevelDetails($levelResponse);
        }
        $feedback['levelCompletedDetails'] = $levelDetails ?: (object)[];

        // Return a response
        return $this->successResponse("Feedback submitted successfully.", $feedback, 201);
    }
    public function popUpShown()
    {
        $user = User::where(['id' => Auth::user()->id])->first();
        $user->previous_level_popup_shown = '1';
        $user->save();

        return response()->json(['success' => true, 'message' => 'Popup shown status updated.'], 200);
    }

    protected function getUserAssignedPrograms()
    {
        try {
            $userAssignedPrograms = UserProgram::with([
                    'program.category',
                    'program.program_session.session_exercises',
                    'program.program_session.session_exercises.exercise',
                    'program.program_session.session',
                    'program.level'
                ])
                ->where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->get();

            if ($userAssignedPrograms->isEmpty()) {
                return [];
            }

            $response = [];

            foreach ($userAssignedPrograms as $userAssignedProgram) {
                $program = $userAssignedProgram->program;

                if (!$program) {
                    continue;
                }

                $programVideos = array_map(fn($video) => asset(Storage::url($video)), explode("|", $program->video));

                $pendingSession = [];

                foreach ($program->program_session as $session) {
                    $userSessionStatus = UserSessionStatus::where([
                        'user_id' => Auth::user()->id,
                        'program_id' => $session->program_id,
                        'session_id' => $session->session_id,
                        'session_week' => $session->session_week,
                    ])->first();

                    if (!$userSessionStatus || $userSessionStatus->user_session_status == 0) {
                        $sessionDetails = Session::find($session->session_id);

                        if (!$sessionDetails) continue;

                        $pendingSession[] = [
                            'week' => $session->session_week,
                            'session' => [[
                                'program_session_id' => $session->id,
                                'session_id' => $session->session_id,
                                'session_title' => $sessionDetails->title_for_frontend,
                                'difficulty_level' => $sessionDetails->difficulty_level,
                                'body_area' => $sessionDetails->body_area,
                                'time' => $sessionDetails->time,
                                'calories' => $sessionDetails->calories,
                                'session_type' => $sessionDetails->session_type,
                                'summary' => $sessionDetails->summary,
                                'user_session_status' => 'pending',
                                'session_material' => $sessionDetails->materials->map(fn($material) => [
                                    'id' => $material->id,
                                    'material_name' => $material->material_name,
                                    'material_image' => asset(Storage::url($material->material_image))
                                ]),
                                'session_exercises' => $session->session_exercises->map(function ($exercise) {
                                    $exerciseDetails = Exercise::find($exercise->session_exercise_id);
                                    return [
                                        'id' => $exercise->id,
                                        'session_exercise_id' => $exercise->session_exercise_id,
                                        'exercise_name' => $exerciseDetails->title_for_frontend,
                                        'exercise_form' => $exerciseDetails->exercise_form,
                                        'duration' => date('H:i:s', strtotime($exerciseDetails->duration)),
                                        'rest_period' => date('H:i:s', strtotime($exerciseDetails->rest_period)),
                                        'no_of_repetition' => $exerciseDetails->no_of_repetition,
                                        'range_of_repetition' => $exerciseDetails->range_of_repetition,
                                        'video_link' => $exerciseDetails->video,
                                        'instructions' => $exerciseDetails->instructions,
                                        'exercise_gif' => asset(Storage::url($exerciseDetails->gif)),
                                        'exercise_type' => $exerciseDetails->exercise_type,
                                        'body_zone' => $exerciseDetails->body_zone,
                                    ];
                                })
                            ]]
                        ];
                        break;
                    }
                }

                $response[] = [
                    'categoryId' => $program->category->id,
                    'categoryName' => $program->category->category_name,
                    'categoryDescription' => $program->category->description,
                    'categoryStatus' => $program->category->status,
                    'programs' => [
                        'programId' => $program->id,
                        'programCategoryId' => $program->category_id,
                        'programTags' => explode("|", $program->program_tag),
                        'programTitle' => $program->title,
                        'programDescription' => $program->description,
                        'programImage' => asset(Storage::url($program->program_image)),
                        'programVideos' => $programVideos,
                        'programFreeAccess' => $program->free_access,
                        'programStatus' => $program->status,
                        'programSession' => $pendingSession
                    ],
                    'information' => [
                        'programObjective' => $program->objective,
                        'programLevel' => $program->level->level_title,
                        'programBodyArea' => $program->body_area,
                        'programDuration' => $program->duration . ' weeks',
                        'programFrequency' => $program->frequency . ' per week',
                        'program_type' => $program->program_type ?? "",
                    ],
                    'user_streak' => $userAssignedProgram->user_streak
                ];
            }

            return $response;

        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong with user assigned programs", $th->getMessage(), 500);
        }
    }

}
