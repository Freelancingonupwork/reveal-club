<?php

use App\Models\ProgramLevel; ?>
<x-admin-layout title="Programs">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Programs</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Programs</li>
                            <li class="breadcrumb-item active">Program</li>
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
                            <h3 class="card-title">Program List</h3>
                            <div class="card-tools">
                                <a type="button" href="{{ route('admin.program-create') }}" class="btn btn-secondary">
                                    <i class="nav-icon fas fa-plus"></i>
                                    Add Program
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
                                        <th>Category</th>
                                        <th>Level</th>
                                        <th>Body Area</th>
                                        <th>Duration</th>
                                        <th>Frequency</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programsData as $key => $program)
                                    <?php

                                    if (!empty($program['category'])) {
                                        $category_name = $program['category']['category_name'];
                                    } else {
                                        $category_name = "";
                                    }

                                    $level = ProgramLevel::where(['id' => $program['level_id']])->first();
                                    ?>
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $program['title'] }}</td>
                                        <td>{{ $category_name }}</td>
                                        <td>{{ $level['level_title'] }}</td>
                                        <td>{{ $program['body_area'] }}</td>
                                        <td>{{ $program['duration'] }}</td>
                                        <td>{{ $program['frequency'] }}</td>
                                        <td>
                                            @if ($program['status'] == 1)

                                            <a class="updateProgramStatus" id="program-{{ $program['id'] }}" program_id="{{ $program['id'] }}" href="javascript:void(0)"><span class="badge badge-success" status="Active">Active</span>
                                                @else
                                                <a class="updateProgramStatus" id="program-{{ $program['id'] }}" program_id="{{ $program['id'] }}" href="javascript:void(0)"><span class="badge badge-warning" status="Inactive">Inactive</span>
                                                    @endif
                                        </td>
                                        <td>
                                            <!-- <a href="" class="btn btn-success"><i class="fas fa-eye"></i></a> -->
                                            <a href="{{ url('admin/program-update/'.$program['slug'] .'/'. $program['id']) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                            <!-- <a href="{{ url('admin/delete/'.$program['slug'] .'/'. $program['id']) }}" class="btn btn-danger"><i class="fas fa-trash"></i></a> -->
                                            <a href="javascript:void(0);" class="btn btn-danger confirmDelete" module="program" slug="{{ $program['slug'] }}" moduleId="{{ $program['id'] }}" message="Program Deleted Successfully"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Level</th>
                                        <th>Body Area</th>
                                        <th>Duration</th>
                                        <th>Frequency</th>
                                        <th>Status</th>
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
