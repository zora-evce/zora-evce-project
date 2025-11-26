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
                    <h1 class="m-0">Stations</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('cpo.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Stations</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-filter me-1"></i> Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-2">
                            <label for="filter_name" class="form-label">Station Name</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder="Search by name...">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_status" class="form-label">Status</label>
                            <select class="form-control form-control-sm" id="filter_status" style="width: 100%;">
                                <option value="">All Statuses</option>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                                <option value="charging">Charging</option>
                                <option value="offline">Offline</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_roaming" class="form-label">Roaming Type</label>
                            <select class="form-control form-control-sm" id="filter_roaming" style="width: 100%;">
                                <option value="">All Types</option>
                                {{-- These should ideally be populated from your DB via Blade --}}
                                <option value="1">Public</option>
                                <option value="2">Private</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_city" class="form-label">City</label>
                            <select class="form-control form-control-sm" id="filter_city" style="width: 100%;">
                                <option value="">Select a City</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div id="colvis-container" style="display: inline-block; margin-left: 5px; vertical-align: middle;"></div>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_filter"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_reset"><i class="fas fa-redo-alt"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_register_new_station" data-toggle="modal" data-target="#registerStationModal"><i class="fas fa-plus mr-2"></i>Register New Station</button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_add"><i class="fas fa-file-excel mr-2"></i>Export Excel</button>
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
                                            <th style="width: 30px;">Online</th>
                                            <th>Charging Stations ID</th>
                                            <th>Charging Stations Name</th>
                                            <th>Last Heartbeat</th>
                                            <th>Connectors</th>
                                            <th>Roaming Type</th>
                                            <th>Location Type</th>
                                            <th>Address</th>
                                            <th>City</th>
                                            <th>Status</th>
                                            <th style="width: 80px;"><i class="fas fa-cogs"></i></th>
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
        <div class="modal fade" id="modal-add">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detail</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </section>
    @include('stations.partials.register-station-modal')
    <script>
        // Standard Select2 for Status
        const $filterStatus = $('#filter_status').select2({
            placeholder: 'All Statuses',
            allowClear: true,
            theme: 'bootstrap4' // Assuming you use Bootstrap 4 with AdminLTE
        });

        // Standard Select2 for Roaming Type
        const $filterRoaming = $('#filter_roaming').select2({
            placeholder: 'All Types',
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
                    text: '<i class="fas fa-columns mr-2"></i>Columns', // Your custom button
                    className: 'btn-sm btn-primary'
                }
            ],
            ajax: {
                url: '{{ route("cpo.stations.get-data") }}',
                type: 'GET',
                data: function(d) {
                    d.filter_name = $('#filter_name').val();
                    d.filter_status = $filterStatus.val();
                    d.filter_roaming = $filterRoaming.val();
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
                    data: 'connectivity_status',
                    name: 'Online',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        if (data == 'online') {
                            return `<center><i class="fas fa-signal-alt text-success"></i></center>`;
                        } else {
                            return `<center><i class="fas fa-signal-alt-slash text-danger"></i></center>`;
                        }

                    }
                },
                {
                    data: 'code',
                    name: 'Charging Stations ID',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'name',
                    name: 'Charging Stations Name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'last_heartbeat_at',
                    name: 'Last Heartbeat',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'connectors_count',
                    name: 'Connectors',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'roaming_type_name',
                    name: 'Roaming Type',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'location_type_name',
                    name: 'Location Type',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'address',
                    name: 'Address',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'city_name',
                    name: 'City',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'status',
                    name: 'Status',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        let status = row.status;
                        if (status == 'available') {
                            return `<span class="badge badge-primary">${status}</span>`;
                        } else {
                            return `<span class="badge badge-warning">${status}</span>`;
                        }
                    }
                },
                {
                    data: null,
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row) {
                        let stationId = row.id;
                        return `
                            <div class="btn-group align-items-center" role="group" aria-label="Station Actions">
                                <a href="#" class="btn btn-primary btn-sm action-detail" id="btn-detail-table">
                                    <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="btn-divider"></div>
                                <a href="{{ route("cpo.stations.details") }}?id=${stationId}" class="btn btn-primary btn-sm action-detail">
                                    <i class="fas fa-eye"></i>
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
            table.draw(); // Redraw the table, which re-triggers the AJAX call with new data
        });

        // Reset button click event
        $('#btn_reset').on('click', function() {
            // Reset all filter inputs to their default state
            $('#filter_name').val('');
            $filterStatus.val(null).trigger('change'); // Reset Select2
            $filterRoaming.val(null).trigger('change');
            $filterCity.val(null).trigger('change'); // Reset AJAX Select2

            // Redraw the table
            table.draw();
        });

        const detailRows = [];

        table.on('click', '#btn-detail-table', function () {
            let btn = $(this);
            let tr = event.target.closest('tr');
            let row = table.row(tr);
            let idx = detailRows.indexOf(tr.id);

            if (row.child.isShown()) {
                tr.classList.remove('details');
                row.child.hide();

                detailRows.splice(idx, 1);
                btn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
            else {
                tr.classList.add('details');
                row.child(format(row.data())).show();

                if (idx === -1) {
                    detailRows.push(tr.id);
                }
                btn.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });

        table.on('draw', () => {
            detailRows.forEach((id, i) => {
                let el = document.querySelector('#' + id + ' td.dt-control');

                if (el) {
                    el.dispatchEvent(new Event('click', { bubbles: true }));
                }
            });
        });

        const detailTableUrl = "{{ route('cpo.stations.detail-table', ['id' => '__ID__']) }}";
        function format(d) {
            let html = '';

            $.ajax({
                url: detailTableUrl.replace('__ID__', d.id),
                async: false,
                success: function(response) {
                    html = response;
                }
            });

            return html;
        }

    </script>
@endsection
