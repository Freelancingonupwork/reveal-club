<?php

use App\Http\Controllers\API\V2\ProgressImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ActivityController;
use App\Http\Controllers\API\PageController;
use App\Http\Controllers\API\MayDayController;
use App\Http\Controllers\API\RecipeController;
use App\Http\Controllers\API\GroceryController;
use App\Http\Controllers\API\ProgramController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ChallengeController;
use App\Http\Controllers\API\CommunityController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\UtilitiesController;
use App\Http\Controllers\API\FoodPlanerController;
use App\Http\Controllers\API\UserRecipeController;
use App\Http\Controllers\API\AppPurchaseController;
use App\Http\Controllers\API\StepTrackerController;
use App\Http\Controllers\API\Auth\ApiAuthController;
use App\Http\Controllers\API\UserMeasurementController;
use App\Http\Controllers\API\Community\CommunityHeaderController;
use App\Http\Controllers\API\Community\CommunityPostController;
use App\Http\Controllers\API\V2\ProgramController as V2ProgramController;
use App\Http\Controllers\API\V2\DashboardController as V2DashboardController;
use App\Http\Controllers\API\V2\FoodPlanerController as V2FoodPlanerController;
use App\Http\Controllers\API\V2\StepTrackerController as V2StepTrackerController;
use App\Http\Controllers\API\V2\CommunityPostController as V2CommunityPostController;
use App\Http\Controllers\API\V2\UserMeasurementController as V2UserMeasurementController;
use App\Http\Controllers\API\V2\ChallengeController as V2ChallengeController;
use App\Http\Controllers\API\V2\RecipeController as V2RecipeController;
use App\Http\Controllers\API\V2\ApiAuthController as V2ApiAuthController;
use App\Http\Controllers\API\V3\FoodPlanerController as V3FoodPlanerController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1/')->group(function () {
    Route::post('user-login', [ApiAuthController::class, 'userLogin'])->name('api-user-login');
    Route::post('user-register', [ApiAuthController::class, 'userRegister'])->name('api-user-register');
    Route::post('forget-password', [ApiAuthController::class, 'forgotPassword'])->name('forget-password');
    Route::match(['get', 'post'], 'reset-password', [ApiAuthController::class, 'resetPassword'])->name('reset-password');
    // Register Guest User
    Route::post('register-guest', [ApiAuthController::class, 'registerGuestUser']);
    // Social Login
    Route::post('social-login', [ApiAuthController::class, 'socialLogin'])->name('social-login');
    // preSignUp
    Route::post('user-presignup', [ApiAuthController::class, 'preSignUp'])->name('user-presignup');
    //CMS Page
    Route::post('get-cms-page-detail', [UtilitiesController::class, 'cmsPageDetail'])->name('get-cms-page-detail');

    Route::group(['middleware' => ['auth:api']], function (): void {
        Route::delete('delete-account', [ApiAuthController::class, 'deleteAccount'])->name('api-delete-account');
        // Logout
        Route::post('user-logout', [ApiAuthController::class, 'logout'])->name('api-user-logout');
        // user Profile
        Route::post('user-profile', [ApiAuthController::class, 'userProfile'])->name('api-user-profile');
        // update user Profile
        Route::post('update-user-profile', [ApiAuthController::class, 'updateUserProfile'])->name('update-user-profile');
        // User Location
        Route::match(['get', 'post'], 'user-location', [ApiAuthController::class, 'userLocation'])->name('user-location');
        // update Security Token
        Route::post('/user/security-token', [ApiAuthController::class, 'updateOrCreateSecurityToken']);
        // Program
        Route::post('program-list', [ProgramController::class, 'programList'])->name('program-list');
        Route::post('program-details', [ProgramController::class, 'programDetails'])->name('program-details');
        Route::post('join-program', [ProgramController::class, 'joinProgram'])->name('join-program');
        Route::post('session-list', [ProgramController::class, 'sessionList'])->name('session-list');
        Route::post('join-session', [ProgramController::class, 'joinSession'])->name('join-session');
        Route::post('category-wise-programs', [ProgramController::class, 'categoryWisePrograms'])->name('category-wise-programs');
        // Exercise
        Route::post('exercise-list', [ProgramController::class, 'exerciseList'])->name('exercise-list');
        Route::post('exercise-detail', [ProgramController::class, 'exerciseDetail'])->name('exercise-detail');
        // Update User Session Status
        Route::post('update-user-session-status', [ProgramController::class, 'updateUserSessionStatus'])->name('update-user-session-status');
        // Category
        Route::post('category-list', [CategoryController::class, 'categoryList'])->name('category-list');
        // Cardio
        Route::post('cardio-list', [ProgramController::class, 'cardioList'])->name('cardio-list');
        // Community
        Route::post('discussion-list', [CommunityController::class, 'discussionList'])->name('discussion-list');
        Route::post('reply-discussion', [CommunityController::class, 'replyDiscussion'])->name('reply-discussion');
        Route::post('discussion-detail', [CommunityController::class, 'discussionDetail'])->name('discussion-detail');
        // Pages
        Route::post('page-list', [PageController::class, 'pageList'])->name('page-list');

        // Nutrition/Recipe/Diet
        Route::post('nutrition-list', [RecipeController::class, 'nutritionList'])->name('nutrition-list');
        Route::post('nutrition-detail', [RecipeController::class, 'nutritionDetail'])->name('nutrition-detail');
        Route::post('recipe-list', [RecipeController::class, 'recipeList'])->name('recipe-list');
        Route::post('recipe-detail', [RecipeController::class, 'recipeDetail'])->name('recipe-detail');
        Route::post('recipe-list-by-mealType', [RecipeController::class, 'recipeListByMealType'])->name('recipe-list-by-mealType');

        // Ingredient List
        Route::post('ingredient-list', [RecipeController::class, 'ingredientList'])->name('ingredient-list');
        // OpenFoodFacts Ingredient List
        Route::post('ingredient-list-by-openff', [RecipeController::class, 'openFoodFactsingredientList'])->name('ingredient-list-by-openff');
        // Recipe Ingredient List
        Route::post('ingredient-list-by-recipe', [RecipeController::class, 'ingredientListByRecipe'])->name('ingredient-list-by-recipe');
        // User Favourite Recipe
        Route::post('user-fav-recipe', [RecipeController::class, 'userFavouriteList'])->name('user-fav-recipe');
        // Total Fav Count on Recipe
        Route::post('recipe-fav-count', [RecipeController::class, 'totalFavCountOnRecipe'])->name('recipe-fav-count');

        // Add recipe to favourite
        Route::post('add-and-remove-recipe-to-fav', [RecipeController::class, 'addAndRemoveRecipeToFavourite'])->name('add-and-remove-recipe-to-fav');
        // Clear User Faourite List
        Route::post('clear-user-fav-list', [RecipeController::class, 'clearUserFavouriteRecipe'])->name('clear-user-fav-list');
        // Top Recipe
        Route::post('top-recipes', [RecipeController::class, 'topRecipes'])->name('top-recipes');

        // Add Recipe Ingredient to Grocery
        Route::post('add-ingredient-to-grocery', [GroceryController::class, 'addIngredientsToGrocery'])->name('add-ingredient-to-grocery');
        // Remove Recipe Ingredient to Grocery
        Route::post('remove-recipe-from-grocery', [GroceryController::class, 'removeRecipeFromGrocery'])->name('remove-recipe-from-grocery');
        // Mark Recipe Ingredient to purchase in Grocery List
        Route::post('mark-grocery-as-purchased', [GroceryController::class, 'markGroceryAsPurchased'])->name('mark-grocery-as-purchased');
        // Grocery List
        Route::post('grocery-list', [GroceryController::class, 'groceryList'])->name('grocery-list');
        // Increase Recipe Ingredients in Grocery List
        Route::post('increase-grocery-ingredients', [GroceryController::class, 'increaseGroceryIngredientsQuantity'])->name('increase-grocery-ingredients');
        // Finish The Race
        Route::post('finish-the-race', [GroceryController::class, 'finishTheRace'])->name('finish-the-race');

        // Search Nutrition
        Route::post('search-nutrition', [FoodPlanerController::class, 'searchNutrition'])->name('search-nutrition');
        Route::post('fetch-nutrition-Barcode', [FoodPlanerController::class, 'fetchByBarcode'])->name('fetch-nutrition-Barcode');
        Route::post('get-nutrition-by-name', [FoodPlanerController::class, 'getNutritionByName'])->name('get-nutrition-by-name');

        // Food Planner
        Route::post('daily-food-planner', [FoodPlanerController::class, 'dailyFoodPlanner'])->name('daily-food-planner');
        Route::post('get-food-planner', [FoodPlanerController::class, 'getFoodPlanner'])->name('get-food-planner');
        Route::post("delete-food-planner-item", [FoodPlanerController::class, 'deleteFoodPlanner'])->name('delete-food-planner-item');
        Route::get('meal-types-in-tracker', [FoodPlanerController::class, 'getVisibleMealTypes']);

        // Food Create
        Route::post('get-nutrition-ingredient-category', [FoodPlanerController::class, 'getNutritionIngredientCategory'])->name('get-nutrition-ingredient-category');
        Route::post('create-nutrition-ingredient-by-user', [FoodPlanerController::class, 'createNutritionIngredientByUser'])->name('create-nutrition-ingredient-by-user');
        Route::post('add-portionsize-to-nutrition', [FoodPlanerController::class, 'addPortionSizeToNutrition'])->name('add-portionsize-to-nutrition');
        Route::post('check-ingredient-exists', [FoodPlanerController::class, 'checkIngredientExist'])->name('check-ingredient-exists');
        // Calendar API
        Route::post("calendar-view-food-log", [FoodPlanerController::class, 'calendarWiseFoodLogRecord'])->name('calendar-view-food-log');

        // Set Steps Goal
        Route::post('set-steps-goal', [StepTrackerController::class, 'setStepsGoal'])->name('set-steps-goal');
        // Update Steps Goal
        Route::post('update-steps-goal', [StepTrackerController::class, 'updateStepsGoal'])->name('update-steps-goal');
        // Step Tracker
        Route::post('step-tracker', [StepTrackerController::class, 'stepsTracker'])->name('step-tracker');
        // Get Steps
        Route::post('get-steps', [StepTrackerController::class, 'getSteps'])->name('get-steps');

        // Change Password
        Route::post('change-password', [ApiAuthController::class, 'changePassword'])->name('change-password');

        // User Body Measurement
        Route::post('user-measurement', [UserMeasurementController::class, 'userMeasures'])->name('user-measurement');
        // Get User Measurement
        Route::post('get-user-measurement', [UserMeasurementController::class, 'getUserMeasurements'])->name('get-user-measurement');
        // Add User Evolution
        Route::post('add-user-evolution', [UserMeasurementController::class, 'addEvolution'])->name('add-user-evolution');
        // Get User Evolution Measurements
        Route::post('get-user-evolution-measurement', [UserMeasurementController::class, 'getUserEvolutionMeasurement'])->name('get-user-evolution-measurement');
        // Change Tracking Current
        Route::post('change-evolution', [UserMeasurementController::class, 'changeTrackingCurrent'])->name('change-evolution');
        // Delete User Evolution
        Route::post('delete-user-transformation-image', [UserMeasurementController::class, 'deleteEvolution'])->name('delete-user-transformation-image');
        // Update User Evolution
        Route::post('update-evolution', [UserMeasurementController::class, 'updateEvolutionImage'])->name('update-evolution');
        // Video About
        Route::get('get-video-about-list', [UtilitiesController::class, 'videoAboutList'])->name('get-video-about-list');
        // Add Personalised Grocery
        Route::post('add-personalised-grocery', [GroceryController::class, 'addPersonalisedGrocery'])->name('add-personalised-grocery');
        // Dashboard API
        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('api-dashboard');
        //standalone for android
        Route::get('stepsTrackingforAndroid', [DashboardController::class, 'stepsTrackingforAndroid'])->name('api-stepsTrackingforAndroid');
        //Lessons
        Route::post('daily-lessons-planner', [DashboardController::class, "dailyLessonsPlanner"])->name('daily-lessons-planner');
        Route::get('logLessonsForUsers', [DashboardController::class, "logLessonsForUsers"])->name('logLessonsForUsers');
        Route::get('logAppearanceForUsers', [DashboardController::class, "logAppearanceForUsers"])->name('logAppearanceForUsers');
        // Onboarding
        Route::get('onboarding-challenge-status', [DashboardController::class, 'onBoardingChallengeStatus'])->name('onboarding-challenge-status');

        //App Purchase
        Route::post('cancel-app-purchase', [AppPurchaseController::class, "cancelAppPurchase"])->name('cancel-app-purchase');
        // Nutrition History
        Route::get('nutrition-history', [FoodPlanerController::class, 'nutritionHistory'])->name('nutrition-history');
        // Nutrition Favourite
        Route::post('nutrition-favourite', [FoodPlanerController::class, 'nutritionFavourite'])->name('nutrition-favourite');
        // Nutrition Favourite List
        Route::post('nutrition-favourite-list', [FoodPlanerController::class, 'nutritionFavouriteList'])->name('nutrition-favourite-list');

        // User Recipe
        Route::post('user-create-recipe', [UserRecipeController::class, 'userRecipeCreate'])->name('user-create-recipe');
        Route::post('user-update-recipe', [UserRecipeController::class, 'userRecipeUpdate'])->name('user-update-recipe');
        Route::post('user-delete-recipe', [UserRecipeController::class, 'userDeleteRecipe'])->name('user-delete-recipe');
        // User Recipe Ingredient
        Route::post('add-ingredient-user-recipe', [UserRecipeController::class, 'addIngredientUserRecipe'])->name('add-ingredient-user-recipe');
        Route::post('user-recipe-list', [UserRecipeController::class, 'userRecipeList'])->name('user-recipe-list');
        Route::post('user-recipe-details', [UserRecipeController::class, 'userRecipeDetails'])->name('user-recipe-details');

        // Challenge
        Route::get('challenge-list', [ChallengeController::class, 'challengeList'])->name('challenge-list');
        Route::post('challenge-level', [ChallengeController::class, 'challengeLevel'])->name('challenge-level');
        Route::post('challenge-user', [ChallengeController::class, 'challengeUser'])->name('challenge-user');
        Route::get('user-challenge-details', [ChallengeController::class, 'UserChallengeDetails'])->name('user-challenge-details');
        Route::post('log-challenge-day', [ChallengeController::class, 'logChallengeDay'])->name('log-challenge-day');

        // Community Post
        Route::post('add-community-post-comment', [CommunityPostController::class, 'addCommunityPostComment'])->name('add-community-post-comment');
        Route::post('reply-to-comment', [CommunityPostController::class, 'replyToComment'])->name('reply-to-comment');
        Route::post('report-to-comment', [CommunityPostController::class, 'reportToComment'])->name('report-to-comment');
        Route::post('report-to-comment-reply', [CommunityPostController::class, 'reportToCommentReply'])->name('report-to-comment-reply');
        Route::post('community-post-comments', [CommunityPostController::class, 'getCommunityPostComments'])->name('community-post-comments');
        Route::delete('delete-community-posts-comment/{id}', [CommunityPostController::class, 'deleteCommunityPostComment'])->name('delete-community-posts-comment');
        Route::delete('delete-comment-reply/{id}', [CommunityPostController::class, 'deleteCommentReply'])->name('delete-comment-reply');

        Route::delete('delete-user-data/{user_id}', [DashboardController::class, 'deleteUserData']);
        Route::delete('delete-user-level-milestone-data/{user_id}', [DashboardController::class, 'deleteUserLevelMilestoneData']);
        Route::post('complete-challenge-upto-last-day', [DashboardController::class, 'completeUptoLastChallengeDay'])->name('complete-challenge-upto-last-day');
        //Community Related APIs
        Route::get('fetch-community-header', [CommunityHeaderController::class, 'fetchCommunityHeader'])->name('fetch-community-header');
        Route::get('fetch-post-topics', [CommunityPostController::class, 'fetchPostTopics'])->name('fetch-post-topics');

        Route::post('create-community-post', [CommunityPostController::class, 'createPost'])->name('create-community-post');
        Route::post('update-community-post', [CommunityPostController::class, 'updatePost'])->name('update-community-post');
        Route::delete('delete-community-posts/{post_id}', [CommunityPostController::class, 'deletePost'])->name('delete-community-posts');

        Route::get('fetch-my-community-posts', [CommunityPostController::class, 'fetchMyPosts'])->name('fetch-my-community-posts');
        Route::post('fetch-all-community-posts', [CommunityPostController::class, 'fetchAllPosts'])->name('fetch-all-community-posts');

        Route::post('like-community-post', [CommunityPostController::class, 'likePost'])->name('like-community-post');
        Route::post('report-community-post', [CommunityPostController::class, 'reportPost'])->name('report-community-post');

        Route::get('log-user-streak', [DashboardController::class,'logUserStreak'])->name('log-user-streak');
        Route::post('get-level-details', [DashboardController::class, 'levelDetails'])->name('get-level-details');

        Route::post('user-app-feedback', [DashboardController::class, 'userAppFeedback'])->name('user-app-feedback');
        Route::get('pop-up-shown', [DashboardController::class,'popUpShown'])->name('pop-up-shown');

        // List activities with search and tracking info
        Route::post('activities', [ActivityController::class, 'listActivities']);
        
        // Tracked activities
        Route::post('tracked-activities', [ActivityController::class, 'listTrackedActivities']);
        Route::post('track-activity', [ActivityController::class, 'trackActivity']);
        Route::post('update-tracked-activity', [ActivityController::class, 'updateTrackedActivity']);
        Route::post('delete-tracked-activity', [ActivityController::class, 'deleteTrackedActivity']);
        
        // Calories calculation
        Route::post('calculate-calories', [ActivityController::class, 'calculateCalories']);

        // Calendar API
        Route::post("calendar-view-activity-log", [ActivityController::class, 'calendarWiseActivityTrackedRecord'])->name('v2-calendar-view-activity-log');

    });
    //App Purchase
    Route::post('app-purchase', [AppPurchaseController::class, "appPurchase"])->name('app-purchase');

    //ios device log
    Route::post('ios-device-log', [AppPurchaseController::class, "iosDeviceLog"])->name('ios-device-log');
});

Route::prefix('v2/')->group(function () {
    Route::group(['middleware' => ['auth:api']], function (): void {
        // Dashboard API
        Route::get('dashboard', [V2DashboardController::class, 'dashboard'])->name('v2-api-dashboard');

        // Lessons API
        Route::post('daily-lessons-planner',[V2DashboardController::class, "dailyLessonsPlanner"])->name('v2-api-daily-lessons-planner');

        // Step-tracker API

        Route::post('step-tracker', [V2StepTrackerController::class, 'stepsTracker'])->name('v2-api-step-tracker');

        // Community API
        Route::post('like-community-post', [V2CommunityPostController::class, 'likePost'])->name('v2-api-like-community-post');
        Route::post('reply-to-comment', [V2CommunityPostController::class, 'replyToComment'])->name('v2-api-reply-to-comment');
        Route::post('add-community-post-comment', [V2CommunityPostController::class, 'addCommunityPostComment'])->name('v2-api-add-community-post-comment');
        Route::post('create-community-post', [V2CommunityPostController::class, 'createPost'])->name('v2-api-create-community-post');
        Route::post('report-to-comment', [V2CommunityPostController::class, 'reportToComment'])->name('v2-api-report-to-comment');
        Route::post('report-to-comment-reply', [V2CommunityPostController::class, 'reportToCommentReply'])->name('v2-api-report-to-comment-reply');
        Route::post('community-post-comments', [V2CommunityPostController::class, 'getCommunityPostComments'])->name('v2-api-community-post-comments');
        Route::delete('delete-community-posts-comment/{id}', [V2CommunityPostController::class, 'deleteCommunityPostComment'])->name('v2-api-delete-community-posts-comment');
        Route::delete('delete-comment-reply/{id}', [V2CommunityPostController::class, 'deleteCommentReply'])->name('v2-api-delete-comment-reply');

        // User Evolutions API
        Route::post('add-user-evolution', [V2UserMeasurementController::class, 'addEvolution'])->name('v2-api-add-user-evolution');
        Route::post('user-measurement', [V2UserMeasurementController::class, 'userMeasures'])->name('v2-api-user-measurement');
        Route::post('get-user-evolution-measurement', [V2UserMeasurementController::class, 'getUserEvolutionMeasurement'])->name('v2-api-get-user-evolution-measurement');
        Route::post('get-user-measurement', [V2UserMeasurementController::class, 'getUserMeasurements'])->name('v2-api-get-user-measurement');
        Route::post('delete-user-transformation-image', [V2UserMeasurementController::class, 'deleteEvolution'])->name('v2-api-delete-user-transformation-image');

        // Progress Images API
        Route::post('save-progress-images', [ProgressImageController::class, 'saveProgressImages'])->name('v2-api-save-progress-images');
        Route::get('progress-images', [ProgressImageController::class, 'getProgressImages'])->name('v2-api-get-progress-images');


        // Program API
        Route::post('update-user-session-status', [V2ProgramController::class, 'updateUserSessionStatus'])->name('v2-api-update-user-session-status');
        Route::post('join-program', [V2ProgramController::class, 'joinProgram'])->name('v2-api-join-program');
        Route::get('user-programs-details', [V2ProgramController::class, 'userProgramsDetails'])->name('v2-api-user-programs-details');

        // Food Planner API
        Route::post('daily-food-planner', [V2FoodPlanerController::class, 'dailyFoodPlanner'])->name('v2-api-daily-food-planner');
        Route::post('update-daily-food-planner', [V2FoodPlanerController::class, 'updateDailyFoodPlanner'])->name('v2-api-update-daily-food-planner');
        Route::post('get-food-planner', [V2FoodPlanerController::class, 'getFoodPlanner'])->name('v2-api-get-food-planner');
        Route::post("delete-food-planner-item", [V2FoodPlanerController::class, 'deleteFoodPlanner'])->name('v2-api-delete-food-planner-item');

        // User Favourite Recipe
        Route::post('user-fav-recipe', [V2RecipeController::class, 'userFavouriteList'])->name('v2-api-user-fav-recipe');

        // Challlenge API
        Route::post("log-challenge-day", [V2ChallengeController::class, 'logChallengeDay'])->name('v2-api-log-challenge-day');

        Route::post('fetch-food-json-from-AI', [V2FoodPlanerController::class, 'fetchFoodJsonFromAI'])->name('v2-api-fetch-food-json-from-AI');


        Route::match(['get', 'post'], 'user-location', [V2ApiAuthController::class, 'userLocation'])->name('v2-api-user-location');

    });
});

Route::prefix('v3/')->group(function () {
    Route::group(['middleware' => ['auth:api']], function (): void {
        Route::post('get-food-planner', [V3FoodPlanerController::class, 'getFoodPlanner'])->name('v3-api-get-food-planner');
    });
});