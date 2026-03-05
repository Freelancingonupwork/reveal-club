<x-home-layout title="Register">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .btn-link {
            font-size: 16px;
            line-height: 22px;
            text-align: center;
            width: 100%;
            height: 50px;
            background-color: var(--primary-color);
            align-items: center;
            justify-content: center;
            color: var(--heading-color);
            border-radius: 12px;
        }

        .google-login {
            font-size: 16px;
            line-height: 22px;
            text-align: center;
            width: 100%;
            height: 50px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;
        }
    </style>
    @endsection

    <section class="main-section" data-setbg="{{ asset('webAssets/img/hero/hero-1.jpg') }}">
        <div class="container">
            <div class="inner-section">
                <div class="main-header">
                    <div class="inner-header age-header">
                        <img class="logo" src="{{ asset('webAssets/images/svgs/logo.svg') }}" alt="logo">

                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <p class="text-center mb-4" style="font-size: xxx-large; font-weight:900; color:darkgoldenrod">REGISTER</p>
                        @if ($errors->any())
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
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
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {{ Session::get('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif

                        @if(Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif
                    </div>
                    <div class="details-body">
                        <div class="details">
                            <div class="col-lg-6 offset-lg-3">
                                <form id="register-form" action="{{ route('user-register') }}" method="post">
                                    {{ csrf_field() }}

                                    <ul class="details-options">
                                        <li class="details-select full-width">
                                            <!-- <i class="fa-solid fa-envelope fa-xl"></i> -->
                                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Your Email">
                                            @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @else
                                            <div class="invalid-feedback">Please enter your email</div>
                                            @enderror
                                        </li>
                                        <li class="details-select full-width">
                                            <!-- <i class="fa-solid fa-key fa-xl"></i> -->
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Your Password">
                                            @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @else
                                            <div class="invalid-feedback">Please enter your password</div>
                                            @enderror
                                        </li>
                                        <li class="details-select full-width">
                                            <!-- <i class="fa-solid fa-eye fa-xl"></i> -->
                                            <input type="password" class="form-control @error('cnf_password') is-invalid @enderror" id="cnf_password" name="cnf_password" placeholder="Confirm Password">
                                            @error('cnf_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @else
                                            <div class="invalid-feedback">Please enter the password as above</div>
                                            @enderror
                                        </li>
                                        <div class="col-lg-12 mb-2">
                                            <center>
                                                <div class="row" style="margin-left:inherit;">
                                                    <button type="submit" class="btn-link" style="text-decoration: none;">Sign Up</button>
                                                    <h1 class="text-center" style="color: gray;">OR</h1>
                                                </div>
                                            </center>
                                        </div>
                                    </ul>
                                </form>
                                <center>
                                    <form action="{{ route('user-login') }}" method="get">
                                        <button type="submit" class="btn-link" style="text-decoration: none;">Sign In</button>
                                    </form>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        $('#register-form').validate({
            rules: {
                email: {
                    required: true,
                },
                password: {
                    required: true
                },
                cnf_password: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "Please enter your email",
                },
                Password: {
                    required: "Please enter Password",
                },
                cnf_password: {
                    required: "Please enter the password as above"
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
    @endsection
</x-home-layout>
