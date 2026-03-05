<x-admin-layout title="Create Quiz Group">
    @section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.css">
    <style>
        .dark-mode input:-webkit-autofill {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #23232329;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-lg-6">
                        <h1>Quiz</h1>
                    </div>
                    <div class="col-lg-6">
                        <ol class="breadcrumb float-lg-right">
                            <li class="breadcrumb-item"><a href="">Home</a></li>
                            <li class="breadcrumb-item">Questions</li>
                            <li class="breadcrumb-item active">Quiz Group</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            <form id="quizGroupForm" action="{{ url('admin/create-quiz-group') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Quiz Group</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="quiz_group_order">Quiz Group Order</label>
                                            <input type="number" class="form-control" min="1" id="quiz_group_order" name="quiz_group_order" @if(isset($getLastQuizGroupOrder)) value="{{ $getLastQuizGroupOrder['quiz_group_order'] + 1 }}" @else value="1" @endif>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="color">Select Group Display color:</label>
                                            <input type="color" id="color" name="color" value="{{ old('title') }}">
                                        </div>
                                    </div>
                                    {{-- <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="image">Select Background Image:</label>
                                            <input title="Please select an image" type="file" id="quiz_group_image" name="quiz_group_image" accept="image/*" class="form-control">
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <div class="col-12 mb-2">
                        <input type="submit" value="Save" class="btn btn-success float-right quizGroupBtn">
                    </div>
                </div>
            </form>
        </section>
        <!-- /.content -->
    </div>

    @section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"></script>

    <script>
        $(document).ready(function() {
            $(function() {
                $('#quizGroupForm').validate({
                    rules: {
                        title: {
                            required: true,
                        },
                        quiz_group_order: {
                            required: true,
                        },
                    },
                    messages: {
                        title: {
                            required: "Please enter Quiz group title.",
                        },
                        quiz_group_order: {
                            required: "Please enter quiz group order",
                        },
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
        });
    </script>

    <script>
        // Convert the PHP array to a JSON string and assign it to a JavaScript variable
        var jsArray = @json($quizGroupOrder);

        // Get form and input elements
        var form = document.getElementById('quizGroupForm');
        var inputOrder = document.getElementById('quiz_group_order');

        // Add an event listener to the form submit event
        form.addEventListener('submit', function(event) {
            // Get the value from the input box
            var value = parseInt(inputOrder.value);

            // Check if the value is in the array
            if (jsArray.includes(value)) {
                // Show a confirmation box
                if (!confirm("Another Quiz Group is already associated with this position. Do you want to replace it?")) {
                    // Prevent form submission if the user clicks "Cancel"
                    event.preventDefault();
                }
            }
        });
    </script>
    @endsection
</x-admin-layout>
