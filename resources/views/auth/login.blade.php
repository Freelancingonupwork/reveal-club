<x-auth-layout title="Login">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    @endsection
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="h1"><b>Template Based App</h1>
            </div>
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <p class="login-box-msg">Sign in to start your session</p>

                <form action="{{ route('admin.login') }}" class="needs-validation" novalidate method="post">
                    @csrf

                    <?php
                    if (isset($_COOKIE["email"]) || !empty($_COOKIE["email"])) {
                        $email = $_COOKIE["email"];
                    } else {
                        $email = "";
                    }

                    if (isset($_COOKIE["password"]) || !empty($_COOKIE["password"])) {
                        $password = $_COOKIE["password"];
                    } else {
                        $password = "";
                    }

                    if (isset($_COOKIE['email'])) {
                        $remember = 'checked="checked"';
                    } else {
                        $remember = '';
                    }
                    ?>
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" required placeholder="Email" value="{{ $email }}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @else
                        <div class="invalid-feedback">Please enter your email</div>
                        @enderror
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" required placeholder="Password" value="{{ $password }}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @else
                        <div class="invalid-feedback">Please enter your password</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" class="form-check-input" name="remember" id="remember" {{ $remember }}>
                                <label for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
                <!-- <a href="{{ route('facebook-login') }}" class="btn btn-primary btn-block mt-2">Sign In With Facebook</a> -->
                @if (Route::has('admin.forgotPassword'))
                <p class="mb-1">
                    <a href="{{ route('admin.forgotPassword') }}">{{ __('Forgot Your Password?') }}</a>
                </p>
                @endif
                <!-- <p class="mb-0">
                    <a href="{{ route('register') }}" class="text-center">Register a new membership</a>
                </p> -->
            </div>
        </div>
    </div>
    @section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
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
    @endsection
</x-auth-layout>
