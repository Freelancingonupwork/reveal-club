<x-admin-layout title="Email Setting">

    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <style>
        .switch-field {
            display: flex;
            margin-bottom: 8px;
            border-color: #0d6efd;
            overflow: hidden;
        }

        .switch-field input {
            position: absolute !important;
            clip: rect(0, 0, 0, 0);
            height: 1px;
            width: 1px;
            border: 0;
            overflow: hidden;
        }

        .switch-field label {
            background-color: #e4e4e4;
            color: #0d6efd;
            /* color: rgba(0, 0, 0, 0.6); */
            font-size: 14px;
            line-height: 1;
            text-align: center;
            padding: 8px 16px;
            margin-right: -1px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            /* box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.1); */
            transition: all 0.1s ease-in-out;
        }

        .switch-field label:hover {
            cursor: pointer;
        }

        .switch-field input:checked+label {
            background-color: #0d6efd;
            color: whitesmoke;
            box-shadow: none;
        }

        .switch-field label:first-of-type {
            border-radius: 4px 0 0 4px;
        }

        .switch-field label:last-of-type {
            border-radius: 0 4px 4px 0;
        }

        .invalid-feedback {
            margin-left: 230px;
        }
    </style>
    @endsection

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>E-Mail Setting</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                            <li class="breadcrumb-item active">E-Mail</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <form id="email-setting" action="" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
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
                                <h3 class="card-title">E-Mail Setting</h3>
                                <!-- <div class="card-tools">
                                <a type="button" class="btn btn-secondary" href="{{ url('admin/cms-index') }}">
                                    <i class="fas fa-arrow-left"></i>
                                    Return to List
                                </a>
                            </div> -->
                            </div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="smtp_host" class="col-sm-2 col-form-label">SMTP Host <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="smtp_host" id="smtp_host" class="form-control" value="{{ $settings->getSetting('smtp_host') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="smtp_port" class="col-sm-2 col-form-label">SMTP Port <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="number" name="smtp_port" id="smtp_port" class="form-control" value="{{ $settings->getSetting('smtp_port') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="smtp_user" class="col-sm-2 col-form-label">SMTP User <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="email" name="smtp_user" id="smtp_user" class="form-control" value="{{ $settings->getSetting('smtp_user') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="smtp_password" class="col-sm-2 col-form-label">SMTP Password <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="password" name="smtp_password" id="smtp_password" class="form-control" value="{{ $settings->getSetting('smtp_password') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputNumber" class="col-sm-2 col-form-label">Encryption <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <div class="switch-field">
                                            <input type="radio" name="smtp_encryption" id="encryption_tls" value="tls" @if($settings->getSetting('smtp_encryption') == 'tls') checked @endif />
                                            <label for="encryption_tls">TLS</label>
                                            <input type="radio" name="smtp_encryption" id="encryption_ssl" value="ssl" @if($settings->getSetting('smtp_encryption') == 'ssl') checked @endif />
                                            <label for="encryption_ssl">SSL</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="from_mail" class="col-sm-2 col-form-label">From Email <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="email" name="from_mail" id="from_mail" class="form-control" value="{{ $settings->getSetting('from_mail') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="from_name" class="col-sm-2 col-form-label">From Name <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="from_name" id="from_name" class="form-control" value="{{ $settings->getSetting('from_name') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <div class="row">
                    <div class="col-12 mb-2">
                        <input type="submit" class="btn btn-success float-right" value="Save">
                    </div>
                </div>
            </form>
        </section>
        <!-- /.content -->
    </div>
    @section('scripts')

    <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
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

    <script>
        $(function() {
            $('#email-setting').validate({
                rules: {
                    smtp_host: {
                        required: true,
                    },
                    smtp_port: {
                        required: true
                    },

                    smtp_user: {
                        required: true
                    },
                    smtp_password: {
                        required: true
                    },
                    smtp_encryption: {
                        required: true
                    },
                    from_mail: {
                        required: true
                    },
                    from_name: {
                        required: true
                    }
                },
                messages: {
                    smtp_host: {
                        required: "Enter Email SMTP HOST.",
                    },
                    smtp_port: {
                        required: "Enter Email SMTP PORT.",
                    },

                    smtp_user: {
                        required: "Enter Email SMTP USER."
                    },
                    smtp_password: {
                        required: "Enter Email SMTP PASSWORD."
                    },
                    smtp_encryption: {
                        required: "Select Email SMTP ENCRYPTION."
                    },
                    from_mail: {
                        required: "Enter FROM MAIL."
                    },
                    from_name: {
                        required: "Enter FROM NAME"
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
