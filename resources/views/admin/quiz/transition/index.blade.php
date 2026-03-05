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
                            <li class="breadcrumb-item active">Transition</li>
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
                                <h3 class="card-title">Transitions</h3>
                                <div class="card-tools">
                                    <a type="button" href="{{ route('create-transition') }}" class="btn btn-secondary">
                                        <i class="nav-icon fas fa-plus"></i>
                                        Add Transition
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
                                            <th>Image</th>
                                            <th>Status </th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transitions as $key => $transition)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $transition['title'] }}</td>
                                            <td>{{ $transition['slug'] }}</td>
                                            <td>@if(!empty($transition['transition_image']))
                                                <img src="{{ url('/storage/'.$transition['transition_image']) }}" class="img-circle elevation-2" alt="Quiz Image" style="width:100px; height: 100px;">
                                                @else
                                                <img src="{{ asset('adminAssets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image" style="width:100px; height: 100px;">
                                                @endif
                                            </td>
                                            <td>
                                                @if($transition['status'] == 1)
                                                    <span class="badge badge-success">Enabled</span>
                                                @else
                                                    <span class="badge badge-warning">Disabled</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/update-transition/'.$transition['slug'] .'/'. $transition['id']) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                                <!-- <a href="{{ url('admin/delete/'.$transition['slug'] .'/'. $transition['id']) }}" class="btn btn-danger"><i class="fas fa-trash"></i></a> -->
                                                <a href="javascript:void(0);" class="btn btn-danger confirmDelete" module="transition" slug="{{ $transition['slug'] }}" moduleId="{{ $transition['id'] }}" message="Transition Deleted Successfully"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Title</th>
                                            <th>Slug</th>
                                            <th>Image</th>
                                            <th>Status </th>
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
    @endsection
</x-admin-layout>
