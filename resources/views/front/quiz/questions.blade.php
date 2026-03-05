<?php

use App\Models\Quiz;
use Illuminate\Support\Facades\Session; ?>
<x-home-layout title="Quiz">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <style>
        .message {
            color: red;
            display: none;
            margin-top: 10px;
        }
        .btn-custom {
            background: #FFEC68;
            color: #1A1B1C;
            font-weight: 600;
            font-size: 16px;
            border: 2px solid #0F1214;
            box-shadow: 2px 4px #000;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .main-fixed {
            position: fixed;
            padding: 0 16px;
            max-width: 375px;
            width: 100%;
            bottom: 88px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99;
        }

        form#quizForm:has(.main-fixed) .main-fixed-btn button.btn-link.btn-custom {
            background-color: white !important;
            color: #00120B !important;
            box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.05), 0px 12px 24px -8px rgba(0, 0, 0, 0.1), 0px 2px 4px 0px rgba(0, 0, 0, 0.05) !important;
        }

        .bottom-checkbox-2 {
            appearance: none;
            -webkit-appearance: none;
            height: 24px;
            width: 24px;
            border-radius: 6px;
            margin: 0;
            margin-right: 12px;
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            position: relative;
        }
        .bottom-checkbox-2:checked {
            background-color: #2A3539;
            border: 1px solid #2A3539;
        }

        .bottom-checkbox-2:checked::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left:50%;
            transform: translate(-50%, -50%);
            height: 100%;
            width: 100%;
            color: #ffffff;
            font-size: 18px;
        }

        .main-fixed {
            position: fixed;
            padding: 0 16px;
            max-width: 375px;
            width: 100%;
            bottom: 88px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99;
        }

        form#quizForm:has(.main-fixed) .main-fixed-btn button.btn-link.btn-custom {
            background-color: white !important;
            color: #00120B !important;
            box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.05), 0px 12px 24px -8px rgba(0, 0, 0, 0.1), 0px 2px 4px 0px rgba(0, 0, 0, 0.05) !important;
        }

        .main-fixed {
            position: fixed;
            padding: 0 16px;
            max-width: 375px;
            width: 100%;
            bottom: 88px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99;
        }

        form#quizForm:has(.main-fixed) .main-fixed-btn button.btn-link.btn-custom {
            background-color: white !important;
            color: #00120B !important;
            box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.05), 0px 12px 24px -8px rgba(0, 0, 0, 0.1), 0px 2px 4px 0px rgba(0, 0, 0, 0.05) !important;
        }
        #quizForm .details-body.reveal-box .details .use-in-profile.user.userInput input.wp-input {
            padding-left: 45px !important;
        }
        /* #quizForm .details-body.reveal-box .details .use-in-profile.trainer.single {
            grid-template-columns: repeat(2, 1fr);
        } */
        #quizForm .details-body.reveal-box .details .use-in-profile .details-select .trainer-img{
            z-index: 1;
            width: 40px;
            height: 40px;
            border-radius: 20px;
            flex: none;
            order: 1;
            flex-grow: 0;
        }
        #quizForm .details-body.reveal-box .details .use-in-profile .details-select label.center-label.trainer-label {
            align-content: center;
        }
    </style>
    @endsection
    <section class="main-section">
        <div class="container">
            <div class="inner-section">
                <div class="main-header">
                    <div class="inner-header age-header">
                        <div class="header-top">
                            @if(isset($currentQuestion) && $currentQuestion['quiz_position'] != 1)
                                <div class="back-btn">
                                    <?php
                                    $sessionId = Session::get('sessionId') ?? session::get('quiz_session_id');
                                    ?>
                                    <a href="{{ route('quiz.previousQuestion', ['question_id' => $currentQuestion['id'], 'session_id' => $sessionId ?? 'NOT_ADDED_YET']) }}">
                                        <img class="arrow-back" src="{{ asset('webAssets/images/quiz/arrow-back.png') }}" alt="arrow-back">
                                    </a>
                                </div>
                            @endif
                            <div class=""><img class="logo" src="{{ asset('webAssets/images/svgs/logo.svg') }}" alt="logo"></div>
                            <div class="step-text">
                                <p class="step-title" style="color:{{ $currentQuizGroupColor }};">{{ $currentQuizGroupTitle }}</p>
                            </div>
                        </div>

                        <div class="steps">
                            STEP
                            <span class="currunt-step">{{ $answeredQuestionsInGroup }}</span>/<span class="total-step">{{ $totalQuestionsInGroup }}</span>
                        </div>
                        <div class="step-bar-top">
                            @foreach($quizProgress as $progress)
                            <div class="step-bar">
                                <div class="step-dot-group">
                                    <div class="step-dot">
                                        <div class="step-progress" style="background:{{ $progress['color'] }}; width: {{ $progress['percentage'] }}%;"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <form id="quizForm" action="{{ route('quiz.saveAnswer', $currentQuestion['id']) }}" method="post">
                        {{ csrf_field() }}
                        <div class="details-body reveal-box">
                            <div class="details">
                                @if(!is_null($currentQuestion['ques_image']) && $currentQuestion['ques_image'] !== '')
                                <div class="details-img">
                                    <img src="{{ asset('storage/'.$currentQuestion['ques_image']) }}" alt="">
                                </div>
                                @endif
                                <h4 class="details-question">
                                    <?php
                                    $userAnsweredData = Session::get('userAnsweredData');
                                    $currentWeight = 0;
                                    $height = 0;
                                    if (!is_null($userAnsweredData)) {
                                        foreach ($userAnsweredData as $userAnswer) {
                                            if ($userAnswer['key'] === 'current_weight') {
                                                $currentWeight = $userAnswer['value'];
                                            }
                                            if($userAnswer['key'] === 'height') {
                                                $height = $userAnswer['value'];
                                            }
                                            if ($userAnswer['key'] === 'desire_weight') {
                                                $currentQuestionTitle = str_replace('{weight}', $userAnswer['value'], $currentQuestion['ques_title']);
                                            }
                                        }
                                    }
                                    ?>
                                    @if(isset($currentQuestionTitle) && !empty($currentQuestionTitle))
                                    {{ $currentQuestionTitle }}
                                    @else
                                    {{ $currentQuestion['ques_title'] }}
                                    @endif
                                </h4>
                                @if(isset($currentQuestion['ques_description']) && !empty($currentQuestion['ques_description']))
                                <p class="ques_description">{{ $currentQuestion['ques_description'] }}</p>
                                @endif
                                <?php

                                if ($currentQuestion['ques_type'] == 0) {
                                    $quesType = "use-in-profile";
                                } else if ($currentQuestion['ques_type'] == 1) {
                                    $quesType = "use-in-program";
                                } else {
                                    $quesType = "use-in-none";
                                }
                                if (is_null($currentQuestion['answer_format'])) {
                                    $answerFormat = "";
                                } else {
                                    $answerFormat = $currentQuestion['answer_format'];
                                }

                                $class = $quesType . " " . $currentQuestion['ques_for'] . " " . $currentQuestion['answer_type'] . " " . $answerFormat;

                                $quesFor = $currentQuestion['ques_for'];
                                if (!is_null($currentQuestion['instruction_message'])) {
                                    $instructionMessage = $currentQuestion['instruction_message'];

                                ?>
                                    <input type="hidden" id="instructionMsg" name="instructionMsg" value="{{ $instructionMessage }}">
                                <?php
                                }
                                ?>



                                <ul class="{{ $class }}" style="justify-content: center;">
                                    <input type="hidden" id="questionId" name="questionId" value="{{ $currentQuestion['id'] }}">
                                    <input type="hidden" id="quizGroupId" name="quizGroupId" value="{{ $currentQuestion['quiz_group_id'] }}">
                                    <input type="hidden" id="answerType" name="answerType" value="{{ $currentQuestion['answer_type'] }}">
                                    <input type="hidden" id="questionTitle" name="questionTitle" value="{{ $currentQuestion['ques_title'] }}">
                                    @foreach ($currentQuestion['answers'] as $index => $answer)
                                        @if ($answer['answer_type'] === 'multiple')
                                        <li class="details-select no-padding">
                                            @if (!is_null($answer['answer_format']) && $answer['answer_format'] === 'image')
                                            <img src="{{ url('storage/' . $answer['answer_img']) }}" alt="#">
                                            @endif

                                            @php
                                            $isChecked = false;

                                            if (!empty($userAnswerBySessionId)) {
                                                $userAnswers = is_iterable($userAnswerBySessionId) ? $userAnswerBySessionId : [$userAnswerBySessionId];

                                                foreach ($userAnswers as $userPrevAns) {
                                                    if (
                                                        isset($userPrevAns['question_id'], $userPrevAns['answer_id']) &&
                                                        $userPrevAns['question_id'] === $answer['question_id'] &&
                                                        $userPrevAns['answer_id'] == $answer['id']
                                                    ) {
                                                        $isChecked = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            @endphp


                                            <input type="checkbox" id="answer_{{ $answer['id'] }}" name="answer_id[]" class="form-check-input"
                                                @if ($isChecked) checked @endif
                                                value="{{ $answer['id'] }}">
                                            <label class="center-label" for="answer_{{ $answer['id'] }}">{{ $answer['ques_answers'] }}</label>
                                        </li>
                                        @endif

                                        @if($answer['answer_type'] === 'single')
                                            <li class="details-select no-padding" @if ($quesFor === 'trainer')
                                            style="display: flex;justify-content: space-between;"
                                            @endif>
                                                @if($quesFor === 'trainer')
                                                    @php
                                                        $trainerImages = [
                                                            asset('webAssets/images/quiz/trainer1.png'),
                                                            asset('webAssets/images/quiz/trainer2.png'),
                                                            asset('webAssets/images/quiz/trainer3.png'),
                                                            asset('webAssets/images/quiz/trainer4.png'),
                                                        ];
                                                    @endphp
                                                    {{-- show one of the 4 static images for the first 4 answers only --}}
                                                    @if(isset($trainerImages[$index]))
                                                        <img src="{{ $trainerImages[$index] }}" alt="#" style="z-index: 1;" @if ($quesFor === 'trainer') class ="trainer-img" @endif>
                                                    @endif
                                                @else
                                                    @if(!is_null($answer['answer_format']) && $answer['answer_format'] === 'image')
                                                        <img src="{{ url('storage/'. $answer['answer_img']) }}" alt="#">
                                                    @endif
                                                @endif
                                                @if($quesFor === 'steps_goal' || $quesFor === 'activity_level' || $quesFor === 'trainer')
                                                    <input type="radio" id="answer_{{ $answer['id'] }}" name="answer_id" class="form-check-input" @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) checked @endif value="{{ $answer['id'] }},{{ $index }}">
                                                    <input type="hidden" name="answer_for" class="form-check-input" value="{{ $quesFor }}">
                                                @else
                                                    <input type="radio" id="answer_{{ $answer['id'] }}" name="answer_id" class="form-check-input" @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) checked @endif value="{{ $answer['id'] }}">
                                                @endif
                                                    <label class="center-label @if ($quesFor === 'trainer') trainer-label @endif" for="answer_{{ $answer['id'] }}">{{ $answer['ques_answers'] }}</label>
                                            </li>
                                        @endif

                                        @if($answer['answer_type'] == 'userInput')
                                            @if($answer['is_numeric'] == '0' && $answer['ques_answers'] == 'gender')
                                                <li class="details-select no-padding gender">
                                                    <img src="{{ url('storage/quiz/answer/image/male.png') }}" alt="#">
                                                    <input type="radio" name="answer" id="answerMale" value="Male" @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['userReferenceAnswer']['answer']==='Male' ) checked @endif>
                                                    <label class="center-label" for="answer_{{ $answer['id'] }}">Homme</label>
                                                </li>
                                                <li class="details-select no-padding gender">
                                                    <img src="{{ url('storage/quiz/answer/image/female.png') }}" alt="#">
                                                    <input type="radio" name="answer" id="answerFemale" value="Female" @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['userReferenceAnswer']['answer']==='Female' ) checked @endif>
                                                    <label class="center-label" for="answer_{{ $answer['id'] }}">Femme</label>
                                                </li>
                                                <input type="hidden" id="answer_{{ $answer['id'] }}" name="answer_id" class="goal-select" value="{{ $answer['id'] }}">
                                            @elseif($answer['ques_answers'] == 'age')
                                                <li class="details-select no-padding">
                                                    <div class="input-container">
                                                        <input type="number" id="answer_{{ $answer['id'] }}" name="answer" class="input-field" placeholder="{{ $answer['label'] }}" @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif required>
                                                        <span class="input-suffix">ans</span>
                                                    </div>
                                                    <input type="hidden" id="answer_{{ $answer['id'] }}" name="answer_id" value="{{ $answer['id'] }}">
                                                </li>
                                            @elseif($answer['is_numeric'] == '0' && $answer['ques_answers'] === 'name' || strtolower($answer['ques_answers']) === 'email')
                                                <li class="details-select no-padding">
                                                    @if (strtolower($answer['ques_answers'])=='email' )

                                                        <input @if(ucfirst($answer['ques_answers'])=='Email' ) type="email" @endif id="answer_{{ $answer['id'] }}" name="answer" class="form-control" placeholder="{{ $answer['label'] }}" @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif @if(Auth::guard('user')->check()) readonly value="{{ Auth::guard('user')->user()->email }}" @else value="{{ old('email') }}" @endif required>
                                                        <input type="hidden" name="answer_for" class="form-check-input" value="email">
                                                        <div class="input-icon">
                                                            <i class="fa-regular fa-envelope"></i>
                                                        </div>
                                                        <div class="row email email-fix">
                                                            <input type="checkbox" id="email_marketing" name="email_marketing" class="form-check-input" value="1" checked>
                                                            <label for="email_marketing">J’accepte de recevoir des conseils personnalisés, des encouragements et des offres privilégiées de la part de Reveal Club.</label>
                                                        </div>

                                                    @else
                                                        <input type="text" id="answer_{{ $answer['id'] }}" name="answer" class="form-control" placeholder="{{ $answer['label'] }}" required @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif>
                                                    @endif
                                                    <input type="hidden" id="answer_{{ $answer['id'] }}" name="answer_id" value="{{ $answer['id'] }}">
                                                </li>
                                            @elseif($answer['is_numeric'] == '1' && $answer['ques_answers'] === 'current_weight' || $answer['ques_answers'] === 'desire_weight')
                                                <li class="details-select no-padding">
                                                    <div class="input-container">
                                                        @if ($answer['ques_answers']=='current_weight' )
                                                            <input @if($answer['ques_answers']==='current_weight' ) type="number" @endif id="answer_{{ $answer['ques_answers'] }}" name="answer" class="form-control" placeholder="{{ $answer['label'] }}" required @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif>
                                                        @else
                                                            <input @if($answer['ques_answers']==='desire_weight' ) type="number" @endif id="answer_{{ $answer['ques_answers'] }}" name="answer" class="form-control" placeholder="{{ $answer['label'] }}" required @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif>
                                                        @endif
                                                        <span class="input-suffix">kg</span>
                                                    </div>
                                                    <input type="hidden" id="answer_{{ $answer['id'] }}" name="answer_id" value="{{ $answer['id'] }}">
                                                </li>
                                            @elseif($answer['is_numeric'] == '1' && $answer['ques_answers'] === 'height')
                                                <li class="details-select no-padding">
                                                    <div class="input-container">
                                                        <input @if($answer['ques_answers']==='height' ) type="number" @endif id="answer_{{ $answer['ques_answers'] }}" name="answer" class="form-control" placeholder="{{ $answer['label'] }}" required @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif>
                                                        <span class="input-suffix">cm</span>
                                                    </div>
                                                    <input type="hidden" id="answer_{{ $answer['id'] }}" name="answer_id" value="{{ $answer['id'] }}">
                                                </li>
                                            {{-- whatsapp question --}}
                                            @elseif($answer['is_numeric'] == '1' && $answer['ques_answers'] === 'whatsapp')
                                                <li class="details-select no-padding">
                                                    <input type="number" id="answer_{{ $answer['id'] }}" name="answer" class="form-control wp-input" placeholder="{{ $answer['label'] }}" required @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif>
                                                    <input type="hidden" id="answer_{{ $answer['id'] }}" name="answer_id" value="{{ $answer['id'] }}">
                                                    <div class="input-icon">
                                                        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <mask id="mask0_6099_32411" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="25">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.45601 0.511793C4.92078 0.530819 4.23096 0.572693 3.91742 0.635975C3.43881 0.732626 2.98683 0.879164 2.61095 1.07074C2.16933 1.2958 1.77352 1.58296 1.43176 1.92419C1.08921 2.26608 0.800899 2.66226 0.574929 3.10454C0.383889 3.4784 0.237434 3.92763 0.140373 4.40353C0.0758162 4.72023 0.0331208 5.41519 0.0137661 5.95388C0.00591736 6.17405 0.00189027 6.45899 0.00189027 6.5904L0 18.4077C0 18.5386 0.003986 18.8236 0.0117936 19.044C0.0308196 19.5792 0.0726932 20.269 0.136017 20.5826C0.232626 21.0611 0.379204 21.5132 0.570738 21.889C0.795844 22.3307 1.08296 22.7264 1.42419 23.0682C1.76609 23.4108 2.1623 23.6991 2.60454 23.9251C2.97845 24.1161 3.42767 24.2626 3.90353 24.3596C4.22023 24.4242 4.91523 24.4669 5.45392 24.4862C5.67401 24.4941 5.95899 24.4981 6.0904 24.4981L17.9077 24.5C18.0386 24.5 18.3236 24.4961 18.544 24.4882C19.0792 24.4691 19.7691 24.4273 20.0826 24.364C20.5612 24.2673 21.0132 24.1208 21.389 23.9293C21.8307 23.7042 22.2265 23.417 22.5682 23.0758C22.9108 22.7339 23.1991 22.3377 23.4251 21.8955C23.6161 21.5216 23.7626 21.0724 23.8596 20.5965C23.9242 20.2798 23.9669 19.5847 23.9862 19.0461C23.9941 18.8259 23.9981 18.541 23.9981 18.4096L24 6.59229C24 6.46137 23.9961 6.17635 23.9882 5.95597C23.9691 5.42078 23.9273 4.73096 23.864 4.41742C23.7674 3.93881 23.6208 3.48683 23.4293 3.11095C23.2042 2.66929 22.917 2.27352 22.5758 1.93171C22.2339 1.58925 21.8377 1.3009 21.3955 1.07493C21.0216 0.883888 20.5724 0.737392 20.0965 0.640373C19.7798 0.575815 19.0848 0.533121 18.5461 0.513807C18.3259 0.505877 18.041 0.50189 17.9096 0.50189L6.09229 0.5C5.96137 0.5 5.67635 0.503944 5.45601 0.511793Z" fill="white"></path>
                                                            </mask>
                                                            <g mask="url(#mask0_6099_32411)">
                                                            <path d="M5.45601 0.511793C4.92078 0.530819 4.23096 0.572693 3.91742 0.635975C3.43881 0.732626 2.98683 0.879164 2.61095 1.07074C2.16933 1.2958 1.77352 1.58296 1.43176 1.92419C1.08921 2.26608 0.800899 2.66226 0.574929 3.10454C0.383889 3.4784 0.237434 3.92763 0.140373 4.40353C0.0758162 4.72023 0.0331208 5.41519 0.0137661 5.95388C0.00591736 6.17405 0.00189027 6.45899 0.00189027 6.5904L0 18.4077C0 18.5386 0.003986 18.8236 0.0117936 19.044C0.0308196 19.5792 0.0726932 20.269 0.136017 20.5826C0.232626 21.0611 0.379204 21.5132 0.570738 21.889C0.795844 22.3307 1.08296 22.7264 1.42419 23.0682C1.76609 23.4108 2.1623 23.6991 2.60454 23.9251C2.97845 24.1161 3.42767 24.2626 3.90353 24.3596C4.22023 24.4242 4.91523 24.4669 5.45392 24.4862C5.67401 24.4941 5.95899 24.4981 6.0904 24.4981L17.9077 24.5C18.0386 24.5 18.3236 24.4961 18.544 24.4882C19.0792 24.4691 19.7691 24.4273 20.0826 24.364C20.5612 24.2673 21.0132 24.1208 21.389 23.9293C21.8307 23.7042 22.2265 23.417 22.5682 23.0758C22.9108 22.7339 23.1991 22.3377 23.4251 21.8955C23.6161 21.5216 23.7626 21.0724 23.8596 20.5965C23.9242 20.2798 23.9669 19.5847 23.9862 19.0461C23.9941 18.8259 23.9981 18.541 23.9981 18.4096L24 6.59229C24 6.46137 23.9961 6.17635 23.9882 5.95597C23.9691 5.42078 23.9273 4.73096 23.864 4.41742C23.7674 3.93881 23.6208 3.48683 23.4293 3.11095C23.2042 2.66929 22.917 2.27352 22.5758 1.93171C22.2339 1.58925 21.8377 1.3009 21.3955 1.07493C21.0216 0.883888 20.5724 0.737392 20.0965 0.640373C19.7798 0.575815 19.0848 0.533121 18.5461 0.513807C18.3259 0.505877 18.041 0.50189 17.9096 0.50189L6.09229 0.5C5.96137 0.5 5.67635 0.503944 5.45601 0.511793Z" fill="url(#paint0_linear_6099_32411)"></path>
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.1606 14.0796C15.9469 13.9727 14.8959 13.4557 14.6999 13.3844C14.504 13.3131 14.3615 13.2774 14.219 13.4913C14.0765 13.7053 13.6669 14.1866 13.5421 14.3292C13.4175 14.4718 13.2928 14.4897 13.079 14.3827C12.8653 14.2758 12.1765 14.0501 11.36 13.322C10.7246 12.7554 10.2956 12.0556 10.1708 11.8417C10.0462 11.6277 10.1576 11.5121 10.2646 11.4056C10.3607 11.3098 10.4783 11.156 10.5852 11.0312C10.6921 10.9064 10.7277 10.8173 10.799 10.6747C10.8702 10.532 10.8346 10.4072 10.7812 10.3003C10.7277 10.1933 10.3002 9.1415 10.1221 8.71364C9.94862 8.29696 9.77237 8.35334 9.64116 8.34681C9.51661 8.3406 9.37398 8.33929 9.23147 8.33929C9.08896 8.33929 8.8574 8.39279 8.66143 8.60668C8.4655 8.82061 7.91329 9.33764 7.91329 10.3894C7.91329 11.4412 8.67926 12.4574 8.78614 12.6C8.89303 12.7426 10.2935 14.9011 12.4378 15.8267C12.9478 16.0469 13.346 16.1783 13.6564 16.2769C14.1685 16.4395 14.6345 16.4165 15.0028 16.3615C15.4135 16.3002 16.2675 15.8445 16.4456 15.3454C16.6237 14.8462 16.6237 14.4184 16.5703 14.3292C16.5169 14.2401 16.3744 14.1866 16.1606 14.0796ZM12.2601 19.4033H12.2572C10.9813 19.4028 9.72988 19.0602 8.63817 18.4125L8.3785 18.2584L5.68729 18.9642L6.40564 16.3411L6.23654 16.0722C5.52477 14.9404 5.14885 13.6323 5.14939 12.2892C5.15095 8.36996 8.34078 5.18136 12.263 5.18136C14.1622 5.1821 15.9475 5.92247 17.29 7.26604C18.6325 8.60965 19.3714 10.3956 19.3707 12.295C19.3691 16.2145 16.1793 19.4033 12.2601 19.4033ZM18.3119 6.24514C16.6967 4.62859 14.5487 3.7379 12.2601 3.737C7.54472 3.737 3.70694 7.5733 3.70505 12.2887C3.70443 13.7961 4.09835 15.2674 4.84698 16.5643L3.6333 20.9961L8.16847 19.8069C9.41802 20.4882 10.8249 20.8472 12.2567 20.8478H12.2602H12.2602C16.9752 20.8478 20.8133 17.011 20.8152 12.2956C20.8161 10.0104 19.9271 7.86164 18.3119 6.24514Z" fill="white"></path>
                                                            </g>
                                                            <defs>
                                                            <linearGradient id="paint0_linear_6099_32411" x1="-2.57493e-05" y1="24.5" x2="-2.57493e-05" y2="0.499972" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#25CF43"></stop>
                                                            <stop offset="1" stop-color="#61FD7D"></stop>
                                                            </linearGradient>
                                                            </defs>
                                                        </svg>
                                                    </div>
                                                </li>
                                                <input type="hidden" id="isWhatsappQue" name="isWhatsappQue" value="1">
                                            @else
                                                <li class="details-select no-padding">
                                                    <input type="number" id="answer_{{ $answer['id'] }}" name="answer" class="form-control" placeholder="{{ $answer['label'] }}" required @if(!empty($userAnswerBySessionId) && $userAnswerBySessionId['question_id']===$answer['question_id'] && $userAnswerBySessionId['answer_id']==$answer['id']) value="{{ $userAnswerBySessionId['userReferenceAnswer']['value'] }}" @endif>
                                                    <input type="hidden" id="answer_{{ $answer['id'] }}" name="answer_id" value="{{ $answer['id'] }}">
                                                </li>
                                            @endif
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @if($answer['answer_type'] === 'single' || $answer['ques_answers'] == 'gender')
                        @elseif($answer['answer_type'] === 'userInput' && $answer['ques_answers'] == 'whatsapp')
                            <div class="main-fixed">
                                <button type="submit" id="supportButton" class="btn-link btn-custom white-bg"> Je veux du soutien </button>
                            </div>
                            <div class="main-fixed-btn">
                                <button type="submit" id="laterButton" class="btn-link btn-custom"> Peut-être plus tard </button>
                            </div>
                        @else
                        <!-- <center> -->
                        <div class="main-fixed-btn">
                            <button type="submit" id="nextButton" class="btn-link btn-custom">@if (strtolower($answer['ques_answers'])=='email' ) Voir mes résultats @else Continuer @endif</button>
                        </div>
                        <!-- </center> -->
                        @endif
                        @if ($currentQuestion['is_turnstile_enabled'] == 1)
                            <x-turnstile />
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div id="cookieConsentPopup" class="popup-overlay" style="display: none;">
            <div class="container">
                <div class="cookie-box">
                    <div class="cookie-header">
                        <img class="cookie-img" src="{{ asset('webAssets/images/cookie.png') }}">
                        <h5 class="cookie-title">Notre site utilise des cookies</h5>
                    </div>
                    <div class="cookie-body">
                        <p class="description">Nous utilisons des cookies pour améliorer votre expérience. En cliquant sur « Accepter tout », vous acceptez que nous les utilisions.</p>
                    </div>
                    <div class="cookie-footer">
                        <button id="cancelCookies" type="button" class="btn reject-cookie btn-link close">Ne pas accepter</button>
                        <button id="acceptCookies" type="button" class="btn accept-cookie btn-link submit">Accepter</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $(document).ready(function () {
            const checkedAnswers = $('input[type="checkbox"][name^="answer_id"]:checked');
            $.each(checkedAnswers, function (index, item) {
                const parent = $(item).closest('li.details-select');
                if (parent.length != 0) {
                    parent.addClass('bg-primary');
                }
            });
        });
    </script>
    <script>
        @if(Session::has('success'))
        toastr.options = {
            "closeButton": true,
            "progressBar": true
        }
        toastr.success("{{ session('success') }}");
        @endif

        @if(Session::has('error'))
        toastr.options = {
            "closeButton": true,
            "progressBar": true
        }
        toastr.error("{{ session('error') }}");
        @endif
    </script>

    <script type="text/javascript">
        window.history.forward();

        function noBack() {
            window.history.forward();
        }
    </script>

    <script>
        $('input[type=radio]').click(function() {
            $("#quizForm").submit();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentWeight = {{ $currentWeight }}; // Pass current weight to JavaScript
            const height = {{ $height }};
            const heightInCm = height/100; // Pass current height to JavaScript
            const desiredWeightInput = document.querySelector('input[id="answer_desire_weight"]'); // Adjust selector based on your structure

            // Create a div for the message
            const messageDiv = document.createElement('div');
            const messageImgDiv = document.createElement('div');
            const messageInstructionDiv = document.createElement('div'); // Create a span for the message
            const messageTrainer = document.createElement('p'); // Create a span for the message
            const messageSpan = document.createElement('span'); // Create a span for the message

            const quizForm = document.getElementById('quizForm'); // Get the quiz form

            const instructionElement = document.getElementById('instructionMsg');
            let  instructionMsg= "";
            if (instructionElement) {
                 instructionMsg  = instructionElement.value;
            }

            // Add event listener to desired weight input
            if (desiredWeightInput) {
                desiredWeightInput.addEventListener('input', function() {
                    messageDiv.className = 'instruction-box';
                    messageTrainer.className = 'instruction-trainer';
                    messageInstructionDiv.className = 'instruction';
                    messageImgDiv.className = 'img-box';
                    messageSpan.className = 'instruction-msg';
                    messageSpan.style.color = 'red'; // Style the message
                    messageSpan.style.display = 'none'; // Hide initially

                    messageDiv.appendChild(messageImgDiv); // Append span to the div
                    messageInstructionDiv.appendChild(messageTrainer);
                    messageInstructionDiv.appendChild(messageSpan);
                    messageDiv.appendChild(messageInstructionDiv); // Append span to the div
                    document.querySelector('.details-body').appendChild(messageDiv); // Append div to the details body

                    const desiredWeight = parseFloat(this.value);
                    const BMICalculattion = desiredWeight / (heightInCm * heightInCm);

                    // Show or hide messageDiv based on input value
                    if (this.value.trim() == '') {
                        messageDiv.style.setProperty('display', 'none', 'important');// Hide message div if input is blank
                        quizForm.querySelector('button[type="submit"]').disabled = false; // Enable the submit button
                        return; // Exit the function
                    } else {
                        messageDiv.style.display = 'block'; // Show message div if input is not blank
                    }


                    if (!isNaN(desiredWeight)) {

                        messageTrainer.textContent = 'Banana Mo’';
                        if (desiredWeight > currentWeight) {
                            messageSpan.textContent = "Ton objectif dépasse ton poids actuel Pour l’ instant, Reveal n’ est pas encore adapté pour la prise de masse.Mais on y travaille!";
                            messageSpan.style.display = 'block';
                            quizForm.querySelector('button[type="submit"]').disabled = true; // Disable the submit button
                        } else if (BMICalculattion < 19){
                            messageTrainer.textContent = 'Banana Mo’';
                            messageSpan.textContent = "Objectif trop bas. Choisis-en un autre.";
                            messageSpan.style.display = 'block';
                            quizForm.querySelector('button[type="submit"]').disabled = true; // Disable the submit button
                        } else if (BMICalculattion >= 19){
                            messageSpan.style.color = 'green'; // Style the message
                            messageSpan.textContent = instructionMsg;
                            messageSpan.style.display = 'block';
                            quizForm.querySelector('button[type="submit"]').disabled = false; // Enable the submit button
                        }
                    } else {
                        messageSpan.style.display = 'none'; // Hide message if input is invalid
                        quizForm.querySelector('button[type="submit"]').disabled = false; // Enable the submit button
                    }
                });
            }
        });
    </script>

	<script>window.amplitude.init('58b25352c5f050c3039f226c757dc9ba', {"fetchRemoteConfig":true,"autocapture":true});</script>
	<script>
        amplitude.track('{{$googleAnalyticScript}}');
	</script>
    <script>
         var supportBtn = document.getElementById('supportButton');
        if (supportBtn) {
            document.getElementById('supportButton').addEventListener('click', function() {
                document.getElementById('answer_{{ $answer["id"] }}').setAttribute('required', 'required');
            });
         }
        var laterButton = document.getElementById('laterButton');
        if (laterButton) {
            document.getElementById('laterButton').addEventListener('click', function() {
                var answerInput = document.getElementById('answer_{{ $answer["id"] }}');
                answerInput.value = ''; // Set the value to empty string
                document.getElementById('answer_{{ $answer["id"] }}').removeAttribute('required');
            });
        }
    </script>
    @endsection
</x-home-layout>
