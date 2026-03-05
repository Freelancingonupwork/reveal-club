<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FoodPlanner;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\UserLevelTask;
use App\Models\UserLevelTaskLog;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    use ApiResponser;

    public static function getLoginKey($user_id)
    {
        $salt = "23df$#%%^66sd$^%fg%^sjg4554dk90fdklndg099ndfg09LKJDJ6*@##lkhlkhlsa#$%";
        $login_key = hash('sha1', $salt . $user_id . time());
        return $login_key;
    }

    public function pagination($data, $page, $limit)
    {
        $totalRecords = count($data);
        $start = ($page - 1) * $limit;
        $end = $start + $limit;
        $paginatedData = array_slice($data, $start, $limit);

        $hasMore = $end < $totalRecords;

        return [
            'data' => $paginatedData,
            'hasMore' => $hasMore,
        ];
    }

    public function logLevelTaskEntries($userId, $task_type, $completed_count, $oneEntry)
    {
        // Get all levelTaskList for the user
        $user = User::find($userId);
        $UserLevel = UserLevel::where('level', $user->level)->with('userLevelTasks')->first();

        if (!$UserLevel) {
            return 0;
        }

        if ($oneEntry == 1) {
            // Update existing record's completed_count or create if not exists
            $log = UserLevelTaskLog::where([
                'user_id' => $userId,
                'level_task_type' => $task_type,
            ])->latest()->first();
            if ($log) {
                $log->completed_count = $completed_count;
                $log->save();
            } else {
                UserLevelTaskLog::create([
                    'user_id' => $userId,
                    'level_task_type' => $task_type,
                    'completed_count' => $completed_count,
                ]);
            }
        } else {
            // Log the record in userTaskListLogs table
            UserLevelTaskLog::create([
                'user_id' => $userId,
                'level_task_type' => $task_type,
                'completed_count' => $completed_count,
            ]);
        }

        $total_completed_count = (int) UserLevelTaskLog::where([
            'user_id' => $userId,
            'level_task_type' => $task_type,
        ])->sum('completed_count');


        $allCompleted = true;
        foreach ($UserLevel->userLevelTasks as $task) {
            $total_completed_count = (int) UserLevelTaskLog::where([
                'user_id' => $userId,
                'level_task_type' => $task->task_type,
            ])->sum('completed_count');
            if ($total_completed_count < $task->total_count) {
                $allCompleted = false;
                break;
            }
        }

        if ($allCompleted) {
            $currentLevel = $user->level;

            // Set user's level to currentLevel + 1 in the users table
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'level' => $currentLevel + 1,
                    'previous_level_popup_shown' => '0',
                ]);
            return $currentLevel;
        } else {
            return 0;
        }
    }

    public function getLevelDetails($level)
    {
        $userCurrentLevel = Auth::user()->level;

        // Check if the level exists
        $SelectedLevel = UserLevel::where('level', $level)->first();
        if (!$SelectedLevel) {
            return null; // Return null if level doesn't exist
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
        ];

        return $levelData;
    }
    public function deleteLogWaterEntries($userId){

        $WaterLog = UserLevelTaskLog::where([
            'user_id' => $userId,
            'level_task_type' => "drink-water",
        ])->latest()->first();

        if ($WaterLog) {
            $WaterLog->delete();
        }
    }

    public function deleteLogMealTypeEntries($userId, $mealType, $date)
    {
        $count = FoodPlanner::where([
            'meal_type' => $mealType,
            'date' => $date,
            'user_id' => $userId
        ])->count();

        if ($count === 1) {
            // If only one record exists, delete the latest 'log-meals' entry from UserLevelTaskLog
            $log = UserLevelTaskLog::where('user_id', $userId)
                ->where('level_task_type', 'log-meals')
                ->latest()
                ->first();

            if ($log) {
                $log->delete();
            }
        }
    }


    public function deleteLogProtienEntries($userId, $proteins)
    {
        $remainingToDelete = $proteins;

        // Get all protein logs for the user, ordered by latest first
        $logs = UserLevelTaskLog::where([
            'user_id' => $userId,
            'level_task_type' => 'protiens',
        ])->orderBy('created_at', 'desc')->get();

        foreach ($logs as $log) {
            if ($remainingToDelete <= 0) {
                break;
            }

            if ($log->completed_count > $remainingToDelete) {
                // Reduce the completed_count and save
                $log->completed_count -= $remainingToDelete;
                $log->save();
                $remainingToDelete = 0;
            } else {
                // Delete the log and reduce the remainingToDelete
                $remainingToDelete -= $log->completed_count;
                $log->delete();
            }
        }
    }
}
