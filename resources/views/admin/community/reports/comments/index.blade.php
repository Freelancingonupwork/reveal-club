<x-admin-layout title="Community Post">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    @endsection
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Community Reports</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Community</li>
                            <li class="breadcrumb-item">Community Reports</li>
                            <li class="breadcrumb-item active">Community Comment Reports</li>
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
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Community Comment Report List</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="customTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Comment Content</th>
                                        <th>Reported User</th>
                                        <th>Reason</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allReports as $key => $report)
                                    <tr @if($report->mark_as_solved == 0) style="background-color: #fffb0091;" @endif>
                                        <td>{{ $key + 1 }}</td>
                                        @if ($report->is_comment_or_reply == 'comment')
                                        <td>{{ $report->comment->comment }}</td>
                                        @else
                                        <td>{{ $report->reply->reply }}</td>
                                        @endif
                                        <td>{{ $report->user->name }}</td>
                                        <td>{{ $report->reason }}</td>
                                        <td>{{ $report->is_comment_or_reply }}</td>
                                        <td>
                                            <a href="{{ url('admin/community-post-comment-report-update/'.$report->is_comment_or_reply.'/'.$report->id) }}" class="btn btn-primary"><i class="fas fa-eye"></i></a>
                                            <a href="{{ url('admin/community-report-delete/'.$report->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this report?')" ><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Comment Content</th>
                                        <th>Reported User</th>
                                        <th>Reason</th>
                                        <th>Type</th>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endsection
</x-admin-layout>