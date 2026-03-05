<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeDay;
use App\Models\ChallengeExercise;
use App\Models\ChallengeLevel;
use App\Models\ChallengeUser;
use App\Models\ChallengeUserStatus;
use App\Models\ExerciseOfChallenge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ChallengeController extends ApiController
{
    public function challengeList()
    {
        try {
            $challengeList = Challenge::select('id', 'title', 'title_for_frontend', 'description', 'video_url', 'gif_url')->where('status' , 1)->get();

            $uniqueChallengeData = ChallengeDay::select('challenge_id', 'challenge_level_id')->distinct()->get();

            $challengeCounts = $uniqueChallengeData->groupBy('challenge_id')->mapWithKeys(function ($items, $challengeId) {
                return [$challengeId => $items->count()];
            });

            $challengeList->transform(function ($challenge) use ($challengeCounts) {
                $challenge->count = $challengeCounts[$challenge->id] ?? 0;
                $challenge->gif = $challenge->gif_url ? asset(Storage::url($challenge->gif_url)) : '';
                return $challenge;
            });
            
            if ($challengeList->isNotEmpty()) {
                return $this->successResponse("data found", $challengeList, 200);
            }
            return $this->successResponse("data not found", $challengeList, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function challengeLevel(Request $request)
    {
        try {
            $rules = ['challenge_id' => ['required']];
            $messages = ['challenge_id.required' => "Please enter challenge."];
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $data = $request->all();

            $challenge = Challenge::select('id', 'title', 'title_for_frontend', 'description', 'video_url', 'gif_url')->where('id', $data['challenge_id'])->first();

            if (!$challenge) {
                return $this->successResponse("Challenge not found", (object)[], 200);
            }

            $uniqueChallengeData = ChallengeDay::select('day', 'challenge_id', 'challenge_level_id')->where('challenge_id', $data['challenge_id'])->distinct()->get();

            $challengeLevelIds = $uniqueChallengeData->pluck('challenge_level_id');

            $challengeLevels = ChallengeLevel::select('id', 'title', 'description as sub_title')->whereIn('id', $challengeLevelIds)->get();

            $challengeLevels->transform(function ($challengeLevel) use ($uniqueChallengeData, $data) {
                $day = $uniqueChallengeData->where('challenge_level_id', $challengeLevel->id)->last()->day ?? null;
                $challengeLevel->day = (int) $day;
    
                // Fetch the first day's first exercise details
                $firstDay = ChallengeDay::where(['challenge_level_id' => $challengeLevel->id,'challenge_id' => $data['challenge_id']] )->where('day', 1)->first();
                if ($firstDay) {
                    $firstExercise = $firstDay->exercisesOfChallenge()->where('order', 1)->first();    
                    if ($firstExercise) {
                        $exerciseDetails = ChallengeExercise::find($firstExercise->exercise_id);
                        $challengeLevel->first_day_first_exercise = [
                            'title' => $exerciseDetails->title_for_frontend,
                            'slug' => $exerciseDetails->slug,
                            'instructions' => $exerciseDetails->instructions,
                            'video_url_exercise' => $exerciseDetails->video_url_exercise,
                            'video_url_presentation' => $exerciseDetails->video_url_presentation,
                            'gif' => $exerciseDetails->gif ? asset(Storage::url($exerciseDetails->gif)) : '',
                        ];
                    } else {
                        $challengeLevel->first_day_first_exercise = null;
                    }
                } else {
                    $challengeLevel->first_day_first_exercise = null;
                }
                
                return $challengeLevel;
            });

            $responseData = [
                "id" => $challenge->id,
                "title" => $challenge->title,
                "title_for_frontend" => $challenge->title_for_frontend,
                "description" => $challenge->description,
                "video_url" => $challenge->video_url,
                "gif" => $challenge->gif_url ? asset(Storage::url($challenge->gif_url)) : '',
                "challenge_level" => $challengeLevels
            ];

            return $this->successResponse("data found", $responseData, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function challengeUser(Request $request)
    {
        try {
            $rules = [
                'challenge_id' => ['required'],
                'challenge_level_id' => ['required']
            ];

            $message = [
                'challenge_id.required' => "Please enter challenge.",
                'challenge_level_id.required' => "Please enter challenge level.",
            ];
            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $challengeId = $request->challenge_id;
            $challengeLevelId = $request->challenge_level_id;
            $userId = Auth::user()->id;

            $challengeDay = ChallengeDay::where(['challenge_id' => $challengeId, 'challenge_level_id' => $challengeLevelId])->first();
            if (!$challengeDay) {
                return $this->notFoundResponse("Challenge not exists!", (object)[], 200);
            }

            ChallengeUser::where('user_id', $userId)
            ->where(function ($query) use ($challengeId, $challengeLevelId) {
                $query->where('challenge_id', '!=', $challengeId)
                      ->orWhere('challenge_level_id', '!=', $challengeLevelId);
            })
            ->update(['status' => 0]);
            
            $challengeUser = ChallengeUser::where(['user_id' => $userId, 'challenge_id' => $challengeId, 'challenge_level_id' => $challengeLevelId])->first();

            if ($challengeUser) {
                $challengeUser->status = 1;
            } else {
                $challengeUser = new ChallengeUser;
                $challengeUser->user_id = $userId;
                $challengeUser->challenge_id = $challengeId;
                $challengeUser->challenge_level_id = $challengeLevelId;
                $challengeUser->status = 1;
            }

            $challengeUser->save();

            $challengeUserDetails = [
                'user_id' => $challengeUser->user_id,
                'challenge_id' => (int) $challengeUser->challenge_id,
                'challenge_level_id' => (int) $challengeUser->challenge_level_id,
                'status' => $challengeUser->status,
                'updated_at' => $challengeUser->updated_at,
                'created_at' => $challengeUser->created_at,
                'id' => $challengeUser->id,
            ];

            return $this->successResponse("Data save successfully", $challengeUserDetails, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function UserChallengeDetails()
    {
        try {
            $userId = Auth::user()->id;
            $challengeUser = ChallengeUser::where(['user_id' => $userId, 'status' => 1])->first();
            
            if (!$challengeUser) {
                return $this->notFoundResponse("You have not chosen any challenge!", (object)[], 200);
            }
    
            $challenge = Challenge::select('id as challenge_id', 'title as challenge_title', 'title_for_frontend as challenge_title_for_frontend', 'description as challenge_description', 'video_url as challenge_video_url', 'gif_url as challenge_gif')
                ->where(['id' => $challengeUser->challenge_id])
                ->first();
    
            if (!$challenge) {
                return $this->notFoundResponse("Challenge not found!", (object)[], 200);
            }
    
            $challengeDays = ChallengeDay::select('id', 'day', 'challenge_level_id', 'description')
                ->where('challenge_id', $challenge->challenge_id)
                ->where('challenge_level_id', $challengeUser->challenge_level_id)
                ->orderByRaw('CAST(day AS UNSIGNED) ASC')
                ->get();

            if ($challengeDays->isEmpty()) {
                return $this->notFoundResponse("Challenge days not found!", (object)[], 200);
            }
    
            $challengeDaysNumbers = $challengeDays->pluck('day')->toArray(); // Using day instead of id
    
            // Get completed days based on completed_day
            $completedDays = ChallengeUserStatus::where('user_id', $userId)
                ->where('challenge_id', $challenge->challenge_id)
                ->where('challenge_level_id', $challengeUser->challenge_level_id)
                ->pluck('completed_day') // Fetch completed_day instead of challenge_day_id
                ->toArray();
    
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
    
            $challengeLevel->total_days = $challengeDays->count();
            $challengeLevel->total_completed_days = count($completedDays);

            $isat = 0;
            $todayDate = date('Y-m-d');
            
            // Check if user has completed today's exercises
            $todayCompletedDay = ChallengeUserStatus::where('user_id', $userId)
                ->where('complete_date', $todayDate)
                ->where('challenge_id', $challenge->challenge_id)
                ->where('challenge_level_id', $challengeUser->challenge_level_id)
                ->pluck('completed_day') // Get completed_day value
                ->first();
            
            // Determine which day's data to return
            $selectedDay = null;
            if ($todayCompletedDay) {
                // Fetch today's completed day
                $selectedDay = $challengeDays->where('day', $todayCompletedDay)->first();
            } else {
                // Find the next incomplete day with exercises
                $selectedDay = $challengeDays->first(function ($day) use ($completedDays, &$isat) {
                    return !in_array($day->day, $completedDays) && $isat++ == 0;
                });
            }
            
            if ($selectedDay) {
                $dayExercises = ExerciseOfChallenge::where('challenge_day_id', $selectedDay->id)
                    ->with('exercise')
                    ->get()
                    ->map(function ($exercise) {
                        if ($exercise->exercise->status == 1) {
                            return [
                                'id' => $exercise->id,
                                'exercise_id' => $exercise->exercise->id ?? '',
                                'name' => $exercise->exercise->title_for_frontend ?? $exercise->exercise->title,
                                'instructions' => $exercise->exercise->instructions ?? '',
                                'video_url_exercise' => $exercise->exercise->video_url_exercise ?? '',
                                'video_url_presentation' => $exercise->exercise->video_url_presentation ?? '',
                                'gif' => $exercise->exercise->gif ? asset(Storage::url($exercise->exercise->gif)) : '',
                                'exercise_type' => $exercise->exercise_type ?? '',
                                'duration' => $this->convertSecondsToTimeFormat($exercise->duration ?? 0),
                                'repetitions' => $exercise->no_of_repetition ?? 0,
                                'rest' => $this->convertSecondsToTimeFormat($exercise->rest_period ?? 0)
                            ];
                        }
                    })
                    ->filter()
                    ->values();
            
                $challengeLevel->challenge_level_days = [[
                    'id' => $selectedDay->id,
                    'day' => (int) $selectedDay->day,
                    'description' => $selectedDay->description,
                    'is_day_completed' => $todayCompletedDay ? 1 : 0,
                    'day_exercise' => $dayExercises
                ]];
            } else {
                $challengeLevel->challenge_level_days = [];
            }
    
            $responseData = [
                "challenge_id" => $challenge->challenge_id,
                "challenge_title" => $challenge->challenge_title,
                "challenge_title_for_frontend" => $challenge->challenge_title_for_frontend,
                "challenge_description" => $challenge->challenge_description,
                "challenge_video_url" => $challenge->challenge_video_url,
                "challenge_gif" => $challenge->challenge_gif ? asset(Storage::url($challenge->challenge_gif)) : '',
                "challenge_level" => $challengeLevel,
                "todayExercisesCompleted" => $todayExercisesCompleted,
                "milestone_screen" => ($challengeLevel->total_completed_days <= 3 ? 1 : ($challengeLevel->total_completed_days > 3 && $challengeLevel->total_completed_days <= 7 ? 2 : 3))
            ];
    
            return $this->successResponse("Data found", $responseData, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }
    private function convertSecondsToTimeFormat($seconds)
    {
        // Ensure it's an integer
        $seconds = (int) $seconds;
    
        // Calculate minutes and remaining seconds
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
    
        // Return in mm:ss format, ensuring two digits for seconds
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function logChallengeDay(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'challenge_day_id' => 'required'
            ], [
                'challenge_day_id.required' => 'Please add challenge day id.'
            ]);
            
            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $userId = Auth::user()->id;
            $challenge_day_id = $request->challenge_day_id;
            $todayDate = date('Y-m-d');

            $challengeDay = ChallengeDay::find($challenge_day_id);
            if (!$challengeDay) {
                return $this->notFoundResponse("Challenge day is not found!", (object)[], 200);
            }
    
            $completed_day = $challengeDay->day; // Get the completed day based on the ChallengeDay record
    
            $alreadyCompletedToday = ChallengeUserStatus::where('user_id', $userId)
                ->where('complete_date', $todayDate)
                ->where('completed_day', $completed_day) // Check by completed_day instead of challenge_day_id
                ->whereHas('challengeDay', function ($query) use ($challengeDay) {
                    $query->where('challenge_id', $challengeDay->challenge_id)
                          ->where('challenge_level_id', $challengeDay->challenge_level_id);
                })
                ->exists();

            if ($alreadyCompletedToday) {
                return $this->successResponse("You have already completed a challenge today.", (object)[], 200);
            }
    
            $userChallengeData = ChallengeUser::where(['user_id' => $userId, 'challenge_id' => $challengeDay->challenge_id, 'challenge_level_id' => $challengeDay->challenge_level_id, 'status' => 1])->first();
            if (!$userChallengeData) {
                return $this->notFoundResponse("Challenge is not found!", (object)[], 200);
            }

            // Check if the user has already completed this day, regardless of challenge_day_id
            $completedDay = ChallengeUserStatus::where(['user_id' => $userId, 'challenge_id' => $challengeDay->challenge_id, 'challenge_level_id' => $challengeDay->challenge_level_id, 'completed_day' => $completed_day,])->first();

            if ($completedDay) {
                return $this->successResponse("Challenge day is already completed.", $completedDay, 200);
            }
    
            // Ensure previous challenge days are completed
            $previousChallengeDays = ChallengeDay::where('challenge_id', $challengeDay->challenge_id)
                ->where('challenge_level_id', $challengeDay->challenge_level_id)
                ->whereRaw('CAST(day AS UNSIGNED) < ?', [$challengeDay->day])
                ->pluck('day') // Now fetching day instead of ID
                ->toArray();
    
            if (!empty($previousChallengeDays)) {
                $previousCompletedDays = ChallengeUserStatus::where('user_id', $userId)
                    ->where('challenge_id', $challengeDay->challenge_id)
                    ->where('challenge_level_id', $challengeDay->challenge_level_id)
                    ->pluck('completed_day') // Now checking by completed_day
                    ->toArray();

                if (array_diff($previousChallengeDays, $previousCompletedDays)) {
                    return $this->successResponse("Please complete previous days before logging this challenge day.", [], 200);
                }
            }

            // Store challenge day completion
            $challengeUserStatus = new ChallengeUserStatus;
            $challengeUserStatus->user_id = $userId;
            $challengeUserStatus->challenge_day_id = $challenge_day_id;
            $challengeUserStatus->challenge_id = $challengeDay->challenge_id;
            $challengeUserStatus->challenge_level_id = $challengeDay->challenge_level_id;
            $challengeUserStatus->completed_day = $completed_day; // Store by day instead of challenge_day_id
            $challengeUserStatus->status = 1;
            $challengeUserStatus->complete_date = $todayDate;
            $challengeUserStatus->save();

            return $this->successResponse("Challenge day completed successfully.", $challengeUserStatus, 200);
        } catch (\Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }
}
