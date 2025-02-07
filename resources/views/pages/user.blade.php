@extends('layouts.dashboard')

@section('title', 'Sorting System')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">List User</h1>
</div>

{{-- Success Alert --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Error Alert --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid mt-4">
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <span data-feather="plus" class="align-text-bottom"></span>
        Add User
    </button>
    <table id="example" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th class="text-center">NPK</th>
                <th class="text-center">Name</th>
                <!-- <th>Section</th>
                <th>Authority</th> -->
                <th class="text-center">Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr data-id="{{ $user->user_id }}">
                <td class="text-center">{{ $user->npk }}</td>
                <td class="text-center">{{ $user->name }}</td>
                <td class="text-center">{{ $user->is_active == '1' ? 'Active' : 'Non Active' }}</td>
                <td class="text-center">
                    <button class="btn btn-success edit-btn" data-id="{{ $user->user_id }}" data-bs-toggle="modal" data-bs-target="#editUserModal">
                        <span data-feather="edit" class="align-text-bottom"></span>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addUserModalLabel">Add User</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ url('users') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="npk" class="form-label">NPK</label>
                        <input type="text" class="form-control" name="npk" id="npk" aria-describedby="npkHelp">
                        <!-- <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div> -->
                    </div>
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Name</label>
                        <input type="text" class="form-control" name="user_name" id="user_name" aria-describedby="userNameHelp">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password">
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Retype Password</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary">Save</button> -->
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editUserModalLabel">Edit User</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" action="{{ url('users') }}" action="{{ route('user.update', ['user_id' => 'user_id_placeholder']) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label for="npk" class="form-label">NPK</label>
                        <input type="text" class="form-control" name="npk" id="editNpk" aria-describedby="npkHelp">
                    </div>
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Name</label>
                        <input type="text" class="form-control" name="user_name" id="editName" aria-describedby="userNameHelp">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="editStatus" aria-label="Status select">
                            <option value="1">Active</option>
                            <option value="0">Non Active</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('addon-script')
    <script>
        new DataTable('#example');

        $(document).ready(function() {
            $(document).on('click', '.edit-btn', function() {
                var userId = $(this).data('id');
                // Optionally, use AJAX to load user data into a modal or form
                $.ajax({
                    url: '/users/' + userId + '/edit',  // Assuming you're using a route for editing
                    method: 'GET',
                    success: function(data) {
                        // Populate the form fields with the user's data
                        $('#editUserId').val(data.user_id);
                        $('#editNpk').val(data.npk);
                        $('#editName').val(data.name);
                        $('#editStatus').val(data.is_active);

                        // Update form action with the correct user_id
                        var formAction = '{{ route("user.update", ["user_id" => ":user_id"]) }}';
                        formAction = formAction.replace(':user_id', userId);
                        $('#editForm').attr('action', formAction);

                        // Show the modal
                        // $('#editUserModal').show();
                    }
                });
            });
        });
    </script>
@endpush