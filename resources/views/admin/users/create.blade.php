<x-admin-layout title="Create User">
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                            <li class="breadcrumb-item active">User</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <form id="quickForm" action="" method="post">
                @csrf
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
                                <h3 class="card-title">Create User</h3>
                                <!-- <div class="card-tools">
                                <a type="button" class="btn btn-secondary" href="{{ url('admin/cms-index') }}">
                                    <i class="fas fa-arrow-left"></i>
                                    Return to List
                                </a>
                            </div> -->
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" placeholder="Name">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                    <div class="invalid-feedback">Please enter your name</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label">User Type</label>
                                    <select class="form-control" id="type" name="type">
                                        <option value="" disabled>None</option>
                                        <option value="1">Customer</option>
                                        <option value="2" selected>User</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" placeholder="Email">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                    <div class="invalid-feedback">Please enter your email</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" id="mobile" value="{{ old('mobile') }}" placeholder="Mobile">
                                    @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                    <div class="invalid-feedback">Please enter your mobile</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-2 text-right">
                        <button type="submit" class="btn btn-primary">Save user</button>
                        <a href="{{ route('admin.users') }}" class="btn btn-default">Back</a>
                    </div>
                </div>


            </form>
        </section>
        <!-- /.content -->
    </div>

    @section('scripts')
    <script src="{{ asset('adminAssets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('adminAssets/plugins/jquery-validation/additional-methods.min.js') }}"></script>

    <script>
        $(function() {
            $('#quickForm').validate({
                rules: {
                    name: {
                        required: true,
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    mobile: {
                        required: true,
                        numeric: true,
                        min: 10,
                        max: 10
                    }
                },
                messages: {
                    name: {
                        required: "Please Enter Name",
                    },
                    email: {
                        required: "Please enter Email",
                        email: "Please enter a valid email"
                    },
                    mobile: {
                        required: "Please enter your mobile",
                        numeric: "Enter valid mobile number"
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
