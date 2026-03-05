
<?php

use App\Http\Controllers\Admin\AnswerController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Challenges\ChallengeController;
use App\Http\Controllers\Admin\Challenges\ChallengeExerciseController;
use App\Http\Controllers\Admin\Community\CommunityHeaderController;
use App\Http\Controllers\Admin\Community\CommunityPostController;
use App\Http\Controllers\Admin\Community\CommunityPostTopicsController;
use App\Http\Controllers\Admin\DiscussionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\Program\CategoryController;
use App\Http\Controllers\Admin\Program\ExerciseController;
use App\Http\Controllers\Admin\Program\ProgramController;
use App\Http\Controllers\Admin\Program\SessionController;
use App\Http\Controllers\Admin\PromocodeController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\Recipe\DietController;
use App\Http\Controllers\Admin\Recipe\IngredientController;
use App\Http\Controllers\Admin\Recipe\MealController;
use App\Http\Controllers\Admin\Recipe\NutritionController;
use App\Http\Controllers\Admin\Recipe\RecipesController;
use App\Http\Controllers\Admin\Recipe\NutritionIngredientController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TransitionController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\StripeWebhookController;
use App\Http\Controllers\API\CommunityController;
use App\Http\Controllers\Testing\FoodProductController;
use App\Http\Controllers\UtitlitiesController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\CalorieCalculator;
use App\Http\Controllers\Web\CMSPageController;
use App\Http\Controllers\Web\CommunityController as WebCommunityController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\NewSubscriptionController as WebNewSubscriptionController;
use App\Http\Controllers\Web\QuizController as WebQuizController;
use App\Http\Controllers\Web\UnsubscriptionController;
use App\Models\CommunityPosts;
use App\Models\UserReferenceAnswer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('auth.login');
// })->name('login');
Route::redirect('/', '/pre-start-quiz');
Route::fallback(function () {
    return view('page404');
});

Route::get('pattern', [AuthController::class, 'patterns']);

Auth::routes();

Route::get('google-login', [WebAuthController::class, 'googleLogin'])->name('google-login');
Route::get('/auth/google/callback', [WebAuthController::class, 'googleHandle']);

Route::get('facebook-login', [AuthController::class, 'facebookLogin'])->name('facebook-login');
Route::get('/auth/facebook/callback', [AuthController::class, 'facebookHandle']);

Route::match(['get', 'post'], 'user-login', [WebAuthController::class, 'userLogin'])->name('user-login');
Route::match(['get', 'post'], 'user-register', [WebAuthController::class, 'userRegister'])->name('user-register');
Route::match(['get', 'post'], 'user-forget-password', [UtitlitiesController::class, 'forgotPassword'])->name('user-forget-password');
Route::match(['get', 'post'], 'user-reset-password/{token}', [UtitlitiesController::class, 'resetPassword'])->name('user-reset-password');
Route::match(['get', 'post'], 'user-finish-registration/{token}', [WebQuizController::class, 'finishRegistration'])->name('user-finish-registration');
Route::match(['get', 'post'], 'referral-source', [WebQuizController::class, 'referralSource'])->name('referral-source');
Route::match(['get', 'post'], 'address', [WebQuizController::class, 'address'])->name('address');
Route::match(['get', 'post'], 'download', [WebQuizController::class, 'download'])->name('download');
Route::match(['get', 'post'], 'countries', [WebQuizController::class, 'countries'])->name('countries');
Route::match(['get', 'post'], 'delete-user', [WebAuthController::class, 'deleteUser'])->name('delete-user');
Route::match(['get', 'post'], 'delete-user-account', [WebAuthController::class, 'deleteUserAccount'])->name('delete-user-account');

Route::prefix('admin')->group(function () {
    Route::match(['get', 'post'], '/', [AuthController::class, 'login'])->name('admin.login');
    Route::match(['get', 'post'], 'forgot-password', [UtitlitiesController::class, 'forgotPassword'])->name('admin.forgotPassword');
    Route::match(['get', 'post'], 'reset-password/{token}', [UtitlitiesController::class, 'adminResetPassword'])->name('admin.reset-password');
    Route::group(['middleware' => ['admin']], function () {

        Route::get('/dashboard', [AuthController::class, 'index'])->name('admin.dashboard');
        Route::get('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::match(['get', 'post'], '/profile', [AuthController::class, 'profile'])->name('admin.profile');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('admin.change-password');

        // Programs
        // Category
        Route::match(['get', 'post'], '/category-create', [CategoryController::class, 'create'])->name('admin.category-create');
        Route::match(['get', 'post'], '/category-index', [CategoryController::class, 'index'])->name('admin.category-index');
        Route::match(['get', 'post'], '/category-update/{slug}/{id}', [CategoryController::class, 'update'])->name('admin.category-update');
        Route::post('update-category-status', [CategoryController::class, 'updateCategoryStatus'])->name('admin.update-category-status');
        Route::get('delete-category/{slug}/{id}', [CategoryController::class, 'destroy'])->name('admin.delete-category');

        // Cardio
        Route::match(['get', 'post'], '/cardio-create', [ProgramController::class, 'createCardio'])->name('admin.cardio-create');
        Route::match(['get', 'post'], '/cardio-index', [ProgramController::class, 'cardioIndex'])->name('admin.cardio-index');
        Route::match(['get', 'post'], '/cardio-update/{slug}/{id}', [ProgramController::class, 'updateCardio'])->name('admin.cardio-update');
        Route::post('update-cardio-status', [ProgramController::class, 'updateCardioStatus'])->name('admin.update-cardio-status');
        Route::get('delete-cardio/{slug}/{id}', [ProgramController::class, 'destroyCardio'])->name('admin.delete-cardio');

        // Program Tags
        Route::match(['get', 'post'], '/tag-create', [ProgramController::class, 'createTag'])->name('admin.tag-create');
        Route::match(['get', 'post'], '/tag-index', [ProgramController::class, 'tagIndex'])->name('admin.tag-index');
        Route::match(['get', 'post'], '/tag-update/{slug}/{id}', [ProgramController::class, 'updateTag'])->name('admin.tag-update');
        Route::post('update-tag-status', [ProgramController::class, 'updateTagStatus'])->name('admin.update-tag-status');
        Route::get('delete-tag/{slug}/{id}', [ProgramController::class, 'destroyTag'])->name('admin.delete-tag');

        // Program Level Setting
        Route::match(['get', 'post'], '/level-create', [ProgramController::class, 'createLevel'])->name('admin.level-create');
        Route::match(['get', 'post'], '/level-index', [ProgramController::class, 'levelIndex'])->name('admin.level-index');
        Route::match(['get', 'post'], '/level-update/{slug}/{id}', [ProgramController::class, 'updateLevel'])->name('admin.level-update');
        Route::post('update-level-status', [ProgramController::class, 'updateLevelStatus'])->name('admin.update-level-status');
        Route::get('delete-level/{slug}/{id}', [ProgramController::class, 'destroyLevel'])->name('admin.delete-level');

        // Muscles Strengthening
        Route::match(['get', 'post'], '/muscles-strength-create', [ProgramController::class, 'createMuscleStrength'])->name('admin.muscles-strength-create');
        Route::match(['get', 'post'], '/muscles-strength-index', [ProgramController::class, 'muscleStrengthIndex'])->name('admin.muscles-strength-index');
        Route::match(['get', 'post'], '/muscles-strength-update/{slug}/{id}', [ProgramController::class, 'updateMuscleStrength'])->name('admin.muscles-strength-update');
        Route::post('update-muscles-strength-status', [ProgramController::class, 'updateMuscleStrengthStatus'])->name('admin.update-muscles-strength-status');
        Route::get('delete-muscles-strength/{slug}/{id}', [ProgramController::class, 'destroyMuscleStrength'])->name('admin.delete-muscles-strength');

        // Programs
        // Program
        Route::match(['get', 'post'], '/program-create', [ProgramController::class, 'create'])->name('admin.program-create');
        Route::match(['get', 'post'], '/program-index', [ProgramController::class, 'index'])->name('admin.program-index');
        Route::match(['get', 'post'], '/program-update/{slug}/{id}', [ProgramController::class, 'update'])->name('admin.program-update');
        Route::post('update-program-status', [ProgramController::class, 'updateProgramStatus'])->name('admin.update-program-status');
        Route::get('delete-program/{slug}/{id}', [ProgramController::class, 'destroy'])->name('admin.delete-program');
        Route::post('update-session', [ProgramController::class, 'updateSession'])->name('admin.update-session');

        // Add Program Session
        Route::post('add-program-session', [ProgramController::class, 'addProgramSession'])->name('admin.add-program-session');

        // Programs
        // Session
        Route::match(['get', 'post'], '/session-create', [SessionController::class, 'create'])->name('admin.session-create');
        Route::match(['get', 'post'], '/session-index', [SessionController::class, 'index'])->name('admin.session-index');
        Route::match(['get', 'post'], '/session-update/{slug}/{id}', [SessionController::class, 'update'])->name('admin.session-update');
        Route::post('update-session-status', [SessionController::class, 'updateSessionStatus'])->name('admin.update-session-status');
        Route::get('delete-session/{slug}/{id}', [SessionController::class, 'destroy'])->name('admin.delete-session');

        Route::get('get-session', [SessionController::class, 'getSession'])->name('admin.get-session');

        Route::get('delete-session-exercise/{id}/{sessionid}', [SessionController::class, 'deleteSessionExercise'])->name('admin.delete-session-exercise');

        // Exercise
        Route::match(['get', 'post'], '/exercise-create', [ExerciseController::class, 'createExercise'])->name('admin.exercise-create');
        Route::match(['get', 'post'], '/exercise-index', [ExerciseController::class, 'exerciseIndex'])->name('admin.exercise-index');
        Route::match(['get', 'post'], '/exercise-update/{slug}/{id}', [ExerciseController::class, 'updateExercise'])->name('admin.exercise-update');
        Route::post('update-exercise-status', [ExerciseController::class, 'updateExerciseStatus'])->name('admin.update-exercise-status');
        Route::get('delete-exercise/{slug}/{id}', [ExerciseController::class, 'destroyExercise'])->name('admin.delete-exercise');

        // Ingredient Category
        Route::match(['get', 'post'], '/ingredient-category-create', [IngredientController::class, 'categoryCreate'])->name('admin.ingredient-category-create');
        Route::match(['get', 'post'], '/ingredient-category-index', [IngredientController::class, 'categoryIndex'])->name('admin.ingredient-category-index');
        Route::match(['get', 'post'], '/ingredient-category-update/{slug}/{id}', [IngredientController::class, 'categoryUpdate'])->name('admin.ingredient-category-update');
        Route::get('delete-ingredient-category/{slug}/{id}', [IngredientController::class, 'categoryDestroy'])->name('admin.delete-ingredient-category');

        // Ingredient
        Route::match(['get', 'post'], '/ingredient-create', [IngredientController::class, 'create'])->name('admin.ingredient-create');
        Route::match(['get', 'post'], '/ingredient-index', [IngredientController::class, 'index'])->name('admin.ingredient-index');
        Route::match(['get', 'post'], '/ingredient-update/{slug}/{id}', [IngredientController::class, 'update'])->name('admin.ingredient-update');
        Route::post('update-ingredient-status', [IngredientController::class, 'updateIngredientStatus'])->name('admin.update-ingredient-status');
        Route::get('delete-recipe-ingredient/{slug}/{id}', [IngredientController::class, 'destroy'])->name('admin.delete-ingredient');
        Route::post('/add-ingredient-name', [IngredientController::class, 'addIngredientName'])->name('admin.add-ingredient-name');
        Route::get('/fetch-ingredients-latest', [IngredientController::class, 'fetchIngredients'])->name('ingredients.latest');

        // Recipe-ingredient
        Route::match(['get', 'post'], '/nutrition-ingredient-create', [NutritionIngredientController::class, 'create'])->name('admin.nutrition-ingredient-create');
        Route::match(['get', 'post'], '/nutrition-ingredient-index', [NutritionIngredientController::class, 'index'])->name('admin.nutrition-ingredient-index');
        Route::match(['get', 'post'], '/nutrition-ingredient-update/{slug}/{id}', [NutritionIngredientController::class, 'update'])->name('admin.nutrition-ingredient-update');
        // Route::post('update-ingredient-status', [IngredientController::class, 'updateIngredientStatus'])->name('admin.update-ingredient-status');
        Route::get('delete-nutrition-ingredient/{slug}/{id}', [NutritionIngredientController::class, 'destroy'])->name('admin.delete-nutrition-ingredient');

        //nutrition-ingredient-Category
        Route::match(['get', 'post'], '/nutrition-ingredient-category-create', [NutritionIngredientController::class, 'categoryCreate'])->name('admin.nutrition-ingredient-category-create');
        Route::match(['get', 'post'], '/nutrition-ingredient-category-index', [NutritionIngredientController::class, 'categoryIndex'])->name('admin.nutrition-ingredient-category-index');
        Route::match(['get', 'post'], '/nutrition-ingredient-category-update/{slug}/{id}', [NutritionIngredientController::class, 'categoryUpdate'])->name('admin.nutrition-ingredient-category-update');
        Route::get('delete-nutrition-ingredient-category/{slug}/{id}', [NutritionIngredientController::class, 'categoryDestroy'])->name('admin.delete-nutrition-ingredient-category');
        // Route::post('update-nutrition-ingredient-status', [NutritionIngredientController::class, 'updateNutritionIngredientStatus'])->name('admin.update-nutrition-ingredient-status');

        // Recepe
        Route::match(['get', 'post'], '/recipe-create', [RecipesController::class, 'create'])->name('admin.recipe-create');
        Route::match(['get', 'post'], '/recipe-index', [RecipesController::class, 'index'])->name('admin.recipe-index');
        Route::match(['get', 'post'], '/recipe-update/{slug}/{id}', [RecipesController::class, 'update'])->name('admin.recipe-update');
        Route::post('update-recipe-status', [RecipesController::class, 'updateRecipeStatus'])->name('admin.update-recipe-status');
        Route::get('delete-recipe/{slug}/{id}', [RecipesController::class, 'destroy'])->name('admin.delete-recipe');
        Route::get('delete-recipe-ingredient/{id}', [RecipesController::class, 'deleteRecipeIngredient'])->name('admin.delete-recipe-ingredient');

        // Meal Type
        Route::match(['get', 'post'], '/meal-type-create', [MealController::class, 'create'])->name('admin.meal-type-create');
        Route::match(['get', 'post'], '/meal-type-index', [MealController::class, 'index'])->name('admin.meal-type-index');
        Route::match(['get', 'post'], '/meal-type-update/{slug}/{id}', [MealController::class, 'update'])->name('admin.meal-type-update');
        Route::post('update-meal-type-status', [MealController::class, 'updateMealTypeStatus'])->name('admin.update-meal-type-status');
        Route::get('delete-meal-type/{slug}/{id}', [MealController::class, 'destroy'])->name('admin.delete-meal-type');

        // Settings
        Route::match(['get', 'post'], '/setting-email', [AuthController::class, 'email'])->name('admin.setting-email');
        Route::match(['get', 'post'], '/setting-twillio', [AuthController::class, 'twillio'])->name('admin.setting-twillio');
        Route::match(['get', 'post'], '/setting-stripe', [AuthController::class, 'stripe'])->name('admin.setting-stripe');
        Route::match(['get', 'post'], '/setting-maintenance', [AuthController::class, 'maintenance'])->name('admin.setting-maintenance');
        Route::match(['get', 'post'], '/setting-pre_screen_quiz', [AuthController::class, 'preScreenQuiz'])->name('admin.setting-pre_screen_quiz');

        // Users
        Route::get('users', [UsersController::class, 'index'])->name('admin.users');
        Route::match(['get', 'post'], 'create-user', [UsersController::class, 'create'])->name('admin.create-user');
        Route::match(['get', 'post'], 'update-user-status', [UsersController::class, 'updateUserStatus'])->name('admin.update-user-status');
        Route::match(['get', 'post'], 'user-profile/{id}', [UsersController::class, 'userProfile'])->name('admin.user-profile');
        Route::get('delete-user/{id}', [UsersController::class, 'destroy'])->name('admin.delete-user');
        Route::post('user-reset-password/{id}', [UsersController::class, 'userResetPassword'])->name('admin.userResetPassword');

        // Community
        Route::get('discussion-index', [DiscussionController::class, 'index'])->name('admin.discussion-index');
        Route::match(['get', 'post'], 'discussion-create', [DiscussionController::class, 'create'])->name('admin.discussion-create');
        Route::match(['get', 'post'], 'discussion-update/{slug}/{id}', [DiscussionController::class, 'update'])->name('admin.discussion-update');
        Route::match(['get', 'post'], 'discussion-detail/{slug}/{id}', [DiscussionController::class, 'discussionDetail'])->name('admin.discussion-detail');
        Route::get('delete-discussion/{slug}/{id}', [DiscussionController::class, 'destroy'])->name('admin.delete-discussion');

        //Community-Header
        Route::match(['get', 'post'], 'community-header', [CommunityHeaderController::class, 'communityHeader'])->name('admin.community-header');

        // Community Topics
        Route::get('community-post-topics-index', [CommunityPostTopicsController::class, 'communityTopicsIndex'])->name('admin.community-post-topics-index');
        Route::match(['get', 'post'], 'community-post-topics-create', [CommunityPostTopicsController::class, 'communityTopicsCreate'])->name('admin.community-post-topics-create');
        Route::match(['get', 'post'], 'community-post-topics-update/{slug}/{id}', [CommunityPostTopicsController::class, 'communityTopicsUpdate'])->name('admin.community-post-topics-update');
        Route::get('delete-community-post-topics/{slug}/{id}', [CommunityPostTopicsController::class, 'communityTopicsDestroy'])->name('admin.delete-community-post-topics');

        // Community Posts
        Route::get('community-posts-index', [CommunityPostController::class, 'communityPostsIndex'])->name('admin.community-posts-index');
        // Route::match(['get', 'post'], 'community-posts-create', [CommunityPostController::class, 'communityPostsCreate'])->name('admin.community-posts-create');
        Route::match(['get', 'post'], 'community-post-update/{id}', [CommunityPostController::class, 'communityPostUpdate'])->name('admin.community-post-update');
        Route::get('community-post-delete/{id}', [CommunityPostController::class, 'communityPostsDestroy'])->name('admin.community-post-delete');

        //community post comment
        Route::put('comment-edit/{id}', [CommunityPostController::class, 'commentEdit'])->name('admin.comment.edit');
        Route::delete('comment-delete/{id}', [CommunityPostController::class, 'commentDestroy'])->name('admin.comment.delete');


        //community post comment reply
        Route::put('comment-reply-edit/{id}', [CommunityPostController::class, 'commentReplyEdit'])->name('admin.comment-reply-edit');
        Route::delete('comment-reply-delete/{id}', [CommunityPostController::class, 'commentReplyDestroy'])->name('admin.comment-reply-delete');

        //Community Reports
        Route::get('community-posts-reports-index', [CommunityPostController::class, 'PostReportIndex'])->name('admin.community-posts-reports-index');
        Route::get('community-comments-reports-index', [CommunityPostController::class, 'CommentReportIndex'])->name('admin.community-comments-reports-index');

        Route::match(['get','post'],'community-post-report-update/{id}', [CommunityPostController::class, 'PostReportUpdate'])->name('admin.community-post-report-update');
        Route::match(['get','post'],'community-post-comment-report-update/{type}/{id}', [CommunityPostController::class, 'CommentReportUpdate'])->name('admin.community-post-comment-report-update');

        Route::get('community-post-report-delete/{id}', [CommunityPostController::class, 'PostReportDelete'])->name('admin.community-post-report-delete');
        Route::get('community-post-comment-report-delete/{id}', [CommunityPostController::class, 'CommentReportDelete'])->name('admin.community-post-comment-report-delete');

        //Page Controller
        Route::get('page-index', [PageController::class, 'index'])->name('admin.page-index');
        Route::match(['get', 'post'], 'page-create', [PageController::class, 'create'])->name('admin.page-create');
        Route::match(['get', 'post'], 'page-update/{slug}/{id}', [PageController::class, 'update'])->name('admin.page-update');
        Route::get('delete-page/{slug}/{id}', [PageController::class, 'destroy'])->name('admin.delete-page');

        // Video About
        Route::get('video-about-index', [SettingsController::class, 'videoAboutIndex'])->name('admin.video-about-index');
        Route::match(['get', 'post'], 'create-video-about', [SettingsController::class, 'createVideoAbout'])->name('admin.create-video-about');
        Route::match(['get', 'post'], 'update-video-about/{slug}/{id}', [SettingsController::class, 'updateVideoAbout'])->name('admin.update-video-about');
        Route::get('delete-video-about/{slug}/{id}', [SettingsController::class, 'destroyVideoAbout'])->name('admin.delete-video-about');

        // Quiz
        Route::get('quiz-index', [QuizController::class, 'index'])->name('quiz-index');
        Route::match(['get', 'post'], 'create-quiz', [QuizController::class, 'addQuiz'])->name('create-quiz');
        Route::match(['get', 'post'], 'update-quiz/{slug}/{id}', [QuizController::class, 'updateQuiz'])->name('update-quiz');
        Route::get('delete-quiz/{slug}/{id}', [QuizController::class, 'destroyQuiz'])->name('admin.delete-quiz');

        // Set Quiz Position
        Route::post('set-quiz-position', [QuizController::class, 'setQuizPosition'])->name('set-quiz-position');
        Route::post('set-level-position', [LevelController::class, 'setLevelPosition'])->name('set-quiz-position');

        // Quiz Group
        Route::get('quiz-group-index', [QuizController::class, 'quizGroupIndex'])->name('quiz-group-index');
        Route::match(['get', 'post'], 'create-quiz-group', [QuizController::class, 'addQuizGroup'])->name('create-quiz-group');
        Route::match(['get', 'post'], 'update-quiz-group/{slug}/{id}', [QuizController::class, 'updateQuizGroup'])->name('update-quiz-group');
        Route::get('delete-quiz-group/{slug}/{id}', [QuizController::class, 'destroyQuizGroup'])->name('admin.delete-quiz-group');
        Route::get('get-group-wise-quiz', [QuizController::class, 'getGroupWiseQuiz']);
        Route::post('change-quiz-group-order/{id}', [QuizController::class, 'updateQuizGroupOrder'])->name('change-quiz-group-order');

        // Set Quiz Order Position
        Route::post('set-position', [QuizController::class, 'setQuizOrderPosition'])->name('set-position');

        // Transition
        Route::get('transition-index', [TransitionController::class, 'index'])->name('transition-index');
        Route::match(['get', 'post'], 'create-transition', [TransitionController::class, 'addTransition'])->name('create-transition');
        Route::match(['get', 'post'], 'update-transition/{slug}/{id}', [TransitionController::class, 'updateTransition'])->name('update-transition');
        Route::get('delete-transition/{slug}/{id}', [TransitionController::class, 'destroyTransition'])->name('admin.delete-transition');

        //Unsubscription
        Route::get('unsubscription-index', [SubscriptionController::class, 'index'])->name('unsubscription-index');
        Route::match(['get', 'post'], 'create-unsubsflow', [SubscriptionController::class, 'addUnsubsflow'])->name('create-unsubsflow');
        Route::match(['get', 'post'], 'update-unsubsflow/{slug}/{id}', [SubscriptionController::class, 'updateUnsubsflow'])->name('update-unsubsflow');
        Route::get('delete-unsubsflow/{slug}/{id}', [SubscriptionController::class, 'destroyUnsubsflow'])->name('admin.delete-unsubsflow');
        Route::post('reorder-unsubsflow', [SubscriptionController::class, 'reorderUnsubsflow'])->name('reorder-unsubsflow');

        // Plan
        Route::match(['get', 'post'], '/plan-create', [PlanController::class, 'create'])->name('admin.plan-create');
        Route::match(['get', 'post'], '/plan-index', [PlanController::class, 'index'])->name('admin.plan-index');
        Route::match(['get', 'post'], '/plan-update/{slug}/{id}', [PlanController::class, 'update'])->name('admin.plan-update');
        Route::get('delete-plan/{slug}/{id}', [PlanController::class, 'destroy'])->name('admin.delete-plan');

        // Lesson
        Route::match(['get', 'post'], '/lesson-create', [LessonController::class, 'create'])->name('admin.lesson-create');
        Route::match(['get', 'post'], '/lesson-index', [LessonController::class, 'index'])->name('admin.lesson-index');
        Route::match(['get', 'post'], '/lesson-update/{slug}/{id}', [LessonController::class, 'update'])->name('admin.lesson-update');
        Route::post('update-lesson-status', [LessonController::class, 'updateLessonStatus'])->name('admin.update-lesson-status');
        Route::get('delete-lesson/{slug}/{id}', [LessonController::class, 'destroy'])->name('admin.delete-lesson');

        // Promocode
        Route::match(['get', 'post'], '/create-promocode', [PromocodeController::class, 'create'])->name('admin.create.promocode');
        Route::match(['get', 'post'], '/promocode-index', [PromocodeController::class, 'index'])->name('admin.promocode.index');
        Route::match(['get', 'post'], '/update-promocode/{slug}/{id}', [PromocodeController::class, 'update'])->name('admin.promocode-update');
        Route::post('update-promocode-status', [PromocodeController::class, 'updatePromocodeStatus'])->name('admin.update-promocode-status');
        Route::get('delete-promocode/{slug}/{id}', [PromocodeController::class, 'destroy'])->name('admin.delete-promocode');

        //export-user-data
        Route::get('/export-new-users', [UsersController::class, 'exportNewUsers'])->name('export.new.users');

        // Challenge Module
        Route::get('/challenge-index', [ChallengeController::class, 'index'])->name('admin.challenges.index');
        Route::match(['get', 'post'], '/challenge-create', [ChallengeController::class, 'create'])->name('admin.challenge-create');
        Route::match(['get', 'post'], '/challenge-edit/{id}', [ChallengeController::class, 'edit'])->name('admin.challenge-edit');
        Route::get('delete-challenge/{slug}/{id}', [ChallengeController::class, 'delete'])->name('admin.challenge-delete');
        Route::post('update-challenge-status', [ChallengeController::class, 'updateChallengeStatus'])->name('admin.update-challenge-status');

        //challenge Level Module
        Route::get('/challenge-level-index', [ChallengeController::class, 'levelIndex'])->name('admin.challenge-level.index');
        Route::match(['get', 'post'], '/challenge-level-create', [ChallengeController::class, 'levelCreate'])->name('admin.challenge-level-create');
        Route::match(['get', 'post'], '/challenge-level-update/{slug}/{id}', [ChallengeController::class, 'levelEdit'])->name('admin.challenge-level-edit');
        Route::get('delete-challengeLevel/{slug}/{id}', [ChallengeController::class, 'levelDestroy'])->name('admin.challenge-level-delete');
        Route::post('update-challengeLevel-status', [ChallengeController::class, 'updateChallengeLevelStatus'])->name('admin.update-challenge-level-status');

        // Activities Module
        Route::match(['get', 'post'], '/activities', [\App\Http\Controllers\Admin\ActivityController::class, 'index'])->name('admin.activities.index');
        Route::match(['get', 'post'], '/activities/create', [\App\Http\Controllers\Admin\ActivityController::class, 'create'])->name('admin.activities.create');
        Route::post('/activities/store', [\App\Http\Controllers\Admin\ActivityController::class, 'store'])->name('admin.activities.store');
        Route::match(['get', 'post'], '/activities/{id}/edit', [\App\Http\Controllers\Admin\ActivityController::class, 'edit'])->name('admin.activities.edit');
        Route::put('/activities/{id}', [\App\Http\Controllers\Admin\ActivityController::class, 'update'])->name('admin.activities.update');
        Route::delete('/activities/{id}', [\App\Http\Controllers\Admin\ActivityController::class, 'destroy'])->name('admin.activities.destroy');
        Route::post('/update-activity-status', [\App\Http\Controllers\Admin\ActivityController::class, 'updateStatus'])->name('admin.update-activity-status');

        // Challenge Exercise Module
        Route::match(['get', 'post'], '/challenge-exercise-create', [ChallengeExerciseController::class, 'create'])->name('admin.challenge-exercise-create');
        Route::match(['get', 'post'], '/challenge-exercise-index', [ChallengeExerciseController::class, 'index'])->name('admin.challenge-exercise-index');
        Route::match(['get', 'post'], '/challenge-exercise-update/{slug}/{id}', [ChallengeExerciseController::class, 'update'])->name('admin.challenge-exercise-update');
        Route::post('challenge-update-exercise-status', [ChallengeExerciseController::class, 'updateChallengeExerciseStatus'])->name('admin.challenge-update-exercise-status');
        Route::get('delete-challenge-exercise/{slug}/{id}', [ChallengeExerciseController::class, 'destroy'])->name('admin.delete-challenge-exercise');

        Route::get('get-challenge-exercises-list', [ChallengeExerciseController::class, 'getExercise'])->name('admin.get-challenge-exercises-list');
        Route::get('get-exercise-name/{id}', [ChallengeExerciseController::class, 'getExerciseName'])->name('admin.get-challenge-exercises-name');

        // user-level
        Route::get('/user-level-index', [LevelController::class, 'levelIndex'])->name('admin.user-level.index');
        Route::match(['get', 'post'], '/user-level-create', [LevelController::class, 'levelCreate'])->name('admin.user-level-create');
        Route::match(['get', 'post'], '/user-level-update/{id}', [LevelController::class, 'levelEdit'])->name('admin.user-level-edit');
        Route::get('delete-userLevel/{id}', [LevelController::class, 'levelDestroy'])->name('admin.user-level-delete');
        Route::post('set-level-position', [LevelController::class, 'setLevelPosition'])->name('set-level-position');

        // user-milestone-task
        Route::get('/task-milestone-index', [LevelController::class, 'milestoneIndex'])->name('admin.task-milestone.index');
        Route::match(['get', 'post'], '/task-milestone-create', [LevelController::class, 'milestoneCreate'])->name('admin.task-milestone-create');
        Route::match(['get', 'post'], '/task-milestone-update/{id}', [LevelController::class, 'milestoneEdit'])->name('admin.task-milestone-edit');
        Route::get('delete-taskMilestone/{id}', [LevelController::class, 'milestoneDestroy'])->name('admin.task-milestone-delete');

    });
});

Route::get('user-answer', [WebQuizController::class, 'userAnswer'])->name('user-answer');
Route::get('test', function () {
    return view('front.auth.test');
});

// Route::post('save-answer', [WebQuizController::class, 'saveAnswer'])->name('save-answer');
Route::group(['middleware' => ['user']], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/user-logout', [WebAuthController::class, 'logout'])->name('user-logout');
    Route::match(['get', 'post'], 'subscription-details', [UnsubscriptionController::class, 'subscriptionDetails'])->name('subscription-details');

    // Community
    Route::get('community', [WebCommunityController::class, 'communityIndex'])->name('community');
    Route::get('topic-details/{id}', [WebCommunityController::class, 'topicDetails'])->name('topic-details');
    Route::post('comment/{id}', [WebCommunityController::class, 'reply'])->name('comment');

    // Profile
    Route::match(['get', 'post'], 'user-profile', [WebAuthController::class, 'userProfile'])->name('user-profile');
    Route::post('user-change-password', [WebAuthController::class, 'userChangePassword'])->name('user-change-password');

    Route::match(['get', 'post'], '/update-card/{custId?}', [UnsubscriptionController::class, 'updateCard'])->name('update-card');


    Route::match(['get', 'post'], '/change-plan-page', [UnsubscriptionController::class, 'modifySubscriptionPlanPage'])->name('change-plan-page');
    Route::match(['get', 'post'], '/change-plan/{plan_id}', [UnsubscriptionController::class, 'modifySubscriptionPlan'])->name('modify.subscription.plan');

    // Add payment method routes for plan modification
    Route::get('/add-klarna-payment', [UnsubscriptionController::class, 'showAddKlarnaPayment'])->name('add.klarna.payment');
    Route::post('/process-klarna-payment', [UnsubscriptionController::class, 'processAddKlarnaPayment'])->name('process.klarna.payment');
    Route::get('/add-card-payment', [UnsubscriptionController::class, 'showAddCardPayment'])->name('add.card.payment');
    Route::post('/process-card-payment', [UnsubscriptionController::class, 'processAddCardPayment'])->name('process.card.payment');

    // Cancel Subscription
    Route::get('/feedback/{position}', [UnsubscriptionController::class, 'showScreen'])->name('feedback.show');
    Route::post('/feedback/{position}', [UnsubscriptionController::class, 'submitScreen'])->name('feedback.submit');

    Route::get('/get-screen-position/{screen_id}', [UnsubscriptionController::class, 'getScreenPositionById']);

    Route::get('/feedbacks/done', [UnsubscriptionController::class, 'complete'])->name('feedback.complete');

    Route::get('/cancel/subs', [UnsubscriptionController::class, 'cancelSubscription']);
    Route::get('discard/cancel/subs', [UnsubscriptionController::class, 'discardCancelSubscription'])->name('discardCancelSubscription');
    Route::get('/update/subs/{per}', [UnsubscriptionController::class, 'updateSubscription'])->name('update-subs');
    Route::get('/extend/subs-trial', [UnsubscriptionController::class, 'extendFreeTrial']);

    Route::match(['get', 'post'], 'new-subscription-payment/{id?}', [WebNewSubscriptionController::class, 'payment'])->name('new-subscription-payment');

    Route::get('new-subscription-payment/klarna/confirm', [WebNewSubscriptionController::class, 'confirmKlarna'])->name('newSubscription.klarna.confirmation');

    // Delete Account
    Route::match(['get', 'post'], 'delete-account', [WebAuthController::class, 'deleteAccount'])->name('delete-account');

    // Calorie Calculator
    Route::match(['get', 'post'], 'calculate-calorie', [CalorieCalculator::class, 'calculateCalorie'])->name('calculate-calorie');
});
Route::match(['get', 'post'], 'pre-start-quiz', [WebQuizController::class, 'preScreenQuiz'])->name('preScreenQuiz');
// User Answers to quiz and Save
Route::match(['get', 'post'], 'process-quiz', [WebQuizController::class, 'getQuesAns'])->name('questions')->middleware('force.get.quiz');
Route::post('/quiz/{id}/save', [WebQuizController::class, 'saveAnswer'])->name('quiz.saveAnswer');
Route::get('/back-quiz/{question_id}/{session_id}/{is_back_from_transition?}', [WebQuizController::class, 'getPreviousQuesAns'])->name('quiz.previousQuestion');
Route::get('transition-view/{id}/{index}', [WebQuizController::class, 'showTransition']);
Route::get('/back-transition/{id}/{index}', [WebQuizController::class, 'getPreviousTransition'])->name('quiz.previousTransition');

// Quiz Completed Route only for IOS Users
Route::get('/finish-quiz', function (Request $request) {
    // You can access the 'id' parameter here if needed
    $sessionId = Session::get('quiz_session_id');
    return response()->json([
        'status' => true,
        'sessionId' => $sessionId
    ]);
})->name('finish-quiz');

// Package
Route::get('get-package', [WebQuizController::class, 'getPackage'])->name('get-package');
Route::get('packages', [WebNewSubscriptionController::class, 'packages'])->name('user-subscription-package');
Route::match(['get', 'post'],'/newSubscription-3dPayment-success', [WebNewSubscriptionController::class, 'newSubscriptionThreeDSuccessPayment'])->name('newSubscriptionThreeDSuccessPayment');
Route::get('/newSubscription-stripe-auth', function (Request $request) {
    return view('newSubscription-stripe-auth', [
        'user_id' => $request->input('user_id'),
        'invoiceID' => $request->input('invoiceID'),
        'plan_id' => $request->input('plan_id'),
        'clientSecret' => $request->input('payment_intent_client_secret'),
        'paymentIntentId' => $request->input('payment_intent'),
    ]);
})->name('newSubscription.stripe.auth');
// Payment
Route::get('payment/{id}', [WebQuizController::class, 'payment'])->name('payment');

Route::get('package/details/{id}', [WebQuizController::class, 'getPackageDetails'])->name('package.details');
// Route::post('payment-success', [WebQuizController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [WebQuizController::class, 'checkout'])->name('checkout');

Route::post('/delete-user-after-failed-attempt', [WebQuizController::class, 'deleteUserAfterFailedAttempt'])->name('delete-user-after-failed-attempt');

Route::match(['get', 'post'],'/3dPayment-success', [WebQuizController::class, 'threeDSuccessPayment'])->name('threeDSuccessPayment');

Route::post('/delete-user-after-failed-attempt', [WebQuizController::class, 'deleteUserAfterFailedAttempt'])->name('delete-user-after-failed-attempt');

Route::get('/stripe-auth', function (Request $request) {
    return view('stripe-auth', [
        'user_id' => $request->input('user_id'),
        'invoiceID' => $request->input('invoiceID'),
        'plan_id' => $request->input('plan_id'),
        'clientSecret' => $request->input('payment_intent_client_secret'),
        'paymentIntentId' => $request->input('payment_intent'),
    ]);
})->name('stripe.auth');

Route::get('/payment-success', function (Request $request) {
    // You can access the 'id' parameter here if needed
    $id = $request->query('id', null);
    $token = session()->get('resetToken');

    return view('front.auth.congrats', ['id' => $id, 'token' => $token]);
})->name('payment.success');

Route::match(['get', 'post'], 'subscribe-program', [WebQuizController::class, 'subscribeProgram'])->name('subscribe-program');
Route::get('stripe-checkout', [WebQuizController::class, 'checkoutSuccess'])->name('stripe-checkout');

// Login for cancle subscription
Route::match(['get', 'post'], 'login/cancel-subscription', [UnsubscriptionController::class, 'login'])->name('cancel-subs-login');

// Check Discount Coupon
Route::post('/check-discount-code/{planId?}', [WebQuizController::class, 'checkDiscountCode']);
//Stripe-webhook routes
Route::match(['get', 'post'], 'subscription-payment-webhook', [StripeWebhookController::class, 'handleWebhook'])->name('subscription-payment-webhook');

Route::get('/payment/apple-pay', [WebQuizController::class, 'showPaymentPage'])->name('apple-pay.show');
Route::post('/apple-pay/validate-merchant', [WebQuizController::class, 'validateMerchant'])->name('apple-pay.validate-merchant');
Route::post('/apple-pay/process-payment', [WebQuizController::class, 'processPayment'])->name('apple-pay.process-payment');

// demo
Route::get('/recipes', [FoodProductController::class, 'search'])->name('recipes.search');
Route::get('/recipes/{barcode}', [FoodProductController::class, 'show'])->name('recipes.show');
Route::get('/back', function () {
    dd('back to app link');
});

// Extra Routes
Route::match(['get', 'post'], 'files-upload-large', [ProgramController::class, 'uploadLargeFiles'])->name('files.upload.large');
// End Extra Routes

// CMS Page
Route::get('/pages/{slug}', [CMSPageController::class, 'page'])->name('cms-page');


// Klarna Payemnet
Route::get('/klarna/confirm', [WebQuizController::class, 'confirmKlarna'])->name('klarna.confirmation');

// Klarna Payment Method Addition Confirmation
Route::get('/klarna/confirm-payment-method', [UnsubscriptionController::class, 'confirmAddKlarnaPayment'])->name('confirm.klarna.payment.method');
