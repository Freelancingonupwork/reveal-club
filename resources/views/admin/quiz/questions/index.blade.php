<?php

use App\Models\Cardio;
use App\Models\MuscleStrength;

$cardioData = Cardio::get()->toArray();
$muscleStrengtheningData = MuscleStrength::get()->toArray();

?>
<x-admin-layout title="Questions">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.css">
    <style>
        .dark-mode input:-webkit-autofill {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #23232329;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Quiz</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Quiz</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
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
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Questions</h3>
                                <div class="card-tools">
                                    <a type="button" @if(!empty($quizzes))href="{{ route('create-quiz') }}" @else data-toggle="modal" data-target=".bd-example-modal-xl" @endif class="btn btn-secondary">
                                        <i class="nav-icon fas fa-plus"></i>
                                        Add Question
                                    </a>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4">

                                    </div>
                                    <div class="col-lg-4">
                                        <div class="quiz-group">
                                            <div class="form-group">
                                                <select class="form-control" name="quiz_group_id" id="quiz_group_id">
                                                    <option value="All">All</option>
                                                    @foreach ($quizGroupData as $quizGroup)
                                                    <option value="{{ $quizGroup['id'] }}">{{ $quizGroup['title'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table id="customTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Title</th>
                                            <th>Gender</th>
                                            <th>Question Type</th>
                                            <th>Question For</th>
                                            <th>Quiz Group</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="reorder_position">
                                        @foreach($quizzes as $key => $quiz)
                                        <tr class="tableRow" data-id="{{ $quiz['id'] }}">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $quiz['ques_title'] }}</td>
                                            <td>{{ ucfirst($quiz['ques_for_gender']) }}</td>
                                            <td>
                                                <?php
                                                    if ($quiz['ques_type'] === 0) {
                                                        $quesType = "Use In Profile";
                                                    } elseif ($quiz['ques_type'] === 1) {
                                                        $quesType = "Use In Program";
                                                    } else {
                                                        $quesType = "None";
                                                    }
                                                ?>
                                                {{ $quesType }}
                                            </td>
                                            <td>{{ $quiz['ques_for'] }}</td>
                                            <td>{{ $quiz['quiz_group']['title'] }}</td>
                                            <td>@if(!empty($quiz['ques_image']))
                                                <img src="{{ url('/storage/'.$quiz['ques_image']) }}" class="img-circle elevation-2" alt="Quiz Image" style="width:150px; height: 150px;">
                                                @else
                                                <img src="{{ asset('adminAssets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="Quiz Image" style="width:150px; height: 150px;">
                                                @endif
                                            </td>
                                            <td>
                                                @if($quiz['is_active'] == 1)
                                                    <span class="badge badge-success">Enabled</span>
                                                @else
                                                    <span class="badge badge-warning">Disabled</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/update-quiz/'.$quiz['slug'] .'/'. $quiz['id']) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                                <!-- <a href="{{ url('admin/delete/'.$quiz['slug'] .'/'. $quiz['id']) }}" class="btn btn-danger"><i class="fas fa-trash"></i></a> -->
                                                <a href="javascript:void(0);" class="btn btn-danger confirmDelete" module="quiz" slug="{{ $quiz['slug'] }}" moduleId="{{ $quiz['id'] }}" message="Question Deleted Successfully"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Title</th>
                                            <th>Gender</th>
                                            <th>Question Type</th>
                                            <th>Question For</th>
                                            <th>Quiz Group</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->

            <!-- Modal -->
            <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Add a Question for Gender</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="quickForm" action="{{ url('admin/create-quiz') }}" method="post" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card card-primary">
                                            <div class="card-header">
                                                <h3 class="card-title">Questions</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label for="title">Title <span style="color: #ff5252;">*</span></label>
                                                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label for="quiz_group_id">Quiz Group <span style="color: #ff5252;">*</span></label>
                                                            <select class="form-control" name="quiz_group_id" id="quiz_group_id">
                                                                <option value="">Select Quiz Group</option>
                                                                @foreach ($quizGroups as $quizGroup)
                                                                <option value="{{ $quizGroup['id'] }}">{{ $quizGroup['title'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group row">
                                                            <div class="col-lg-6">
                                                                <label for="isQuesImage">Do you want to add Image for Question ?</label>
                                                            </div>
                                                            <div class="col-lg-1">
                                                                <input type="checkbox" id="isQuesImage" name="isQuesImage" style="width: 20px; height:20px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 questionImage">
                                                        <div class="form-group">
                                                            <label for="ques_image">Image</label>
                                                            <input type="file" class="form-control" id="ques_image" name="ques_image" accept="image/*">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="quesDescription">Description <span style="color: #ff5252;">*</span></label>
                                                    <textarea class="form-control" id="quesDescription" name="quesDescription"></textarea>
                                                    <input type="hidden" id="all" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="all" checked>&nbsp; All
                                                </div>

                                                <div class="card card-primary">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Question Type</h3>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-lg-3">
                                                                <input type="radio" id="profile" style="width: 20px; height:20px; margin-top: 5px;" name="ques_type" value="0" checked>&nbsp; Use in profile &nbsp;
                                                            </div>
                                                        </div>

                                                        <div class="card card-primary mt-2 profile">
                                                            <div class="card-header">
                                                                <h3 class="card-title">Use In Profile</h3>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-lg-3">
                                                                        <input type="radio" id="forUser" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="user" checked>&nbsp; User &nbsp;
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row transitionDiv">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <!-- <input type="checkbox" id="have_transition" name="have_transition" style="width: 20px; height:20px;" onchange="toggleTransitionLogic()"> -->
                                                            <input type="checkbox" id="have_transition" name="have_transition" style="width: 20px; height:20px;">
                                                            <label for="have_transition"> Have Transition ?</label>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 transLogic">
                                                        <label for="trans_logic">Does transition have logic?</label>
                                                        <div class="row mb">
                                                            <div class="col-lg-6 transLogicYes">
                                                                <!-- <input type="radio" name="trans_logic" id="trans_logic_yes" value="Yes" style="width: 20px; height:20px;" onchange="toggleAnswerType()"> Yes -->
                                                                <input type="radio" name="trans_logic" id="trans_logic_yes" value="Yes" style="width: 20px; height:20px;"> Yes
                                                            </div>
                                                            <div class="col-lg-6 transLogicNo">
                                                                <!-- <input type="radio" name="trans_logic" id="trans_logic_no" value="No" style="width: 20px; height:20px;" onchange="toggleAnswerType()"> No -->
                                                                <input type="radio" name="trans_logic" id="trans_logic_no" value="No" style="width: 20px; height:20px;"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card card-primary">
                                                    <div class="card-header">
                                                        <h3 class="card-title">
                                                            Answer
                                                        </h3>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label for="answer_type">Answer Type</label>
                                                                </div>

                                                                <div class="col-lg-12">
                                                                    <div class="row">
                                                                        <div class="col-lg-3 inputRadio">
                                                                            <input type="radio" id="inputType" style="width: 20px; height:20px; margin-top: 5px;" name="answer_type" value="userInput">&nbsp; Input
                                                                            &nbsp;
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card card-primary mt-2 input">
                                                            <div class="card-header">
                                                                <h3 class="card-title">
                                                                    Input Type Answers
                                                                </h3>
                                                            </div>
                                                            <div class="card-body">
                                                                <!-- <label for="answer">Add Answers</label> -->
                                                                <div class="row">
                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <label for="userQues">User Question For</label>
                                                                            <select title="Please select an option from dropdown." name="userQues" id="userQues" class="form-control" required>
                                                                                <option value="gender" selected> Gender</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <label for="answer">Label</label>
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter Label you want to take input from user." id="answer" name="answer" class="form-control" placeholder="Enter label you want to take input from user" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <label for="isNumericAnswer">Accept Numeric Value</label>
                                                                        <div class="form-group">
                                                                            <input type="checkbox" title="Please select if you want answer in numeric value from user." id="isNumericAnswer" name="isNumericAnswer" class="form-control" value="yes" style="width: 20px; height:20px;">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-4 commonTransition">
                                                            <div class="form-group">
                                                                <label for="common_transition_id">Select Transition</label>
                                                                <select name="common_transition_id[]" id="common_transition_id" class="form-control">
                                                                    <option value="">Select Transition</option>
                                                                    @foreach ($transitions as $transition)
                                                                    <option value="{{ $transition['id'] }}">{{ $transition['title'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <input type="checkbox" id="sales_page" name="sales_page" style="width: 20px; height:20px;">
                                                            <label for="sales_page"> Sales Page</label>
                                                        </div>
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
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
    @section('scripts')

    <script>
        $(document).ready(function() {
            var table = $('#customTable').DataTable();

            $('#quiz_group_id').change(function() {
                var selectedGroup = $(this).val();

                $.ajax({
                    url: '/admin/get-group-wise-quiz',
                    data: {
                        quiz_group_id: selectedGroup
                    },
                    success: function(data) {
                        table.clear();
                        if (data.length === 0) {
                            table.draw();
                        } else {
                            data.forEach(function(item, index) {
                                var image = item.ques_image ? `<img src="/storage/${item.ques_image}" class="img-circle elevation-2" alt="Quiz Image" style="width:150px; height: 150px;">` : `<img src="{{ asset('adminAssets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="Quiz Image" style="width:150px; height: 150px;">`;
                                // var actions = `
                                //     <a href="/admin/update-quiz/${item.slug}/${item.id}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                //     <a href="javascript:void(0);" class="btn btn-danger confirmDelete" module="quiz" slug="${item.slug}" moduleId="${item.id}" message="Question Deleted Successfully"><i class="fas fa-trash"></i></a>
                                // `;
                                var actions = `
                                    
                                    <a href="javascript:void(0);" class="btn btn-danger confirmDelete" module="quiz" slug="${item.slug}" moduleId="${item.id}" message="Question Deleted Successfully"><i class="fas fa-trash"></i></a>
                                `;
                                table.row.add([
                                    index + 1,
                                    item.ques_title,
                                    item.slug,
                                    item.quiz_group.title,
                                    image,
                                    actions
                                ]).draw(false);
                            });
                        }
                    }
                })
            })
        })
    </script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(document).ready(function() {
            $("#reorder_position").sortable({
                items: "tr",
                cursor: 'move',
                opacity: 0.6,
                update: function() {
                    updateQuizGroupPosition();
                }
            });

            function updateQuizGroupPosition() {
                var orderPosition = [];
                var token = $('meta[name="csrf-token"]').attr('content');
                var url = "/admin/set-quiz-position"

                $('tr.tableRow').each(function(index, element) {
                    orderPosition.push({
                        id: $(this).attr('data-id'),
                        position: index + 1
                    });
                });

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "/admin/set-quiz-position",
                    data: {
                        order: orderPosition,
                        _token: token
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            alert(response)
                            console.log(response);
                        } else {
                            alert("error");
                            console.log(response);
                        }
                    }
                });
            }
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        @if(Session::has('success'))
        toastr.options = {
            "closeButton": true,
            "progressBar": true
        }
        toastr.success("{{ session('success') }}");
        @endif
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"></script>

    <script>
        $(document).ready(function() {
            $(function() {
                // Custom validation method to check file size
                $.validator.addMethod("maxfilesize", function(value, element, param) {
                    if (element.files.length > 0) {
                        // Check if file size exceeds the limit
                        return element.files[0].size <= param;
                    }
                    return true;
                }, "File size must be less than 2 MB.");
                $('#quickForm').validate({
                    rules: {
                        title: {
                            required: true,
                        },
                        quesDescription: {
                            required: true
                        },
                        quiz_group_id: {
                            required: true
                        },
                        ques_image: {
                            accept: "image/*",
                            extension: "jpg,jpeg,png",
                            maxfilesize: 2097152
                        }
                    },
                    messages: {
                        title: {
                            required: "Please enter recipe title",
                        },
                        quesDescription: {
                            required: "Please write somethin in short about this question"
                        },
                        quiz_group_id: {
                            required: "Please select quiz group"
                        },
                        ques_image: {
                            accept: "This field only accept image file",
                            extension: "File format should be jpg, jpeg, png",
                            maxfilesize: "File size must be less than 2 MB." // Message for file size validation
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
        $(document).ready(function() {
            $(window).on("load", function() {
                $("#profile").prop("checked", true).trigger("click");
                $("#basic").prop("checked", true).trigger("click");
                $("#singleType").prop("checked", true).trigger("click");
                $(".single, .withoutTransition").show();
            });

            // Question Image
            $(window).on("load", function() {
                var isQuesImage = $('input[name="isQuesImage"]:checked').val();
                if (isQuesImage === 'on') {
                    $('.questionImage').show();
                } else {
                    $('.questionImage').hide();
                }
            })
            $('#isQuesImage').change(function() {
                if (!this.checked)
                    $('.questionImage').hide();
                else
                    $('.questionImage').show();
            });

            $(".program").hide();
            $(".commonTransition, .transLogic, .inputRadio, .another_ques").hide();
            $(".withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multiple, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel, .multipelAnsProgramPoints, .input, .info, .answerFormat, .imageAnswerRepeater").hide();

            $("input[name$='ques_type']").click(function() {
                var quesType = $(this).val();
                if (quesType === '0') {
                    $(".profile, .answerFormat").show();
                    $("#basic").prop("checked", true)
                    $(".transitionDiv, .withoutTransition").show();
                    $(".program, .another_ques, .commonTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel, .multipleAnswersWithoutPoints").hide();
                } else if (quesType === '1') {

                    $(".program, .single, .singleRadio, .multipleRadio, .singleAnsForCardio").show();
                    $(".answerFormat").hide();

                    const answerFormatRadios = document.querySelectorAll('input[name="answer_format"]');
                    if (document.querySelector('input[name="ques_type"]:checked')) {
                        // Deselect all ques_for radio buttons
                        answerFormatRadios.forEach(radio => {
                            radio.checked = false;
                        });
                    }

                    $("#cardio").prop("checked", true);
                    $("#singleType").prop("checked", true).trigger("click");
                    $(".profile, .transitionDiv, .another_ques, .commonTransition, .inputType, .input, .inputRadio").hide();
                    document.getElementById("another_ques").checked = false;
                    document.getElementById("have_transition").checked = false;
                    document.getElementById("trans_logic_yes").checked = false;
                    document.getElementById("trans_logic_no").checked = false;
                } else {
                    const quesForRadios = document.querySelectorAll('input[name="ques_for"]');
                    if (document.querySelector('input[name="ques_type"]:checked')) {
                        // Deselect all ques_for radio buttons
                        quesForRadios.forEach(radio => {
                            radio.checked = false;
                        });
                    }

                    const answerFormatRadios = document.querySelectorAll('input[name="answer_format"]');
                    if (document.querySelector('input[name="ques_type"]:checked')) {
                        // Deselect all ques_for radio buttons
                        answerFormatRadios.forEach(radio => {
                            radio.checked = false;
                        });
                    }
                    $("#singleType").prop("checked", true).trigger("click");
                    $(".transitionDiv, .another_ques, .singleRadio, .multipleRadio").show();
                    $(".profile, .program, .commonTransition, .inputType, .input, .inputRadio, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel, .answerFormat").hide();
                }

                var quesType = $("input[name$='ques_type']:checked").val();
                var quesFor = $("input[name$='ques_for']:checked").val();
                var transLogic = $("input[name$='trans_logic']:checked").val();
                var answerType = $("input[name$='answer_type']:checked").val();
                var answerFormat = $("input[name$='answer_format']:checked").val();

                $('.selectQues').hide();
                $('#another_ques').change(function() {
                    if (!this.checked)
                        //  ^
                        $('.selectQues').hide();
                    else
                        $('.selectQues').show();
                });

                if (quesType === '0' || quesType === '2') {
                    // Use Answer in other question
                    $('input[name$="another_ques"]').prop("checked", false);

                    $("input[name$='ques_for']").change(function() {
                        const transLogicRadios = document.querySelectorAll('input[name="trans_logic"]');
                        if ($('#have_transition').is(':checked')) {
                            $(".transLogicYes").show();
                            // Deselect all ques_for radio buttons
                            transLogicRadios.forEach(radio => {
                                radio.checked = false;
                            });
                        }
                        if ($(this).val() === 'basic') {
                            $("#singleType").prop("checked", true).trigger("click");
                        }
                        toggleTransitionLogic();
                    });
                    toggleTransitionLogic();
                } else {
                    handleAnswerType();
                }

                $("#imageAnswer").change(function() {
                    toggleTransitionLogic();
                })

                $("#have_transition").change(function() {
                    const transLogicRadios = document.querySelectorAll('input[name="trans_logic"]');
                    if ($('#have_transition').is(':checked')) {
                        // Deselect all ques_for radio buttons
                        transLogicRadios.forEach(radio => {
                            radio.checked = false;
                        });
                    }
                    toggleTransitionLogic();
                });

                $("input[name$='answer_type']").change(function() {
                    toggleTransitionLogic();
                });

                $("input[name$='trans_logic']").change(function() {
                    toggleTransitionLogic();
                });
            });

            function toggleTransitionLogic() {
                var haveTransition = $('#have_transition').is(':checked');
                var quesFor = $("input[name$='ques_for']:checked").val();
                if (haveTransition) {
                    $(".transLogic, .commonTransition").show();
                    if (quesFor == 'user' || quesFor == 'steps_goal') {
                        $(".transLogicYes").hide();
                        document.getElementById("trans_logic_no").checked = true;
                    }
                    handleAnswerType();
                } else {
                    $(".transLogic, .commonTransition").hide();
                    handleAnswerType();
                }
            }

            function handleAnswerType(transLogic) {
                var quesType = $("input[name$='ques_type']:checked").val();
                var quesFor = $("input[name$='ques_for']:checked").val();
                var transLogic = $("input[name$='trans_logic']:checked").val();
                var answerType = $("input[name$='answer_type']:checked").val();
                var haveTransition = $('#have_transition').is(':checked');
                var answerFormat = $("input[name$='answer_format']:checked").val();

                console.log("Question Type :" + quesType)
                console.log("Question For :" + quesFor)
                console.log("Transition Logic :" + transLogic)
                console.log("Answer Type :" + answerType)
                console.log("Have Transition :" + haveTransition)
                console.log("Answer Format :" + answerFormat)

                if (quesType == 0) {
                    if (quesFor == 'user') {
                        $("#inputType").prop("checked", true);
                        $(".inputRadio, .input").show();
                        $(".singleRadio, .single, .multiple, .multipleRadio, .multipleAnswersWithoutPoints, .multipleAnsForMuscle, .multipleAnsForCardio, .multipleAnsForLevel, .multipleAnswersWithoutPoints, .withTransition, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .answerFormat, .imageAnswerRepeater").hide();
                    }
                    if (quesFor == 'steps_goal') {
                        $("#singleType").prop("checked", true);
                        $(".singleRadio, .single, .withoutTransition, .info, .textAnswerRepeater").show();
                        $(".multiple, .multipleRadio, .multipleAnswersWithoutPoints, .multipleAnsForMuscle, .multipleAnsForCardio, .multipleAnsForLevel, .multipleAnswersWithoutPoints, .inputRadio, .withTransition, .input, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .answerFormat, .imageAnswerRepeater").hide();
                    }
                    if (((quesFor === 'basic')) && transLogic == 'Yes') {
                        if (answerFormat === 'image') {
                            $("#singleType").prop("checked", true)
                            $(".singleRadio, .single, .withTransition, .imageAnswerRepeater").show();
                            $(".multipleRadio, .inputRadio, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .textAnswerRepeater, .commonTransition").hide();
                        } else {
                            $("#singleType").prop("checked", true)
                            $(".singleRadio, .single, .withTransition, .textAnswerRepeater").show();
                            $(".multipleRadio, .inputRadio, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater, .commonTransition").hide();
                        }
                    }
                    if (((quesFor === 'basic')) && ((transLogic == undefined) || (transLogic === 'No')) && answerType === 'single') {
                        if (answerFormat == 'image') {
                            $("#singleType").prop("checked", true)
                            $(".singleRadio, .single, .multipleRadio, .withoutTransition, .answerFormat, .imageAnswerRepeater").show();
                            $(".multiple, .multipleAnswersWithoutPoints, .inputRadio, .input, .withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .textAnswerRepeater").hide();
                        } else {
                            $("#singleType").prop("checked", true)
                            $(".singleRadio, .single, .multipleRadio, .withoutTransition, .answerFormat, .textAnswerRepeater").show();
                            $(".multiple, .multipleAnswersWithoutPoints, .inputRadio, .input, .withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater").hide();
                        }
                    }
                    if (((quesFor === 'basic')) && ((transLogic == undefined) || (transLogic === 'No')) && answerType === 'multiple') {
                        if (answerFormat == 'image') {
                            $(".multiple, .multipleAnswersWithoutPoints, .imageAnswerRepeater").show();
                            $(".single, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .textAnswerRepeater").hide();
                        } else {
                            $(".multiple, .multipleAnswersWithoutPoints, .textAnswerRepeater").show();
                            $(".single, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater").hide();
                        }
                    }
                }

                if (quesType == 1 && answerType === 'single') {
                    $(".info").hide();
                    if (quesFor === 'cardio') {
                        $(".single, .singleRadio, .multipleRadio, .singleAnsForCardio").show();
                        $(".multiple, .inputRadio, .input, .withoutTransition, .singleAnsForMuscle, .singleAnsForLevel, .withTransition").hide();
                    }

                    if (quesFor === 'musclestrengthening') {
                        $(".single, .singleRadio, .multipleRadio, .singleAnsForMuscle").show();
                        $(".multiple, .inputRadio, .input, .withoutTransition, .singleAnsForCardio, .singleAnsForLevel, .withTransition").hide();
                    }

                    if (quesFor === 'level') {
                        $(".single, .singleRadio, .multipleRadio, .singleAnsForLevel").show();
                        $(".multiple, .inputRadio, .input, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .withTransition").hide();
                    }
                }

                if (quesType == 1 && answerType === 'multiple') {
                    $(".info").hide();
                    if (quesFor === 'cardio') {
                        $(".multiple, .singleRadio, .multipleRadio, .multipleAnsForCardio").show();
                        $(".single, .inputRadio, .input, .withoutTransition, .withTransition, .multipleAnsForMuscle, .multipleAnsForLevel, .multipleAnswersWithoutPoints, .singleAnsForMuscle, .singleAnsForLevel").hide();
                    }

                    if (quesFor === 'musclestrengthening') {
                        $(".multiple, .singleRadio, .multipleRadio, .multipleAnsForMuscle").show();
                        $(".single, .inputRadio, .input, .withoutTransition, .withTransition, .multipleAnsForCardio, .multipleAnsForLevel, .multipleAnswersWithoutPoints, .singleAnsForCardio, .singleAnsForLevel").hide();
                    }

                    if (quesFor === 'level') {
                        $(".multiple, .singleRadio, .multipleRadio, .multipleAnsForLevel").show();
                        $(".single, .inputRadio, .input, .withoutTransition, .withTransition, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnswersWithoutPoints, .singleAnsForCardio, .singleAnsForMuscle").hide();
                    }
                }

                if (quesType == 2) {
                    $(".info").hide();
                    if (transLogic === 'Yes') {
                        $("#singleType").prop("checked", true).trigger("click");
                        $(".single, .withTransition").show();
                        $(".multiple, .multipleRadio, .commonTransition, .withoutTransition").hide();
                    }

                    if (answerType === 'single' && (transLogic === undefined || transLogic === 'No')) {
                        $(".single, .multipleRadio, .withoutTransition").show();
                        $(".withTransition, .multiple, .multipleAnswersWithoutPoints").hide();
                    }

                    if (answerType === 'multiple' && (transLogic === undefined || transLogic === 'No')) {
                        $(".multiple, .multipleAnswersWithoutPoints").show();
                        $(".single, .withoutTransition, .withTransition").hide();
                    }
                }
            }
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

        $('.repeaterOne').repeater({
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