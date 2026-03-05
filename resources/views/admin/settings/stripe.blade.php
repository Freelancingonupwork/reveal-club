<x-admin-layout title="Stripe Setting">

    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <style>
        .invalid-feedback {
            margin-left: 230px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
    @endsection

    @section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Stripe Setting</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                            <li class="breadcrumb-item active">Stripe</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <form id="stripe-setting" action="" method="post" enctype="multipart/form-data">
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
                                <h3 class="card-title">Test Mode</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="stripe_secret_key" class="col-sm-2 col-form-label">Stripe Secret Key <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="stripe_secret_key" id="stripe_secret_key" class="form-control" value="{{ $settings->getSetting('stripe_secret_key') }}">
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="stripe_publishable_key" class="col-sm-2 col-form-label">Stripe Publishable Key <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="stripe_publishable_key" id="stripe_publishable_key" class="form-control" value="{{ $settings->getSetting('stripe_publishable_key') }}">
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="stripe_webhook_secret" class="col-sm-2 col-form-label">Stripe Webhook Secret <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="stripe_webhook_secret" id="stripe_webhook_secret" class="form-control" value="{{ $settings->getSetting('stripe_webhook_secret') }}">
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Live Mode</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="live_stripe_secret_key" class="col-sm-2 col-form-label">Stripe Secret Key <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="live_stripe_secret_key" id="live_stripe_secret_key" class="form-control" value="{{ $settings->getSetting('live_stripe_secret_key') }}">
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="live_stripe_publishable_key" class="col-sm-2 col-form-label">Stripe Publishable Key <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="live_stripe_publishable_key" id="live_stripe_publishable_key" class="form-control" value="{{ $settings->getSetting('live_stripe_publishable_key') }}">
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="live_stripe_webhook_secret" class="col-sm-2 col-form-label">Stripe Webhook Secret <span style="color: #ff5252;">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" name="live_stripe_webhook_secret" id="live_stripe_webhook_secret" class="form-control" value="{{ $settings->getSetting('live_stripe_webhook_secret') }}">
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <div class="row">
                    <label for="live" class="col-form-label" style="padding-right: 5px;">Live </label>
                    <label class="switch">
                        <input type="hidden" name="switch_mode" value="0">
                        <input type="checkbox" class="form-check-input" id="switch_mode" name="switch_mode" value="1" {{ $settings->getSetting('switch_mode') ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                    <label for="test" class="col-form-label" style="padding-left: 5px;">Test </label>
                </div>
                <div class="row">
                    <div class="col-12 mb-2">
                        <input type="submit" value="Save" class="btn btn-success float-right">
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
            $('#stripe-setting').validate({
                rules: {
                    stripe_secret_key: {
                        required: true,
                    },
                    stripe_publishable_key: {
                        required: true
                    },
                    stripe_webhook_secret: {
                        required: true
                    },
                    live_stripe_secret_key: {
                        required: true,
                    },
                    live_stripe_publishable_key: {
                        required: true
                    },
                    live_stripe_webhook_secret: {
                        required: true
                    }
                },
                messages: {
                    stripe_secret_key: {
                        required: "Enter Your Stripe Account Secret ID.",
                    },
                    stripe_publishable_key: {
                        required: "Enter Your Stripe Account Auth Token.",
                    },

                    stripe_webhook_secret: {
                        required: "Enter Your Stripe Account Mobile Number."
                    },
                    live_stripe_secret_key: {
                        required: "Enter Your Live Stripe Account Secret ID.",
                    },
                    live_stripe_publishable_key: {
                        required: "Enter Your Live Stripe Account Auth Token.",
                    },

                    live_stripe_webhook_secret: {
                        required: "Enter Your Live Stripe Account Mobile Number."
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