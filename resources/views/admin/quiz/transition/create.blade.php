<x-admin-layout title="Create Transition">
    @section('styles')
    <style>
        .dark-mode input:-webkit-autofill {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #23232329;
        }

        .notranslate {
            display: block;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-lg-6">
                        <h1>Quiz</h1>
                    </div>
                    <div class="col-lg-6">
                        <ol class="breadcrumb float-lg-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item">Questions</li>
                            <li class="breadcrumb-item active">Transition</li>
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
            <form id="transitionForm" action="{{ url('admin/create-transition') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Transition</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4" style="display: flex; justify-content: center; align-items: center;"> 
                                        <div class="form-group row">
                                            <div class="col-lg-8">
                                                <label for="color">Select Primary color for this Transition:</label>
                                            </div>
                                            <div class="col-lg-2">
                                                <input type="color" id="color" name="color" value="{{ old('color') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <div class="col-lg-6">
                                                <label for="is_trans_image">Do you want to add Backgound Image for this Transition Page?</label>
                                            </div>
                                            <div class="col-lg-1">
                                                <input type="checkbox" id="is_trans_image" name="is_trans_image" style="width: 20px; height:20px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 transitionImage">
                                        <div class="form-group">
                                            <label for="transition_image">Background Image</label>
                                            <input type="file" class="form-control" id="transition_image" name="transition_image" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group row" id="weight-id">
                                            <div class="col-lg-6">
                                                <label for="is_chart_trans">Do this transition contain Chart information for weight?</label>
                                            </div>
                                            <div class="col-lg-1">
                                                <input type="checkbox" id="is_chart_trans" name="is_chart_trans" style="width: 20px; height:20px;" onchange="toggleChartInfo()">
                                            </div>
                                        </div>

                                        <div class="form-group chartInfoLabel" style="display: none;">
                                            <label for="chartOptions">Select Chart Option:</label>
                                            <select id="chartOptions" class="form-control" name="chartOptions">
                                                <option value="">Select Chart Option</option>
                                                <option value="Graph-1-5kg-Max">Graph-1-5kg-Max</option>
                                                <option value="Graph-1-10kg-Max">Graph-1-10kg-Max</option>
                                                <option value="Graph-1-10kg-Plus">Graph-1-10kg-Plus</option>
                                                <option value="Graph-2-5kg-Max">Graph-2-5kg-Max</option>
                                                <option value="Graph-2-10kg-Max">Graph-2-10kg-Max</option>
                                                <option value="Graph-2-10kg-Plus">Graph-2-10kg-Plus</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 chartInfoLabel" id="chartInfoLabel" style="display: none;">
                                        <div class="form-group">
                                            <label>Place the following placeholders to keep them in chart:</label>
                                            <ul style="list-style-type: none; padding-left: 0;">
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">Current Weight:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{CurrentWeight}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">Current Date:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{CurrentDate}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">Goal Weight:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{GoalWeight}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">Five Kg Loss Date:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{FiveKgLossDate}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">Ten Kg Loss Date:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{TenKgLossDate}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">Goal Achievement Date:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{GoalAchieveDate}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">Weight Difference:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{ActualWeightDiffrence}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">New Five Kg Loss Date:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{NewFiveKgLossDate}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">New Ten Kg Loss Date:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{NewTenKgLossDate}</strong>
                                                </li>
                                                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="flex: 1;">New Goal Achievement Date:</span>
                                                    <strong style="margin-left: 10px; text-align: right;">{NewGoalAchieveDate}</strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" id="trans_description_test">
                                    <label for="trans_description">Description</label>
                                    <textarea class="form-control" id="trans_description" name="trans_description"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="trans_description">Button Label</label>
                                    <input type="text" class="form-control" id="button_label" name="button_label" value="{{ old('button_label') }}">
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="is_term_and_cond">Is this transition for <strong>Term & Condition</strong> ?</label>
                                            </div>
                                            <div class="col-lg-1">
                                                <input type="checkbox" id="is_term_and_cond" name="is_term_and_cond" style="width: 20px; height:20px;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="is_paywall">Is Pre-PayWall?</label>
                                            </div>
                                            <div class="col-lg-1">
                                                <input type="checkbox" id="is_paywall" name="is_paywall" style="width: 20px; height:20px;" value="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="is_animation">Is this transition have <strong>Animated Button</strong>?</label>
                                            </div>
                                            <div class="col-lg-1">
                                                <input type="checkbox" id="is_animation" name="is_animation" style="width: 20px; height:20px;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 isAnimation">
                                        <div class="form-group">
                                            <label for="animation_text">Animation Text</label>
                                            <input type="text" name="animation_text" id="animation_text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="is_amplitude_track">Want to Add Amplitude Tracking Word??</label>
                                            </div>
                                            <div class="col-lg-1">
                                                <input type="checkbox" id="is_amplitude_track" name="is_amplitude_track" style="width: 20px; height:20px;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 isAmplitude">
                                        <div class="form-group">
                                            <label for="amplitude_tracking_word">Enter Amplitude Tracking Word</label>
                                            <input type="text" name="amplitude_tracking_word" id="amplitude_tracking_word" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-5">
                                        <label for="status">Is Active?</label>
                                    </div>
                                    <div class="col-lg-1">
                                        <input type="checkbox" id="status" name="status" style="width: 20px; height:20px;" value="1" {{ old('status') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
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
        $(document).ready(function() {
            $(function() {
                $('#transitionForm').validate({
                    rules: {
                        title: {
                            required: true,
                        },
                        trans_description: {
                            required: true
                        },
                        button_label: {
                            required: true
                        }
                    },
                    messages: {
                        title: {
                            required: "Please enter transition title",
                        },
                        trans_description: {
                            required: "Please write something in short about this transition"
                        },
                        button_label: {
                            required: "Please enter text for button"
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
            });
        });
    </script>
    <script>
           tinymce.init({
            selector: '#trans_description',
            plugins: 'link image code media',
            toolbar: 'undo redo | styleselect | bold italic | link image | code',
            valid_elements: '*[*]', // Allow all elements
            extended_valid_elements: 'script[src|async|defer],style',
            images_upload_handler: function (blobInfo, success, failure) {
                // Create a FileReader to read the uploaded image
                const reader = new FileReader();
                reader.onload = function(event) {
                    const base64Image = event.target.result;
                    // Pass the base64 string to TinyMCE
                    success(base64Image);
                };
                reader.readAsDataURL(blobInfo.blob()); // Convert the image to a base64 string
            },
            setup: function(editor) {
                editor.on('init', function() {
                    editor.setContent('<p>Start typing your content here...</p>');
                });
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // Question Image
            $(".transitionImage").hide();
            $('#is_trans_image').change(function() {
                if (!this.checked)
                    //  ^
                    $('.transitionImage').hide();
                else
                    $('.transitionImage').show();
            });

            // Animated Button
            $(".isAnimation").hide();
            $('#is_animation').change(function() {
                if (!this.checked)
                    $('.isAnimation').hide();
                else
                    $('.isAnimation').show();
            });

            // Amplitude Button
            $(".isAmplitude").hide();
            $('#is_amplitude_track').change(function() {
                if (!this.checked)
                    $('.isAmplitude').hide();
                else
                    $('.isAmplitude').show();
            });

            //Graph 
            $(".chartInfoLabel").hide();
            $('#is_chart_trans').change(function() {
                if (!this.checked)
                    $('.chartInfoLabel').hide();
                else
                    $('.chartInfoLabel').show();
            });
        });
    </script>

    <script>
        $('.repeater').repeater({
            initEmpty: false,
            isFirstItemUndeletable: true,
            show: function() {
                $(this).slideDown();
            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                }
            }
        });
    </script>

    @endsection
</x-admin-layout>