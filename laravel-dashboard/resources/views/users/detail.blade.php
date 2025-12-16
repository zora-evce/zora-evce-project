@extends('templates/template')
@section('content')
    <section class="content">
        <div class="container-fluid">
            <!-- Account Information Card -->
            <div class="row g-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-primary text-white text-center py-3">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="fas fa-info-circle me-2"></i>Account Information
                            </h5>
                        </div>
                        <div class="card-body p-4 bg-white">
                            @if($account)
                                @php
                                    $accountArray = $account->toArray();
                                @endphp
                                <div class="row">
                                    @foreach($accountArray as $key => $value)
                                        @if(!in_array($key, ['deleted_at', 'created_at', 'updated_at']) && $value !== null)
                                            <div class="col-md-4 mb-3">
                                                <div class="pb-2 border-bottom">
                                                    <span class="text-muted small text-uppercase fw-semibold">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                    <div class="fw-medium text-dark mt-1">
                                                        @if(is_bool($value))
                                                            {{ $value ? 'Yes' : 'No' }}
                                                        @elseif(is_array($value))
                                                            {{ json_encode($value) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    <!-- Address from Stations -->
                                    @if($stations && $stations->count() > 0)
                                        @php
                                            $stationsWithAddress = $stations->filter(function($station) {
                                                return !empty($station->address);
                                            });
                                        @endphp

                                        @if($stationsWithAddress->count() > 0)
                                            @foreach($stationsWithAddress as $station)
                                                <div class="col-md-4 mb-3">
                                                    <div class="pb-2 border-bottom">
                                                        <span class="text-muted small text-uppercase fw-semibold">
                                                            @if($station->name)
                                                                Address ({{ $station->name }})
                                                            @elseif($station->code)
                                                                Address (Station {{ $station->code }})
                                                            @else
                                                                Address
                                                            @endif
                                                        </span>
                                                        <div class="fw-medium text-dark mt-1">
                                                            {{ $station->address }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-exclamation-circle text-muted fa-3x mb-3"></i>
                                    <p class="text-muted mb-0">No account information found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 1: Stations -->
            <div class="row g-4 mt-2">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-primary text-white text-center py-3">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="fas fa-charging-station me-2"></i>Stations
                            </h5>
                        </div>
                        <div class="card-body p-4 bg-white">
                            @if($stations && $stations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30px;">No</th>
                                                <th>Station Code</th>
                                                <th>Station Name</th>
                                                <th>Status</th>
                                                <th>Connectivity</th>
                                                <th>Connectors</th>
                                                <th>Last Heartbeat</th>
                                                <th style="width: 80px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stations as $index => $station)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $station->code ?? '-' }}</td>
                                                    <td>{{ $station->name ?? '-' }}</td>
                                                    <td>
                                                        @if($station->status == 'available')
                                                            <span class="badge badge-primary">Available</span>
                                                        @elseif($station->status == 'charging')
                                                            <span class="badge badge-warning">Charging</span>
                                                        @elseif($station->status == 'faulted')
                                                            <span class="badge badge-danger">Faulted</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ ucfirst($station->status ?? '-') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($station->connectivity_status == 'online')
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-signal-alt"></i> Online
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-signal-alt-slash"></i> Offline
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $station->connectors_count ?? 0 }}</td>
                                                    <td>
                                                        @if($station->last_heartbeat_at)
                                                            {{ \Carbon\Carbon::parse($station->last_heartbeat_at)->format('Y-m-d H:i:s') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('cpo.stations.details') }}?id={{ $station->id }}"
                                                           class="btn btn-primary btn-sm"
                                                           title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-charging-station text-muted fa-3x mb-3"></i>
                                    <p class="text-muted mb-0">No stations found for this account.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: CRUD Users -->
            <div class="row g-4 mt-2">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-primary text-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-semibold">
                                    <i class="fas fa-users me-2"></i>Users
                                </h5>
                                <button type="button" class="btn btn-light btn-sm" id="btn-add-user">
                                    <i class="fas fa-plus"></i> Add User
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4 bg-white">
                            <div class="table-responsive">
                                <table id="usersTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Created At</th>
                                            <th style="width: 150px;"><i class="fas fa-cogs"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($users && $users->count() > 0)
                                            @foreach($users as $index => $user)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $user->name ?? '-' }}</td>
                                                    <td>{{ $user->email ?? '-' }}</td>
                                                    <td>
                                                        @if($user->id_role == 1)
                                                            <span class="badge badge-primary">Admin</span>
                                                        @elseif($user->id_role == 2)
                                                            <span class="badge badge-info">Partner</span>
                                                        @else
                                                            <span class="badge badge-secondary">Unknown</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($user->created_at)
                                                            {{ $user->created_at->format('Y-m-d H:i:s') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group align-items-center" role="group">
                                                            <a href="#" class="btn btn-primary btn-sm action-edit" data-id="{{ $user->id }}">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('cpo.users.destroy', $user->id) }}" method="POST" class="d-inline action-delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm action-delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="fas fa-users text-muted fa-3x mb-3"></i>
                                                    <p class="text-muted mb-0">No users found for this account.</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Add/Edit User -->
        <div class="modal fade" id="modal-form">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal-title">Add User</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modal-body">
                        <!-- Form will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Add User button click event
        $('#btn-add-user').on('click', function() {
            let accountId = {{ $account->account_id }};
            $('#modal-title').text('Add User');
            let createUrl = '{{ route("cpo.users.create") }}?account_id=' + accountId;
            $('#modal-body').load(createUrl, function() {
                // Automatically set role to Partner (2) and show partner field
                $('#id_role').val('2').trigger('change');
                $('#modal-form').modal('show');
            });
        });

        // Edit User button click event
        $(document).on('click', '.action-edit', function(e) {
            e.preventDefault();
            let userId = $(this).data('id');
            let editUrl = '{{ route("cpo.users.edit", ":id") }}'.replace(':id', userId);
            $('#modal-title').text('Edit User');
            $('#modal-body').load(editUrl, function() {
                $('#modal-form').modal('show');
            });
        });

        // Handle form submission via AJAX
        $(document).on('submit', '#user-form', function(e) {
            e.preventDefault();
            let form = $(this);
            let accountId = {{ $account->account_id }};

            Swal.fire({
                title: 'Warning!',
                text: "Are you sure want to save this data?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Save',
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.value) {
                    let formData = form.serialize();
                    let url = form.attr('action');
                    let method = form.find('input[name="_method"]').val() || 'POST';

                    // Remove previous error messages
                    $('.alert-danger').remove();
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    $.ajax({
                        url: url,
                        type: method,
                        data: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#modal-form').modal('hide');
                                toastr.success(response.message || 'Operation successful');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 401 || xhr.status === 403) {
                                let response = xhr.responseJSON;
                                if (response && response.redirect) {
                                    toastr.error(response.message || 'Session expired. Please login again.');
                                    setTimeout(function() {
                                        window.location.href = response.redirect;
                                    }, 1500);
                                } else {
                                    toastr.error('Session expired. Please login again.');
                                    setTimeout(function() {
                                        window.location.href = '{{ route("cpo.login") }}';
                                    }, 1500);
                                }
                            } else if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                                $.each(errors, function(key, value) {
                                    errorHtml += '<li>' + value[0] + '</li>';
                                    let field = form.find('[name="' + key + '"]');
                                    field.addClass('is-invalid');
                                    field.after('<div class="invalid-feedback">' + value[0] + '</div>');
                                });
                                errorHtml += '</ul></div>';
                                $('#modal-body').prepend(errorHtml);
                                $('#modal-body').scrollTop(0);
                            } else {
                                toastr.error('An error occurred. Please try again.');
                            }
                        }
                    });
                }
            });
        });

        // Reset modal on close
        $('#modal-form').on('hidden.bs.modal', function() {
            $('#modal-body').html('');
            $('.alert-danger').remove();
        });

        // Handle delete user with confirmation
        $(document).on('submit', '.action-delete-form', function(e) {
            e.preventDefault();
            let form = $(this);
            let userName = form.closest('tr').find('td:eq(1)').text() || 'this user';

            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete ${userName}? This action cannot be undone!`,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            toastr.success('User deleted successfully');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            if (xhr.status === 401 || xhr.status === 403) {
                                let response = xhr.responseJSON;
                                if (response && response.redirect) {
                                    toastr.error(response.message || 'Session expired. Please login again.');
                                    setTimeout(function() {
                                        window.location.href = response.redirect;
                                    }, 1500);
                                } else if (response && response.message) {
                                    toastr.error(response.message);
                                } else {
                                    toastr.error('Session expired. Please login again.');
                                    setTimeout(function() {
                                        window.location.href = '{{ route("cpo.login") }}';
                                    }, 1500);
                                }
                            } else {
                                let errorMessage = 'An error occurred while deleting the user.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                toastr.error(errorMessage);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
