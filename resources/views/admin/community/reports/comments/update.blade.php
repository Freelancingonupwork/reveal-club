<x-admin-layout title="Community Post">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <style>
        /* Optional: improves nested reply indentation */
        .list-group .list-group {
            margin-left: 1.5rem;
            border-left: 2px solid #dee2e6;
            padding-left: 1rem;
        }

        /* Smaller buttons neatly aligned */
        .btn-sm {
            margin-left: 0.25rem;
        }

        /* Show pointer for reply toggle */
        .btn-link {
            padding: 0;
            font-size: 0.9rem;
        }

        .no-link-style {
            color: inherit !important;
            text-decoration: none !important;
            cursor: pointer;
        }
        .no-link-style:hover {
            text-decoration: underline !important;
            color: #007bff;
        }
        .card-title button {
            font-size: 0.875rem;
        }
        .list-group-item {
            background-color: #f9f9f9;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Community Post</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Community</li>
                            <li class="breadcrumb-item">Community Report</li>
                            <li class="breadcrumb-item active">Community Comment Report</li>
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
            <div class="card">
                <div class="card-header d-flex">
                    <h4 class="mb-0">Comment Details</h4>
                    <a href="{{ route('admin.community-post-update', $commentData->post_id) }}" 
                       class="btn btn-sm btn-outline-primary"
                       title="Edit Post">
                        <i class="fas fa-edit"></i> Edit Comment with Post
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Comment:</strong>
                        <div class="border rounded p-3 bg-light" style="min-height:100px;">
                            {!! $commentData->content ?? '<em>No content available.</em>' !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    Posted by: <a href="{{ url('admin/user-profile/'.$commentData['user_id']) }}" class="no-link-style"> {{ $commentData->user->name ?? 'Unknown' }} </a><br>
                    Date: {{ $commentData->created_at ? $commentData->created_at->format('Y-m-d H:i') : 'N/A' }}
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="mb-0">Report Details</h4>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Reason</dt>
                        <dd class="col-sm-9">{{ $reportData->reason ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $reportData->feedback ?? 'No description provided.' }}</dd>

                        <dt class="col-sm-3">Reported By</dt>
                        <dd class="col-sm-9">
                            @if(isset($reportData->user))
                                <a href="{{ url('admin/user-profile/'.$reportData->user->id) }}" class="no-link-style">
                                    {{ $reportData->user->name ?? 'Unknown User' }}
                                </a>
                            @else
                                Unknown User
                            @endif
                        </dd>

                        <dt class="col-sm-3">Reported At</dt>
                        <dd class="col-sm-9">
                            {{ $reportData->created_at ? $reportData->created_at->format('Y-m-d H:i') : 'N/A' }}
                        </dd>
                    </dl>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.community-post-comment-report-update', [$reportData->is_comment_or_reply,$reportData->id]) }}">
                @csrf
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="mark_as_solved" id="markAsSolved" value="1" {{ $reportData->mark_as_solved ? 'checked' : '' }}>
                    <label class="form-check-label" for="markAsSolved">
                        Mark as Solved
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
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
