<x-admin-layout title="Activity">
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Activities</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Activities</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Activities List</h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.activities.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New Activity
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    {{ session('success') }}
                                </div>
                            @endif
                            
                            <table id="activitiesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Default Burnout/Hour</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activities as $key => $activity)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if($activity->image)
                                                <img src="{{ $activity->image }}" alt="{{ $activity->name }}" style="max-width: 50px; max-height: 50px;">
                                            @else
                                                <img src="{{ asset('admin/dist/img/default-150x150.png') }}" alt="No Image" style="max-width: 50px; max-height: 50px;">
                                            @endif
                                        </td>
                                        <td>{{ $activity->name }}</td>
                                        <td>{{ number_format($activity->default_burnout_per_hour, 2) }}</td>
                                        <td>
                                            <a href="javascript:void(0);" class="updateStatus" id="activity-{{ $activity->id }}" activity_id="{{ $activity->id }}">
                                                @if($activity->status == 1)
                                                    <span class="badge badge-success" status="Active">Active</span>
                                                @else
                                                    <span class="badge badge-danger" status="Inactive">Inactive</span>
                                                @endif
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.activities.edit', $activity->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.activities.destroy', $activity->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this activity?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {
            // Update status
            $(document).on("click", ".updateStatus", function() {
                var status = $(this).children().attr("status");
                var activity_id = $(this).attr("activity_id");
                
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: "{{ route('admin.update-activity-status') }}",
                    data: {
                        status: status,
                        activity_id: activity_id
                    },
                    success: function(resp) {
                        if (resp.status == 0) {
                            $("#activity-" + activity_id).html(
                                "<a href=\"javascript:void(0);\" class=\"updateStatus\" id=\"activity-" + activity_id + "\" activity_id=\"" + activity_id + "\"><span class=\"badge badge-danger\" status=\"Inactive\">Inactive</span></a>"
                            );
                        } else if (resp.status == 1) {
                            $("#activity-" + activity_id).html(
                                "<a href=\"javascript:void(0);\" class=\"updateStatus\" id=\"activity-" + activity_id + "\" activity_id=\"" + activity_id + "\"><span class=\"badge badge-success\" status=\"Active\">Active</span></a>"
                            );
                        }
                    }
                });
            });
        });
    </script>
@endsection
</x-admin-layout>
