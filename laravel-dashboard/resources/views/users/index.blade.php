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
    <section class="content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-filter me-1"></i> Data
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label for="filter_account_id" class="form-label">Partner ID</label>
                            <input type="text" class="form-control form-control-sm" id="filter_account_id" placeholder="Search by Partner ID...">
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div id="colvis-container" style="display: inline-block; margin-left: 5px; vertical-align: middle;"></div>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_filter"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_reset"><i class="fas fa-redo-alt"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_add_new_account" data-toggle="modal" data-target="#addAccount"><i class="fas fa-plus mr-2"></i>Add New Partner</button>
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="auditTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr id="table-headers">
                                            <th style="width: 30px;">No</th>
                                            <th>Partner ID</th>
                                            <th>Partner Details</th>
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
    </section>
    @include('users.partials.add-new-account-modal')
    <script>
        const IS_ROLE_2 = @json(auth()->check() && auth()->user()->id_role == 2);

        if (IS_ROLE_2) {
            $('#btn_add_new_account').hide();
        }
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
                    d.filter_account_id = $('#filter_account_id').val();
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
                                window.location.href = '{{ route("cpo.login") }}';
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
                    data: 'account_id',
                    name: 'account_id',
                    searchable: true,
                    orderable: true
                },
                {
                    data: null,
                    name: 'account_details',
                    searchable: true,
                    orderable: false,
                    render: function(data, type, row) {
                        // Display all account fields except excluded ones in a compact format
                        let excludedFields = ['deleted_at', 'password', 'remember_token', 'email_verified_at', 'account_id', 'created_at', 'updated_at'];
                        let details = [];

                        for (let key in row) {
                            if (excludedFields.indexOf(key) === -1 && row.hasOwnProperty(key) && row[key] !== null && row[key] !== '') {
                                let label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                let value = row[key];

                                if (typeof value === 'boolean') {
                                    value = value ? 'Yes' : 'No';
                                } else if (key.includes('date') || key.includes('at')) {
                                    try {
                                        value = new Date(value).toLocaleString();
                                    } catch(e) {
                                        // Keep original value
                                    }
                                }

                                // For search functionality, include the value
                                if (type === 'type' || type === 'sort') {
                                    return value;
                                }

                                details.push('<div class="mb-1"><small class="text-muted">' + label + ':</small><br><strong>' + value + '</strong></div>');
                            }
                        }

                        if (details.length === 0) {
                            return '<span class="text-muted">-</span>';
                        }

                        return '<div style="max-width: 500px; font-size: 0.9em;">' + details.join('') + '</div>';
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
                        let accountId = row.account_id;
                        let detailUrl = '{{ route("cpo.users.detail", ":id") }}'.replace(':id', accountId);
                        return `
                            <div class="btn-group align-items-center" role="group" aria-label="Partner Actions">
                                <a href="${detailUrl}" class="btn btn-primary btn-sm action-detail" data-id="${accountId}">
                                    <i class="fas fa-info-circle"></i> Detail
                                </a>
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
            $('#filter_account_id').val('');
            table.draw();
        });
    </script>
@endsection

