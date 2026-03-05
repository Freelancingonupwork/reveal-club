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

        .select2-container--default .select2-selection--multiple {
            background-color: #343a40;
            border: 1px solid #6c757d;
        }

        .dark-mode .select2-purple .select2-container--default .select2-search--inline .select2-search__field:focus {
            border: none;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #000000 !important;
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
            <form id="quickForm" action="{{ url('admin/update-quiz/'.$quizData['slug'].'/'.$quizData['id']) }}" method="post" enctype="multipart/form-data">
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
                                            <input type="text" class="form-control" id="title" name="title" value="{{ $quizData['ques_title'] }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="quiz_group_id">Quiz Group <span style="color: #ff5252;">*</span></label>
                                            <select class="form-control" name="quiz_group_id" id="quiz_group_id">
                                                <option value="">Select Quiz Group</option>
                                                @foreach ($quizGroups as $quizGroup)
                                                <option value="{{ $quizGroup['id'] }}" @if($quizGroup['id']==$quizData['quiz_group_id']) selected @endif>{{ $quizGroup['title'] }}</option>
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
                                                <input type="checkbox" id="isQuesImage" name="isQuesImage" style="width: 20px; height:20px;" @if($quizData['is_ques_image']==1) checked @endif>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 questionImage">
                                        <div class="form-group">
                                            <label for="ques_image">Image</label>
                                            <input type="file" class="form-control" id="ques_image" name="ques_image" accept="image/*">
                                        </div>
                                        @if(!empty($quizData['ques_image']) && !is_null($quizData['ques_image']))
                                        <img src="{{ url('/storage/'.$quizData['ques_image']) }}" class="img-circle elevation-2" alt="Quiz Image" style="width:150px; height: 150px;">
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="quesDescription">Description</label>
                                    <textarea class="form-control" id="quesDescription" name="quesDescription">{{ $quizData['ques_description'] }}</textarea>
                                </div>

                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">This Question is for </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <input type="radio" id="male" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="male" @if($quizData['ques_for_gender']==='male' ) checked @endif>&nbsp; Male &nbsp;
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="radio" id="female" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="female" @if($quizData['ques_for_gender']==='female' ) checked @endif>&nbsp; Female &nbsp;
                                            </div>
                                            <!-- <div class="col-lg-3">
                                                <input type="radio" id="transgender" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="transgender">&nbsp; Transgender
                                            </div> -->
                                            <div class="col-lg-3">
                                                <input type="radio" id="all" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for_gender" value="all" @if($quizData['ques_for_gender']==='all' ) checked @endif>&nbsp; All
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
                                                <input type="radio" id="profile" style="width: 20px; height:20px; margin-top: 5px;" name="ques_type" value="0" @if($quizData['ques_type']==0) checked @endif>&nbsp; Use in profile &nbsp;
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="radio" id="use_in_program" style="width: 20px; height:20px; margin-top: 5px;" name="ques_type" value="1" @if($quizData['ques_type']==1) checked @endif>&nbsp; Use in program &nbsp;
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="radio" id="none" style="width: 20px; height:20px; margin-top: 5px;" name="ques_type" value="2" @if($quizData['ques_type']==2) checked @endif>&nbsp; None &nbsp;
                                            </div>
                                        </div>

                                        <div class="card card-primary mt-2 profile">
                                            <div class="card-header">
                                                <h3 class="card-title">Use In Profile</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="basic" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="basic" @if($quizData['ques_for']==='basic' ) checked @endif>&nbsp; Basic &nbsp;
                                                    </div>
                                                    <!-- <div class="col-lg-3">
                                                        <input type="radio" id="marketing" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="marketing">&nbsp; Marketing &nbsp;
                                                    </div> -->
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="forUser" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="user" @if($quizData['ques_for']==='user' ) checked @endif>&nbsp; User &nbsp;
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="stepsGoal" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="steps_goal" @if($quizData['ques_for']==='steps_goal' ) checked @endif>&nbsp; Step Goal
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="activityLevel" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="activity_level" @if($quizData['ques_for']==='activity_level' ) checked @endif>&nbsp; Activity Level
                                                    </div>
                                                        <div class="col-lg-3">
                                                        <input type="radio" id="trainer" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="trainer" @if($quizData['ques_for']==='trainer' ) checked @endif>&nbsp; trainer &nbsp;
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

                                        <div class="card card-primary mt-2 program">
                                            <div class="card-header">
                                                <h3 class="card-title">Use In Program</h3>
                                            </div>

                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="cardio" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="cardio" @if($quizData['ques_for']==='cardio' ) checked @endif>&nbsp; Cardio &nbsp;
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="musclestrengthening" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="musclestrengthening" @if($quizData['ques_for']==='musclestrengthening' ) checked @endif>&nbsp; Muscle Strengthening &nbsp;
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <input type="radio" id="level" style="width: 20px; height:20px; margin-top: 5px;" name="ques_for" value="level" @if($quizData['ques_for']==='level' ) checked @endif>&nbsp; Level &nbsp;
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
                                            <input type="checkbox" id="have_transition" name="have_transition" style="width: 20px; height:20px;" @if($quizData['is_have_transition']===1) checked @endif>
                                            <label for="have_transition"> Have Transition ?</label>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 transLogic">
                                        <label for="trans_logic">Does transition have logic?</label>
                                        <div class="row mb">
                                            <div class="col-lg-6 transLogicYes">
                                                <!-- <input type="radio" name="trans_logic" id="trans_logic_yes" value="Yes" style="width: 20px; height:20px;" onchange="toggleAnswerType()"> Yes -->
                                                <input type="radio" name="trans_logic" id="trans_logic_yes" value="Yes" style="width: 20px; height:20px;" @if($quizData['transition_logic']==='Yes' ) checked @endif> Yes
                                            </div>
                                            <div class="col-lg-6 transLogicNo">
                                                <!-- <input type="radio" name="trans_logic" id="trans_logic_no" value="No" style="width: 20px; height:20px;" onchange="toggleAnswerType()"> No -->
                                                <input type="radio" name="trans_logic" id="trans_logic_no" value="No" style="width: 20px; height:20px;" @if($quizData['transition_logic']==='No' ) checked @endif> No
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row another_quess">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <input type="checkbox" id="another_ques" name="another_ques" style="width: 20px; height:20px;" @if($quizData['is_another_ques']===1) checked @endif>
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
                                                <option value="{{ $quiz['id'] }}" @if($quizData['ques_id']==$quiz['id']) selected @endif>{{ $quiz['ques_title'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                {{-- new start --}}
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
                                                            <input type="radio" id="singleType" style="width: 20px; height:20px; margin-top: 5px;" name="answer_type" value="single" @if($quizData['answer_type']==='single' ) checked @endif>&nbsp;
                                                            Single &nbsp;
                                                        </div>
                                                        <div class="col-lg-3 multipleRadio">
                                                            <input type="radio" id="multipleType" style="width: 20px; height:20px; margin-top: 5px;" name="answer_type" value="multiple" @if($quizData['answer_type']==='multiple' ) checked @endif>&nbsp; Multiple
                                                            &nbsp;
                                                        </div>
                                                        <div class="col-lg-3 inputRadio">
                                                            <input type="radio" id="inputType" style="width: 20px; height:20px; margin-top: 5px;" name="answer_type" value="userInput" @if($quizData['answer_type']==='userInput' ) checked @endif>&nbsp; Input
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
                                                            <input type="checkbox" id="imageAnswer" style="width: 20px; height:20px; margin-top: 5px;" name="answer_format" value="image" @if($quizData['answer_format']==='image' ) checked @endif>&nbsp; Image Answer
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Single type answers --}}
                                        @if($quizData['answer_type'] === 'single' && $quizData['ques_type'] != 1 && $quizData['answer_format'] !== 'image' && !empty($quizData['answers']) && $quizData['is_have_transition']===1 && $quizData['transition_logic']==='Yes')
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withTransition">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>

                                                <div class="card-body withTransition">
                                                    <label for="answer">Add Answers</label>    
                                                    <div class="textAnswerRepeater">
                                                        <div class="repeater">
                                                            <div data-repeater-list="singleAnswersWithTransition">
                                                                @foreach($quizData['answers'] as $singleAnswersWithTransition)
                                                                <div data-repeater-item>
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group">
                                                                                <input type="hidden" class="item-id" name="id" value="{{ $singleAnswersWithTransition['id'] }}" />
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" value="{{ $singleAnswersWithTransition['ques_answers'] }}" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-4">
                                                                            <div class="form-group">
                                                                                <div class="select2-purple">
                                                                                    <select title="Please select transition." name="transition_id" class="form-control select2" multiple>
                                                                                        <option value="">Select Transition</option>
                                                                                        @foreach ($transitions as $transition)
                                                                                        <option value="{{ $transition['id'] }}"
                                                                                            @if(isset($singleAnswersWithTransition['transition_id']))
                                                                                                {{ in_array($transition['id'], explode("|", $singleAnswersWithTransition['transition_id'])) ? 'selected' : '' }}
                                                                                            @endif>
                                                                                            {{ $transition['title'] }}
                                                                                        </option>
                                                                                        @endforeach
                                                                                    </select>
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
                                                                @endforeach
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Default (create mode or fallback) --}}
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withTransition">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
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
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-4">
                                                                            <div class="form-group">
                                                                                <div class="select2-purple">
                                                                                    <select title="Please select transition." name="transition_id" class="form-control select2" multiple >
                                                                                        <option value="">Select Transition</option>
                                                                                        @foreach ($transitions as $transition)
                                                                                        <option value="{{ $transition['id'] }}">{{ $transition['title'] }}</option>
                                                                                        @endforeach
                                                                                    </select>
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
                                                </div>
                                            </div>
                                        @endif


                                        @if($quizData['answer_type'] === 'single' && $quizData['ques_type'] != 1 && $quizData['answer_format'] === 'image' && !empty($quizData['answers']) && $quizData['is_have_transition']===1 && $quizData['transition_logic']==='Yes')
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withTransitionImg">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>

                                                <div class="card-body withTransitionImg">
                                                    <label for="answer">Add Answers</label>    
                                                    <div class="imageAnswerRepeater">
                                                        <div class="repeater">
                                                            <div data-repeater-list="singleAnswersWithTransitionImg">
                                                                @foreach($quizData['answers'] as $singleAnswersWithTransitionAndImage)
                                                                <div data-repeater-item>
                                                                    <div class="row">
                                                                        <div class="col-lg-3">
                                                                            <div class="form-group">
                                                                                <input type="hidden" class="item-id" name="id" value="{{ $singleAnswersWithTransitionAndImage['id'] }}" />
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" value="{{ $singleAnswersWithTransitionAndImage['ques_answers'] }}" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-3">
                                                                            <div class="form-group">
                                                                                {{-- Display existing image --}}
                                                                                @if(!empty($singleAnswersWithTransitionAndImage['answer_img']))
                                                                                <div class="mb-2">
                                                                                    <img src="{{ asset('storage/'.$singleAnswersWithTransitionAndImage['answer_img']) }}" alt="Answer Image" style="max-height: 80px;">
                                                                                </div>
                                                                                @endif

                                                                                {{-- Allow user to upload new image (optional) --}}
                                                                                <input type="file" title="You may upload a new image." name="answer_img" class="form-control">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-4">
                                                                            <div class="form-group">
                                                                                <select title="Please select transition." name="transition_id" class="form-control select2" multiple required>
                                                                                    <option value="">Select Transition</option>
                                                                                    @foreach ($transitions as $transition)
                                                                                    <option value="{{ $transition['id'] }}"
                                                                                        @if(isset($singleAnswersWithTransitionAndImage['transition_id']))
                                                                                            {{ in_array($transition['id'], explode("|", $singleAnswersWithTransitionAndImage['transition_id'])) ? 'selected' : '' }}
                                                                                        @endif>
                                                                                        {{ $transition['title'] }}
                                                                                    </option>
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
                                                                @endforeach
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Fallback: Create Mode --}}
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withTransitionImg">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>

                                                <div class="card-body withTransitionImg">
                                                    <label for="answer">Add Answers</label> 
                                                    <div class="imageAnswerRepeater">
                                                        <div class="repeater">
                                                            <div data-repeater-list="singleAnswersWithTransitionImg">
                                                                <div data-repeater-item>
                                                                    <div class="row">
                                                                        <div class="col-lg-3">
                                                                            <div class="form-group">
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-3">
                                                                            <div class="form-group">
                                                                                <input type="file" title="Please select an image for answer." name="answer_img" class="form-control" required>
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
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- TEXT ANSWERS WITHOUT TRANSITION --}}
                                        @if($quizData['answer_type'] === 'single' && $quizData['ques_type'] != 1 && $quizData['answer_format'] !== 'image' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withoutTransition">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body withoutTransition">
                                                    <label for="answer">Add Answers</label>
                                                    <div class="textAnswerRepeater">
                                                        <div class="repeater">
                                                            <div data-repeater-list="singleAnswers">
                                                                @foreach($quizData['answers'] as $singleAnswersWithoutTransition)
                                                                <div data-repeater-item>
                                                                    <div class="row">
                                                                        <div class="col-lg-10">
                                                                            <div class="form-group">
                                                                                <input type="hidden" class="item-id" name="id" value="{{ $singleAnswersWithoutTransition['id'] }}" />
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" value="{{ $singleAnswersWithoutTransition['ques_answers'] }}" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-2 mt-1">
                                                                            <div class="form-group">
                                                                                <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- DEFAULT TEXT ANSWER CREATE MODE --}}
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withoutTransition">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
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
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" required>
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
                                            </div>
                                        @endif

                                        {{-- IMAGE ANSWERS WITHOUT TRANSITION --}}
                                        @if($quizData['answer_type'] === 'single' && $quizData['ques_type'] != 1 && $quizData['answer_format'] === 'image' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withoutTransitionImg">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body withoutTransitionImg">
                                                    <label for="answer">Add Answers</label>
                                                    <div class="imageAnswerRepeater">
                                                        <div class="repeater">
                                                            <div data-repeater-list="singleAnswersImg">
                                                                @foreach($quizData['answers'] as $singleAnswersWithoutTransitionAndImage)
                                                                <div data-repeater-item>
                                                                    <div class="row">
                                                                        <div class="col-lg-5">
                                                                            <div class="form-group">
                                                                                <input type="hidden" class="item-id" name="id" value="{{ $singleAnswersWithoutTransitionAndImage['id'] }}" />
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" value="{{ $singleAnswersWithoutTransitionAndImage['ques_answers'] }}" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-5">
                                                                            <div class="form-group">
                                                                                @if(!empty($singleAnswersWithoutTransitionAndImage['answer_img']))
                                                                                <div class="mb-2">
                                                                                    <img src="{{ asset('storage/'.$singleAnswersWithoutTransitionAndImage['answer_img']) }}" alt="Answer Image" style="max-height: 80px;">
                                                                                </div>
                                                                                @endif
                                                                                <input type="file" title="Upload new image (optional)" name="answer_img" class="form-control">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-2 mt-1">
                                                                            <div class="form-group">
                                                                                <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- DEFAULT IMAGE ANSWER CREATE MODE --}}
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header withoutTransitionImg">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body withoutTransitionImg">
                                                    <label for="answer">Add Answers</label>
                                                    <div class="imageAnswerRepeater">
                                                        <div class="repeater">
                                                            <div data-repeater-list="singleAnswersImg">
                                                                <div data-repeater-item>
                                                                    <div class="row">
                                                                        <div class="col-lg-5">
                                                                            <div class="form-group">
                                                                                <input type="text" title="Please enter answer." name="answer" class="form-control" placeholder="Type Answer" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-lg-5">
                                                                            <div class="form-group">
                                                                                <input type="file" title="Please select an image for answer." name="answer_img" class="form-control" required>
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
                                            </div>
                                        @endif


                                        @if($quizData['answer_type'] === 'single' && $quizData['ques_type'] === 1 && $quizData['ques_for'] === 'cardio' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header singleAnsForCardio">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body singleAnsForCardio">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnsForCardio">
                                                            <label for="answer">Add Answers</label><br>
                                                            <label for="cardio_id">Cardio Type</label>
                                                            @foreach($quizData['answers'] as $singleAnsForCardio)
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6 mb-3">
                                                                        <input type="hidden" class="item-id" name="id" value="{{ $singleAnsForCardio['id'] }}" />
                                                                        <select name="cardio_id" class="form-control" required>
                                                                            <option value="">Select Cardio</option>
                                                                            @foreach($cardioData as $cardio)
                                                                            <option value="{{ $cardio['id'] }}" 
                                                                                @if($singleAnsForCardio['cardio_and_muscle_id'] == $cardio['id']) selected @endif
                                                                                style="font-weight: bold;">
                                                                                {{ $cardio['title'] }}
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
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Fallback to create mode if no answers exist --}}
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header singleAnsForCardio">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body singleAnsForCardio">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnsForCardio">
                                                            <label for="answer">Add Answers</label><br>
                                                            <label for="cardio_id">Cardio Type</label>
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6 mb-3">
                                                                        <select name="cardio_id" class="form-control" required>
                                                                            <option value="">Select Cardio</option>
                                                                            @foreach($cardioData as $cardio)
                                                                            <option value="{{ $cardio['id'] }}" style="font-weight: bold;">{{ $cardio['title'] }}</option>
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
                                            </div>
                                        @endif


                                        @if($quizData['answer_type'] === 'single' && $quizData['ques_type'] === 1 && $quizData['ques_for'] === 'musclestrengthening' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header singleAnsForMuscle">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body singleAnsForMuscle">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnsForMuscle">
                                                            <label for="answer">Add Answers</label><br>
                                                            <label for="muscle_id">Muscle Strengthening Type</label>

                                                            @foreach($quizData['answers'] as $singleAnsForMuscleStrengthening)
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6 mb-3">
                                                                        <input type="hidden" class="item-id" name="id" value="{{ $singleAnsForMuscleStrengthening['id'] }}" />
                                                                        <select name="muscle_id" class="form-control" required>
                                                                            <option value="">Muscle Strengthening</option>
                                                                            @foreach($muscleStrengtheningData as $muscleStrengthening)
                                                                            <option value="{{ $muscleStrengthening['id'] }}"
                                                                                @if($singleAnsForMuscleStrengthening['cardio_and_muscle_id'] == $muscleStrengthening['id']) selected @endif
                                                                                style="font-weight: bold;">
                                                                                {{ $muscleStrengthening['title'] }}
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
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Fallback to create mode --}}
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header singleAnsForMuscle">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body singleAnsForMuscle">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnsForMuscle">
                                                            <label for="answer">Add Answers</label><br>
                                                            <label for="muscle_id">Muscle Strengthening Type</label>
                                                                            
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6 mb-3">
                                                                        <select name="muscle_id" class="form-control" required>
                                                                            <option value="">Muscle Strengthening</option>
                                                                            @foreach($muscleStrengtheningData as $muscleStrengthening)
                                                                            <option value="{{ $muscleStrengthening['id'] }}" style="font-weight: bold;">
                                                                                {{ $muscleStrengthening['title'] }}
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
                                            </div>
                                        @endif


                                        @if($quizData['answer_type'] === 'single' && $quizData['ques_type'] === 1 && $quizData['ques_for'] === 'level' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header singleAnsForLevel">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body singleAnsForLevel">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnsForLevel">
                                                            <label for="answer">Add Answers</label>
                                                            @foreach($quizData['answers'] as $singleAnswersWithLevels)
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group">
                                                                            <input type="hidden" class="item-id" name="id" value="{{ $singleAnswersWithLevels['id'] }}" />
                                                                            <input type="text" title="Please enter answer." name="level" class="form-control" placeholder="Type Answer" value="{{ $singleAnswersWithLevels['ques_answers'] }}" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer points." name="points" class="form-control" placeholder="Enter Points" value="{{ $singleAnswersWithLevels['ans_points'] }}" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Fallback to create mode --}}
                                            <div class="card card-primary mt-2 single">
                                                <div class="card-header singleAnsForLevel">
                                                    <h3 class="card-title">
                                                        Single Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body singleAnsForLevel">
                                                    <div class="repeater">
                                                        <div data-repeater-list="singleAnsForLevel">
                                                            <label for="answer">Add Answers</label>
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer." name="level" class="form-control" placeholder="Type Answer" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer points." name="points" class="form-control" placeholder="Enter Points" required>
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
                                        @endif
                                        


                                        {{-- Multiple type answers --}}
                                        @if($quizData['answer_type'] === 'multiple' && $quizData['ques_type'] != 1 && $quizData['answer_format'] !== 'image' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnswersWithoutPoints">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body multipleAnswersWithoutPoints">
                                                    <div class="repeater">
                                                        <div data-repeater-list="multipleAnswers">
                                                            <label for="answer">Add Answers</label>
                                                            @foreach($quizData['answers'] as $multipleAnswersWithoutPoints)
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-10">
                                                                        <div class="form-group">
                                                                            <input type="hidden" class="item-id" name="id" value="{{ $multipleAnswersWithoutPoints['id'] }}" />
                                                                            <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" value="{{ $multipleAnswersWithoutPoints['ques_answers'] }}" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnswersWithoutPoints">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body multipleAnswersWithoutPoints">
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
                                            </div>
                                        @endif
                                        
                                        
                                        @if($quizData['answer_type'] === 'multiple' && $quizData['ques_type'] != 1 && $quizData['answer_format'] === 'image' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnswersWithoutPointsImg">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body multipleAnswersWithoutPointsImg">
                                                    <div class="repeater">
                                                        <div data-repeater-list="multipleAnswersImg">
                                                            @foreach($quizData['answers'] as $singleAnswersWithTransitionAndImage)
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-3">
                                                                        <div class="form-group">
                                                                            <input type="hidden" class="item-id" name="id" value="{{ $singleAnswersWithTransitionAndImage['id'] }}" />
                                                                            <input type="text" title="Please enter answer." id="answer" name="answer" class="form-control" placeholder="Type Answer" value="{{ $singleAnswersWithTransitionAndImage['ques_answers'] }}" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-3">
                                                                        <div class="form-group">
                                                                            <img src="{{ asset('storage/'.$singleAnswersWithTransitionAndImage['answer_img']) }}" alt="" srcset="">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <select title="Please select transition." name="transition_id" class="form-control select2" multiple required>
                                                                                <option value="">Select Transition</option>
                                                                                @foreach ($transitions as $transition)
                                                                                <option value="{{ $transition['id'] }}" @if(isset($singleAnswersWithTransitionAndImage['transition_id'])) {{in_array($transition['id'], explode("|", $singleAnswersWithTransitionAndImage['transition_id'])) ? 'selected' : ''}} @endif>{{ $transition['title'] }}</option>
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
                                                            @endforeach
                                                            <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnswersWithoutPointsImg">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body multipleAnswersWithoutPointsImg">
                                                    <div class="imageAnswerRepeater">
                                                        <div class="repeater">
                                                            <div data-repeater-list="multipleAnswersImg">
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
                                            </div>
                                        @endif
                                        
                                        
                                        @if($quizData['answer_type'] === 'multiple' && $quizData['ques_type'] === 1 && $quizData['ques_for'] === 'cardio' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnsForCardio">
                                                    <h3 class="card-title">Multiple Type Answers</h3>
                                                </div>
                                                <div class="card-body multipleAnsForCardio">
                                                    <div class="repeater">
                                                        <div data-repeater-list="multipleAnsForCardio">
                                                            <label for="answer">Add Answers</label><br>
                                                            <label for="cardio_id">Cardio Type</label>
                                                            @foreach($quizData['answers'] as $multipleAnsForCardio)
                                                                <div data-repeater-item>
                                                                    <div class="row">
                                                                        <div class="col-lg-6 mb-3">
                                                                            <input type="hidden" class="item-id" name="id" value="{{ $multipleAnsForCardio['id'] }}" />
                                                                            <select name="cardio_id" class="form-control" required>
                                                                                <option value="">Select Cardio</option>
                                                                                @foreach($cardioData as $cardio)
                                                                                    <option value="{{ $cardio['id'] }}" 
                                                                                        @if($multipleAnsForCardio['cardio_and_muscle_id']==$cardio['id'] ) 
                                                                                            selected 
                                                                                        @endif 
                                                                                        style="font-weight: bold;">
                                                                                        {{ $cardio['title'] }}
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
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnsForCardio">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
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
                                            </div>
                                        @endif
                                        
                                        
                                        @if($quizData['answer_type'] === 'multiple' && $quizData['ques_type'] === 1 && $quizData['ques_for'] === 'musclestrengthening' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnsForMuscle">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body multipleAnsForMuscle">
                                                    <div class="repeater">
                                                        <div data-repeater-list="multipleAnsForMuscle">
                                                            <label for="answer">Add Answers</label><br>
                                                            <label for="muscle_id">Muscle Strengthening Type</label>
                                                            @foreach($quizData['answers'] as $multipleAnsForMuscle)
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6 mb-3">
                                                                        <input type="hidden" class="item-id" name="id" value="{{ $multipleAnsForMuscle['id'] }}" />
                                                                        <select name="muscle_id" id="muscle_id" class="form-control">
                                                                            <option value="">Muscle Strengthening</option>
                                                                            @foreach($muscleStrengtheningData as $muscleStrengthening)
                                                                            <option value="{{ $muscleStrengthening['id'] }}" 
                                                                                @if($multipleAnsForMuscle['cardio_and_muscle_id']==$muscleStrengthening['id'])
                                                                                    selected 
                                                                                @endif 
                                                                                style="font-weight: bold;">{{ $muscleStrengthening['title'] }}
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
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnsForMuscle">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
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
                                            </div>
                                        @endif
                                        
                                        
                                        @if($quizData['answer_type'] === 'multiple' && $quizData['ques_type'] === 1 && $quizData['ques_for'] === 'level' && !empty($quizData['answers']))
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnsForLevel">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body multipleAnsForLevel">
                                                    <div class="repeater">
                                                        <div data-repeater-list="multipleAnsForLevel">
                                                            <label for="answer">Add Answers</label>
                                                            @foreach($quizData['answers'] as $multipleAnsForLevel)
                                                            <div data-repeater-item>
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group">
                                                                            <input type="hidden" class="item-id" name="id" value="{{ $multipleAnsForLevel['id'] }}" />
                                                                            <input type="text" title="Please enter answer." id="level" name="level" class="form-control" placeholder="Type Answer" value="{{ $multipleAnsForLevel['ques_answers'] }}" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <input type="text" title="Please enter answer points." id="points" name="points" class="form-control" placeholder="Enter Points" value="{{ $multipleAnsForLevel['ans_points'] }}" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-2 mt-1">
                                                                        <div class="form-group">
                                                                            <button type="button" class="btn btn-danger btn-sm" data-repeater-delete>X</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm mt-2 float-right" data-repeater-create>Add Answer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="card card-primary mt-2 multiple">
                                                <div class="card-header multipleAnsForLevel">
                                                    <h3 class="card-title">
                                                        Multiple Type Answers
                                                    </h3>
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
                                        @endif

                                        {{-- Input type Answer --}}
                                        @if($quizData['answer_type'] === 'userInput' && $quizData['ques_type'] === 0 && $quizData['ques_for'] === 'user')
                                            <div class="card card-primary mt-2 input">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        Input Type Answers
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-4">
                                                            <div class="form-group">
                                                                <label for="userQues">User Question For</label>
                                                                <input type="hidden" class="item-id" name="ans_id" value="{{ $quizData['answers'][0]['id'] }}" />
                                                                <select title="Please select an option from dropdown." name="userQues" id="userQues" class="form-control" required>
                                                                    <option value="" disabled> Select User Question</option>
                                                                    <option value="name" @if(strtolower($quizData['answers'][0]['ques_answers'])==="name" ) selected @endif> Name</option>
                                                                    <option value="current_weight" @if(strtolower($quizData['answers'][0]['ques_answers'])==="current_weight" ) selected @endif> Current Weight</option>
                                                                    <option value="desire_weight" @if(strtolower($quizData['answers'][0]['ques_answers'])==="desire_weight" ) selected @endif> Desired Weight</option>
                                                                    <option value="height" @if(strtolower($quizData['answers'][0]['ques_answers'])==="height" ) selected @endif> Height</option>
                                                                    <option value="email" @if(strtolower($quizData['answers'][0]['ques_answers'])==="email" ) selected @endif> Email</option>
                                                                    <option value="age" @if(strtolower($quizData['answers'][0]['ques_answers'])==="age" ) selected @endif> Age</option>
                                                                    <option value="gender" @if(strtolower($quizData['answers'][0]['ques_answers'])==="gender" ) selected @endif> Gender</option>
                                                                    <option value="whatsapp" @if(strtolower($quizData['answers'][0]['ques_answers'])==="whatsapp" ) selected @endif> Whatsapp</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-4">
                                                            <label for="answer">Label</label>
                                                            <div class="form-group">
                                                                <input type="text" title="Please enter Label you want to take input from user." id="answer" name="answer" class="form-control" placeholder="Enter placeholder for user input." value="{{ $quizData['answers'][0]['label'] }}" required>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-4">
                                                            <label for="isNumericAnswer">Accept Numeric Value</label>
                                                            <div class="form-group">
                                                                <input type="checkbox" title="Please select if you want answer in numeric value from user." id="isNumericAnswer" name="isNumericAnswer" class="form-control" value="1" style="width: 20px; height:20px;" 
                                                                    @if($quizData['answers'][0]['is_numeric']==='1' )
                                                                        checked 
                                                                    @endif
                                                                >
                                                                <input type="hidden" name="isNumericAnswer" value="{{ $quizData['answers'][0]['is_numeric'] }}">
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-4" style="display: flex;">
                                                            <label for="haveInstruction">Have Instruction?</label> &nbsp; &nbsp;
                                                            <input type="checkbox" title="Please select if quiz has instruction." id="haveInstruction" name="haveInstruction" class="form-control" value="yes" 
                                                                @if($quizData['have_instruction']===1 ) 
                                                                    checked 
                                                                @endif 
                                                                style="width: 20px; height: 20px;">
                                                            <input type="hidden" name="haveInstruction" value="{{ $quizData['have_instruction'] }}">
                                                        </div>

                                                        <div class="col-lg-8" id="instructionDiv" style="display: none;">
                                                            <label for="instructionMessage">Instruction</label> &nbsp; &nbsp;
                                                            <input type="text" title="Please enter the message you want to display as an instruction." id="instructionMessage" name="instructionMessage" class="form-control" value="{{ $quizData['instruction_message'] }}" placeholder="Enter small instruction message for user." required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
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
                                            
                                        @endif

                                        {{-- Transition --}}

                                        @if( $quizData['is_have_transition']===1 && $quizData['transition_logic']==='No') 
                                            <div class="col-lg-4 commonTransition">
                                                <div class="form-group">
                                                    <label for="common_transition_id">Select Transition</label>

                                                    <!-- Hidden input to store the selected transition IDs in order -->
                                                    <input type="hidden" name="selected_transition_ids" id="selected_transition_ids" value="{{ isset($quizData['answers'][0]['transition_id']) ? $quizData['answers'][0]['transition_id'] : '' }}">

                                                    <!-- Display selected transitions in the order stored in the database -->
                                                    <div id="selected-transitions">
                                                        <strong>Current Selected Transitions:</strong>
                                                        <ul>
                                                            @if(isset($quizData['answers'][0]['transition_id']))
                                                            @php
                                                            $storedIds = explode("|", $quizData['answers'][0]['transition_id']);
                                                            @endphp
                                                            @foreach ($storedIds as $id)
                                                                @foreach ($transitions as $transition)
                                                                    @if ($transition['id'] == $id)
                                                                        <li>{{ $transition['title'] }}</li>
                                                                    @endif
                                                                @endforeach
                                                            @endforeach
                                                            @endif
                                                        </ul>
                                                    </div>

                                                    <div class="select2-purple">
                                                        <select name="common_transition_id[]" id="common_transition_id" class="form-control select2" multiple>
                                                            <option value="">Select Transition</option>
                                                            @foreach ($transitions as $transition)
                                                            <option value="{{ $transition['id'] }}">{{ $transition['title'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <small class="form-text text-muted">If you want to change the order or remove any transitions, please reselect all transitions.</small>
                                                </div>
                                            </div>
                                        @else
                                        <div class="col-lg-4 commonTransition">
                                            <div class="form-group">
                                                <label for="common_transition_id">Select Transition</label>
                                                
                                                <!-- Hidden input to store the selected transition IDs in order -->
                                                <input type="hidden" name="selected_transition_ids" id="selected_transition_ids" value="">

                                                <!-- Display selected transitions in the order stored in the database -->
                                                <div id="selected-transitions">
                                                    <strong>Current Selected Transitions:</strong>
                                                    <ul></ul>
                                                </div>
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
                                        @endif
                                    </div>
                                </div>
                                {{-- new end  --}}
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="checkbox" id="sales_page" name="sales_page" style="width: 20px; height:20px;" @if($quizData['is_sales_page']===1) checked @endif>
                                            <label for="sales_page"> Sales Page</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="checkbox" name="is_turnstile_enabled" value="1" style="width: 20px; height:20px;" @if($quizData['is_turnstile_enabled']===1) checked @endif>
                                            <label> Enable Turnstile on this question </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input type="checkbox" id="is_google_analytics" name="is_google_analytics" style="width: 20px; height:20px;" @if($quizData['is_google_analytics']===1) checked @endif>
                                            <label for="is_google_analytics"> Want to Add Amplitude Tracking Word?</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row googgle-analytics">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="google_analytic_script"> Enter Amplitude Tracking Word</label>
                                            <input type="text"  name="google_analytic_script" id="google_analytic_script" class="form-control" @if(!empty($quizData['google_analytic_script'])) value= "{{ $quizData['google_analytic_script'] }}" @endif required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" id="is_active" name="is_active" style="width: 20px; height:20px;" @if(isset($quizData['is_active']) && $quizData['is_active'] == 1) checked @endif>
                                    <label for="is_active"> Is Active</label>
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
                            required: "Please enter recipe title",
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
        let isUpdatingForm = false;

        function updateFormVisibility() {
            isUpdatingForm = true;

            const checkedQuesType = document.querySelector('input[name="ques_type"]:checked')?.value || null;
            const checkedQuesFor = document.querySelector('input[name="ques_for"]:checked')?.value || null;
            const checkedHaveTransition = document.querySelector('input[name="have_transition"]:checked')?.value || null;
            if (checkedQuesType == 1 && checkedHaveTransition == "on") {
                $('#trans_logic_no').prop("checked", true).trigger("click");
            }
            const checkedTransLogic = document.querySelector('input[name="trans_logic"]:checked')?.value || null;
            if(checkedTransLogic === "Yes"){
                $('#singleType').prop("checked", true).trigger("click");
            }
            const checkedAnswerType = document.querySelector('input[name="answer_type"]:checked')?.value || null;
            const checkedAnswerFormat = document.querySelector('input[name="answer_format"]:checked')?.value || null;
            const checkedAnotherQues = document.querySelector('input[name="another_ques"]:checked')?.value || null;

            
            $(".program, .profile").hide();
            $(".inputRadio, .singleRadio, .multipleRadio").hide();
            $(".commonTransition, .transLogic, .another_ques, .answerFormat").hide();
            $(".single, .multiple, .input").hide();
            $(".withTransition, .withTransitionImg, .withoutTransition, .withoutTransitionImg, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multipleAnswersWithoutPoints, .multipleAnswersWithoutPointsImg, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel").hide();
            $(".commonTransition").hide();
            $(".withTransition, .singleAnsForCardio, .singleAnsForMuscle, .singleAnsForLevel, .multiple, .multipleAnsForCardio, .multipleAnsForMuscle, .multipleAnsForLevel, .multipleAnsProgramPoints, .input, .info, .imageAnswerRepeater, .user-info, .trainer-info").hide();
            $('.transLogicYes').show();


            if (checkedQuesType == 0) {
                $("#profile").prop("checked", true).trigger("click");
                $(".profile").show();
                if (checkedQuesFor === "basic") {
                    $('#basic').prop("checked", true).trigger("click");
                    $(".multipleRadio, .singleRadio").show();
                    $(".answerFormat").show();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $(".transLogic").show();
                        if (checkedTransLogic == "Yes") {
                            $('#trans_logic_yes').prop("checked", true).trigger("click");
                            $('.multipleRadio').hide();
                            if (checkedAnswerType === "single") {
                                $('.single').show();
                                $('#singleType').prop("checked", true).trigger("click");
                                if (checkedAnswerFormat === "image") {
                                    $('#imageAnswer').prop("checked", true);
                                    $('.withTransitionImg').show();
                                    $('.imageAnswerRepeater').show();
                                    console.log("Type 0 - Basic - Trans 1 - Logic yes - Single - Image");
                                } else {
                                    $('#imageAnswer').prop("checked", false);
                                    $('.withTransition').show();
                                    console.log("Type 0 - Basic - Trans 1 - Logic yes - Single - Null");
                                }
                            }
                        } else if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "single") {
                                $('.single').show();
                                $('#singleType').prop("checked", true).trigger("click");
                                if (checkedAnswerFormat === "image") {
                                    $('#imageAnswer').prop("checked", true);
                                    $('.withoutTransitionImg').show();
                                    $('.imageAnswerRepeater').show();
                                    console.log("Type 0 - Basic - Trans 1 - Logic no - Single - Image");
                                } else {
                                    $('#imageAnswer').prop("checked", false);
                                    $('.withoutTransition').show();
                                    console.log("Type 0 - Basic - Trans 1 - Logic no - Single - Null");
                                }
                            } else if (checkedAnswerType === "multiple") {
                                $('.multiple').show();
                                $('#multipleType').prop("checked", true).trigger("click");
                                if (checkedAnswerFormat === "image") {
                                    $('#imageAnswer').prop("checked", true);
                                    $('.multipleAnswersWithoutPointsImg').show();
                                    $('.imageAnswerRepeater').show();
                                    console.log("Type 0 - Basic - Trans 1 - Logic no - Multiple - Image");
                                } else {
                                    $('#imageAnswer').prop("checked", false);
                                    $('.multipleAnswersWithoutPoints').show();
                                    console.log("Type 0 - Basic - Trans 1 - Logic no - Multiple - Null");
                                }
                            }
                        }
                    } else {
                        $('#have_transition').prop("checked", false).trigger("change");
                        
                        if (checkedAnswerType === "single") {
                            $('#singleType').prop("checked", true).trigger("click");
                            $('.single').show();
                            if (checkedAnswerFormat === "image") {
                                $('#imageAnswer').prop("checked", true);
                                $('.withoutTransitionImg').show();
                                $('.imageAnswerRepeater').show();
                                console.log("Type 0 - Basic - Trans 0 - Single - Image");
                            } else {
                                $('#imageAnswer').prop("checked", false);
                                $('.withoutTransition').show();
                                console.log("Type 0 - Basic - Trans 0 - Single - Null");
                            }
                        } else if (checkedAnswerType === "multiple") {
                            $('#multipleType').prop("checked", true).trigger("click");
                            $('.multiple').show();
                            if (checkedAnswerFormat === "image") {
                                $('#imageAnswer').prop("checked", true);
                                $('.multipleAnswersWithoutPointsImg').show();
                                $('.imageAnswerRepeater').show();
                                console.log("Type 0 - Basic - Trans 0 - Multiple - Image");
                            } else {
                                $('#imageAnswer').prop("checked", false);
                                $('.multipleAnswersWithoutPoints').show();
                                console.log("Type 0 - Basic - Trans 0 - Multiple - Null");
                            }
                        }
                    }

                } else if (checkedQuesFor === "trainer") {
                    $('#trainer').prop("checked", true).trigger("click");
                    $(".singleRadio, .trainer-info").show();
                    $(".multipleRadio, .answerFormat").hide();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $(".transLogic").show();
                        if (checkedTransLogic == "Yes") {
                            $('#trans_logic_yes').prop("checked", true).trigger("click");
                            if (checkedAnswerType === "single") {
                                $('.single').show();
                                $('#singleType').prop("checked", true).trigger("click");
                                $('#imageAnswer').prop("checked", false);
                                $('.withTransition').show();
                                console.log("Type 0 - trainer - Trans 1 - Logic yes - Single - Null");
                            }
                        } else if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "single") {
                                $('.single').show();
                                $('#singleType').prop("checked", true).trigger("click");
                                $('#imageAnswer').prop("checked", false);
                                $('.withoutTransition').show();
                                console.log("Type 0 - trainer - Trans 1 - Logic no - Single - Null");
                            }
                        }
                    } else {
                        $('#have_transition').prop("checked", false).trigger("change");
                        
                        if (checkedAnswerType === "single") {
                            $('#singleType').prop("checked", true).trigger("click");
                            $('.single').show();
                            $('#imageAnswer').prop("checked", false);
                            $('.withoutTransition').show();
                            console.log("Type 0 - Basic - Trans 0 - Single - Null");
                        }
                    }
                } else if (checkedQuesFor === "user") {
                    $('#user').prop("checked", true).trigger("click");
                    $('.inputRadio, .user-info').show();
                    $('.transLogicYes').hide();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $('.transLogic').show();
                        if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "userInput") {
                                $('.input').show();
                                console.log("Type 0 - User - Trans 1 - Logic no - UserInput - Null");
                            }
                        }
                    } else {
                        $('#have_transition').prop("checked", false).trigger("change");
                        if (checkedAnswerType === "userInput") {
                            $('.input').show();
                            console.log("Type 0 - User - Trans 0 - UserInput - Null");
                        }
                    }

                } else if (checkedQuesFor === "steps_goal") {
                    $('#stepsGoal').prop("checked", true).trigger("click");
                    $('.singleRadio').show();
                    $('.single').show();
                    $('.transLogicYes').hide();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $('.transLogic').show();
                        if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "single") {
                                $('.withoutTransition').show();
                                console.log("Type 0 - Steps_goal - Trans 1 - Logic no - Single - Null");
                            }
                        }
                    } else {
                        $('#have_transition').prop("checked", false).trigger("change");
                        if (checkedAnswerType === "single") {
                            $('.withoutTransition').show();
                            console.log("Type 0 - Steps_goal - Trans 0 - Single - Null");
                        }
                    }

                } else if (checkedQuesFor === "activity_level") {
                    $('#activityLevel').prop("checked", true).trigger("click");
                    $('.singleRadio').show();
                    $('.single').show();
                    $('.transLogicYes').hide();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $('.transLogic').show();
                        if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "single") {
                                $('.withoutTransition').show();
                                console.log("Type 0 - Activity_level - Trans 1 - Logic no - Single - Null");
                            }
                        }
                    } else {
                        $('#have_transition').prop("checked", false).trigger("change");
                        if (checkedAnswerType === "single") {
                            $('.withoutTransition').show();
                            console.log("Type 0 - Activity_level - Trans 0 - Single - Null");
                        }
                    }
                }

            } else if (checkedQuesType == 1) {
                $("#use_in_program").prop("checked", true).trigger("click");
                $(".program").show();
                $('.transLogicYes').hide();
                if (checkedQuesFor === "cardio") {
                    $('#cardio').prop("checked", true).trigger("click");
                    $(".singleRadio, .multipleRadio").show();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $('.transLogic').show();
                        if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "single") {
                                $('.single').show();
                                $('.singleAnsForCardio').show();
                                console.log("Type 1 - Cardio - Trans 1 - Logic no - Single - Null");
                            } else if (checkedAnswerType === "multiple") {
                                $('.multiple').show();
                                $('.multipleAnsForCardio').show();
                                console.log("Type 1 - Cardio - Trans 1 - Logic no - Multiple - Null");
                            }
                        }
                    } else {
                        $('#have_transition').prop("checked", false).trigger("change");
                        if (checkedAnswerType === "single") {
                            $('.single').show();
                            $('.singleAnsForCardio').show();
                            console.log("Type 1 - Cardio - Trans 0 - Single - Null");
                        } else if (checkedAnswerType === "multiple") {
                            $('.multiple').show();
                            $('.multipleAnsForCardio').show();
                            console.log("Type 1 - Cardio - Trans 0 - Multiple - Null");
                        }
                    }
                } else if (checkedQuesFor === "musclestrengthening") {
                    $('#musclestrengthening').prop("checked", true).trigger("click");
                    $(".singleRadio, .multipleRadio").show();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $('.transLogic').show();
                        if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "single") {
                                $('.single').show();
                                $('.singleAnsForMuscle').show();
                                console.log("Type 1 - MuscleStrengthening - Trans 1 - Logic no - Single - Null");
                            } else if (checkedAnswerType === "multiple") {
                                $('.multiple').show();
                                $('.multipleAnsForMuscle').show();
                                console.log("Type 1 - MuscleStrengthening - Trans 1 - Logic no - Multiple - Null");
                            }
                        }
                    } else {
                        $('#have_transition').prop("checked", false).trigger("change");
                        if (checkedAnswerType === "single") {
                            $('.single').show();
                            $('.singleAnsForMuscle').show();
                            console.log("Type 1 - MuscleStrengthening - Trans 0 - Single - Null");
                        } else if (checkedAnswerType === "multiple") {
                            $('.multiple').show();
                            $('.multipleAnsForMuscle').show();
                            console.log("Type 1 - MuscleStrengthening - Trans 0 - Multiple - Null");
                        }
                    }
                } else if (checkedQuesFor === "level") {
                    $('#level').prop("checked", true).trigger("click");
                    $(".singleRadio, .multipleRadio").show();
                    if (checkedHaveTransition == "on") {
                        $('#have_transition').prop("checked", true).trigger("change");
                        $('.transLogic').show();
                        if (checkedTransLogic == "No") {
                            $('#trans_logic_no').prop("checked", true).trigger("click");
                            $('.commonTransition').show();
                            if (checkedAnswerType === "single") {
                                $('.single').show();
                                $('.singleAnsForLevel').show();
                                console.log("Type 1 - Level - Trans 1 - Logic no - Single - Null");
                            } else if (checkedAnswerType === "multiple") {
                                $('.multiple').show();
                                $('.multipleAnsForLevel').show();
                                console.log("Type 1 - Level - Trans 1 - Logic no - Multiple - Null");
                            }
                        }
                    } else {
                        if (checkedAnswerType === "single") {
                            $('.single').show();
                            $('#singleType').prop("checked", true).trigger("click");
                            console.log("Type 1 - Level - Trans 0 - Single - Null");
                        } else if (checkedAnswerType === "multiple") {
                            $('.multiple').show();
                            $('#multipleType').prop("checked", true).trigger("click");
                            console.log("Type 1 - Level - Trans 0 - Multiple - Null");
                        }
                    }
                }

            } else if (checkedQuesType == 2) {
                $("#none").prop("checked", true).trigger("click");
                $(".multipleRadio, .singleRadio").show();
                if (checkedHaveTransition == "on") {
                    $('#have_transition').prop("checked", true).trigger("change");
                    $('.transLogic').show();
                    if (checkedTransLogic == "Yes") {
                        $('#trans_logic_yes').prop("checked", true).trigger("click");
                        $('.multipleRadio').hide();
                        if (checkedAnswerType === "single") {
                            $('.single').show();
                            $('.withTransition').show();
                            console.log("Type 2 - Basic - Trans 1 - Logic yes - Single - Null");
                        }
                    } else if (checkedTransLogic == "No") {
                        $('#trans_logic_no').prop("checked", true).trigger("click");
                        $('.commonTransition').show();
                        if (checkedAnswerType === "single") {
                            $('.single').show();
                            $('.withoutTransition').show();
                            console.log("Type 2 - Basic - Trans 1 - Logic no - Single - Null");
                        } else if (checkedAnswerType === "multiple") {
                            $('.multiple').show();
                            $('.multipleAnswersWithoutPoints').show();
                            console.log("Type 2 - Basic - Trans 1 - Logic no - Multiple - Null");
                        }
                    }
                } else {
                    if (checkedAnswerType === "single") {
                        $('.single').show();
                        $('.withoutTransition').show();
                        console.log("Type 2 - Basic - Trans 0 - Single - Null");
                    } else if (checkedAnswerType === "multiple") {
                        $('.multiple').show();
                        $('.multipleAnswersWithoutPoints').show();
                        console.log("Type 2 - Basic - Trans 0 - Multiple - Null");
                    }
                }
            }
            isUpdatingForm = false;
        }

        $(window).on("load", function () {
            updateFormVisibility();
        });

        $(document).on("change", 'input[name="ques_type"], input[name="ques_for"], input[name="have_transition"], input[name="trans_logic"], input[name="answer_type"], input[name="answer_format"], input[name="another_ques"]', function () {
            if (!isUpdatingForm) {
                updateFormVisibility();
            }
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
        if ($('#is_google_analytics').is(':checked')) {
            $('.googgle-analytics').show();
        } else {
            $('.googgle-analytics').hide();
        }
        $('#is_google_analytics').change(function() {
            $('.googgle-analytics').toggle(this.checked);
        });

        // Show/hide select question dropdown
        if ($('#another_ques').is(':checked')) {
            $('.selectQues').show();
        } else {
            $('.selectQues').hide();
        }
        $('#another_ques').change(function() {
            $('.selectQues').toggle(this.checked);
        });
        
        if ($('#haveInstruction').is(':checked')) {
            $('#instructionDiv').show();
        } else {
            $('#instructionDiv').hide();
        }
        $('#haveInstruction').change(function() {
            $('#instructionDiv').toggle($(this).is(':checked'));
        });

    </script>
    <script>
        $('.repeater').repeater({
            initEmpty: false,
            isFirstItemUndeletable: false,
            show: function() {
                $(this).slideDown();
                // Reinitialize select2 for newly added items
                $(this).find('.select2').select2();
            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                }
            }
        });
    </script>

    <script>
        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
        $(document).ready(function() {
            let selectedOptions = [];
            let firstClick = true; // Flag to track the first click

            // Initialize select2
            $('#common_transition_id').select2({
                placeholder: "Select Transition",
                allowClear: true
            }).on('select2:select', function(e) {
                let selectedId = e.params.data.id;

                // Add to selected options if not already present
                if (!selectedOptions.includes(selectedId)) {
                    selectedOptions.push(selectedId);
                }

                // Update displayed options
                displaySelectedOptions();
                // Update hidden input with the selected options in order
                $('#selected_transition_ids').val(selectedOptions.join('|'));
            }).on('select2:unselect', function(e) {
                let unselectedId = e.params.data.id;

                // Remove the unselected option
                selectedOptions = selectedOptions.filter(id => id !== unselectedId);

                // Update displayed options
                displaySelectedOptions();
                // Update hidden input with the selected options in order
                $('#selected_transition_ids').val(selectedOptions.join('|'));
            });

            function displaySelectedOptions() {
                // Clear and update the displayed list
                const displayList = $('#selected-transitions ul');
                displayList.empty();

                // Add selected options in the order they were selected
                selectedOptions.forEach(id => {
                    const optionText = $(`#common_transition_id option[value="${id}"]`).text();
                    displayList.append(`<li>${optionText}</li>`);
                });
            }

            // Confirmation alert before opening the select2 dropdown, only on the first click
            $('#common_transition_id').on('select2:opening', function(e) {
                if (firstClick && selectedOptions.length >= 0) {
                    const confirmChange = confirm("You have existing selections. Changing them will require you to reselect all transitions. Do you want to proceed?");
                    if (!confirmChange) {
                        e.preventDefault(); // Prevent the dropdown from opening
                        return false;
                    }
                    firstClick = false; // Set flag to false after the first click
                }
            });

            // Rearrange selected options before form submission
            $('#quickForm').on('submit', function(event) {
                // Get the select element
                const selectElement = $('#common_transition_id');

                // Rearrange options based on selectedOptions order
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