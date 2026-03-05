<x-admin-layout title="Create Program">
    @section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .ck.ck-editor__editable_inline {
            color: #000;
            min-height: 200px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered{
            color: #fff
        }
        .select2-container--default .select2-selection--single{
            background-color: #343a40;
            height: 38px;
            color: #fff;
            border-color: #6c757d;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{
             height: 38px;
        }
        button.btn.btn-sm.btn-primary.mt-2.ml-40 {
            display: block;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: #fff !important;
        }
        .select2-results__option{
        background-color: #343a40 !important;
        color: #ffffff !important;            /* faded text */
        }
        .dark-mode input:-webkit-autofill {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #23232329;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: transparent;
        }

        .dark-mode .select2-purple .select2-container--default .select2-search--inline .select2-search__field:focus {
            border: none;
        }

        ul.sortable-list {
            list-style-type: none;
            padding: 0;
        }

        .dropdown-and-list {
            margin-bottom: 10px;
        }
        .dropdown-and-list label {
            display: block;
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

        .program-type-radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-top: 8px;
        }
        .program-type-radio-group label {
            margin-right: 10px;
            font-weight: 500;
            color: #3f6791;
        }
        #cardio_dropdown, #muscle_dropdown {
            transition: all 0.3s;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-lg-6">
                        <h1>Programs</h1>
                    </div>
                    <div class="col-lg-6">
                        <ol class="breadcrumb float-lg-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item">Programs</li>
                            <li class="breadcrumb-item active">Program</li>
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
            <form id="createProgramForm" action="{{ url('admin/program-create') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Program Basic information</h3>
                                <div class="card-tools">
                                    <!-- <a type="button" title="Return to List" class="btn btn-secondary" href="{{ url('admin/program-index') }}">
                                        <i class="fas fa-arrow-left"></i>
                                    </a> -->
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inputName">Title <span style="color: #ff5252;">*</span></label>
                                            <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inputDescription">Category <span style="color: #ff5252;">*</span></label>
                                            <select name="category_id" id="category_id" class="form-control">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }} style="font-weight: bold;">
                                                        {{ $category['category_name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="program_tag">Tags</label>
                                            <div class="select2-purple">
                                                <select name="program_tag[]" id="program_tag" class="form-control select2" data-dropdown-css-class="select2-purple" multiple>
                                                    <option value="" disabled>Select Program Tags</option>
                                                    @foreach ($tags as $tag)
                                                    <option value="{{ $tag['tag_name'] }}" {{ (is_array(old('program_tag')) && in_array($tag['tag_name'], old('program_tag'))) ? 'selected' : '' }}>{{ $tag['tag_name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputName">Description <span style="color: #ff5252;">*</span></label>
                                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="inputName">Objective <span style="color: #ff5252;">*</span></label>
                                    <textarea id="objective" name="objective" class="form-control">{{ old('objective') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputName">Video Link<span style="color: #ff5252;">*</span></label>
                                            <input title="Please enter video link" type="text" id="videos" name="videos" class="form-control" value="{{ old('videos') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputName">Program Image <span style="color: #ff5252;">*</span></label>
                                            <input title="Please select an image" type="file" id="program_image" name="program_image" accept="image/*" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Program Information -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Program Information</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputName">Body Area <span style="color: #ff5252;">*</span></label>
                                            <input type="text" id="body_area" name="body_area" class="form-control" value="{{ old('body_area') }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="inputName">Duration (<small>in weeks</small>) <span style="color: #ff5252;">*</span></label>
                                                    <input type="number" id="duration" name="duration" min="0" oninput="generateDropdowns()" class="form-control" max="54" value="{{ old('duration') }}" onkeydown="return event.key != 'Enter';">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputName">Frequency (<small>Number of session per week</small>)<span style="color: #ff5252;">*</span></label>
                                            <input type="number" id="frequency" name="frequency" min="1" class="form-control" value="{{ old('frequency') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputName">Level <span style="color: #ff5252;">*</span></label>
                                            <select name="level" id="level" class="form-control">
                                                <option value="">Select Level</option>
                                                @foreach ($programLevels as $level)
                                                <option value="{{ $level['id'] }}" {{ old('level') == $level['id'] ? 'selected' : '' }}  style="font-weight: bold;">{{ $level['level_title'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Program Type <span style="color: #ff5252;">*</span></label>
                                            <div class="program-type-radio-group">
                                                <input type="radio" id="type_cardio" name="program_type" value="cardio" {{ old('program_type', 'cardio') == 'cardio' ? 'checked' : '' }}>
                                                <label for="type_cardio">Cardio</label>
                                                <input type="radio" id="type_muscle" name="program_type" value="muscle" {{ old('program_type') == 'muscle' ? 'checked' : '' }}>
                                                <label for="type_muscle">Muscle Strengthening</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4" id="cardio_dropdown" style="{{ old('program_type', 'cardio') == 'cardio' ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label for="inputDescription">Cardio Type <span style="color: #ff5252;">*</span></label>
                                            <select name="cardio_id" id="cardio_id" class="form-control">
                                                <option value="">Select Cardio</option>
                                                @foreach($cardioData as $cardio)
                                                <option value="{{ $cardio['id'] }}" {{ old('cardio_id') == $cardio['id'] ? 'selected' : '' }} style="font-weight: bold;">{{ $cardio['title'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4" id="muscle_dropdown" style="{{ old('program_type') == 'muscle' ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label for="inputDescription">Muscle Strengthening Type <span style="color: #ff5252;">*</span></label>
                                            <select name="muscle_strength_id" id="muscle_strength_id" class="form-control">
                                                <option value="">Select Muscle Strengthening Type</option>
                                                @foreach($muscleData as $muscle)
                                                <option value="{{ $muscle['id'] }}" {{ old('muscle_strength_id') == $muscle['id'] ? 'selected' : '' }} style="font-weight: bold;">{{ $muscle['title'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>

                        <!-- Session Information -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Session Information</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="dropdownAndListContainer"></div>

                                <!-- Hidden inputs for storing list items -->
                                <div id="hiddenInputsContainer"></div>

                            </div>
                            <!-- /.card-body -->
                        </div>

                        <!-- Program Status -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Program Status</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="free_access" name="free_access" @if(isset($program) && $program['free_access']==1) checked @endif>
                                            <label for="free_access">Free Access</label>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="status" name="status" @if(isset($program) && $program['status']==1) checked @endif>
                                            <label for="status">Enable</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
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

    <script>
        $(function() {
            // Custom validation method to check file size
            $.validator.addMethod("maxfilesize", function(value, element, param) {
                if (element.files.length > 0) {
                    // Check if file size exceeds the limit
                    return element.files[0].size <= param;
                }
                return true;
            }, "File size must be less than 2 MB.");

            $('#createProgramForm').validate({
                rules: {
                    title: {
                        required: true,
                    },
                    category_id: {
                        required: true
                    },
                    videos: {
                        required: true,
                    },
                    level: {
                        required: true
                    },
                    body_area: {
                        required: true
                    },
                    duration: {
                        required: true
                    },
                    frequency: {
                        required: true
                    },
                    session_duration: {
                        required: true
                    },
                    session_id: {
                        required: true
                    },
                    cardio_id: {
                        required: true
                    },
                    muscle_strength_id: {
                        required: true
                    },
                    description: {
                        required: true
                    },
                    objective: {
                        required: true
                    },
                    program_image: {
                        required: true,
                        accept: "image/*",
                        extension: "jpg,jpeg,png",
                        maxfilesize: 2097152
                    }
                },
                messages: {
                    title: {
                        required: "Please enter program title",
                    },
                    category_id: {
                        required: "Please select category",
                    },
                    level: {
                        required: "Please Select Level"
                    },
                    body_area: {
                        required: "Please enter body area"
                    },
                    duration: {
                        required: "Please enter program duration"
                    },
                    frequency: {
                        required: "Please enter program frequency"
                    },
                    session_duration: {
                        required: "Please enter program's session duratoin"
                    },
                    session_id: {
                        required: "Please select sessions."
                    },
                    videos: {
                        required: "Please upload a video file.",
                    },
                    cardio_id: {
                        required: "Please select cardio type."
                    },
                    muscle_strength_id: {
                        required: "Please select muscle strengthening type."
                    },
                    description: {
                        required: "Please write a short description about this program."
                    },
                    objective: {
                        required: "Please write in short about the objective of this program."
                    },
                    program_image: {
                        required: "Please upload an image file",
                        accept: "This field only accept image file",
                        maxfilesize: "File size must be less than 2 MB." // Message for file size validation
                    },
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
        });
    </script>

    <!-- Add Dynamic Session Field -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function generateDropdowns() {
            var input = document.getElementById("duration").value;
            if (input > 54) {
                alert("Duration weeks should not more than 54 week.");
                return false;
            }
            var dropdownAndListContainer = document.getElementById("dropdownAndListContainer");

            // Clear previous content
            dropdownAndListContainer.innerHTML = "";

            // Generate dropdowns and lists based on the entered number
            for (var i = 0; i < input; i++) {
                var dropdownAndListDiv = document.createElement("div");
                dropdownAndListDiv.classList.add("dropdown-and-list");

                var week = i + 1;
                var label = document.createElement("label");
                label.textContent = "Session Semaine " + week;
                dropdownAndListDiv.appendChild(label);

                var dropdown = document.createElement("select");
                dropdown.className = "form-control w-50 select2Duration";
                dropdown.name = "dropdown" + i; // Set the name attribute
                dropdown.id = "dropdown" + i;
                dropdownAndListDiv.appendChild(dropdown);

                var addBtn = document.createElement("button");
                addBtn.textContent = "Add Session";
                addBtn.className = "btn btn-sm btn-primary mt-2 ml-40";
                addBtn.type = "button"; // Change the type to prevent form submission
                addBtn.setAttribute("onclick", "addToSelectedList('dropdown" + i + "')");
                dropdownAndListDiv.appendChild(addBtn);

                var list = document.createElement("ul");
                list.id = "list" + i;
                list.classList.add("sortable-list"); // Add a class for SortableJS
                dropdownAndListDiv  .appendChild(list);

                dropdownAndListContainer.appendChild(dropdownAndListDiv);
                //search in select
                 $(document).on('focus', '.select2Duration', function () {
                    if (!$(this).hasClass("select2-hidden-accessible")) {
                        $(this).select2({
                            width: '50%',
                             minimumResultsForSearch: 0
                        });
                    }
                });
                // Fetch options for the dropdown
                fetchOptions(dropdown);
            }
                    $('.select2Duration').select2({
                        width: '50%',
                        minimumResultsForSearch: 0
                    });

            // Initialize Sortable on each list
            var sortableLists = document.querySelectorAll(".sortable-list");
            sortableLists.forEach(function(list) {
                new Sortable(list, {
                    animation: 150
                });
            });
        }


        $(document).ready(function() {
            $('input[name="program_type"]').change(function() {
                if ($(this).val() === 'cardio') {
                    $('#cardio_dropdown').show();
                    $('#muscle_dropdown').hide();
                } else {
                    $('#cardio_dropdown').hide();
                    $('#muscle_dropdown').show();
                }
            });
        });

        function addToSelectedList(dropdownId) {
            var dropdown = document.getElementById(dropdownId);
            var selectedOptionValue = dropdown.options[dropdown.selectedIndex].value;
            var selectedOption = dropdown.options[dropdown.selectedIndex].text;
            var dropdownIndex = dropdownId.match(/\d+/)[0];
            var listId = "list" + dropdownIndex;
            var list = document.getElementById(listId);
            var frequency = parseInt(document.getElementById("frequency").value, 10);

            // Check for duplicates
            var isDuplicate = false;
            var listItems = list.getElementsByTagName("li");
            for (var i = 0; i < listItems.length; i++) {
                if (listItems[i].textContent === selectedOption) {
                    isDuplicate = false;
                    // isDuplicate = true; //uncomment this line to check duplication
                    break;
                }
            }

            // Check for maximum limit of items to add
            if (listItems.length >= frequency) {
                alert("You can only add up to " + frequency + " items in the list as you mentioned into the frequency");
                return;
            }

            if (!isDuplicate) {
                var listItem = document.createElement("li");
                listItem.textContent = selectedOption;

                var deleteBtn = document.createElement("button");
                deleteBtn.className = "btn btn-sm btn-danger ml-2 float-right";
                deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                deleteBtn.type = "button";
                deleteBtn.onclick = function() {
                    list.removeChild(listItem);
                    // Remove corresponding hidden input
                    var hiddenInputs = document.querySelectorAll("input[name='selectedOptions[" + dropdownIndex + "][]']");
                    hiddenInputs.forEach(function(input) {
                        if (input.value === selectedOptionValue) {
                            input.parentElement.removeChild(input);
                        }
                    });
                };
                listItem.appendChild(deleteBtn);
                list.appendChild(listItem);

                // Add the selected option to the hidden input field with dropdown identifier
                var hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "selectedOptions[" + dropdownIndex + "][]";
                hiddenInput.value = selectedOptionValue; // Store the value instead of text
                document.getElementById("hiddenInputsContainer").appendChild(hiddenInput);
            } else {
                alert("This option is already in the list.");
            }
        }

        function fetchOptions(dropdown) {
            // Replace 'your-options-endpoint' with your actual endpoint
            fetch('/admin/get-session')
                .then(response => response.json())
                .then(data => {
                    // Populate the dropdown with options from the server
                    data.forEach(option => {
                        var optionElement = document.createElement("option");
                        optionElement.value = option.value;
                        optionElement.textContent = option.label;
                        dropdown.appendChild(optionElement);
                    });
                })
                .catch(error => {
                    console.error('Error fetching options:', error);
                });
        }
    </script>

    <script>
        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>
    @endsection
</x-admin-layout>
