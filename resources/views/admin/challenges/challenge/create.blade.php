<x-admin-layout title="Create Challenge">
    @section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .ck.ck-editor__editable_inline {
            color: #000;
            min-height: 200px;
        }

        .dark-mode input:-webkit-autofill {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #23232329;
        }
        ul.sortable-list {
            list-style-type: none;
            padding: 0;
        }

        .dropdown-and-list {
            margin-bottom: 10px;
        }

        .btn-primary {
            margin-left: 44%;
            margin-bottom: 2px;
        }

        ul.sortable-list li {
            background-color: #3f6791;
            border: 0px solid;
            border-radius: 5px;
            margin-top: 2px;
            margin-bottom: 5px;
            padding: 10px;
            cursor: pointer;
            width: 50%;
        }

        .error-help-block{
            color: red;
        }

        /* New */
        ul.sortable-list {
            margin-top: 10px
        }

        ul.sortable-list li {
            display: flex;
            width: 100%;
            gap: 15px;
            white-space: nowrap;
            align-items: center;
        }

        ul.sortable-list li button {
            background-color: transparent;
            box-shadow: none;
            border-radius: 100%;
            font-size: 16px;
            height: 36px;
            width: 36px;
            border: 1px solid red;
            color: red;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        ul.sortable-list li .exercise-heading {
            width: 12%;
            flex-shrink: 0;
        }

        .day-group .form-group > button {
            background-color: transparent;
            box-shadow: none;
            border-radius: 5px;
            font-size: 16px;
            padding: 4px 15px;
            border: 1px solid #00bc8c;
            color: #00bc8c;
            flex-shrink: 0;
        }

        .day-group .form-group > button:last-child {
            background-color: #00bc8c;
            color: #ffffff;
            margin-left: 5px
        }
        
        
    </style>
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            line-height: 38px;
            border: 1px solid #ced4da;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        /* Dark mode support */
        .dark-mode .select2-container--default .select2-selection--single {
            background-color: #343a40;
            border-color: #6c757d;
            padding: 0;
        }
        
        .dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
            margin-top: -1px;
            color: #fff;
        }

        .dark-mode .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #ffffff;
        }
        
        .dark-mode .select2-dropdown {
            background-color: #343a40;
            border-color: #6c757d;
        }
        
        .dark-mode .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #454d55;
            border-color: #6c757d;
            color: #fff;
        }
        
        .dark-mode .select2-container--default .select2-results__option {
            color: #fff;
        }
        
        .dark-mode .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #6c757d;
        }
        
        .dark-mode .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-lg-6">
                        <h1>Challenges</h1>
                    </div>
                    <div class="col-lg-6">
                        <ol class="breadcrumb float-lg-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item">Challenges</li>
                            <li class="breadcrumb-item active">Challenge</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach:wq
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if(Session::has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            <form id="createChallengeForm" action="{{ url('admin/challenge-create') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Challenge Basic Information</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="title">Title <span style="color: #ff5252;">*</span></label>
                                            <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="title_for_frontend">Frontend Title<span style="color: #ff5252;">*</span></label>
                                            <input type="text" id="title_for_frontend" name="title_for_frontend" class="form-control" value="{{ old('title_for_frontend') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description <span style="color: #ff5252;">*</span></label>
                                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="video_url">Video Link<span style="color: #ff5252;">*</span></label>
                                            <input title="Please enter video link" type="text" id="video_url" name="video_url" class="form-control" value="{{ old('video_url') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="gif">Image<span style="color: #ff5252;">*</span></label>
                                            <input type="file" id="gif" name="gif" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        <!-- Levels Section -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Challenge Levels</h3>
                                <button type="button" class="btn btn-success btn-sm float-right" onclick="addLevel()">Add Level</button>
                            </div>
                            <div class="card-body" id="levelsContainer">
                                <!-- Levels will be added dynamically here -->
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="status" name="status">
                            <label for="status">Enable</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-2">
                        <input type="submit" value="Save" class="btn btn-success float-right">
                    </div>
                </div>
            </form>
        </section>
        <!-- /.content -->
    </div>

    @section('scripts')
    <script src="{{ asset('adminAssets/plugins/jquery/jquery.min.js') }}"></script>
    
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    
    <!-- jQuery Validation -->
    <script src="{{ asset('adminAssets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('adminAssets/plugins/jquery-validation/additional-methods.min.js') }}"></script>
    
    <!-- Sortable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.js"></script>
    <script src="{{ asset('adminAssets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
      $(document).ready(function() {
        // Initialize form validation
        const formValidator = $('#createChallengeForm').validate({
            rules: {
                title: {
                    required: true,
                    minlength: 3
                },
                title_for_frontend: {
                    required: true,
                    minlength: 3
                },
                description: {
                    required: true,
                    minlength: 10
                },
                video_url: {
                    required: true,
                    // url: true
                },
                gif: {
                    required: true,
                    accept: "image/*"
                }
            },
            messages: {
                title: {
                    required: "Please enter challenge title",
                    minlength: "Title must be at least 3 characters long"
                },
                title_for_frontend: {
                    required: "Please enter frontend title",
                    minlength: "Frontend title must be at least 3 characters long"
                },
                description: {
                    required: "Please write a description about this challenge",
                    minlength: "Description must be at least 10 characters long"
                },
                video_url: {
                    required: "Please enter video URL",
                    url: "Please enter a valid URL"
                },
                gif: {
                    required: "Please upload a GIF file",
                    accept: "Please upload only image files"
                }
            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        // Function to validate levels and exercises
        function validateLevelsAndExercises() {
            let isValid = true;
            let errorMessages = [];

            // Check if at least one level exists
            if ($('.level-group').length === 0) {
                errorMessages.push("Please add at least one level");
                isValid = false;
            }

            // Validate each level
            $('.level-group').each(function(levelIndex) {
                const levelSelect = $(this).find(`select[name^="levels[${levelIndex}][id]"]`);
                const durationInput = $(this).find(`input[name^="levels[${levelIndex}][duration]"]`);

                // Validate level selection
                if (!levelSelect.val()) {
                    levelSelect.addClass('is-invalid');
                    errorMessages.push(`Please select level for Level ${levelIndex + 1}`);
                    isValid = false;
                }

                // Validate duration
                if (!durationInput.val()) {
                    durationInput.addClass('is-invalid');
                    errorMessages.push(`Please enter duration for Level ${levelIndex + 1}`);
                    isValid = false;
                } else {
                    const duration = parseInt(durationInput.val());
                    if (duration < 1 || duration > 31) {
                        durationInput.addClass('is-invalid');
                        errorMessages.push(`Duration must be between 1 and 31 days for Level ${levelIndex + 1}`);
                        isValid = false;
                    }

                    // Validate each day within the level
                    for (let day = 1; day <= duration; day++) {
                        const dayDescription = $(this).find(`input[name="levels[${levelIndex}][days][${day}][description]"]`);
                        if (!dayDescription.val()) {
                            dayDescription.addClass('is-invalid');
                            errorMessages.push(`Please enter description for Level ${levelIndex + 1}, Day ${day}`);
                            isValid = false;
                        }

                        // Validate exercises for each day
                        const exerciseList = $(`#exerciseList${levelIndex}_${day}`);
                        const exercises = exerciseList.find('li');

                        if (exercises.length === 0) {
                            errorMessages.push(`Please add at least one exercise for Level ${levelIndex + 1}, Day ${day}`);
                            isValid = false;
                        } else {
                            exercises.each(function(exerciseIndex) {
                                const type = $(this).find('.exercise-type').val();
                                const reps = $(this).find('.repetition-input').val();
                                const duration = $(this).find('.duration-input').val();
                                const rest = $(this).find('input[name*="rest_periods"]').val();

                                // Validate exercise type
                                if (!type) {
                                    $(this).find('.exercise-type').addClass('is-invalid');
                                    errorMessages.push(`Please select exercise type for Exercise ${exerciseIndex + 1}, Level ${levelIndex + 1}, Day ${day}`);
                                    isValid = false;
                                } else {
                                    // Validate repetitions or duration based on type
                                    if (type === 'repetitions' && !reps) {
                                        $(this).find('.repetition-input').addClass('is-invalid');
                                        errorMessages.push(`Please enter repetitions for Exercise ${exerciseIndex + 1}, Level ${levelIndex + 1}, Day ${day}`);
                                        isValid = false;
                                    }
                                    if (type === 'duration' && !duration) {
                                        $(this).find('.duration-input').addClass('is-invalid');
                                        errorMessages.push(`Please enter duration for Exercise ${exerciseIndex + 1}, Level ${levelIndex + 1}, Day ${day}`);
                                        isValid = false;
                                    }
                                }

                                // Validate rest period
                                if (!rest) {
                                    $(this).find('input[name*="rest_periods"]').addClass('is-invalid');
                                    errorMessages.push(`Please enter rest period for Exercise ${exerciseIndex + 1}, Level ${levelIndex + 1}, Day ${day}`);
                                    isValid = false;
                                }
                            });
                        }
                    }
                }
            });

            // Display error messages if validation fails
            if (!isValid) {
                $('.alert-danger').remove();
                const errorHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('#createChallengeForm').find('.card:first').before(errorHtml);
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            }

            return isValid;
        }

        // Handle form submission
        $('#createChallengeForm').on('submit', function(e) {
            e.preventDefault();
            
            // Run both validations
            const isBasicValid = formValidator.form();
            const isLevelsValid = validateLevelsAndExercises();

            if (isBasicValid && isLevelsValid) {
                this.submit(); // Submit the form if both validations pass
            }
        });

        // Clear invalid state on input change
        $(document).on('input change', 'input, select, textarea', function() {
            $(this).removeClass('is-invalid');
        });
    });
    </script>
    <script>
        // Add a global variable to track selected levels
        let selectedLevels = {};

        function addLevel() {
            let levelIndex = document.querySelectorAll('.level-group').length;
            let levelContainer = document.createElement('div');
            levelContainer.classList.add('level-group', 'card', 'mt-3', 'p-3');

            levelContainer.innerHTML = `
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #355e89d1;">
                    <h3 class="card-title mb-0">Level ${levelIndex + 1}</h3>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeLevel(this)" style="margin-left: auto;">
                        <i class="fas fa-trash"></i> Remove Level
                    </button>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Level <span style="color: #ff5252;">*</span></label>
                                <select name="levels[${levelIndex}][id]" class="form-control level-select" onchange="handleLevelChange(this)">
                                    <option value="">Select Level</option>
                                    @foreach ($challengeLevels as $level)
                                        <option value="{{ $level['id'] }}">{{ $level['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Duration (Days) <span style="color: #ff5252;">*</span></label>
                                <input type="number" name="levels[${levelIndex}][duration]" class="form-control" min="1" max="31" oninput="addDays(this, ${levelIndex})">
                            </div>
                        </div>
                    </div>
                    <div class="days-container" id="daysContainer${levelIndex}"></div>
                </div>
            `;
            document.getElementById('levelsContainer').appendChild(levelContainer);
            
            // Update all level dropdowns to reflect current selections
            updateAllLevelDropdowns();
        }

        function handleLevelChange(select) {
            const levelId = select.value;
            const previousValue = select.getAttribute('data-previous-value');
            
            // If there was a previous selection, make it available again
            if (previousValue && previousValue !== '') {
                delete selectedLevels[previousValue];
            }
            
            // Mark the new selection as selected
            if (levelId && levelId !== '') {
                selectedLevels[levelId] = true;
            }
            
            // Store the current value as previous for next change
            select.setAttribute('data-previous-value', levelId);
            
            // Update all dropdowns
            updateAllLevelDropdowns();
        }

        // Update all level dropdowns
        function updateAllLevelDropdowns() {
            const dropdowns = document.querySelectorAll('.level-select');
            
            dropdowns.forEach(dropdown => {
                const currentValue = dropdown.value;
                
                // Store current selection
                const currentSelection = dropdown.value;
                
                // Update options availability
                Array.from(dropdown.options).forEach(option => {
                    if (option.value === '' || option.value === currentSelection) {
                        // Always keep empty option and current selection enabled
                        option.disabled = false;
                    } else {
                        // Disable if already selected elsewhere
                        option.disabled = selectedLevels[option.value] === true;
                    }
                });
            });
        }

        function removeLevel(button) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const levelContainer = button.closest('.level-group');
                    
                    // Free up the selected level before removing
                    const levelSelect = levelContainer.querySelector('.level-select');
                    if (levelSelect && levelSelect.value) {
                        delete selectedLevels[levelSelect.value];
                    }
                    
                    levelContainer.remove();
                    reorderLevels();
                    
                    // Update dropdowns after removal
                    updateAllLevelDropdowns();
                    
                    Swal.fire(
                        'Deleted!',
                        'The level has been removed.',
                        'success'
                    );
                }
            });
        }

        // Initialize selected levels on page load
        $(document).ready(function() {
            // Add to existing document ready function
            
            // Initialize selected levels from any pre-existing levels
            document.querySelectorAll('.level-select').forEach(select => {
                const levelId = select.value;
                if (levelId && levelId !== '') {
                    selectedLevels[levelId] = true;
                    select.setAttribute('data-previous-value', levelId);
                }
            });
            
            // Initial update of all dropdowns
            updateAllLevelDropdowns();

            // Add change event handler to any existing level selects
            $(document).on('change', '.level-select', function() {
                handleLevelChange(this);
            });
        });

        function reorderLevels() {
            const levels = document.querySelectorAll('.level-group');
            levels.forEach((level, index) => {
                // Update level title
                const levelTitle = level.querySelector('.card-title');
                if (levelTitle) {
                    levelTitle.textContent = `Level ${index + 1}`;
                }

                // Update select name
                const select = level.querySelector('select[name^="levels"]');
                if (select) {
                    select.name = `levels[${index}][id]`;
                    if (!select.classList.contains('level-select')) {
                        select.classList.add('level-select');
                    }
                    if (!select.hasAttribute('onchange')) {
                        select.setAttribute('onchange', 'handleLevelChange(this)');
                    }
                }

                // Update duration input name
                const durationInput = level.querySelector('input[name^="levels"][name$="[duration]"]');
                if (durationInput) {
                    durationInput.name = `levels[${index}][duration]`;
                    // Update the oninput handler
                    durationInput.setAttribute('oninput', `addDays(this, ${index})`);
                }

                // Update days container ID
                const daysContainer = level.querySelector('.days-container');
                if (daysContainer) {
                    daysContainer.id = `daysContainer${index}`;
                }

                // Update all day inputs and exercise lists within this level
                const dayGroups = level.querySelectorAll('.day-group');
                dayGroups.forEach((dayGroup, dayIndex) => {
                    const dayNumber = dayIndex + 1;
                    
                    // Update day description input
                    const descInput = dayGroup.querySelector(`input[name^="levels"][name$="[description]"]`);
                    if (descInput) {
                        descInput.name = `levels[${index}][days][${dayNumber}][description]`;
                    }

                    // Update exercise list ID and related elements
                    const exerciseList = dayGroup.querySelector('.sortable-list');
                    if (exerciseList) {
                        exerciseList.id = `exerciseList${index}_${dayNumber}`;
                        
                        // Update exercise dropdown onchange handler
                        const exerciseDropdown = dayGroup.querySelector('.exercise-dropdown');
                        if (exerciseDropdown) {
                            exerciseDropdown.setAttribute('onchange', `addExercise(this, ${index}, ${dayNumber})`);
                        }

                        // Update copy/paste buttons
                        const copyButton = dayGroup.querySelector('button[onclick^="copyConfig"]');
                        const pasteButton = dayGroup.querySelector('button[onclick^="pasteConfig"]');
                        if (copyButton) {
                            copyButton.setAttribute('onclick', `copyConfig(${index}, ${dayNumber})`);
                        }
                        if (pasteButton) {
                            pasteButton.setAttribute('onclick', `pasteConfig(${index}, ${dayNumber})`);
                        }

                        // Update all exercise items' input names
                        const exerciseItems = exerciseList.querySelectorAll('li');
                        exerciseItems.forEach((item, exerciseIndex) => {
                            const inputs = item.querySelectorAll('input, select');
                            inputs.forEach(input => {
                                const nameType = input.name.match(/\[(exercises|types|repetitions|durations|rest_periods)\]/)[1];
                                input.name = `levels[${index}][days][${dayNumber}][${nameType}][]`;
                            });
                        });
                    }
                });
            });
        }
        
        function addDays(input, levelIndex) {
            let daysContainer = document.getElementById(`daysContainer${levelIndex}`);
            let newDaysCount = parseInt(input.value);
            let existingDays = daysContainer.querySelectorAll('.day-group');
            let currentDaysCount = existingDays.length;

            if (newDaysCount < 1 || newDaysCount > 31) {
                input.value = Math.max(1, Math.min(31, newDaysCount));
                newDaysCount = parseInt(input.value);
            }

            if (newDaysCount < currentDaysCount) {
                // Confirmation for removing days
                Swal.fire({
                    title: 'Remove Days?',
                    text: "Reducing the number of days will permanently delete the last day(s) and their configurations. This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove days',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Remove excess days from the end
                        for (let i = currentDaysCount; i > newDaysCount; i--) {
                            let lastDay = daysContainer.querySelector('.day-group:last-child');
                            if (lastDay) {
                                lastDay.remove();
                            }
                        }
                    } else {
                        // Revert the input to the original number of days
                        input.value = currentDaysCount;
                    }
                });
            } else if (newDaysCount > currentDaysCount) {
                // Information alert for adding days
                Swal.fire({
                    title: 'Adding New Days',
                    text: `You are adding ${newDaysCount - currentDaysCount} new day(s) to this level. You can configure exercises for these new days.`,
                    icon: 'info',
                    confirmButtonText: 'Proceed'
                }).then((result) => {
                    // Add new days while preserving existing ones
                    for (let i = currentDaysCount + 1; i <= newDaysCount; i++) {
                        let dayDiv = document.createElement('div');
                        dayDiv.classList.add('day-group', 'card', 'mt-2', 'p-2');
                        dayDiv.innerHTML = `
                            <div class="card-header" style="background: #355e89d1;">Day ${i}</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Day ${i} - Short Description</label>
                                    <input type="text" name="levels[${levelIndex}][days][${i}][description]" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Select Exercise</label>
                                    <select class="exercise-dropdown form-control" onchange="addExercise(this, ${levelIndex}, ${i})">
                                        <option value="">Select Exercise</option>
                                    </select>
                                    <ul class="sortable-list" id="exerciseList${levelIndex}_${i}"></ul>
                                    <button type="button" onclick="copyConfig(${levelIndex}, ${i})">Copy</button>
                                    <button type="button" onclick="pasteConfig(${levelIndex}, ${i})">Paste</button>
                                </div>
                            </div>
                        `;
                        daysContainer.appendChild(dayDiv);
                        
                        // Initialize sortable for the new exercise list
                        let newExerciseList = dayDiv.querySelector('.sortable-list');
                        new Sortable(newExerciseList, {
                            animation: 150,
                            onSort: function(evt) {
                                reorderInputNames(newExerciseList, levelIndex, i);
                            }
                        });

                        // Fetch exercise options for the new day
                        fetchOptions(dayDiv.querySelector('.exercise-dropdown'));
                    }
                });
            }

            // Validate input range
            if (newDaysCount < 1) {
                input.value = 1;
                addDays(input, levelIndex);
            } else if (newDaysCount > 31) {
                input.value = 31;
                addDays(input, levelIndex);
            }
        }
        
        let copiedConfig = null;

        function copyConfig(levelIndex, dayIndex) {
            let list = document.getElementById(`exerciseList${levelIndex}_${dayIndex}`);
            let exercises = [];

            list.querySelectorAll('li').forEach(item => {
                exercises.push({
                    exerciseId: item.querySelector('input[type="hidden"]').value,
                    exerciseName: item.querySelector('.exercise-heading').textContent.trim(),
                    exerciseType: item.querySelector('.exercise-type').value,
                    repetitions: item.querySelector('.repetition-input').value,
                    duration: item.querySelector('.duration-input').value,
                    restPeriod: item.querySelector('input[name*="rest_periods"]').value
                });
            });
            copiedConfig = exercises;
            Swal.fire({
                icon: "success",
                title: "Day config copied!",
                showConfirmButton: false,
                timer: 1500
            });
        }

        function pasteConfig(levelIndex, dayIndex) {
            if (!copiedConfig) {
                Swal.fire({
                    icon: "error",
                    title: "nothing copied!",
                    showConfirmButton: false,
                    timer: 1500
                });
                return;
            }
            
            let list = document.getElementById(`exerciseList${levelIndex}_${dayIndex}`);
            list.innerHTML = '';

            copiedConfig.forEach((exercise, index) => {
                let listItem = document.createElement("li");
                listItem.innerHTML = `
                    <input type="hidden" name="levels[${levelIndex}][days][${dayIndex}][exercises][${index}]" value="${exercise.exerciseId}">
                    <div class="exercise-heading">${exercise.exerciseName}</div>
                    <select name="levels[${levelIndex}][days][${dayIndex}][types][${index}]" class="exercise-type form-control" onchange="toggleInputFields(this)">
                        <option value="">Select Type</option>
                        <option value="repetitions" ${exercise.exerciseType === 'repetitions' ? 'selected' : ''}>Repetitions</option>
                        <option value="duration" ${exercise.exerciseType === 'duration' ? 'selected' : ''}>Duration</option>
                    </select>
                    <input type="number" name="levels[${levelIndex}][days][${dayIndex}][repetitions][${index}]" placeholder="Repetitions" class="form-control repetition-input ${exercise.exerciseType === 'repetitions' ? '' : 'd-none'}" value="${exercise.repetitions}">
                    <input type="number" name="levels[${levelIndex}][days][${dayIndex}][durations][${index}]" placeholder="Duration (seconds)" class="form-control duration-input ${exercise.exerciseType === 'duration' ? '' : 'd-none'}" value="${exercise.duration}">
                    <input type="number" name="levels[${levelIndex}][days][${dayIndex}][rest_periods][${index}]" placeholder="Rest (seconds)" class="form-control" value="${exercise.restPeriod}">
                    <button type="button" onclick="removeExercise(this)"><i class='fas fa-trash'></i></button>
                `;
                list.appendChild(listItem);
            });

            Swal.fire({
                icon: "success",
                title: "Day config pasted!",
                showConfirmButton: false,
                timer: 1500
            });

            new Sortable(list, { 
                animation: 150,
                onSort: function(evt) {
                    reorderInputNames(list, levelIndex, dayIndex);
                }
            });
        }
                
        function fetchOptions(dropdown) {
            fetch('/admin/get-challenge-exercises-list')
                .then(response => response.json())
                .then(data => {
                    data.forEach(option => {
                        let optionElement = document.createElement("option");
                        optionElement.value = option.value;
                        optionElement.textContent = option.label;
                        dropdown.appendChild(optionElement);
                    });
                    
                    // Initialize Select2
                    $(dropdown).select2({
                        placeholder: "Search and select exercise",
                        allowClear: true,
                        width: '100%'
                    });

                    // Add change event handler after Select2 initialization
                    $(dropdown).on('select2:select', function(e) {
                        // Call your existing addExercise function
                        addExercise(this, ...this.getAttribute('onchange').match(/\d+/g));
                        // Reset the select after adding
                        $(this).val('').trigger('change');
                    });
                })
                .catch(error => {
                    console.error('Error fetching options:', error);
                });
        }
        
        function addExercise(select, levelIndex, dayIndex) {
            let selectedValue = select.value;
            let selectedText = select.options[select.selectedIndex].text;
            if (!selectedValue) return;

            let list = document.getElementById(`exerciseList${levelIndex}_${dayIndex}`);
            let listItem = document.createElement("li");
            
            // Add hidden input for exercise ID
            listItem.innerHTML = `
                <input type="hidden" name="levels[${levelIndex}][days][${dayIndex}][exercises][]" value="${selectedValue}">
                <div class="exercise-heading">${selectedText}</div>
                <select name="levels[${levelIndex}][days][${dayIndex}][types][]" class="exercise-type form-control" onchange="toggleInputFields(this)">
                    <option value="">Select Type</option>
                    <option value="repetitions">Repetitions</option>
                    <option value="duration">Duration</option>
                </select>
                <input type="number" name="levels[${levelIndex}][days][${dayIndex}][repetitions][]" placeholder="Repetitions" class="form-control repetition-input d-none">
                <input type="number" name="levels[${levelIndex}][days][${dayIndex}][durations][]" placeholder="Duration (seconds)" class="form-control duration-input d-none">
                <input type="number" name="levels[${levelIndex}][days][${dayIndex}][rest_periods][]" placeholder="Rest (seconds)" class="form-control">
                <button type="button" onclick="removeExercise(this)"><i class='fas fa-trash'></i></button>
            `;
            
            list.appendChild(listItem);
            new Sortable(list, { 
                animation: 150,
                onSort: function(evt) {
                    reorderInputNames(list, levelIndex, dayIndex);
                }
            });
            select.value = "";
        }
    
        function removeExercise(button) {
            let listItem = button.parentElement;
            let list = listItem.parentElement;
            let levelIndex = list.id.split('_')[0].replace('exerciseList', '');
            let dayIndex = list.id.split('_')[1];
            
            listItem.remove();
            reorderInputNames(list, levelIndex, dayIndex);
        }
        
        function reorderInputNames(list, levelIndex, dayIndex) {
            // Reorder all input names after sorting or removal
            let items = list.getElementsByTagName('li');
            Array.from(items).forEach((item, index) => {
                let inputs = item.getElementsByTagName('input');
                let selects = item.getElementsByTagName('select');
                
                Array.from(inputs).forEach(input => {
                    let nameBase = input.name.split('[').slice(0, -1).join('[');
                    input.name = `${nameBase}[${index}]`;
                });
                
                Array.from(selects).forEach(select => {
                    let nameBase = select.name.split('[').slice(0, -1).join('[');
                    select.name = `${nameBase}[${index}]`;
                });
            });
        }

        function toggleInputFields(select) {
            let parent = select.parentElement;
            parent.querySelector('.repetition-input').classList.toggle('d-none', select.value !== 'repetitions');
            parent.querySelector('.duration-input').classList.toggle('d-none', select.value !== 'duration');
        }
    </script>
    @endsection
</x-admin-layout>
