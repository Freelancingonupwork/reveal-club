<x-admin-layout title="Create Package">
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Package Add</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item active">Package Add</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <form id="quickForm" @if(!isset($plan) || empty($plan)) action="{{ url('admin/plan-create') }}" @else action="{{ url('admin/plan-update/'.$plan['slug'].'/'.$plan['id']) }}" @endif method="post" enctype="multipart/form-data">
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
                                <h3 class="card-title">Package Add</h3>
                                <div class="card-tools">
                                    <a type="button" class="btn btn-secondary" href="{{ url('admin/plan-index') }}">
                                        <i class="fas fa-arrow-left"></i>
                                        Return to List
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputName">Name<span style="color: #ff5252;">*</span></label>
                                            <input type="text" id="name" name="name" class="form-control" @if(isset($plan)) value="{{ $plan->name }}" @else value="{{ old('name') }}" @endif>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                    <div class="form-group">
                                            <label for="inputName">Offer Label </label>
                                            <input type="text" id="offer_label" name="offer_label" class="form-control" @if(isset($plan)) value="{{ $plan->offer_label }}" @else value="{{ old('offer_label') }}" @endif>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputDescription">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="10">@if(isset($plan)) {{ $plan->description }} @else {{ old('description') }} @endif</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputName">Price<span style="color: #ff5252;">*</span></label>
                                            <input type="number" id="price" name="price" class="form-control" @if(isset($plan)) value="{{ $plan->price }}" @else value="{{ old('price') }}" @endif>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="discprice">Discounted Price<span style="color: #ff5252;">*</span></label>
                                            <input type="number" id="discprice" name="discprice" class="form-control" @if(isset($plan)) value="{{ $plan->discprice }}" @else value="{{ old('discprice') }}" @endif>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputName">Total Price<span style="color: #ff5252;">*</span></label>
                                            <input type="number" id="total_price" name="total_price" class="form-control" @if(isset($plan)) value="{{ $plan->total_price }}" @else value="{{ old('total_price') }}" @endif>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="discprice">Total Discounted Price<span style="color: #ff5252;">*</span></label>
                                            <input type="number" id="total_disc_price" name="total_disc_price" class="form-control" @if(isset($plan)) value="{{ $plan->total_disc_price }}" @else value="{{ old('total_disc_price') }}" @endif>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputName">Package Duration<span style="color: #ff5252;">*</span></label>
                                    <select class="form-control" name="plan_type" id="plan_type">
                                        <option value="">Select Package Duration</option>
                                        <option value="0" @if(isset($plan) && $plan['plan_type']==0) selected @endif style="font-weight: bold;">Monthly</option>
                                        <option value="1" @if(isset($plan) && $plan['plan_type']==1) selected @endif style="font-weight: bold;">Quartarly</option>
                                        <option value="4" @if(isset($plan) && $plan['plan_type']==4) selected @endif style="font-weight: bold;">Half Yearly</option>
                                        <option value="2" @if(isset($plan) && $plan['plan_type']==2) selected @endif style="font-weight: bold;">Yearly</option>
                                        <option value="3" @if(isset($plan) && $plan['plan_type']==3) selected @endif style="font-weight: bold;">Bi-Yearly</option>
                                    </select>
                                </div>
                                <div class="form-check freetrial">
                                    <input type="checkbox" class="form-check-input" id="freetrial" name="freetrial" @if(isset($plan) && $plan['freetrial'] == 1) checked @endif>
                                    <label class="form-check-label" for="freetrial">Free Trial</label>
                                </div>

                                <div class="form-group trialdays">
                                    <label for="trialdays">Free Trial Days</label>
                                    <input type="number" class="form-control" id="trialdays" name="trialdays" value="{{ isset($plan) ? $plan->trialdays : old('trialdays') }}" placeholder="Enter number of free trial days">
                                </div>

                                <div class="form-group" id="is_yearly_commitment_group" style="display:none;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_yearly_commitment" name="is_yearly_commitment"
                                            @if(isset($plan) && $plan->is_yearly_commitment) checked @elseif(old('is_yearly_commitment')) checked @endif>
                                        <label class="form-check-label" for="is_yearly_commitment">Yearly Commitment – Monthly Billing (lock-in for 12 months, cancel only last month)</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputName">Image</label>
                                    <input type="file" id="image" name="image" class="form-control">
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="for_klarna" name="for_klarna" @if(isset($plan) && $plan['for_klarna']==1) checked @endif>
                                            <label for="for_klarna">Only use with Klarna</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="status" name="status" @if(isset($plan) && $plan['status']==1) checked @endif>
                                            <label for="status">Enable</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- new lines start here -->
                                @if(isset($plan) && !empty($plan))
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="apply_to_existing" name="apply_to_existing" value="1"
                                                {{ old('apply_to_existing', $plan->apply_to_existing ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="apply_to_existing">
                                            Apply new price to existing customers?
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <strong>Note:</strong> If checked, all active subscriptions for this plan will be updated to the new price immediately in Stripe.
                                        Users will be notified via email about the price change. If unchecked, existing customers will remain on their current price (grandfathered).
                                    </small>
                                </div>
                                @endif
                                <!-- new lines ends here -->
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-2">
                        <input type="submit" @if(!isset($plan)) value="Create Package" @else value="Update Package" @endif class="btn btn-success float-right">
                    </div>
                </div>
            </form>
        </section>
        <!-- /.content -->
    </div>

    @section('scripts')

    <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
    <script src="{{ asset('assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-validation/additional-methods.min.js') }}"></script>

    <script>
        $(function() {
            $('#quickForm').validate({
                rules: {
                    name: {
                        required: true,
                    },
                    price: {
                        required: true
                    },
                    discprice: {
                        required: true
                    },
                    plan_type: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Please Enter Package Name",
                    },
                    price: {
                        required: "Please enter Package Price"
                    },
                    discprice: {
                        required: "Please enter Package Discounted Price"
                    },
                    plan_type: {
                        required: "Please select plan type."
                    }
                },
                errorElement: 'span',
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
        $(document).ready(function() {
            // Initially hide elements
            $(".freetrial, .trialdays").hide();

            // Function to handle Klarna checkbox logic
            function handleKlarnaToggle() {
                if ($('#for_klarna').is(':checked')) {
                    // Hide and disable freetrial fields when Klarna is selected
                    $(".freetrial, .trialdays").hide();
                    $('#freetrial').prop('checked', false);
                    $('#trialdays').val('');
                } else {
                    // Show freetrial based on plan type when Klarna is not selected
                    var toggle = $('select[name="plan_type"]').val();
                    if (toggle == 1 || toggle == 2 || toggle == 3 || toggle == 4) {
                        $(".freetrial").show();
                        if ($('#freetrial').is(':checked')) {
                            $('.trialdays').show();
                        }
                    }
                }
            }

            var toggle = $('select[name="plan_type"]').val();

            // Initial display logic
            if ($('#for_klarna').is(':checked')) {
                // If Klarna is checked, hide trial fields
                $(".freetrial, .trialdays").hide();
            } else if (toggle == 1 || toggle == 2 || toggle == 3 || toggle == 4) {
                $(".freetrial").show();
                if ($('#freetrial').is(':checked')) {
                    $('.trialdays').show();
                } else {
                    $('.trialdays').hide();
                }
            } else {
                $(".freetrial, .trialdays").hide();
            }

            // When Klarna checkbox is toggled
            $('#for_klarna').change(function() {
                handleKlarnaToggle();
            });

            // When plan type is changed
            $('#plan_type').change(function () {
                if ($('#for_klarna').is(':checked')) {
                    // If Klarna is selected, keep fields hidden
                    $(".freetrial, .trialdays").hide();
                } else if (this.value == 1 || this.value == 2 || this.value == 3 || this.value == 4) {
                    $(".freetrial").show();
                    if ($('#freetrial').is(':checked')) {
                        $('.trialdays').show();
                    } else {
                        $('.trialdays').hide();
                    }
                } else {
                    $(".freetrial, .trialdays").hide();
                }
            });

            // When freetrial checkbox is toggled
            $('#freetrial').change(function () {
                if ($(this).is(':checked')) {
                    $('.trialdays').show();
                } else {
                    $('.trialdays').hide();
                }
            });
        });
    </script>
    <script>
        $(function() {
            // Summernote
            $('#description').summernote();
        })
    </script>
    <script>
        $(document).ready(function() {
            function toggleYearlyCommitment() {
                if($('#plan_type').val() == '0') {
                    $('#is_yearly_commitment_group').show();
                } else {
                    $('#is_yearly_commitment').prop('checked', false);
                    $('#is_yearly_commitment_group').hide();
                }
            }
            toggleYearlyCommitment();
            $('#plan_type').change(toggleYearlyCommitment);
        });
    </script>

    @endsection
</x-admin-layout>
