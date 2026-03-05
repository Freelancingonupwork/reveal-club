<?php

use App\Models\Cardio;
use App\Models\MuscleStrength;

$cardioData = Cardio::get()->toArray();
$muscleStrengtheningData = MuscleStrength::get()->toArray();

?>
<x-admin-layout title="Create Quiz">
    @section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.css">
    <style>
        .dark-mode input:-webkit-autofill {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #23232329;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #000000 !important;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: #343a40;
            border: 1px solid #6c757d;
        }

        .dark-mode .select2-purple .select2-container--default .select2-search--inline .select2-search__field:focus {
            border: none;
        }

        .select2 {
            background-color: #343a40;
        }

        .select2-container--default .select2-selection--single {
            background-color: #343a40;
            border: 1px solid #6c757d;
            border-radius: 4px;
            height: 37px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff;
            line-height: 28px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #888 transparent transparent transparent;
            border-style: solid;
            border-width: 6px 4px 0px 5px;
            height: 0px;
            left: 50%;
            margin-left: -8px;
            margin-top: 4px;
            position: absolute;
            top: 50%;
            width: 10px;
        }

        .select2-selection__rendered {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        .select2-selection {
            min-width: 375px;
            width: fit-content !important;
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
                            <li class="breadcrumb-item">Quiz</li>
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

            @if (Session::has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
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
                                    <label for="quesDescription">Description</label>
                                    <textarea class="form-control" id="quesDescription" name="quesDescription"></textarea>
                                </div>

                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">This Question is for </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <input type="radio" id="male" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="male">&nbsp; Male &nbsp;
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="radio" id="female" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="female">&nbsp; Female &nbsp;
                                            </div>
                                            <!-- <div class="col-lg-3">
                                                <input type="radio" id="transgender" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="transgender">&nbsp; Transgender
                                            </div> -->
                                            <div class="col-lg-3">
                                                <input type="radio" id="all" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="all" checked>&nbsp; All
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Question Type</h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <input type="radio" id="profile" style="width: 20px; height:20px; margin-top: 5px;" name="ques_type" value="0">&nbsp; Use in profile &nbsp;
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="radio" id="use_in_program" style="width: 20px; height:20px; margin-top: 5px;" name="ques_type" value="1">&nbsp; Use in program &nbsp;
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="radio" id="none" style="width: 20px; height:20px; margin-top: 5px;" name="ques_type" value="2">&nbsp; None
                                            </div>
                                        </div>

                                        <div class="card card-primary mt-2 profile">
                                            <div class="card-header">
                                                <h3 class="card-title">Use In Profile</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-2">
                                                        <input type="radio" id="basic" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="basic">&nbsp; Basic &nbsp;
                                                    </div>
                                                    <!-- <div class="col-lg-3">
                                                        <input type="radio" id="marketing" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="marketing">&nbsp; Marketing &nbsp;
                                                    </div> -->
                                                    <div class="col-lg-2">
                                                        <input type="radio" id="forUser" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="user">&nbsp; User &nbsp;
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <input type="radio" id="stepsGoal" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="steps_goal">&nbsp; Step Goal
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <input type="radio" id="activityLevel" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="activity_level">&nbsp; Activity Level
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <input type="radio" id="trainer" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="trainer" >&nbsp; trainer &nbsp;
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card card-info p-1 user-info">
                                                <div class="card-header">
                                                    <h3 class="card-title">Note:</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="info-points">
                                                        <ul class="points">
                                                            <li>
                                                                <p>If you are adding this question for accepting user email then add <strong>{weight}</strong> in question title.</p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card card-info p-1 trainer-info">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Note:</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="info-points">
                                                            <ul class="points">
                                                                <li>
                                                                    <p>if you are adding trainer Question , Please keep <strong>BananaMo</strong> on first option, <strong>Morgan Brown</strong> on second option,<strong>Both</strong> on third option and <strong>Neither</strong> on fourth option in answers section</p>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- <div class="card card-info p-1 info">
                                                <div class="card-header">
                                                    <h3 class="card-title">Note:</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="info-points">
                                                        <p>Please set below options as answers for steps goal:</p>
                                                        <ul class="points">
                                                            <li>0-3000</li>
                                                            <li>3000-5000</li>
                                                            <li>5000-8000</li>
                                                            <li>8000-10000+</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div> -->
                                        </div>

                                        <div class="card card-primary mt-2 program">
                                            <div class="card-header">
                                                <h3 class="card-title">Use In Program</h3>
                                            </div>

                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="cardio" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="cardio">&nbsp; Cardio &nbsp;
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="musclestrengthening" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="musclestrengthening">&nbsp; Muscle Strengthening &nbsp;
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="level" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="level">&nbsp; Level &nbsp;
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

                                <div class="row another_quess">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <input type="checkbox" id="another_ques" name="another_ques" style="width: 20px; height:20px;">
                                            <label for="another_ques"> Use Answer in Question</label>
                                            <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Check this option to use answer of the current question into another question title"></i>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 selectQues">
                                        <div class="form-group">
                                            <label for="ques_id"> Select Question</label>
                                            <select name="ques_id" id="ques_id" class="form-control">
                                                <option value="">Select Question</option>
                                                @foreach ($quizes as $quiz)
                                                <option value="{{ $quiz['id'] }}">{{ $quiz['ques_title'] }}</option>
                                                @endforeach
                                            </select>
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
                                                        <div class="col-lg-3 singleRadio">
                                                            <input type="radio" id="singleType" style="width: 20px; height:20px; margin-top: 5px;" name="answer_type" value="single">&nbsp;
                                                            Single &nbsp;
                                                        </div>
                                                        <div class="col-lg-3 multipleRadio">
                                                            <input type="radio" id="multipleType" style="width: 20px; height:20px; margin-top: 5px;" name="answer_type" value="multiple">&nbsp; Multiple
                                                            &nbsp;
                                                        </div>
                                                        <div class="col-lg-3 inputRadio">
                                                            <input type="radio" id="inputType" style="width: 20px; height:20px; margin-top: 5px;" name="answer_type" value="userInput">&nbsp; Input
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-primary mt-2 answerFormat">
                                            <div class="card-header">
                                                <h3 class="card-title">
                                                    Answer Format
                                                </h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-3 imageAnswer">
                                                            <input type="checkbox" id="imageAnswer" style="width: 20px; height:20px; margin-top: 5px;" name="answer_format" value="image">&nbsp; Image Answer
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-primary mt-2 single">
                                            <div class="card-header">
                                                <h3 class="card-title">
                                                    Single Type Answers
                                                </h3>
                                                <!-- <div class="info text-right">
                                                    <i class="fas fa-info-circle" title="Please set below options as answers for steps goal:0-3000, 3000-5000, 5000-8000, 8000-10000+"></i>
                                                </div> -->
                                            </div>

                                            <div class="card-body withTransition">
                                                <label for="answer">Add Answers</label>
                                                <div class="textAnswerRepeater">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnswersWithTransition">
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group">
                                                                            <!-- <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" required> -->

                                                                            <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <div class="select2-purple">
                                                                                <select title="Please select transition." name="transition_id" class="form-control select2" multiple>
                                                                                    <option value="">Select Transition</option>
                                                                                    @foreach ($transitions as $transition)
                                                                                    <option value="{{ $transition['id'] }}">{{ $transition['title'] }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <!-- <select title="Please select transition." name="transition_id" id="transition_id" class="form-control select2" multiple required>
                                                                                    <option value="">Select Transition</option>
                                                                                    @foreach ($transitions as $transition)
                                                                                    <option value="{{ $transition['id'] }}">{{ $transition['title'] }}</option>
                                                                                    @endforeach
                                                                                </select> -->
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>

                                                <div class="imageAnswerRepeater">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnswersWithTransition">
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-3">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-3">
                                                                        <div class="form-group">
                                                                            <input type="file" title="Please select an image for answer." id="answer_img" name="answer_img" class="form-control" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <select title="Please select transition." name="transition_id" class="form-control select2" multiple required>
                                                                                <option value="">Select Transition</option>
                                                                                @foreach ($transitions as $transition)
                                                                                <option value="{{ $transition['id'] }}">{{ $transition['title'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body withoutTransition">
                                                <label for="answer">Add Answers</label>
                                                <div class="textAnswerRepeater">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnswers">
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-10">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>

                                                <div class="imageAnswerRepeater">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnswers">
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-5">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-5">
                                                                        <div class="form-group">
                                                                            <input type="file" title="Please select an image for answer." id="answer_img" name="answer_img" class="form-control" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="card-body singleAnsForCardio">
                                                <div class="repeater">
                                                    <div data-repeater-list="singleAnsForCardio">
                                                        <label for="answer">Add Answers</label><br>
                                                        <label for="cardio_id">Cardio Type</label>
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <div class="col-lg-6 mb-3">
                                                                    <select name="cardio_id" id="cardio_id" class="form-control">
                                                                        <option value="">Select Cardio</option>
                                                                        @foreach($cardioData as $cardio)
                                                                        <option value="{{ $cardio['id'] }}" style="font-weight: bold;">{{ $cardio['title'] }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-lg-2 mt-1">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                </div>
                                            </div>

                                            <div class="card-body singleAnsForMuscle">
                                                <div class="repeater">
                                                    <div data-repeater-list="singleAnsForMuscle">
                                                        <label for="answer">Add Answers</label><br>
                                                        <label for="muscle_id">Muscle Strengthening Type</label>
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <div class="col-lg-6 mb-3">
                                                                    <select name="muscle_id" id="muscle_id" class="form-control">
                                                                        <option value="">Muscle Strengthening</option>
                                                                        @foreach($muscleStrengtheningData as $muscleStrengthening)
                                                                        <option value="{{ $muscleStrengthening['id'] }}" style="font-weight: bold;">{{ $muscleStrengthening['title'] }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-lg-2 mt-1">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                </div>
                                            </div>

                                            <div class="card-body singleAnsForLevel">
                                                <div class="repeater">
                                                    <div data-repeater-list="singleAnsForLevel">
                                                        <label for="answer">Add Answers</label>
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        <input type="text" title="Please enter answer." id="level" name="level" class="form-control" placeholder="Type Answer" required>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-4">
                                                                    <div class="form-group">
                                                                        <input type="text" title="Please enter answer points." id="points" name="points" class="form-control" placeholder="Enter Points" required>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-2 mt-1">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-primary mt-2 multiple">
                                            <div class="card-header">
                                                <h3 class="card-title">
                                                    Multiple Type Answers
                                                </h3>
                                            </div>

                                            <div class="card-body multipleAnswersWithoutPoints">
                                                <div class="textAnswerRepeater">
                                                    <div class="repeater">
                                                        <div data-repeater-list="multipleAnswers">
                                                            <label for="answer">Add Answers</label>
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-10">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>

                                                <div class="imageAnswerRepeater">
                                                    <div class="repeater">
                                                        <div data-repeater-list="multipleAnswers">
                                                            <label for="answer">Add Answers</label>
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-5">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-5">
                                                                        <div class="form-group">
                                                                            <input type="file" title="Please select an image for answer." id="answer_img" name="answer_img" class="form-control" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body multipleAnsForCardio">
                                                <div class="repeater">
                                                    <div data-repeater-list="multipleAnsForCardio">
                                                        <label for="answer">Add Answers</label><br>
                                                        <label for="cardio_id">Cardio Type</label>
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <div class="col-lg-6 mb-3">
                                                                    <select name="cardio_id" id="cardio_id" class="form-control">
                                                                        <option value="">Select Cardio</option>
                                                                        @foreach($cardioData as $cardio)
                                                                        <option value="{{ $cardio['id'] }}" style="font-weight: bold;">{{ $cardio['title'] }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-lg-2 mt-1">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                </div>
                                            </div>

                                            <div class="card-body multipleAnsForMuscle">
                                                <div class="repeater">
                                                    <div data-repeater-list="multipleAnsForMuscle">
                                                        <label for="answer">Add Answers</label><br>
                                                        <label for="muscle_id">Muscle Strengthening Type</label>
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <div class="col-lg-6 mb-3">
                                                                    <select name="muscle_id" id="muscle_id" class="form-control">
                                                                        <option value="">Muscle Strengthening</option>
                                                                        @foreach($muscleStrengtheningData as $muscleStrengthening)
                                                                        <option value="{{ $muscleStrengthening['id'] }}" style="font-weight: bold;">{{ $muscleStrengthening['title'] }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-lg-2 mt-1">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                </div>
                                            </div>

                                            <div class="card-body multipleAnsForLevel">
                                                <div class="repeater">
                                                    <div data-repeater-list="multipleAnsForLevel">
                                                        <label for="answer">Add Answers</label>
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        <input type="text" title="Please enter answer." id="level" name="level" class="form-control" placeholder="Type Answer" required>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-4">
                                                                    <div class="form-group">
                                                                        <input type="text" title="Please enter answer points." id="points" name="points" class="form-control" placeholder="Enter Points" required>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-2 mt-1">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
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
                                                                <option value=""> Select User Question</option>
                                                                <option value="name"> Name</option>
                                                                <option value="current_weight"> Current Weight</option>
                                                                <option value="desire_weight"> Desired Weight</option>
                                                                <option value="height"> Height</option>
                                                                <option value="email"> Email</option>
                                                                <option value="age"> Age</option>
                                                                <option value="whatsapp"> Whatsapp</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-4">
                                                        <label for="answer">Label</label>
                                                        <div class="form-group">
                                                            <input type="text" title="Please enter Label you want to take input from user." id="answer" name="answer" class="form-control" placeholder="Enter placeholder for user input." required>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-4">
                                                        <label for="isNumericAnswer">Accept Numeric Value</label>
                                                        <div class="form-group">
                                                            <input type="checkbox" title="Please select if you want answer in numeric value from user." id="isNumericAnswer" name="isNumericAnswer" class="form-control" value="1" style="width: 20px; height:20px;">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4" style="display: flex;">
                                                        <label for="haveInstruction">Have Instruction?</label> &nbsp; &nbsp;
                                                        <input type="checkbox" title="Please select if quiz has instruction." id="haveInstruction" name="haveInstruction" class="form-control" value="yes" style="width: 20px; height: 20px;">
                                                    </div>
                                                    <div class="col-lg-8" id="instructionDiv" style="display: none;">
                                                        <label for="instructionMessage">Instruction</label> &nbsp; &nbsp;
                                                        <input type="text" title="Please enter the message you want to display as an instruction." id="instructionMessage" name="instructionMessage" class="form-control" placeholder="Enter small instruction message for user." required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 commonTransition">
                                            <div class="form-group">
                                                <label for="common_transition_id">Select Transition</label>
                                                <div class="select2-purple">
                                                    <select name="common_transition_id[]" id="common_transition_id" class="form-control select2" multiple>
                                                        <option value="">Select Transition</option>
                                                        @foreach ($transitions as $transition)
                                                        <option value="{{ $transition['id'] }}">{{ $transition['title'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="checkbox" id="sales_page" name="sales_page" style="width: 20px; height:20px;">
                                            <label for="sales_page"> Sales Page</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="checkbox" name="is_turnstile_enabled" value="1" style="width: 20px; height:20px;" {{ old('is_turnstile_enabled') ? 'checked' : '' }}>
                                            <label> Enable Turnstile on this question </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="checkbox" id="is_google_analytics" name="is_google_analytics" style="width: 20px; height:20px;">
                                            <label for="is_google_analytics"> Want to Add Amplitude Tracking Word?</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row googgle-analytics">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="google_analytic_script"> Enter Amplitude Tracking Word</label>
                                            <input type="text" name="google_analytic_script" id="google_analytic_script" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" style="width: 20px; height:20px;" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label for="is_active">Is Active</label>
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
                        quiz_group_id: {
                            required: true
                        },
                        ques_image: {
                            accept: "image/*",
                            extension: "jpg,jpeg,png",
                            maxfilesize: 2097152
                        },
                        ques_for_gender: {
                            required: true
                        }
                    },
                    messages: {
                        title: {
                            required: "Please enter question title",
                        },
                        quiz_group_id: {
                            required: "Please select quiz group"
                        },
                        ques_image: {
                            accept: "This field only accept image file",
                            extension: "File format should be jpg, jpeg, png",
                            maxfilesize: "File size must be less than 2 MB." // Message for file size validation
                        },
                        ques_for_gender: {
                            required: "Please select one gender from This Question is for"
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
            // Set default selections on page load
            $(window).on("load", function() {
                $("#profile").prop("checked", true).trigger("click");
                $("#basic").prop("checked", true).trigger("click");
                $("#singleType").prop("checked", true).trigger("click");
                $(".single, .withoutTransition").show();
            });

            // Show/hide question image input
            if ($('#isQuesImage').is(':checked')) {
                $('.questionImage').show();
            } else {
                $('.questionImage').hide();
            }
            $('#isQuesImage').change(function() {
                $('.questionImage').toggle(this.checked);
            });

            // Show/hide Amplitude Tracking Word input
            $('.googgle-analytics').hide();
            $('#is_google_analytics').change(function() {
                $('.googgle-analytics').toggle(this.checked);
            });

            // Show/hide select question dropdown
            $('.selectQues').hide();
            $('#another_ques').change(function() {
                $('.selectQues').toggle(this.checked);
            });

            // Initial hide for various sections
            $(".program").hide();
            $(".commonTransition, .transLogic, .inputRadio, .another_ques").hide();
            $(".withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multiple, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel, .multipelAnsProgramPoints, .input, .info, .answerFormat, .imageAnswerRepeater, .user-info, .trainer-info ").hide();

            // Question type change logic
            $(document).on('click', "input[name$='ques_type']", function() {
                $('input[name$="have_transition"]').prop("checked", false);

                // Deselect all ques_for radio buttons
                document.querySelectorAll('input[name="ques_for"]').forEach(radio => radio.checked = false);

                var quesType = $(this).val();
                if (quesType === '0') {
                    $(".profile, .answerFormat").show();
                    $("#basic").prop("checked", true);
                    $(".transitionDiv, .withoutTransition, .transLogicYes").show();
                    $(".program, .another_ques, .commonTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel, .multipleAnswersWithoutPoints, .user-info, .trainer-info").hide();
                } else if (quesType === '1') {
                    $(".program, .single, .singleRadio, .multipleRadio, .singleAnsForCardio").show();
                    $(".answerFormat, .user-info, .transLogicYes, .trainer-info").hide();

                    // Deselect all answer_format checkboxes
                    document.querySelectorAll('input[name="answer_format"]').forEach(radio => radio.checked = false);

                    $("#cardio").prop("checked", true);
                    $("#singleType").prop("checked", true).trigger("click");
                    $(".profile, .another_ques, .commonTransition, .inputType, .input, .inputRadio, .user-info").hide();
                } else {
                    // Deselect all ques_for and answer_format radios
                    document.querySelectorAll('input[name="ques_for"]').forEach(radio => radio.checked = false);
                    document.querySelectorAll('input[name="answer_format"]').forEach(radio => radio.checked = false);

                    $("#singleType").prop("checked", true).trigger("click");
                    $(".transitionDiv, .another_ques, .singleRadio, .multipleRadio").show();
                    $(".profile, .program, .commonTransition, .inputType, .input, .inputRadio, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel, .answerFormat, .user-info, .trainer-info").hide();
                }

                // Use Answer in other question
                $('.selectQues').hide();
                $('#another_ques').change(function() {
                    $('.selectQues').toggle(this.checked);
                });

                if (quesType === '0' || quesType === '2') {
                    $('input[name$="another_ques"]').prop("checked", false);

                    $("input[name$='ques_for']").change(function() {
                        $('input[name$="have_transition"]').prop("checked", false);
                        if ($('#have_transition').is(':checked')) {
                            $(".transLogicYes").show();
                            document.querySelectorAll('input[name="trans_logic"]').forEach(radio => radio.checked = false);
                        }
                        if ($(this).val() === 'basic' || $(this).val() === 'trainer') {
                            $(".transLogicYes").show();
                            $("#singleType").prop("checked", true).trigger("click");
                            if ($(this).val() === 'trainer'){
                                $(".trainer-info").show();
                            }
                        }
                        toggleTransitionLogic();
                    });
                    toggleTransitionLogic();
                } else {
                    $('input[name$="have_transition"]').prop("checked", false);
                    handleAnswerType();
                }

                $("#imageAnswer").change(function() {
                    toggleTransitionLogic();
                });

                $("#have_transition").change(function() {
                    if ($('#have_transition').is(':checked')) {
                        document.querySelectorAll('input[name="trans_logic"]').forEach(radio => radio.checked = false);
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
                    if (quesFor == 'user' || quesFor == 'steps_goal' || quesFor == 'activity_level') {
                        $(".transLogicYes").hide();
                        $("#trans_logic_no").prop("checked", true);
                    }
                    var quesType = $("input[name$='ques_type']:checked").val();
                    if (quesType === '1') {
                        $(".transLogicYes").hide();
                        $("#trans_logic_no").prop("checked", true);
                    }
                    handleAnswerType();
                } else {
                    $(".transLogic, .commonTransition").hide();
                    handleAnswerType();
                }
            }

            function handleAnswerType() {
                var quesType = $("input[name$='ques_type']:checked").val();
                var quesFor = $("input[name$='ques_for']:checked").val();
                var transLogic = $("input[name$='trans_logic']:checked").val();
                var answerType = $("input[name$='answer_type']:checked").val();
                var haveTransition = $('#have_transition').is(':checked');
                var answerFormat = $("input[name$='answer_format']:checked").val();

                if (quesType == 0) {
                    if (quesFor == 'user') {
                        $("#inputType").prop("checked", true);
                        $(".inputRadio, .input, .user-info").show();
                        $(".singleRadio, .single, .multiple, .multipleRadio, .multipleAnswersWithoutPoints, .multipleAnsForMuscle, .multipleAnsForCardio, .multipleAnsForLevel, .withTransition, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .answerFormat, .imageAnswerRepeater, .trainer-info").hide();
                    }
                    if (quesFor == 'steps_goal' || quesFor == 'activity_level') {
                        $("#singleType").prop("checked", true);
                        $(".singleRadio, .single, .withoutTransition, .info, .textAnswerRepeater").show();
                        $(".multiple, .multipleRadio, .multipleAnswersWithoutPoints, .multipleAnsForMuscle, .multipleAnsForCardio, .multipleAnsForLevel, .inputRadio, .withTransition, .input, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .answerFormat, .imageAnswerRepeater, .user-info, .trainer-info").hide();
                    }
                    if (quesFor == 'trainer' && transLogic == 'Yes') {
                        $("#singleType").prop("checked", true);
                        $(".singleRadio, .single, .withTransition, .textAnswerRepeater, .trainer-info").show();
                        $(".multipleRadio, .inputRadio, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater, .commonTransition, .user-info, .answerFormat").hide();
                    }
                    if (quesFor == 'trainer' && (!transLogic || transLogic === 'No')) {
                        $("#singleType").prop("checked", true);
                        $(".singleRadio, .single, .withoutTransition, .textAnswerRepeater, .trainer-info").show();
                        $(".multipleRadio, .inputRadio, .withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater, .user-info, .answerFormat").hide();
                    }
                    if (quesFor === 'basic' && transLogic == 'Yes') {
                        if (answerFormat === 'image') {
                            $("#singleType").prop("checked", true);
                            $(".singleRadio, .single, .withTransition, .imageAnswerRepeater").show();
                            $(".multipleRadio, .inputRadio, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .textAnswerRepeater, .commonTransition, .user-info, .trainer-info").hide();
                        } else {
                            $("#singleType").prop("checked", true);
                            $(".singleRadio, .single, .withTransition, .textAnswerRepeater").show();
                            $(".multipleRadio, .inputRadio, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater, .commonTransition, .user-info, .trainer-info").hide();
                        }
                    }
                    if (quesFor === 'basic' && (!transLogic || transLogic === 'No') && answerType === 'single') {
                        if (answerFormat == 'image') {
                            $("#singleType").prop("checked", true);
                            $(".singleRadio, .single, .multipleRadio, .withoutTransition, .answerFormat, .imageAnswerRepeater").show();
                            $(".multiple, .multipleAnswersWithoutPoints, .inputRadio, .input, .withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .textAnswerRepeater, .user-info, .trainer-info").hide();
                        } else {
                            $("#singleType").prop("checked", true);
                            $(".singleRadio, .single, .multipleRadio, .withoutTransition, .answerFormat, .textAnswerRepeater").show();
                            $(".multiple, .multipleAnswersWithoutPoints, .inputRadio, .input, .withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater, .user-info, .trainer-info").hide();
                        }
                    }
                    if (quesFor === 'basic' && (!transLogic || transLogic === 'No') && answerType === 'multiple') {
                        if (answerFormat == 'image') {
                            $(".multiple, .multipleAnswersWithoutPoints, .imageAnswerRepeater").show();
                            $(".single, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .textAnswerRepeater, .user-info, .trainer-info").hide();
                        } else {
                            $(".multiple, .multipleAnswersWithoutPoints, .textAnswerRepeater").show();
                            $(".single, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .info, .imageAnswerRepeater, .user-info, .trainer-info").hide();
                        }
                    }
                }

                if (quesType == 1 && answerType === 'single') {
                    $(".info").hide();
                    if (quesFor === 'cardio') {
                        $(".single, .singleRadio, .multipleRadio, .singleAnsForCardio").show();
                        $(".multiple, .inputRadio, .input, .withoutTransition, .singleAnsForMuscle, .singleAnsForLevel, .withTransition, .user-info, .trainer-info").hide();
                    }

                    if (quesFor === 'musclestrengthening') {
                        $(".single, .singleRadio, .multipleRadio, .singleAnsForMuscle").show();
                        $(".multiple, .inputRadio, .input, .withoutTransition, .singleAnsForCardio, .singleAnsForLevel, .withTransition, .user-info, .trainer-info").hide();
                    }

                    if (quesFor === 'level') {
                        $(".single, .singleRadio, .multipleRadio, .singleAnsForLevel").show();
                        $(".multiple, .inputRadio, .input, .withoutTransition, .singleAnsForCardio, .singleAnsForMuscle, .withTransition, .user-info, .trainer-info").hide();
                    }
                }

                if (quesType == 1 && answerType === 'multiple') {
                    $(".info").hide();
                    if (quesFor === 'cardio') {
                        $(".multiple, .singleRadio, .multipleRadio, .multipleAnsForCardio").show();
                        $(".single, .inputRadio, .input, .withoutTransition, .withTransition, .multipleAnsForMuscle, .multipleAnsForLevel, .multipleAnswersWithoutPoints, .singleAnsForMuscle, .singleAnsForLevel, .user-info, .trainer-info ").hide();
                    }

                    if (quesFor === 'musclestrengthening') {
                        $(".multiple, .singleRadio, .multipleRadio, .multipleAnsForMuscle").show();
                        $(".single, .inputRadio, .input, .withoutTransition, .withTransition, .multipleAnsForCardio, .multipleAnsForLevel, .multipleAnswersWithoutPoints, .singleAnsForCardio, .singleAnsForLevel, .user-info, .trainer-info ").hide();
                    }

                    if (quesFor === 'level') {
                        $(".multiple, .singleRadio, .multipleRadio, .multipleAnsForLevel").show();
                        $(".single, .inputRadio, .input, .withoutTransition, .withTransition, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnswersWithoutPoints, .singleAnsForCardio, .singleAnsForMuscle, .user-info, .trainer-info ").hide();
                    }
                }

                if (quesType == 2) {
                    $(".info").hide();
                    if (transLogic === 'Yes') {
                        $("#singleType").prop("checked", true).trigger("click");
                        $(".single, .withTransition").show();
                        $(".multiple, .multipleRadio, .commonTransition, .withoutTransition, .user-info, .trainer-info").hide();
                    }
                    if (answerType === 'single' && (!transLogic || transLogic === 'No')) {
                        $(".single, .multipleRadio, .withoutTransition").show();
                        $(".withTransition, .multiple, .multipleAnswersWithoutPoints, .user-info, .trainer-info").hide();
                    }
                    if (answerType === 'multiple' && (!transLogic || transLogic === 'No')) {
                        $(".multiple, .multipleAnswersWithoutPoints").show();
                        $(".single, .withoutTransition, .withTransition, .user-info, .trainer-info").hide();
                    }
                }
            }

            // Show/hide instruction input
            $('#haveInstruction').change(function() {
                $('#instructionDiv').toggle($(this).is(':checked'));
            });
        });
    </script>

    <script>
        // Initialize all repeaters
        $('.repeater').repeater({
            initEmpty: true,
            isFirstItemUndeletable: false,
            show: function() {
                $(this).slideDown();
                // Reinitialize select2 for newly added items
                $(this).find('.select2').select2({
                    theme: 'default'
                });
            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                }
            }
        });

        // Initialize select2 for all .select2 elements
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'default'
            });

            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            // For #common_transition_id, maintain selection order
            let selectedOptions = [];

            $('#common_transition_id').select2({
                placeholder: "Select Transition",
                allowClear: true
            }).on('select2:select', function(e) {
                let selectedId = e.params.data.id;
                if (!selectedOptions.includes(selectedId)) {
                    selectedOptions.push(selectedId);
                }
                displaySelectedOptions();
            }).on('select2:unselect', function(e) {
                let unselectedId = e.params.data.id;
                selectedOptions = selectedOptions.filter(id => id !== unselectedId);
                displaySelectedOptions();
            });

            function displaySelectedOptions() {
                $('#common_transition_id').val(selectedOptions).trigger('change');
                // Optional: Display selected options as text (for custom view)
                // let displayText = selectedOptions.map(id => {
                //     return $(`#common_transition_id option[value="${id}"]`).text();
                // }).join(', ');
                // $('.select2-selection__rendered').text(displayText);
            }

            // Rearrange selected options before form submission
            $('#quickForm').on('submit', function() {
                const selectElement = $('#common_transition_id');
                selectedOptions.forEach(id => {
                    let option = selectElement.find(`option[value="${id}"]`);
                    if (option.length) {
                        option.appendTo(selectElement);
                    }
                });
            });
        });
    </script>
    @endsection
</x-admin-layout>