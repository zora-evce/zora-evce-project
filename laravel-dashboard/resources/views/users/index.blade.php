@extends('templates/template')
@section('content')
    <style>
        .btn-group .btn-divider {
            width: 2px;
            margin: 0 0px;
            height: 24px;
            align-self: center;
        }
    </style>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Users</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('cpo.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-filter me-1"></i> Data
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-user">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label for="filter_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="filter_name" placeholder="Search by name...">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="filter_email" placeholder="Search by email...">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_role" class="form-label">Role</label>
                            <select class="form-control" id="filter_role" style="width: 100%;">
                                <option value="">All Roles</option>
                                <option value="1">Admin</option>
                                <option value="2">Partner</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-primary" id="btn_filter"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_reset"><i class="fas fa-redo-alt"></i></button>
                            <div id="colvis-container" style="display: inline-block; margin-left: 5px; vertical-align: middle;"></div>
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="auditTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Created At</th>
                                            <th style="width: 120px;"><i class="fas fa-cogs"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end bg-white border-0 pt-0">
                </div>
            </div>
        </div>
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
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </section>
    <script>
        // Standard Select2 for Role
        const $filterRole = $('#filter_role').select2({
            placeholder: 'All Roles',
            allowClear: true,
            theme: 'bootstrap4'
        });

        let table = $("#auditTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12 col-md-6'Bl><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-columns"></i> Columns',
                    className: 'btn-sm btn-primary'
                }
            ],
            ajax: {
                url: '{{ route("cpo.users.get-data") }}',
                type: 'GET',
                data: function(d) {
                    d.filter_name = $('#filter_name').val();
                    d.filter_email = $('#filter_email').val();
                    d.filter_role = $filterRole.val();
                },
                error: function(xhr) {
                    if (xhr.status === 401 || xhr.status === 403) {
                        // Session expired or unauthorized
                        let response = xhr.responseJSON;
                        if (response && response.redirect) {
                            toastr.error(response.message || 'Session expired. Please login again.');
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        } else {
                            toastr.error('Session expired. Please login again.');
                            setTimeout(function() {
                                window.location.href = '{{ route("zora.login") }}';
                            }, 1500);
                        }
                    }
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'email',
                    name: 'email',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'id_role',
                    name: 'id_role',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        if (data == 1) {
                            return `<span class="badge badge-primary">Admin</span>`;
                        } else if (data == 2) {
                            return `<span class="badge badge-info">Partner</span>`;
                        }
                        return `<span class="badge badge-secondary">Unknown</span>`;
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        if (data) {
                            return new Date(data).toLocaleString();
                        }
                        return '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let userId = row.id;
                        let currentUserId = {{ auth()->id() }};
                        let deleteBtn = '';
                        if (userId != currentUserId) {
                            let deleteUrl = '{{ route("cpo.users.destroy", ":id") }}'.replace(':id', userId);
                            deleteBtn = `
                                <div class="btn-divider"></div>
                                <form action="${deleteUrl}" method="POST" class="d-inline action-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm action-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            `;
                        }
                        return `
                            <div class="btn-group align-items-center" role="group" aria-label="User Actions">
                                <a href="#" class="btn btn-primary btn-sm action-edit" data-id="${userId}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                ${deleteBtn}
                            </div>
                        `;
                    }
                }
            ],
            order: [
                [1, 'asc']
            ],
        });
        table.buttons().container().appendTo('#colvis-container');

        // Filter button click event
        $('#btn_filter').on('click', function() {
            table.draw();
        });

        // Reset button click event
        $('#btn_reset').on('click', function() {
            $('#filter_name').val('');
            $('#filter_email').val('');
            $filterRole.val(null).trigger('change');
            table.draw();
        });

        // Add User button click event
        $('#btn-add-user').on('click', function() {
            $('#modal-title').text('Add User');
            $('#modal-body').load('{{ route("cpo.users.create") }}', function() {
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

        // Handle form submission via AJAX with confirmation
        $(document).on('submit', '#user-form', function(e) {
            e.preventDefault();
            let form = $(this);

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
                                table.draw();
                                toastr.success(response.message || 'Operation successful');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 401 || xhr.status === 403) {
                                // Session expired or unauthorized
                                let response = xhr.responseJSON;
                                if (response && response.redirect) {
                                    toastr.error(response.message || 'Session expired. Please login again.');
                                    setTimeout(function() {
                                        window.location.href = response.redirect;
                                    }, 1500);
                                } else {
                                    toastr.error('Session expired. Please login again.');
                                    setTimeout(function() {
                                        window.location.href = '{{ route("zora.login") }}';
                                    }, 1500);
                                }
                            } else if (xhr.status === 422) {
                                // Validation errors
                                let errors = xhr.responseJSON.errors;
                                let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                                $.each(errors, function(key, value) {
                                    errorHtml += '<li>' + value[0] + '</li>';
                                    // Mark field as invalid
                                    let field = form.find('[name="' + key + '"]');
                                    field.addClass('is-invalid');
                                    field.after('<div class="invalid-feedback">' + value[0] + '</div>');
                                });
                                errorHtml += '</ul></div>';
                                $('#modal-body').prepend(errorHtml);
                                // Scroll to top of modal
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
            // Remove any error messages
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
                    // Submit the form via AJAX
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            table.draw();
                            toastr.success('User deleted successfully');
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
                                    // Handle specific error messages (e.g., cannot delete own account)
                                    toastr.error(response.message);
                                } else {
                                    toastr.error('Session expired. Please login again.');
                                    setTimeout(function() {
                                        window.location.href = '{{ route("zora.login") }}';
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

