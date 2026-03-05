<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\API\ProgramController as APIProgramControllerV1;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramLevel;
use App\Models\ProgramSession;
use App\Models\Session;
use App\Models\TaskMilestone;
use App\Models\UserLevelTaskLog;
use App\Models\userMilestone;
use App\Models\UserProgram;
use App\Models\UserSessionStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProgramController extends APIProgramControllerV1

{
    public function updateUserSessionStatus(Request $request)
    {
        try {
            $firstDayOfWeek = date('Y-m-d', strtotime('monday this week')); 
            $lastDayOfWeek = date('Y-m-d', strtotime('sunday this week')); 
            $todayDate = date('Y-m-d');
    
            // Validation
            $request->validate([
                'program_id' => ['required', 'numeric'],
                'program_session_id' => ['required', 'numeric'], 
                'user_session_status' => ['required', 'numeric', 'in:0,1'],
            ], [
                'program_id.required' => "Program Id is required.",
                'program_id.numeric' => "Program Id must be a numeric value.",
                'program_session_id.required' => "Program Session Id is required.",
                'program_session_id.numeric' => "Program Session Id must be a numeric value.",
                'user_session_status.required' => "User Session Status is required.",
                'user_session_status.numeric' => "User Session Status must be a numeric value.",
                'user_session_status.in' => "User Session Status must be 0 or 1. 0: Pending, 1: Completed.",
            ]);
    
            $user = Auth::user();

            // Get session_id and session_week from program_sessions
            $programSession = ProgramSession::where('id', $request->program_session_id)
                ->where('program_id', $request->program_id)
                ->first();
    
            if (!$programSession) {
                return $this->notFoundResponse("Program Session Not Found", (object)[], 200);
            }
    
            $session_id = $programSession->session_id; // Fetch session_id
            $session_week = $programSession->session_week; // Fetch session_week
    

            // Check if the user is subscribed
            if ($user->isSubscribedUser === 1) {
                $userAssignedProgram = UserProgram::where([
                    'user_id' => $user->id,
                    'program_id' => $request->program_id
                ])->first();

                if (!$userAssignedProgram) {
                    return $this->successResponse("You have not been assigned this program.", (object)[], 200);
                }
            }

            // Check if user session status exists with session_week
            $userSessionStatus = UserSessionStatus::where([
                'user_id' => $user->id,
                'program_id' => $request->program_id,
                'session_id' => $session_id,
                'session_week' => $session_week
            ])->first();
            $levelDetails = null;
            if ($userSessionStatus) {
                $userSessionStatus->update([
                    'user_session_status' => $request->user_session_status,
                    'program_session_id' => $request->program_session_id // Store program_session_id
                ]);
            } else {
                $userSessionStatus = UserSessionStatus::create([
                    'user_id' => $user->id,
                    'program_id' => $request->program_id,
                    'session_id' => $session_id,
                    'session_week' => $session_week,
                    'program_session_id' => $request->program_session_id, // Store program_session_id
                    'user_session_status' => $request->user_session_status
                ]);

                $levelResponse = $this->logLevelTaskEntries($user->id, 'workouts',1,0);
                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }
            }

            // Update user streak for subscribed users
            if ($user->isSubscribedUser === 1 && ($todayDate >= $firstDayOfWeek && $todayDate <= $lastDayOfWeek)) {
                $userAssignedProgram->update([
                    'user_streak' => $userAssignedProgram->user_streak === 3 ? 1 : $userAssignedProgram->user_streak + 1
                ]);
            }

            // Check if all sessions are completed by the user
            $allSessions = ProgramSession::where('program_id', $request->program_id)->get();
            $allSessionIdsWithWeeks = $allSessions->map(function ($item) {
                return $item->session_id . '_' . $item->session_week;
            });

            $userSessionStatuses = UserSessionStatus::where([
                'user_id' => $user->id,
                'program_id' => $request->program_id
            ])->get()->keyBy(function ($item) {
                return $item->session_id . '_' . $item->session_week;
            });

            $isLastSessionOfLastWeek = $allSessionIdsWithWeeks->every(function ($key) use ($userSessionStatuses) {
                return isset($userSessionStatuses[$key]) && $userSessionStatuses[$key]->user_session_status == 1;
            }) ? 1 : 0;

            $milestoneCompletedArray = [];

            $workoutMilestones = TaskMilestone::where('milestone_type', 'workouts')->get();

            $userCurrentMilestones = userMilestone::where([

                'user_id' => $user->id,
                'milestone_type' => 'workouts'
            ])->pluck('milestone_id')->toArray();

            $userWorkoutsCompleteCount = UserLevelTaskLog::where([
                'user_id' => $user->id,
                'level_task_type' => 'workouts'
            ])->sum('completed_count');

            foreach ($workoutMilestones as $milestone) {
                if (
                    $userWorkoutsCompleteCount >= $milestone->milestone_count &&
                    !in_array($milestone->id, $userCurrentMilestones)
                ) {
                    $milestoneCompletedArray[] = [
                        'type' => 'workouts',
                        'color' => 'yellow',
                        'title' => $milestone->milestone_title,
                        'description' => $milestone->milestone_description,
                        'count' => $milestone->milestone_count,
                    ];

                    // Save to user_milestones table
                    UserMilestone::create([
                        'user_id' => $user->id,
                        'milestone_id' => $milestone->id,
                        'milestone_type' => 'workouts',
                    ]);
                }
            }

            return $this->successResponse("User Session Status Updated", [
                'id' => (int) $userSessionStatus->id,
                'user_id' => (int) $userSessionStatus->user_id,
                'program_id' => (int) $userSessionStatus->program_id,
                'program_session_id' => (int) $userSessionStatus->program_session_id,
                'session_id' => (int) $userSessionStatus->session_id,
                'session_week' => $userSessionStatus->session_week, // Keep as string
                'user_session_status' => (int) $userSessionStatus->user_session_status,
                'created_at' => $userSessionStatus->created_at,
                'updated_at' => $userSessionStatus->updated_at,
                'isLastSessionOfLastWeek' => $isLastSessionOfLastWeek,
                'milestoneCompletedArray' => $milestoneCompletedArray,
                'levelCompletedDetails' => $levelDetails ?: (object)[]
            ], 200);

        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function joinProgram(Request $request)
    {
        try {
            /* ✅ Validate Data */
            $validation = [
                'program_id' => ['required', 'numeric'],
            ];

            $validation_messages = [
                'program_id.required' => 'Please select the program you want to join.',
            ];

            $validator = Validator::make($request->all(), $validation, $validation_messages);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $data = $request->all();

            if (!Auth::check()) {
                return $this->successResponse("You do not have permission.", (object)[], 400);
            }

            $choosenProgram = Program::where(['id' => $data['program_id'], 'status' => 1])->first();

            if (!$choosenProgram) {
                return $this->successResponse("Program not found", (object)[], 200);
            }

            // ✅ Check if already joined
            $alreadyJoined = UserProgram::where([
                'program_id' => $data['program_id'],
                'user_id' => Auth::user()->id,
                'status' => 1
            ])->exists();

            if ($alreadyJoined) {
                return $this->successResponse("You already joined this program", (object)[], 200);
            }

            // ✅ Check if same program_type exists
            $currentUserPrograms = UserProgram::with('program')
                ->where(['user_id' => Auth::user()->id, 'status' => 1])
                ->get();

            $matchingUserProgram = $currentUserPrograms->firstWhere(function ($userProgram) use ($choosenProgram) {
                return $userProgram->program->program_type == $choosenProgram->program_type;
            });

            if ($matchingUserProgram) {
                $matchingUserProgram->update([
                    'program_id' => $data['program_id'],
                    'join_date' => Carbon::today(),
                    'user_streak' => 0
                ]);
            } else {
                $userProgram = new UserProgram;
                $userProgram->user_id = Auth::user()->id;
                $userProgram->program_id = $data['program_id'];
                $userProgram->join_date = date("Y-m-d");
                $userProgram->status = 1;
                $userProgram->user_streak = 0;
                $userProgram->save();
            }

            // ✅ Fetch all active programs for user
            $userAssignedPrograms = UserProgram::with([
                    'program.category',
                    'program.program_session.session_exercises',
                    'program.program_session.session_exercises.exercise',
                    'program.program_session.session',
                    'program.level'
                ])
                ->where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->take(2)
                ->get();

            if ($userAssignedPrograms->isEmpty()) {
                return $this->successResponse("No programs assigned", (object)[], 200);
            }

            $response = [];

            foreach ($userAssignedPrograms as $userAssignedProgram) {
                $program = $userAssignedProgram->program;
                if (!$program) continue;

                $programVideos = array_map(fn($video) => asset(Storage::url($video)), explode("|", $program->video));

                $pendingSession = [];

                // ✅ Get only the first incomplete session
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

                        // ✅ Stop after the first incomplete session
                        break;
                    }
                }

                $response[] = [
                    'categoryId' => $program->category->id,
                    'categoryName' => $this->sanitizeText($program->category->category_name),
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

            return $this->successResponse("Programs retrieved successfully", $response, 200);

        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function userProgramsDetails()
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
                return $this->notFoundResponse("Program Not Found", (object)[], 200);
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
                    'categoryName' => $this->sanitizeText($program->category->category_name),
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

            return $this->successResponse("Program Found", $response, 200);

        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong with user assigned programs", $th->getMessage(), 500);
        }
    }
}
