<x-admin-layout title="Users">
    @section('styles')
    <style>
        /* Overlay background */
        #date-range-popup {
            display: none; /* Initially hidden */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        /* Popup container */
        .popup-content {
            background: #343a40;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
            position: relative;
        }
        
        /* Close button */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: #555;
        }
        
        .close-btn:hover {
            color: #000;
        }
        
        /* Label styles */
        .popup-content label {
            font-size: 14px;
            font-weight: 600;
            display: block;
            margin: 10px 0 5px;
        }
        
        /* Input fields */
        .popup-content input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        
        /* Export button */
        #export-users-range-btn {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        
        #export-users-range-btn:hover {
            background: #0056b3;
        }
    </style>
    @endsection
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">User</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- @if(session()->has('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
            @endif -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">User List</h3>
                                <!-- <div class="card-tools">
                                    <a type="button" href="{{ route('admin.create-user') }}" class="btn btn-secondary">
                                        <i class="nav-icon fas fa-plus"></i>
                                        Add User
                                    </a>
                                </div> -->
                                <div class="card-tools">
                                    <button id="export-users-btn" class="btn btn-secondary">
                                        Export New Users
                                    </button>
                                </div>
                            </div>
                            <!-- Date Range Popup (Hidden by default) -->
                            <div id="date-range-popup" style="display: none;">
                                <div class="popup-content">
                                    <span class="close-btn" onclick="closePopup()">&times;</span>
                                    <h3>Select Date Range</h3>
                                    <label for="start-date">Start Date:</label>
                                    <input type="date" id="start-date">
                                    <label for="end-date">End Date:</label>
                                    <input type="date" id="end-date">
                                    <button id="export-users-range-btn">Export</button>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                @if(count($users) > 0)
                                <table id="customTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Avatar</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $key => $user)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $user['name'] }}</td>
                                            <td>{{ $user['email'] }}</td>
                                            <td>{{ $user['mobile'] }}</td>
                                            <td>
                                                @if(!$user['avatar'])
                                                <img src="{{ asset('adminAssets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image" style="width:35px; height: 35px;">
                                                @else
                                                <img src="{{ url('/storage/'.$user['avatar']) }}" class="img-circle elevation-2" alt="User Image" style="width:35px; height: 35px;">
                                                @endif
                                            </td>
                                            <td>
                                                @if ($user['status'] == 1)

                                                <a class="updateUserStatus" id="user-{{ $user['id'] }}" user_id="{{ $user['id'] }}" href="javascript:void(0)"><span class="badge badge-success" status="Active">Active</span>
                                                    @else
                                                    <a class="updateUserStatus" id="user-{{ $user['id'] }}" user_id="{{ $user['id'] }}" href="javascript:void(0)"><span class="badge badge-warning" status="Blocked">Blocked</span>
                                                        @endif
                                            </td>
                                            <td>
                                                <!-- <a href="{{ url('admin/user-show', $user['id']) }}" class="btn btn-success"><i class="fas fa-eye"></i></a> -->
                                                <a href="{{ url('admin/user-profile/'.$user['id']) }}" class="btn btn-primary"><i class="fas fa-address-card"></i></a>
                                                <a href="javascript:void(0);" class="btn btn-danger confirmDelete" module="user" moduleId="{{ $user['id'] }}"  message="User Deleted Successfully"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Avatar</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                @else
                                <h1>Users Not Found</h1>
                                @endif
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

        function closePopup() {
            document.getElementById('date-range-popup').style.display = 'none';
        }

        document.getElementById('export-users-btn').addEventListener('click', function () {
            document.getElementById('date-range-popup').style.display = 'flex';
        });

        document.getElementById('export-users-range-btn').addEventListener('click', function () {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            // Check if a date range is selected
            if (startDate && endDate) {
                fetch(`/admin/export-new-users?start_date=${startDate}&end_date=${endDate}`)
                    .then(response => handleResponse(response))
                    .catch(error => handleError(error));
            } else {
                // No date range, export users as before
                fetch('/admin/export-new-users')
                    .then(response => handleResponse(response))
                    .catch(error => handleError(error));
            }

            // Close the popup
            document.getElementById('date-range-popup').style.display = 'none';
        });

        // Common function to handle the response
        function handleResponse(response) {
            const isFile = response.headers.get('content-disposition');

            if (isFile) {
                // If it's a file, process as Blob
                response.blob().then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'new_users.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    Swal.fire({
                        title: 'Success!',
                        text: 'CSV downloaded successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                    });
                });
            } else {
                // If it's a JSON response (like no users found), handle accordingly
                response.json().then(data => {
                    Swal.fire({
                        title: 'No New Users',
                        text: data.message || 'No users to export.',
                        icon: 'info',
                        confirmButtonText: 'OK',
                    });
                });
            }
        }

        // Common error handler
        function handleError(error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to export users. Please try again later.',
                icon: 'error',
                confirmButtonText: 'OK',
            });
        }
    </script>
    @endsection
</x-admin-layout>
