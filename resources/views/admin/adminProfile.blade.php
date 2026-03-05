<x-admin-layout title="User profile">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">

    <style>
        .dark-mode input:-webkit-autofill {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #23232329;
        }

        .invalid-feedback {
            margin-left: 170px;
        }
    </style>

    <style>
        #password_rules ul li {
            /*font-size: 12px;
        font-weight: normal;*/
            color: #fff;
        }

        #password_rules ul li.complete {
            color: #5DB406;
        }

        span.togglePassword {
            color: gray;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Profile</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">User Profile</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Admin Profile</h3>
                            <!-- <div class="card-tools">
                                <a type="button" class="btn btn-secondary" href="{{ url('admin/cms-index') }}">
                                    <i class="fas fa-arrow-left"></i>
                                    Return to List
                                </a>
                            </div> -->
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                @if(Auth::guard('admin')->user()->avatar)
                                <img class="profile-user-img img-fluid img-circle" src="{{ asset('/storage/'.Auth::guard('admin')->user()->avatar) }}" id="user_img" alt="User profile picture">
                                @else
                                <img class="profile-user-img img-fluid img-circle" src="{{ asset('adminAssets/dist/img/user2-160x160.jpg') }}" id="user_img" alt="User profile picture">
                                @endif
                            </div>

                            <h3 class="profile-username text-center">{{ Auth::guard('admin')->user()->name }}</h3>

                            <!-- <p class="text-muted text-center">Software Engineer</p> -->

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Name</b> <a class="float-right">{{ Auth::guard('admin')->user()->name }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Email</b> <a class="float-right">{{ Auth::guard('admin')->user()->email }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Mobile</b> <a class="float-right">{{ Auth::guard('admin')->user()->mobile }}</a>
                                </li>
                            </ul>

                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-block"><b>Dashboard</b></a>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card card-primary card-outline">

                        <div class="card-body ">
                            <div class="tab-content">
                                <div class="active tab-pane" id="settings">
                                    <form id="user-profile" class="form-horizontal" action="{{ route('admin.profile') }}" method="post" enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Name <span style="color: #ff5252;">*</span></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ Auth::guard('admin')->user()->name }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" name="email" id="email" value="{{ Auth::guard('admin')->user()->email }}" placeholder="Email" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-2 col-form-label">Mobile <span style="color: #ff5252;">*</span></label>
                                            <div class="col-sm-10">
                                                <input type="tel" class="form-control" name="mobile" id="mobile" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" placeholder="Mobile" value="{{ Auth::guard('admin')->user()->mobile }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputSkills" class="col-sm-2 col-form-label">Profile Picture</label>
                                            <div class="col-sm-10">
                                                <input type="file" class="form-control" name="avatar" id="avatar" onchange="show(this)">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" class="btn btn-danger float-right">Submit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Change Password</h3>
                            <!-- <div class="card-tools">
                                <a type="button" class="btn btn-secondary" href="{{ url('admin/cms-index') }}">
                                    <i class="fas fa-arrow-left"></i>
                                    Return to List
                                </a>
                            </div> -->
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-body">
                            <form id="change-password" action="{{ route('admin.change-password') }}" method="post">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="old_password">Old Password <span style="color: #ff5252;">*</span></label>
                                            <input type="text" id="old_password" name="old_password" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="new_password">New Password <span style="color: #ff5252;">*</span></label>
                                            <input type="password" id="new_password" name="new_password" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm Password <span style="color: #ff5252;">*</span></label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div id="password_rules">
                                    <h4>Passwords must meet these requirements:</h4>
                                    <ul>
                                        <li class="password_length">At least 8 characters</li>
                                        <li class="password_uppercase">At least 1 uppercase letter</li>
                                        <li class="password_number">At least one number</li>
                                        <li class="password_special_character">At least one special character</li>
                                        <li class="password_match">Password and confirmation must match</li>
                                    </ul>
                                </div>
                                <button type="submit" class="btn btn-info float-right">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
    @section('scripts')
    <SCRIPT type="text/javascript">
        function show(input) {
            debugger;
            var validExtensions = ['jpg', 'png', 'jpeg']; //array of valid extensions
            var fileName = input.files[0].name;
            var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
            if ($.inArray(fileNameExt, validExtensions) == -1) {
                input.type = ''
                input.type = 'file'
                $('#user_img').attr('src', "");

                Swal.fire({
                    title: "Invalid Extension!",
                    text: "Only these file types are accepted : " + validExtensions.join(', '),
                    icon: "error"
                });

                alert("Only these file types are accepted : " + validExtensions.join(', '));
            } else {
                if (input.files && input.files[0]) {
                    var filerdr = new FileReader();
                    filerdr.onload = function(e) {
                        $('#user_img').attr('src', e.target.result);
                    }
                    filerdr.readAsDataURL(input.files[0]);
                }
            }
        }
    </SCRIPT>
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

    <script>
        var phone_input = document.getElementById("mobile");

        phone_input.addEventListener('input', () => {
            phone_input.setCustomValidity('');
            phone_input.checkValidity();
        });

        phone_input.addEventListener('invalid', () => {
            if (phone_input.value === '') {
                phone_input.setCustomValidity('Enter phone number!');
            } else {
                phone_input.setCustomValidity('Enter phone number in this format: 123-456-7890');
            }
        });
    </script>

    <script src="{{ asset('adminAssets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('adminAssets/plugins/jquery-validation/additional-methods.min.js') }}"></script>
    <script>
        $(function() {
            $('#change-password').validate({
                rules: {
                    old_password: {
                        required: true,
                    },
                    new_password: {
                        minlength: 8,
                        maxlength: 30,
                        required: true,
                    },
                    confirm_password: {
                        equalTo: '#new_password',
                    },
                },
                messages: {
                    old_password: {
                        required: "Please enter your current password",
                    },
                    new_password: {
                        required: "Please enter new password",
                        pwcheck: "Password is not strong enough",

                    },
                    confirm_password: {
                        equalsTo: "New password and confirm password should be same"
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
    </script>

    <script>
        $(function() {
            $('#user-profile').validate({
                rules: {
                    name: {
                        required: true,
                    },
                    mobile: {
                        required: true,
                    }
                },
                messages: {
                    name: {
                        required: "Please enter your name",
                    },
                    mobile: {
                        required: "Please enter your mobile number",

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
    </script>
    @endsection
</x-admin-layout>
