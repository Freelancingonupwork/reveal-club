<x-home-layout title="Login">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            margin: 0;
            color: #00120B;
            font-size: 14px;
            line-height: 20px;
            font-family: 'SF Pro Text';
        }

        * {
            box-sizing: border-box;
        }

        img {
            max-width: 100%;
        }

        p {
            margin: 0;
        }

        a {
            text-decoration: none;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            font-family: 'SFRounded';
        }

        button.btn {
            border: none;
            outline: none;
            box-shadow: none;
        }

        .container {
            max-width: 375px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .space-ptb {
            padding: 24px 0;
        }

        .space-pt {
            padding-top: 24px;
        }

        .space-pb {
            padding-bottom: 24px;
        }

        .section-title {
            margin-bottom: 16px;
        }

        .section-title h2.title {
            font-size: 24px;
            line-height: 29px;
            letter-spacing: -0.48px;
        }

        .section-title h4.title {
            font-size: 20px;
            line-height: 24px;
        }

        .inner-header.age-header {
            margin-bottom: 25px;
            padding-top: 20px;
        }

        .inner-header.age-header .header-top {
            display: flex;
            position: relative;
            text-align: center;
            justify-content: center;
            align-items: center;
        }

        .inner-header.age-header .header-top .logo {
            line-height: 0;
        }

        .inner-header.age-header .header-top .logo .logo-img {
            height: 32px;
            max-height: 30px;
        }



        /*New CSS*/

        .main-section.full-height {
            min-height: 100vh;
        }

        .question-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .question-title .title {
            color: #17191C;
            font-size: 24px;
            line-height: 28px;
            margin: 0;
            font-weight: 700;
        }

        .question-title .description {
            margin: 0;
            margin-top: 12px;
        }

        .answers {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-input {
            width: 100%;
            height: 62px;
            padding: 0 14px 0 46px;
            border-radius: 12px;
            background-color: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0px 12px 24px 0px rgba(0, 0, 0, 0.1);
            margin: 0;
        }

        .form-input:focus {
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0px 12px 24px 0px rgba(0, 0, 0, 0.1);
            outline: none;
        }

        .input-w-icon {
            position: relative;
        }

        .input-w-icon.second-icon-input .form-input {
            padding-right: 46px;
        }

        .answers .answer {
            position: relative;
            line-height: 0;
        }

        .answer .input-icon {
            position: absolute;
            top: 31px;
            left: 14px;
            transform: translateY(-50%);
        }

        .answer .second-icon {
            position: absolute;
            right: 14px;
            top: 31px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .answer .hide-pw {
            display: none;
        }

        .answers.single .answer .input-icon {
            font-weight: 500;
        }

        .main-btn,
        .submit-btn {
            font-family: 'SFRounded';
            font-size: 16px;
            line-height: 20px !important;
            letter-spacing: -0.32px;
            height: auto !important;
            font-weight: 600;
            text-align: center;
            width: 100%;
            max-width: 375px;
            padding: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #7662F1 !important;
            border-radius: 50px !important;
            box-shadow: none !important;
            border: none !important;
            color: #ffffff !important;
            margin-bottom: 0 !important;
            margin-top: 30px;
        }

        .main-btn:focus {
            border: 0;
            outline: 0;
            box-shadow: 0px 12px 24px 0px rgba(255, 255, 255, 0.5);
        }



        /* Change Plan  */

        .change-plan .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .change-plan .transition-img {
            text-align: center;
        }

        .change-plan .transition-img img {
            width: 180px;
        }

        .change-plan-box {
            margin-top: -30px;
        }

        .change-plan-box .accordion {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .change-plan-box .accordion-item {
            position: relative;
            padding: 16px;
            background-color: #ffffff;
            border-radius: 16px;
            border: 2px solid rgba(0, 18, 11, 0.1);
            margin-bottom: 12px;
        }

        .change-plan-box .accordion-item:last-child {
            margin-bottom: 0;
        }

        .change-plan-box .accordion-item:has(.currunt-plan-tag.active) {
            padding-top: 35px;
        }

        .change-plan-box .accordion-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .change-plan-box .currunt-plan-tag {
            background-color: #B2B7B5;
            line-height: 1;
            padding: 6px;
            width: 100%;
            max-width: 254px;
            text-align: center;
            position: absolute;
            top: -2px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 0 0 16px 16px;
            display: none;
        }

        .change-plan-box .currunt-plan-tag.active {
            display: block;
        }

        .change-plan-box .currunt-plan-tag span {
            font-family: 'SFRounded';
            font-size: 13px;
            font-weight: 700;
            line-height: 16px;
            color: #ffffff;
        }

        .change-plan-box .plan-select-check {
            height: 20px;
            width: 20px;
            border-radius: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #1BC666;
            position: absolute;
            top: 50%;
            right: 0;
            transform: translate(50%, -50%);
            opacity: 0;
            visibility: hidden;
        }

        .change-plan-box .accordion-header .accordion-header-left .plan-duration {
            font-size: 16px;
            line-height: 19px;
            letter-spacing: -0.32px;
            margin-bottom: 2px;
            color: rgba(0, 18, 11, 0.6);
        }

        .change-plan-box .accordion-header .accordion-header-left .plan-discount {
            color: #323D4B;
            font-size: 12px;
            font-weight: 400;
            line-height: 14px;
        }

        .change-plan-box .accordion-header-right {
            display: flex;
            gap: 4px;
        }

        .change-plan-box .accordion-header-right .plan-main-amount {
            color: rgba(0, 18, 11, 0.6);
            font-size: 28px;
            line-height: 33px;
        }

        .change-plan-box .accordion-header-right .plan-before span {
            color: rgba(0, 18, 11, 0.6);
            font-size: 12px;
            font-weight: 500;
            line-height: 14px;
        }

        .change-plan-box .accordion-header-right .plan-before .plan-before-amount {
            display: block;
            margin-bottom: 1px;
            font-weight: normal;
        }

        .change-plan-box .accordion-body {
            display: none;
            margin-top: 12px;
        }

        .change-plan-box .plan-feature-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .change-plan-box .plan-feature-list li {
            display: flex;
            gap: 6px;
            color: #00120B;
            font-size: 12px;
            line-height: 14px;
        }

        .change-plan-box .accordion-item:has(.accordion-body.show) {
            border-color: #1BC666;
        }

        .change-plan-box .accordion-item:has(.accordion-body.show) .currunt-plan-tag {
            background-color: #1BC666;
        }

        .change-plan-box .accordion-item:has(.accordion-body.show) .plan-select-check {
            opacity: 1;
            visibility: visible;
        }

        .change-plan-box .accordion-item:has(.accordion-body.show) .accordion-header-left .plan-duration,
        .change-plan-box .accordion-item:has(.accordion-body.show) .accordion-header-right .plan-main-amount,
        .change-plan-box .accordion-item:has(.accordion-body.show) .accordion-header-right .plan-before span {
            color: #00120B;
        }

        .title {
            font-size: 24px;
            line-height: 28px;
            letter-spacing: -0.48px;
        }

        .footer-button {
            position: sticky;
            bottom: 0;
            margin: auto;
            border: none;
            font-weight: 600;
            padding: 30px 16px;
            background-color: white;
            margin: 0 -16px;
        }

        .btn-link {
            font-family: 'SFRounded';
            font-size: 14px;
            line-height: 20px;
            font-weight: 600;
            text-align: center;
            padding: 12px;
            width: 100%;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #7662F1;
            border-radius: 50px;
            box-shadow: none;
            border: none;
            color: #ffffff;
        }

        .btn-link.style-2 {
            padding: 6px 12px;
            background-color: #ffffff;
            color: #000000;
            margin-top: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0px 12px 24px -8px rgba(0, 0, 0, 0.1);
        }


        /* Sign in */

        .sign-in-header {
            padding: 16px 0;
            text-align: center;
        }

        .sign-in-header img {
            height: 32px;
        }

        .main-block {
            padding: 32px 16px 50px 16px;
            background-color: #ffffff;
            border-radius: 32px 32px 0 0;
            min-height: calc(100vh - 100px);
        }

        .question-title.mb-60 {
            margin-bottom: 60px;
        }

        .sign-in-form .answers {
            gap: 48px;
        }

        .sign-in-form .answer label {
            font-family: SFRounded;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            position: absolute;
            top: -32px;
            left: 0;
            color: rgba(0, 18, 11, 0.5);
        }

        .sign-in-form .answer .forgot-pw-btn {
            font-family: SFRounded;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            position: absolute;
            top: -32px;
            right: 0;
            color: #2A3539;
        }

        .terms-condition {
            font-family: SFRounded;
            font-size: 12px;
            font-weight: 700;
            line-height: 16px;
            text-align: center;
            margin-top: 100px;
            display: block !important;
        }
    </style>
    @endsection

    <body style="background-image: url(webAssets/images/bg.png);background-position: center top;background-repeat: no-repeat;background-size: 100% 100%;">
        <section class="main-section full-height">
            <div class="container">
                <div class="inner-section">
                    <!-- Header -->
                    <header class="sign-in-header">
                        <img class="logo-img" src="{{ asset('webAssets/images/logo-light.png') }}">
                    </header>

                    <div class="main-block">
                        <div class="question-title mb-60">
                            <h3 class="title">Connexion</h3>
                        </div>

                        <form id="loginForm" action="{{ route('user-login') }}" method="post" class="sign-in-form">
                            {{ csrf_field() }}
                            <ul class="answers">
                                <li class="answer input-w-icon">
                                    <label for="email">E-mail</label>
                                    <input class="form-input @error('email') is-invalid @enderror" id="email" type="email" name="email" placeholder="nom@email.com">
                                    <div class="input-icon" for="email">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.02074 7.34742C2.12622 5.48088 3.67304 4 5.566 4H18.449C20.4105 4 22 5.58997 22 7.552V17C22 19.2093 20.2093 21 18 21H6C3.79072 21 2 19.2093 2 17V7.551C2 7.48124 2.00714 7.41315 2.02074 7.34742ZM4 10.7796V17C4 18.1047 4.89528 19 6 19H18C19.1047 19 20 18.1047 20 17V10.7898L15.9352 13.5457C13.5634 15.1533 10.4517 15.1534 8.07993 13.5458L4 10.7796ZM20 7.552C20 6.69403 19.3055 6 18.449 6H5.566C4.70928 6 4.015 6.69428 4.015 7.551C4.015 8.06556 4.26974 8.54635 4.69595 8.83515L9.20207 11.8902C10.8963 13.0386 13.1187 13.0386 14.8129 11.8902L19.3199 8.8346C19.7452 8.54702 20 8.06673 20 7.552Z" fill="#17191C" />
                                        </svg>
                                    </div>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </li>
                                <li class="answer input-w-icon second-icon-input">
                                    <label for="password">Mot de passe</label>
                                    <a class="forgot-pw-btn" href="{{ route('user-forget-password') }}">Mot de passe oublié</a>
                                    <input class="form-input height-select @error('password') is-invalid @enderror" id="password" type="password" name="password" placeholder="Mot de passe">
                                    <div class="input-icon" for="password">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.13892 13.6854C1.01739 6.85154 6.85176 1.0162 13.6867 2.13798C17.8403 2.81948 21.1801 6.16054 21.8616 10.3139C22.9822 17.1488 17.1478 22.9823 10.3139 21.8616C6.16072 21.1802 2.81947 17.8401 2.13892 13.6854ZM13.3628 4.11157C7.87791 3.21137 3.21237 7.8778 4.11256 13.3618C4.65415 16.6689 7.33098 19.3454 10.6376 19.888C16.1217 20.7873 20.7873 16.1226 19.8879 10.6376C19.3454 7.33095 16.6691 4.65406 13.3628 4.11157ZM9.70686 11.2927C9.31634 10.9022 8.68317 10.9022 8.29265 11.2927C7.90212 11.6832 7.90212 12.3164 8.29265 12.7069C8.68317 13.0974 9.31634 13.0974 9.70686 12.7069C10.0974 12.3164 10.0974 11.6832 9.70686 11.2927ZM6.87843 9.87847C8.05001 8.7069 9.9495 8.7069 11.1211 9.87847C11.4486 10.206 11.6845 10.5903 11.8289 10.9998H16.9998C17.5521 10.9998 17.9998 11.4475 17.9998 11.9998V13.8598C17.9998 14.4121 17.5521 14.8598 16.9998 14.8598C16.4475 14.8598 15.9998 14.4121 15.9998 13.8598V12.9998H14.9998V13.8598C14.9998 14.4121 14.5521 14.8598 13.9998 14.8598C13.4475 14.8598 12.9998 14.4121 12.9998 13.8598V12.9998H11.8289C11.6845 13.4092 11.4486 13.7936 11.1211 14.1211C9.9495 15.2927 8.05001 15.2927 6.87843 14.1211C5.70686 12.9495 5.70686 11.05 6.87843 9.87847Z" fill="#17191C" />
                                        </svg>
                                    </div>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="second-icon" onclick="togglePasswordVisibility('password', this)">
                                        <div class="show-pw" style="display: none">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.122 9.88C15.293 11.051 15.293 12.952 14.122 14.125C12.951 15.296 11.05 15.296 9.877 14.125C8.706 12.954 8.706 11.053 9.877 9.88C11.05 8.707 12.95 8.707 14.122 9.88Z" stroke="#17191C" stroke-opacity="0.6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3 12C3 11.341 3.152 10.689 3.446 10.088C4.961 6.991 8.309 5 12 5C15.691 5 19.039 6.991 20.554 10.088C20.848 10.689 21 11.341 21 12C21 12.659 20.848 13.311 20.554 13.912C19.039 17.009 15.691 19 12 19C8.309 19 4.961 17.009 3.446 13.912C3.152 13.311 3 12.659 3 12Z" stroke="#17191C" stroke-opacity="0.6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <div class="hide-pw" style="display: block">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.3461 6.16278C8.0072 4.87893 9.9644 4 11.9998 4C13.7493 4 15.4418 4.64962 16.9375 5.64801L18.2926 4.29289C18.6832 3.90237 19.3163 3.90237 19.7069 4.29289C20.0974 4.68342 20.0974 5.31658 19.7069 5.70711L17.7604 7.6536C17.7535 7.66065 17.7466 7.66758 17.7396 7.67439L7.69005 17.7239C7.67165 17.7439 7.65259 17.7629 7.63292 17.781L5.70686 19.7071C5.31633 20.0976 4.68317 20.0976 4.29264 19.7071C3.90212 19.3166 3.90212 18.6834 4.29264 18.2929L5.4783 17.1072C4.18214 15.924 3.06581 14.4739 2.23817 12.9437L2.23767 12.9428C1.92078 12.3555 1.92078 11.6455 2.23767 11.0582C3.24441 9.19582 4.67952 7.45085 6.3461 6.16278ZM6.89422 15.6913L8.44432 14.1412C7.48179 12.5457 7.6889 10.4416 9.06564 9.06489C10.4424 7.68815 12.5464 7.48104 14.142 8.44357L15.4898 7.09572C14.3374 6.39219 13.1443 6 11.9998 6C10.5401 6 9.0023 6.63757 7.56915 7.74522C6.14382 8.84684 4.88683 10.3659 4.0018 12.0005C4.74449 13.3713 5.74863 14.6608 6.89422 15.6913ZM12.6394 9.94618C11.9013 9.71725 11.0641 9.89489 10.4799 10.4791C9.89564 11.0633 9.718 11.9005 9.94693 12.6386L12.6394 9.94618ZM18.4293 8.16062C18.8492 7.80192 19.4804 7.85158 19.8391 8.27153C20.5725 9.13008 21.2274 10.0699 21.7614 11.0574C22.0783 11.6447 22.0787 12.3555 21.7618 12.9428C20.7551 14.8047 19.3199 16.5495 17.6534 17.8374C15.9923 19.1211 14.0351 20 11.9998 20C11.0163 20 10.0508 19.7921 9.13479 19.4376C8.61974 19.2382 8.36382 18.6591 8.56317 18.144C8.76252 17.629 9.34166 17.3731 9.85671 17.5724C10.5787 17.8519 11.2992 18 11.9998 18C13.4594 18 14.9972 17.3624 16.4304 16.2549C17.8557 15.1534 19.1127 13.6346 19.9977 12.0005C19.5364 11.1489 18.965 10.3275 18.3184 9.57048C17.9597 9.15053 18.0093 8.51931 18.4293 8.16062Z" fill="#00120B" fill-opacity="0.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="col-lg-12">
                                @if(Session::has('error'))
                                <span style="color: red;">
                                    {{ Session::get('error') }}
                                </span>
                                @endif
                            </div>
                            <button type="submit" class="main-btn">Connexion</button>
                        </form>
                        <p class="terms-condition">En poursuivant, vous acceptez les<br><a href="{{ url('/pages/terms-conditions')}}">conditions d’utilisation</a> et <a href="{{ url('/pages/privacy-policy')}}">la politique de confidentialité</a> de Reveal Club</p>
                    </div>
                </div>
        </section>
    </body>
    @section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        $('#login-form').validate({
            rules: {
                email: {
                    required: true,
                },
                password: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "Veuillez entrer votre email",
                },
                Password: {
                    required: "Veuillez entrer le mot de passe",
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
    </script>
    <script>
        function togglePasswordVisibility(passwordFieldId, iconElement) {
            const passwordField = document.getElementById(passwordFieldId);
            const showIcon = iconElement.querySelector('.show-pw');
            const hideIcon = iconElement.querySelector('.hide-pw');

            // Toggle the type of password field
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                showIcon.style.display = 'block';
                hideIcon.style.display = 'none';
            } else {
                passwordField.type = 'password';
                showIcon.style.display = 'none';
                hideIcon.style.display = 'block';
            }
        }
    </script>
    @endsection
</x-home-layout>