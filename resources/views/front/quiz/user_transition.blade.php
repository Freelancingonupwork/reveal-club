<?php

use App\Models\Answer;
use Illuminate\Support\Facades\Session; ?>
<x-home-layout title="Transition">
    @section('styles')
    <style>
        .btn-link {
            font-size: 16px;
            line-height: 22px;
            text-align: center;
            display: inline-block;
            width: 100%;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #7662F1 !important;
            border-radius: 50px !important;
            box-shadow: none !important;
            border: none !important;
            color: white !important;
        }

        .animated-button {
            text-align: left;
        }

        @keyframes spin {
            0% {
                transform: rotate(0);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    @endsection

    <?php
        $userAnsweredData = Session::get('userAnsweredData');
        $age = false;
        $ques_answers = Answer::find($selectedAnswerId)->ques_answers;
        if (isset($userAnsweredData) && !is_null($userAnsweredData) && $ques_answers === 'age') {
            foreach ($userAnsweredData as $index => $userAnswer) {
                if ($userAnswer['key'] === 'age') {
                    $age = $userAnswer['value'];
                    $ageTransition = $allTransition[0]; // Default transition
                    if ($age >= 18 && $age <= 29 && count($allTransition) > 0) {
                        $ageTransition = $allTransition[0];
                    } elseif ($age >= 30 && $age <= 39 && count($allTransition) >= 2) {
                        $ageTransition = $allTransition[1];
                    } elseif ($age >= 40 && $age <= 49 && count($allTransition) >= 3) {
                        $ageTransition = $allTransition[2];
                    } elseif ($age >= 50 && $age <= 59 && count($allTransition) >= 4) {
                        $ageTransition = $allTransition[3];
                    } elseif ($age >= 60 && count($allTransition) >= 5) {
                        $ageTransition = $allTransition[4];
                    }
                    $age = true;
                }
            }
        }

        $imageUrl = $transition['is_trans_image'] == 1 ? asset(Storage::url($transition['transition_image'])) : null;
        $primaryColour = $transition['color'] ?? null;

        // Check if the age condition is true and the transition image is valid
        if (isset($age) && $age === true && isset($ageTransition)) {
            $primaryColour = $ageTransition['color'] ?? null;
            if($ageTransition['is_trans_image'] == 1){
                $imageUrl = asset(Storage::url($ageTransition['transition_image']));
            }
        }
    ?>
    <section class="main-section" style="background-image: url('{{ $imageUrl }}') ;background-size: 375px 100%;background-position: center;background-repeat: no-repeat;">
        <div class="container">
            <div class="inner-section">
                <div class="main-header">
                    <div class="inner-header age-header">
                        <div class="header-top">
                            <div class="back-btn">
                                    <?php
                                    $sessionId = Session::get('sessionId') ?? session::get('quiz_session_id');
                                    ?>
                                    <a href="{{ route('quiz.previousTransition', ['id' => $selectedAnswerId, 'index' => $prevIndex]) }}">
                                        <img class="arrow-back" src="{{ asset('webAssets/images/quiz/arrow-back.png') }}" alt="arrow-back">
                                    </a>
                            </div>
                            <div class=""><img class="logo" src="{{ asset('webAssets/images/svgs/logo-white.svg') }}" alt="logo"></div>
                        </div>
                    </div>
                    <form id="quizForm" action="{{ url('process-quiz') }}" method="post">
                        {{ csrf_field() }}
                        <div class="details-body">

                            <input type="hidden" id="transitionTitle" name="transitionTitle" value="{{ $transition['title'] }}">

                            @if(isset($age) && $age === true)
                            <div class="details">
                                <h4 class="details-question small-align-left">{!! $ageTransition['trans_description'] !!}</h4>
                            </div>
                            @elseif(isset($age) && $age === false)
                            <div class="details">
                                <h4 class="details-question small-align-left">{!! $transition['trans_description'] !!}</h4>
                            </div>
                            @endif
                        </div>
                        <div class="loader-box" style="display:none;">
                            <div class="loader" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                                <div style="animation: spin 2s linear infinite;line-height: 0;">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.99918 14.7619C12.1813 14.7619 14.761 12.1823 14.761 9.00015C14.761 5.81802 12.1813 3.23838 8.99918 3.23838C5.81704 3.23838 3.2374 5.81802 3.2374 9.00015C3.2374 12.1823 5.81704 14.7619 8.99918 14.7619ZM8.99918 17.4212C13.65 17.4212 17.4202 13.651 17.4202 9.00015C17.4202 4.34934 13.65 0.579102 8.99918 0.579102C4.34836 0.579102 0.578125 4.34934 0.578125 9.00015C0.578125 13.651 4.34836 17.4212 8.99918 17.4212Z" fill="#00120B" fill-opacity="0.05"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.99918 3.10542C5.7436 3.10542 3.10444 5.74458 3.10444 9.00015C3.10444 12.2557 5.7436 14.8949 8.99918 14.8949C9.6968 14.8949 10.2623 15.4604 10.2623 16.158C10.2623 16.8557 9.6968 17.4212 8.99918 17.4212C4.34836 17.4212 0.578125 13.651 0.578125 9.00015C0.578125 4.34934 4.34836 0.579102 8.99918 0.579102C13.65 0.579102 17.4202 4.34934 17.4202 9.00015C17.4202 9.69778 16.8547 10.2633 16.1571 10.2633C15.4595 10.2633 14.8939 9.69778 14.8939 9.00015C14.8939 5.74458 12.2548 3.10542 8.99918 3.10542Z" fill="{{$primaryColour}}"></path>
                                    </svg>
                                </div>
                                <span style="font-size: 16px;line-height: 20px; font-weight: 700; color: #00120B;">@if($transition['is_animation'] == 1) {{$transition['animation_text']}} @endif</span>
                            </div>
                            <div class="skill-wrapper">
                                <div class="progress" style="padding: 0; width: 100%; height: 24px; overflow: hidden; background: #f2f3f1; border: 2px solid #f2f3f1; border-radius: 50px;">
                                    <div class="bar" id="myBar" style="width: 0%; height: 100%; background: {{$primaryColour}}; border-radius: 50px; position: relative;">
                                        <p class="percent" id="percentText" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0; font-size: 12px; line-height: 14px; font-weight: 700; color: #ffffff;">0%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="footer-button">
                            @if(isset($age) && $age === true)
                            <button class="footer-btn btn-link @if($transition['is_animation'] == 1) animated-button @endif" next_location="{{ route('questions') }}">
                                @if(!is_null($transition['button_label']) && !empty($transition['button_label'])) {{ $transition['button_label'] }} @else Ok, I got it! @endif
                            </button>
                            @endif

                            @if($age === false)
                            @if($nextIndex === count($allTransition))
                            <?php
                            $quizSessionId = Session::get('quiz_session_id');
                            $userType = Session::get('userType');
                            ?>
                            @if($transition['is_paywall'] === 1 && $userType === 1)
                            <a href="{{ url('/finish-quiz?sessionId='. $quizSessionId) }}" class="footer-btn btn-link @if($transition['is_animation'] == 1) animated-button @endif" next_location="{{ url('/finish-quiz?sessionId='. $quizSessionId) }}">
                                @if(!is_null($transition['button_label']) && !empty($transition['button_label'])) {{ $transition['button_label'] }} @else Obtenir mon programme maintenant (-50 %) @endif
                            </a>
                            @else
                            <button class="footer-btn btn-link @if($transition['is_animation'] == 1) animated-button @endif" next_location="{{ route('questions') }}">
                                @if(!is_null($transition['button_label']) && !empty($transition['button_label'])) {{ $transition['button_label'] }} @else Ok, I got it! @endif
                            </button>
                            @endif
                            @else
                            <a href="{{ url('transition-view/'.$selectedAnswerId.'/'.$nextIndex) }}" class="footer-btn btn-link @if($transition['is_animation'] == 1) animated-button @endif" next_location="{{ url('transition-view/'.$selectedAnswerId.'/'.$nextIndex) }}">
                                @if(!is_null($transition['button_label']) && !empty($transition['button_label'])) {{ $transition['button_label'] }} @else Next Transition @endif
                            </a>
                            @endif
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
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
    @section('scripts')
    <script type="text/javascript">
        window.history.forward();

        function noBack() {
            window.history.forward();
        }
    </script>
    @if($transition['is_animation'] == 1)
    <script>
        $(document).ready(function() {
            if ($('.progress-label-value').length > 0 ||
                $('.progress-label-value-2').length > 0 ||
                $('.progress-label-value-3').length > 0) {
                return;
            }
            let width = 0;

            // Automatically run the loader without a button click
            const nextLocation = $('.footer-btn.btn-link.animated-button').attr("next_location");

            $('.loader-box').show();
            $('.animated-button').hide();
            width = 0;
            $('#myBar').css('width', width + '%');
            $('#percentText').text(width + '%');

            let loadingInterval = setInterval(function() {
                if (width >= 100) { // Load to 100%
                    clearInterval(loadingInterval);
                    window.location.href = nextLocation;
                } else {
                    width += (100 / (4000 / 20));
                    $('#myBar').css('width', width + '%');
                    $('#percentText').text(Math.round(width) + '%');
                }
            }, 3); // Adjust the interval speed
        });
    </script>
    @endif

    <script> window.amplitude.init('58b25352c5f050c3039f226c757dc9ba', {"fetchRemoteConfig":true,"autocapture":true});</script>
    @if(isset($age) && $age === true)
        <script>amplitude.track('{{$ageTransition['amplitude_tracking_word'] ?? $ageTransition['title'] }}');</script>
    @elseif(isset($age) && $age === false)
        <script>amplitude.track('{{$transition['amplitude_tracking_word'] ?? $transition['title']}}');</script>
    @endif
    @endsection
</x-home-layout>
