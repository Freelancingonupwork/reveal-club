<x-admin-layout title="Questions">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    @endsection

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Quiz</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Questions</li>
                            <li class="breadcrumb-item active">Quiz Group</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
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
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Quiz Group</h3>
                                <div class="card-tools">
                                    <a type="button" href="{{ route('create-quiz-group') }}" class="btn btn-secondary">
                                        <i class="nav-icon fas fa-plus"></i>
                                        Add Quiz Group
                                    </a>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="customTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Title</th>
                                            <th>Slug</th>
                                            <!-- <th style="width: 25%;">Change Order</th> -->
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reorder_position">
                                        @foreach($quizGroups as $key => $quizGroup)
                                        <tr class="tableRow" data-id="{{ $quizGroup['id'] }}">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $quizGroup['title'] }}</td>
                                            <td>{{ $quizGroup['slug'] }}</td>
                                            <!-- <td>
                                                <form id="quizGroupForm" action="{{ url('admin/change-quiz-group-order/'. $quizGroup['id']) }}" method="post">
                                                    {{ csrf_field() }}
                                                    <div class="row">
                                                        <input type="numeric" id="quiz_group_order" name="quiz_group_order" class="form-control" value="{{ $quizGroup['quiz_group_order'] }}" style="width: 15%;">
                                                        <input type="submit" value="Update Order" class="btn btn-sm btn-info ml-2 quizGroupBtn">
                                                    </div>
                                                </form>
                                            </td> -->
                                            <td>
                                                <a href="{{ url('admin/update-quiz-group/'.$quizGroup['slug'] .'/'. $quizGroup['id']) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                                <!-- <a href="{{ url('admin/delete/'.$quizGroup['slug'] .'/'. $quizGroup['id']) }}" class="btn btn-danger"><i class="fas fa-trash"></i></a> -->
                                                <a href="javascript:void(0);" class="btn btn-danger confirmDelete" module="quiz-group" slug="{{ $quizGroup['slug'] }}" moduleId="{{ $quizGroup['id'] }}" message="Quiz Group Deleted Successfully"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Title</th>
                                            <th>Slug</th>
                                            <!-- <th style="width: 25%;">Change Order</th> -->
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
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
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(document).ready(function() {
            $("#reorder_position").sortable({
                items: "tr",
                cursor: 'move',
                opacity: 0.6,
                update: function() {
                    updateQuizGroupPosition();
                }
            });

            function updateQuizGroupPosition() {
                var orderPosition = [];
                var token = $('meta[name="csrf-token"]').attr('content');
                var url = "/admin/set-position"

                $('tr.tableRow').each(function(index, element) {
                    orderPosition.push({
                        id: $(this).attr('data-id'),
                        position: index + 1
                    });
                });

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "/admin/set-position",
                    data: {
                        order: orderPosition,
                        _token: token
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            alert(response)
                            console.log(response);
                        } else {
                            alert("error");
                            console.log(response);
                        }
                    }
                });
            }
        });
    </script>
    @endsection
</x-admin-layout>
