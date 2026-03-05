<?php

use App\Models\Session as ModelSession;
?>
<x-admin-layout title="Update Program">
    @section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
         .select2-container--default .select2-selection--single .select2-selection__rendered{
            color: #fff;
        }

        button.btn.btn-sm.btn-primary.mt-2.ml-40  {
            display: block;
        }
        .select2-container--default .select2-selection--single{
            background-color: #343a40;
            height: 38px;
            border-color: #6c757d;
            color: #fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{
             height: 38px;
        }
        .ck.ck-editor__editable_inline {
            color: #000;
            min-height: 200px;
        }
        .select2-search__field{
            border-color: #6c757d;
        }
        .swal2-modal .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: #fff !important;
        }
        .swal2-modal .select2-results__option{
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
            width: 100%;
            position: relative;
        }

        ul.sortable-list {
            position: relative;
            width: 50%;
        }

        .sortable-list .edit-remove-session {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            margin: 0 !important;
            display: flex;
            align-items: center;
        }


        ul.sortable-list {
            list-style-type: none;
            padding: 0;
        }

        ul.sortable-list li {
            background-color: #3f6791;
            border-radius: 5px;
            margin-top: 2px;
            margin-bottom: 5px;
            padding: 10px;
            cursor: move;
            width: 100%;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .session-actions {
            display: flex;
            gap: 5px;
        }

        .session-title {
            flex-grow: 1;
            margin-right: 10px;
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
                    @endforeach
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
            <form id="programUpdateForm" acion="{{ url('admin/program-update/'.$program['slug']) }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Program Basic information</h3>
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
                                            <label for="inputName">Title <span style="color: #ff5252;">*</span></label>
                                            <input type="text" id="title" name="title" class="form-control" value="{{ $program->title }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="inputDescription">Category <span style="color: #ff5252;">*</span></label>
                                            <select name="category_id" id="category_id" class="form-control">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category['id'] }}" @if($program['category_id']==$category['id']) selected @endif style="font-weight: bold;">{{ $category['category_name'] }}</option>
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
                                                    @foreach ($tagData as $tag)
                                                    <option value="{{ $tag['tag_name'] }}" @if(isset($program)) {{in_array($tag['tag_name'], explode("|", $program['program_tag'])) ? 'selected' : ''}} @endif>{{ $tag['tag_name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputName">Description <span style="color: #ff5252;">*</span></label>
                                    <textarea id="description" name="description" class="form-control">{{ $program->description }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="inputName">Objective <span style="color: #ff5252;">*</span></label>
                                    <textarea id="objective" name="objective" class="form-control">{{ $program->objective }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputName">Video Link <span style="color: #ff5252;">*</span></label>
                                            <input title="Please enter video link" type="text" id="videos" name="videos" value="{{ $program->video }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputName">Program Image</label>
                                            <input title="Please select an image" type="file" id="program_image" name="program_image" accept="image/*" class="form-control"><br>
                                            @if(!empty($program->program_image))
                                            <img src="{{ asset('/storage/'. $program->program_image) }}" alt="" srcset="" style="height: 100px; width:auto;">
                                            @endif
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
                                            <input type="text" id="body_area" name="body_area" class="form-control" value="{{ $program->body_area }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputName">Duration (<small>in weeks</small>) <span style="color: #ff5252;">*</span></label>
                                            <input type="number" id="duration" name="duration" class="form-control" value="{{ $program->duration }}" min="1" onchange="adjustSessionLayout(this.value)">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputName">Frequency (<small>Number of session per week</small>) <span style="color: #ff5252;">*</span></label>
                                            <input type="number" id="frequency" name="frequency" class="form-control" value="{{ $program->frequency }}" min="1">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputName">Level <span style="color: #ff5252;">*</span></label>
                                            <select name="level" id="level" class="form-control">
                                                <option value="">Select Level</option>
                                                @foreach ($programLevels as $level)
                                                <option value="{{ $level['id'] }}" @if($program['level_id']==$level['id'] ) selected @endif style="font-weight: bold;">{{ $level['level_title'] }}</option>
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
                                                <input type="radio" id="type_cardio" name="program_type" value="cardio"
                                                    @if(old('program_type', $program->program_type ?? 'cardio') == 'cardio') checked @endif>
                                                <label for="type_cardio">Cardio</label>
                                                <input type="radio" id="type_muscle" name="program_type" value="muscle"
                                                    @if(old('program_type', $program->program_type ?? '') == 'muscle') checked @endif>
                                                <label for="type_muscle">Muscle Strengthening</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4" id="cardio_dropdown" style="{{ (old('program_type', $program->program_type ?? 'cardio') == 'cardio') ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label for="inputDescription">Cardio Type <span style="color: #ff5252;">*</span></label>
                                            <select name="cardio_id" id="cardio_id" class="form-control">
                                                <option value="">Select Cardio</option>
                                                @foreach($cardioData as $cardio)
                                                <option value="{{ $cardio['id'] }}" @if($program['cardio_id']==$cardio['id']) selected @endif style="font-weight: bold;">{{ $cardio['title'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4" id="muscle_dropdown" style="{{ (old('program_type', $program->program_type ?? '') == 'muscle') ? '' : 'display:none;' }}"><div class="form-group">
                                            <label for="inputDescription">Muscle Strengthening Type <span style="color: #ff5252;">*</span></label>
                                            <select name="muscle_strength_id" id="muscle_strength_id" class="form-control">
                                                <option value="">Select Muscle Strengthening Type</option>
                                                @foreach($muscleData as $muscle)
                                                <option value="{{ $muscle['id'] }}" @if($program['muscle_strength_id']==$muscle['id']) selected @endif style="font-weight: bold;">{{ $muscle['title'] }}</option>
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
                                <div id="sessionContainer">
                                    <!-- Sessions will be dynamically populated here -->
                                </div>
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
                        <input type="submit" @if(!isset($program)) value="Save" @else value="Update" @endif class="btn btn-success float-right">
                    </div>
                </div>
            </form>
        </section>
        <!-- /.content -->
    </div>

    @section('scripts')
    <script src="{{ asset('adminAssets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('adminAssets/plugins/jquery-validation/additional-methods.min.js') }}"></script>

    <!-- <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script> -->
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

            $('#programUpdateForm').validate({
                rules: {
                    title: {
                        required: true,
                    },
                    category_id: {
                        required: true
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
                        accept: "This field only accept image file",
                        maxfilesize: "File size must be less than 2 MB."
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Global variables to store session data
        let allSessions = [];
        let modifiedSessions = {}; // Stores user-added sessions and their order
        let removedSessions = {};  // Stores user-removed sessions
        let existingSessions = @json($prevSession);
        console.log(existingSessions);
        // Fetch available sessions on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchAvailableSessions();
        });

        function fetchAvailableSessions() {
            fetch('/admin/get-session')
                .then(response => response.json())
                .then(data => {
                    allSessions = data;
                    // After fetching sessions, populate the layout
                    adjustSessionLayout({{ $program->duration }});
                })
                .catch(error => {
                    console.error('Error fetching sessions:', error);
                });
        }

        function adjustSessionLayout(duration) {
            const sessionContainer = document.getElementById('sessionContainer');
            const frequency = parseInt(document.getElementById('frequency').value);

            saveModifications(); // Store the current session state

            sessionContainer.innerHTML = ''; // Clear existing sessions

            for (let week = 1; week <= duration; week++) {
                const weekDiv = document.createElement('div');
                weekDiv.classList.add('card', 'card-secondary');
                weekDiv.innerHTML = `
                    <div class="card-header">
                        <h3 class="card-title">Session Semaine ${week}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul id="week-${week}-sessions" class="sortable-list" data-week="${week}"></ul>
                        <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addSessionToWeek(${week})">
                            Add Session to Week ${week}
                        </button>
                    </div>
                `;
                sessionContainer.appendChild(weekDiv);

                const weekKey = `Semaine ${week}`;
                const weekList = document.getElementById(`week-${week}-sessions`);
                let alreadyAddedSessions = new Set();
                let orderedSessions = modifiedSessions[week] || [];

                if (!orderedSessions.length && existingSessions[weekKey]) {
                    orderedSessions = existingSessions[weekKey].map(s => s.session_id);
                }

                // **Handle Frequency Reduction**: If more sessions exist than allowed, remove extra ones from the end
                if (orderedSessions.length > frequency) {
                    orderedSessions = orderedSessions.slice(0, frequency);
                }

                orderedSessions.forEach(sessionId => {
                    if (!removedSessions[week]?.includes(sessionId)) {
                        const sessionDetails = findSessionDetails(sessionId);
                        if (sessionDetails && !alreadyAddedSessions.has(sessionDetails.value)) {
                            addSessionToList(week, sessionDetails, false);
                            alreadyAddedSessions.add(sessionDetails.value);
                        }
                    }
                });

                new Sortable(weekList, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: () => saveModifications(),
                });
            }
        }


        function findSessionDetails(sessionId) {
            // Find session details from allSessions array
            return allSessions.find(session => session.value == sessionId);
        }
        function addSessionToWeek(week) {
            const weekList = document.getElementById(`week-${week}-sessions`);
            const frequency = parseInt(document.getElementById('frequency').value);

            if (weekList.children.length >= frequency) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Maximum Sessions Reached',
                    text: `You can only add ${frequency} sessions per week.`
                });
                return;
            }

// Example: run after dynamic select is added
            const dropdown = document.createElement('select');
            dropdown.classList.add('form-control','select2Duration');
            allSessions.forEach(session => {
                const option = document.createElement('option');
                option.value = session.value;
                option.textContent = session.label;
                dropdown.appendChild(option);
                $(document).on('focus', '.select2Duration', function () {
                    if (!$(this).hasClass("select2-hidden-accessible")) {
                        $(this).select2({
                            placeholder: "Search...",
                            dropdownParent: $('.swal2-modal'),
                            width: '100%',
                             minimumResultsForSearch: 0
                        });
                    }
                });
            });

            Swal.fire({
                title: 'Select Session',
                html: dropdown,
                showCancelButton: true,
                confirmButtonText: 'Add Session',
                didOpen: () => {
                    $('.select2Duration').select2({
                        placeholder: "Search...",
                        width: '100%',
                        dropdownParent: $('.swal2-modal'), // attach to Swal modal
                        minimumResultsForSearch: 0
                    });
                },

                preConfirm: () => {
                    const selectedSession = allSessions.find(s => s.value == dropdown.value);
                    if (selectedSession) {
                        addSessionToList(week, selectedSession);
                    }
                }
            });
        }

        function saveModifications() {
            modifiedSessions = {};
            removedSessions = {};

            document.querySelectorAll('.sortable-list').forEach(list => {
                const week = list.dataset.week;
                modifiedSessions[week] = Array.from(list.children).map(item => item.dataset.sessionId);
            });
        }

        function addSessionToList(week, sessionDetails, save = true) {
            const weekList = document.getElementById(`week-${week}-sessions`);

            if (!weekList || weekList.querySelector(`[data-session-id="${sessionDetails.value}"]`)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Session Already Added',
                    text: 'This session is already added to the list.'
                });
                return; // Prevent duplicate sessions
            }

            const listItem = document.createElement('li');
            listItem.dataset.sessionId = sessionDetails.value;
            listItem.innerHTML = `
                <span class="session-title">${sessionDetails.label}</span>
                <div class="session-actions">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSession(this, ${week})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input type="hidden" name="selectedOptions[${week - 1}][]" value="${sessionDetails.value}">
            `;

            weekList.appendChild(listItem);

            // Save session to modifiedSessions if manually added
            if (save) {
                if (!modifiedSessions[week]) modifiedSessions[week] = [];
                modifiedSessions[week].push(sessionDetails.value);
            }
        }

        function removeSession(button, week) {
            Swal.fire({
                title: 'Remove Session',
                text: 'Are you sure you want to remove this session?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const listItem = button.closest('li');
                    const sessionId = listItem.dataset.sessionId;

                    // Track removed sessions
                    if (!removedSessions[week]) removedSessions[week] = [];
                    removedSessions[week].push(sessionId);

                    listItem.remove();
                    saveModifications(); // Update stored sessions
                }
            });
        }

        let previousFrequency = parseInt(document.getElementById('frequency').value); // Store the last frequency

        document.getElementById('frequency').addEventListener('change', function () {
            const newFrequency = parseInt(this.value);

            if (newFrequency < previousFrequency) {
                Swal.fire({
                    title: 'Confirm Frequency Change',
                    text: 'Reducing frequency will remove extra sessions from each week. You will need to add them again if necessary.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, decrease it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        previousFrequency = newFrequency; // Update stored frequency
                        adjustSessionLayout(document.getElementById('duration').value);
                    } else {
                        this.value = previousFrequency; // Revert frequency change
                    }
                });
            } else {
                previousFrequency = newFrequency; // Update stored frequency if increasing
                adjustSessionLayout(document.getElementById('duration').value);
            }
        });

    </script>
    <script>

        $(document).ready(function() {
            // On load, set dropdown visibility based on selected program type
            var selectedType = $('input[name="program_type"]:checked').val();
            if (selectedType === 'cardio') {
                $('#cardio_dropdown').show();
                $('#muscle_dropdown').hide();
            } else if (selectedType === 'muscle') {
                $('#cardio_dropdown').hide();
                $('#muscle_dropdown').show();
            } else {
                $('#cardio_dropdown').hide();
                $('#muscle_dropdown').hide();
            }

            // On change, toggle dropdowns
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


        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    @endsection
</x-admin-layout>
