<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <!-- <img src="{{ asset('adminAssets/dist/img/AdminLTELogo.png') }}" alt="John" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
        <span class="brand-text font-weight-light">John</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar os-theme-light">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                @if(empty(Auth::guard('admin')->user()->avatar))
                <img src="{{ asset('adminAssets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                @else
                <img src="{{ url('/storage/'.Auth::guard('admin')->user()->avatar) }}" class="img-circle elevation-2" alt="User Image" style="width:35px; height: 35px;">
                @endif
            </div>
            <div class="info">
                <a href="{{ route('admin.dashboard') }}" class="d-block">{{ Auth::guard('admin')->user()->name }}</a>
            </div>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                @if (Session::get('page') == 'dashboard')
                @php $active = "active"; $beat = "fa-beat"; @endphp
                @else
                @php $active = ""; $beat = ""; @endphp
                @endif
                <li class="nav-item menu-open">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $active }}">
                        <i class="nav-icon fas fa-tachometer-alt {{ $beat }}"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                @if (Session::get('page') == 'categories' || Session::get('page') == 'programs' || Session::get('page') == 'cardio' || Session::get('page') == 'muscleStrengths' || Session::get('page') == 'tag' || Session::get('page') == 'levels')
                @php $active = "active"; $menuOpen = "menu-open"; $beat = "fa-beat"; $background = "#424242"; @endphp
                @else
                @php $active = ""; $menuOpen = ""; $beat = ""; $background = "transparent"; @endphp
                @endif
                <li class="nav-item {{ $menuOpen }}">
                    <a href="#" class="nav-link {{ $active }}">
                        <i class="nav-icon fa-solid fa-tasks {{ $beat }}"></i>
                        <p>
                            Programs
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                        @if (Session::get('page') == 'programs')
                        @php $active = "active"; $beat = "fa-beat"; @endphp
                        @else
                        @php $active = ""; $beat = ""; @endphp
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('admin.program-index') }}" class="nav-link {{ $active }}">
                                <i class="fa fa-tasks {{ $beat }} nav-icon"></i>
                                <p>Program</p>
                            </a>
                        </li>
                        @if (Session::get('page') == 'categories')
                        @php $active = "active"; $beat = "fa-beat"; @endphp
                        @else
                        @php $active = ""; $beat = ""; @endphp
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('admin.category-index') }}" class="nav-link {{ $active }}">
                                <i class="fas fa-list-alt {{ $beat }} nav-icon"></i>
                                <p>
                                    Category
                                </p>
                            </a>
                        </li>
                        @if (Session::get('page') == 'cardio')
                        @php $active = "active"; $beat = "fa-beat"; @endphp
                        @else
                        @php $active = ""; $beat = ""; @endphp
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('admin.cardio-index') }}" class="nav-link {{ $active }}">
                                <i class="fa-solid fa-heart-circle-bolt {{ $beat }} nav-icon"></i>
                                <p>Type of Cardio</p>
                            </a>
                        </li>
                </li>
                @if (Session::get('page') == 'muscleStrengths')
                @php $active = "active"; $beat = "fa-beat"; @endphp
                @else
                @php $active = ""; $beat = ""; @endphp
                @endif
                <li class="nav-item">
                    <a href="{{ route('admin.muscles-strength-index') }}" class="nav-link {{ $active }}">
                        <i class="fa-solid fa-heart-circle-bolt {{ $beat }} nav-icon"></i>
                        <p>Muscle Strengthening</p>
                    </a>
                </li>
                @if (Session::get('page') == 'tag')
                @php $active = "active"; $beat = "fa-beat"; @endphp
                @else
                @php $active = ""; $beat = ""; @endphp
                @endif
                <li class="nav-item">
                    <a href="{{ route('admin.tag-index') }}" class="nav-link {{ $active }}">
                        <i class="fa-solid fa-tags {{ $beat }} nav-icon"></i>
                        <p>Program Tags</p>
                    </a>
                </li>
                @if (Session::get('page') == 'levels')
                @php $active = "active"; $beat = "fa-beat"; @endphp
                @else
                @php $active = ""; $beat = ""; @endphp
                @endif
                <li class="nav-item">
                    <a href="{{ route('admin.level-index') }}" class="nav-link {{ $active }}">
                        <i class="fa-solid fa-turn-up {{ $beat }} nav-icon"></i>
                        <p>Program Levels</p>
                    </a>
                </li>
            </ul>
            </li>
            @if (Session::get('page') == 'sessions' || Session::get('page') == 'exercises')
            @php $active = "active"; $menuOpen = "menu-open"; $beat = "fa-beat"; $background = "#424242"; @endphp
            @else
            @php $active = ""; $menuOpen = ""; $beat = ""; $background = "transparent"; @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="nav-icon fa-solid fa-tasks {{ $beat }}"></i>
                    <p>
                        Sessions
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    @if (Session::get('page') == 'sessions')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.session-index') }}" class="nav-link {{ $active }}">
                            <i class="far fa-task {{ $beat }} nav-icon"></i>
                            <p>Session</p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'exercises')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.exercise-index') }}" class="nav-link {{ $active }}">
                            <i class="fas fa-dumbbell {{ $beat }} nav-icon"></i>
                            <p>
                                Exercises
                            </p>
                        </a>
                    </li>
                </ul>
            </li>
            @if (Session::get('page') == 'lessons')
            @php $active = "active"; $beat = "fa-beat"; @endphp
            @else
            @php $active = ""; $beat = ""; @endphp
            @endif
            <li class="nav-item">
                <a href="{{ route('admin.lesson-index') }}" class="nav-link {{ $active }}">
                    <i class="nav-icon fa fa-person-chalkboard {{ $beat }}"></i>
                    <p>
                        Lessons
                    </p>
                </a>
            </li>
            @if (Session::get('page') == 'recepies' || Session::get('page') == 'diets' || Session::get('page') == 'ingredients' || Session::get('page') == 'recipeIngredientCategory' || Session::get('page') == 'mealType')
            @php
            $active = "active";
            $menuOpen = "menu-open";
            $beat = "fa-beat";
            $background = "#424242";
            @endphp
            @else
            @php
            $active = "";
            $menuOpen = "";
            $beat = "";
            $background = "transparent";
            @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="nav-icon fas fa-bowl-food {{ $beat }}"></i>
                    <p>
                        Recipe
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    @if (Session::get('page') == 'recepies')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.recipe-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fas fa-bowl-food {{ $beat }}"></i>
                            <p>Recipes</p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'recipeIngredientCategory')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.ingredient-category-index') }}" class="nav-link {{ $active }}">
                            <i class="fa-brands fa-nutritionix {{ $beat }} nav-icon"></i>
                            <p>
                                Ingredient Category
                            </p>
                        </a>
                    </li>
                    {{-- @if (Session::get('page') == 'ingredients')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.ingredient-index') }}" class="nav-link {{ $active }}">
                            <i class="fa-solid fa-mortar-pestle {{ $beat }} nav-icon"></i>
                            <p>
                                Ingredients
                            </p>
                        </a>
                    </li> --}}
                    @if (Session::get('page') == 'mealType')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.meal-type-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fas fa-bowl-rice {{ $beat }}"></i>
                            <p>Meal Type</p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'diets')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <!-- <li class="nav-item">
                        <a href="#" class="nav-link {{ $active }}">
                            <i class="fa fa-seedling {{ $beat }} nav-icon"></i>
                            <p>Diet</p>
                        </a>
                    </li> -->
                </ul>
            </li>
            @if (Session::get('page') == 'nutritions' || Session::get('page') == 'nutrition-ingredients'  || Session::get('page') == 'nutrition-ingredients-category')
            @php
            $active = "active";
            $menuOpen = "menu-open";
            $beat = "fa-beat";
            $background = "#424242";
            @endphp
            @else
            @php
            $active = "";
            $menuOpen = "";
            $beat = "";
            $background = "transparent";
            @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="fa-brands fa-nutritionix {{ $beat }} nav-icon"></i>
                    <p>
                        Nutritions
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    {{-- @if (Session::get('page') == 'nutritions')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.recipe-nutrition-index') }}" class="nav-link {{ $active }}">
                            <i class="fa-brands fa-nutritionix {{ $beat }} nav-icon"></i>
                            <p>
                                Nutrition
                            </p>
                        </a>
                    </li> --}}
                    @if (Session::get('page') == 'nutrition-ingredients')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.nutrition-ingredient-index') }}" class="nav-link {{ $active }}">
                            <i class="fa-solid fa-mortar-pestle {{ $beat }} nav-icon"></i>
                            <p>
                                Nutrition Ingredients
                            </p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'nutrition-ingredients-category')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.nutrition-ingredient-category-index') }}" class="nav-link {{ $active }}">
                            <i class="fa-solid fa-mortar-pestle {{ $beat }} nav-icon"></i>
                            <p>
                                Nutrition Ingredients Category
                            </p>
                        </a>
                    </li>
                </ul>
            </li>
            @if (Session::get('page') == 'quiz' || Session::get('page') == 'quizGroup' || Session::get('page') == 'transition' || Session::get('page') == 'promocode')
            @php
            $active = "active";
            $menuOpen = "menu-open";
            $beat = "fa-beat";
            $background = "#424242";
            @endphp
            @else
            @php
            $active = "";
            $menuOpen = "";
            $beat = "";
            $background = "transparent";
            @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="nav-icon fas fa-circle-question {{ $beat }}"></i>
                    <p>
                        Quiz
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    @if (Session::get('page') == 'quizGroup')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('quiz-group-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fas fa-file-circle-question {{ $beat }}"></i>
                            <p>Quiz Group</p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'quiz')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('quiz-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fas fa-circle-question {{ $beat }}"></i>
                            <p>Questions</p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'transition')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('transition-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-solid fa-shuffle {{ $beat }}"></i>
                            <p>
                                Transition
                            </p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'promocode')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.promocode.index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-solid fa-ticket {{ $beat }}"></i>
                            <p>
                                Promocode
                            </p>
                        </a>
                    </li>
                </ul>
            </li>
            @if (Session::get('page') == 'unsubscription')
            @php $active = "active"; $beat = "fa-beat"; @endphp
            @else
            @php $active = ""; $beat = ""; @endphp
            @endif
            <li class="nav-item">
                <a href="{{ route('unsubscription-index') }}" class="nav-link {{ $active }}">
                    <i class="nav-icon fa-solid fa-tent-arrow-left-right {{ $beat }}"></i>
                    <p>
                        Un-Subscription
                    </p>
                </a>
            </li>
            @if (Session::get('page') == 'challenges' || Session::get('page') == 'challengeExercise' || Session::get('page') == 'challengeLevel')
            @php
            $active = "active";
            $menuOpen = "menu-open";
            $beat = "fa-beat";
            $background = "#424242";
            @endphp
            @else
            @php
            $active = "";
            $menuOpen = "";
            $beat = "";
            $background = "transparent";
            @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="nav-icon fa-solid fa-trophy {{ $beat }}"></i>
                    <p>
                        challenges
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    @if (Session::get('page') == 'challenges')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.challenges.index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-solid fa-medal {{ $beat }}"></i>
                            <p>challenges</p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'challengeExercise')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.challenge-exercise-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-solid fa-hand-fist {{ $beat }}"></i>
                            <p>
                                Challenge Exercise
                            </p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'challengeLevel')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.challenge-level.index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa fa-level-up {{ $beat }}"></i>
                            <p>
                                Challenge Level
                            </p>
                        </a>
                    </li>
                </ul>
            </li>
            @if(Session::get('page') == 'users')
            @php $active = "active"; $beat = "fa-beat"; @endphp
            @else
            @php $active = ""; $beat = ""; @endphp
            @endif
            <li class="nav-item">
                <a href="{{ url('admin/users') }}" class="nav-link {{ $active }}">
                    <i class="fa-solid fa-users {{ $beat }} nav-icon"></i>
                    <p>Users</p>
                </a>
            </li>
            @if (Session::get('page') == 'user-level-task'  || Session::get('page') == 'user-task-milestone')
            @php
            $active = "active";
            $menuOpen = "menu-open";
            $beat = "fa-beat";
            $background = "#424242";
            @endphp
            @else
            @php
            $active = "";
            $menuOpen = "";
            $beat = "";
            $background = "transparent";
            @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="fa-brands fa-nutritionix {{ $beat }} nav-icon"></i>
                    <p>
                        User Level
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    @if (Session::get('page') == 'user-level-task')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.user-level.index') }}" class="nav-link {{ $active }}">
                            <i class="fa-solid fa-mortar-pestle {{ $beat }} nav-icon"></i>
                            <p>
                               Level Tasks
                            </p>
                        </a>
                    </li>
                    @if (Session::get('page') == 'user-task-milestone')
                    @php $active = "active"; $beat = "fa-beat"; @endphp
                    @else
                    @php $active = ""; $beat = ""; @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.task-milestone.index') }}" class="nav-link {{ $active }}">
                            <i class="fa-solid fa-mortar-pestle {{ $beat }} nav-icon"></i>
                            <p>
                                Milestone Tasks
                            </p>
                        </a>
                    </li>
                </ul>
            </li>
            @if(Session::get('page') == 'email' || Session::get('page') == 'twillio' || Session::get('page') == 'stripe' || Session::get('page') == 'unit'|| Session::get('page') == 'videoAbout' || Session::get('page') == 'maintenanceMode' || Session::get('page') == 'pre_screen_quiz')
            @php
            $active = "active";
            $menuOpen = "menu-open";
            $beat = "fa-beat";
            $background = "#424242";
            @endphp
            @else
            @php
            $active = "";
            $menuOpen = "";
            $beat = "";
            $background = "transparent";
            @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="nav-icon fa-solid fa-gear {{ $beat }}"></i>
                    <p>
                        Settings
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    @if(Session::get('page') == 'email')
                    @php
                    $active = "active";
                    $menuOpen = "menu-open";
                    $beat = "fa-beat";
                    $background = "#424242";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $menuOpen = "";
                    $beat = "";
                    $background = "transparent";
                    @endphp
                    @endif
                    <li class="nav-item {{ $menuOpen }}">
                        <a href="#" class="nav-link {{ $active }}">
                            <i class="far fa-envelope {{ $beat }} nav-icon"></i>
                            <p>
                                Email
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                            @if(Session::get('page') == 'email')
                            @php
                            $active = "active";
                            $beat = "fa-beat";
                            @endphp
                            @else
                            @php
                            $active = "";
                            $beat = "";
                            @endphp
                            @endif
                            <li class="nav-item">
                                <a href="{{ url('admin/setting-email') }}" class="nav-link {{ $active }}">
                                    <i class="far fa-message {{ $beat }} nav-icon"></i>
                                    <p>Configuration</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @if(Session::get('page') == 'twillio')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/setting-twillio') }}" class="nav-link {{ $active }}">
                            <i class="far fa-message {{ $beat }} nav-icon"></i>
                            <p>Twillio</p>
                        </a>
                    </li>
                    @if(Session::get('page') == 'stripe')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/setting-stripe') }}" class="nav-link {{ $active }}">
                            <i class="far fa-message {{ $beat }} nav-icon"></i>
                            <p>stripe</p>
                        </a>
                    </li>
                    <!-- @if(Session::get('page') == 'unit')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/unit-conversion-index') }}" class="nav-link {{ $active }}">
                            <i class="far fa-message {{ $beat }} nav-icon"></i>
                            <p>Unit</p>
                        </a>
                    </li> -->
                    @if(Session::get('page') == 'videoAbout')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/video-about-index') }}" class="nav-link {{ $active }}">
                            <i class="far fa-message {{ $beat }} nav-icon"></i>
                            <p>Video About</p>
                        </a>
                    </li>
                    @if(Session::get('page') == 'maintenanceMode')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/setting-maintenance') }}" class="nav-link {{ $active }}">
                            <i class="far fa-message {{ $beat }} nav-icon"></i>
                            <p>Maintenance Mode</p>
                        </a>
                    </li>
                    @if(Session::get('page') == 'pre_screen_quiz')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/setting-pre_screen_quiz') }}" class="nav-link {{ $active }}">
                            <i class="far fa-message {{ $beat }} nav-icon"></i>
                            <p>Pre Quiz Screen</p>
                        </a>
                    </li>
                </ul>
            </li>
            @if(Session::get('page') == 'discussion' || Session::get('page') == 'communityHeaders' || Session::get('page') == 'communityPostTopics' || Session::get('page') == 'communityPost' || Session::get('page') == 'CommunityPostReport' || Session::get('page') == 'CommunityCommentReport')
            @php
            $active = "active";
            $menuOpen = "menu-open";
            $beat = "fa-beat";
            $background = "#424242";
            @endphp
            @else
            @php
            $active = "";
            $menuOpen = "";
            $beat = "";
            $background = "transparent";
            @endphp
            @endif
            <li class="nav-item {{ $menuOpen }}">
                <a href="#" class="nav-link {{ $active }}">
                    <i class="nav-icon fa-solid fa-group-arrows-rotate {{ $beat }}"></i>
                    <p>
                        Community
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                    @if(Session::get('page') == 'communityHeaders')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/community-header') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-regular fa-comments {{ $beat }}"></i>
                            <p>Community Header</p>
                        </a>
                    </li>
                    @if(Session::get('page') == 'communityPostTopics')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/community-post-topics-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-regular fa-comments {{ $beat }}"></i>
                            <p>Community Post Topics</p>
                        </a>
                    </li>
                    @if(Session::get('page') == 'communityPost')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/community-posts-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-regular fa-comments {{ $beat }}"></i>
                            <p>Community Post</p>
                        </a>
                    </li>
                    @if(Session::get('page') == 'CommunityPostReport' || Session::get('page') == 'CommunityCommentReport')
                    @php
                    $active = "active";
                    $menuOpen = "menu-open";
                    $beat = "fa-beat";
                    $background = "#424242";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $menuOpen = "";
                    $beat = "";
                    $background = "transparent";
                    @endphp
                    @endif
                    <li class="nav-item {{$menuOpen}}">
                        <a href="#" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-regular fa-comments {{ $beat }}"></i>
                            <p>
                                Community Reports
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview" style="background-color: {{ $background }};">
                            @if(Session::get('page') == 'CommunityPostReport')
                            @php
                            $active = "active";
                            $beat = "fa-beat";
                            @endphp
                            @else
                            @php
                            $active = "";
                            $beat = "";
                            @endphp
                            @endif
                            <li class="nav-item">
                                <a href="{{ url('admin/community-posts-reports-index') }}" class="nav-link {{ $active }}">
                                    <i class="far fa-message {{ $beat }} nav-icon"></i>
                                    <p>Post Reports</p>
                                </a>
                            </li>
                            @if(Session::get('page') == 'CommunityCommentReport')
                            @php
                            $active = "active";
                            $beat = "fa-beat";
                            @endphp
                            @else
                            @php
                            $active = "";
                            $beat = "";
                            @endphp
                            @endif
                            <li class="nav-item">
                                <a href="{{ url('admin/community-comments-reports-index') }}" class="nav-link {{ $active }}">
                                    <i class="far fa-message {{ $beat }} nav-icon"></i>
                                    <p>Comment Reports</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @if(Session::get('page') == 'discussion')
                    @php
                    $active = "active";
                    $beat = "fa-beat";
                    @endphp
                    @else
                    @php
                    $active = "";
                    $beat = "";
                    @endphp
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/discussion-index') }}" class="nav-link {{ $active }}">
                            <i class="nav-icon fa-regular fa-comments {{ $beat }}"></i>
                            <p>Discussion</p>
                        </a>
                    </li>
                </ul>
            </li>
            @if (Session::get('page') == 'activities')
            @php $active = "active"; $beat = "fa-beat"; @endphp
            @else
            @php $active = ""; $beat = ""; @endphp
            @endif
            <li class="nav-item">
                <a href="{{ route('admin.activities.index') }}" class="nav-link {{ $active }}">
                    <i class="nav-icon fas fa-running {{ $beat }}"></i>
                    <p>
                        Activities
                    </p>
                </a>
            </li>
            @if (Session::get('page') == 'pages')
            @php $active = "active"; $beat = "fa-beat"; @endphp
            @else
            @php $active = ""; $beat = ""; @endphp
            @endif
            <li class="nav-item">
                <a href="{{ route('admin.page-index') }}" class="nav-link {{ $active }}">
                    <i class="nav-icon fa fa-file-alt {{ $beat }}"></i>
                    <p>
                        CMS Pages
                    </p>
                </a>
            </li>
            @if (Session::get('page') == 'plans')
            @php $active = "active"; $beat = "fa-beat"; @endphp
            @else
            @php $active = ""; $beat = ""; @endphp
            @endif
            <li class="nav-item">
                <a href="{{ route('admin.plan-index') }}" class="nav-link {{ $active }}">
                    <i class="nav-icon fa fa-clipboard-list {{ $beat }}"></i>
                    <p>
                        Packages
                    </p>
                </a>
            </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
