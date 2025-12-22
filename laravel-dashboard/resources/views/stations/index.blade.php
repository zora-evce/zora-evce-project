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
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-filter me-1"></i> Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-2">
                            <label for="filter_code" class="form-label">Station Code</label>
                            <input type="text" class="form-control form-control-sm" id="filter_code" placeholder="Search by code...">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_name" class="form-label">Station Name</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder="Search by name...">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_status" class="form-label">Status</label>
                            <select class="form-control form-control-sm" id="filter_status" style="width: 100%;">
                                <option value="">All Statuses</option>
                                @if (!empty($data['connectivity_status']))
                                    @foreach ($data['connectivity_status'] as $data_status)
                                        <option value="{{ $data_status->lookup_code }}">{{ $data_status->lookup_value }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_city" class="form-label">City</label>
                            <select class="form-control form-control-sm" id="filter_city" style="width: 100%;">
                                <option value="">Select a City</option>
                                @if (!empty($data['city']))
                                    @foreach ($data['city'] as $data_city)
                                        <option value="{{ $data_city->city_id }}">{{ $data_city->city_name }}
                                        </option>
                                    @endforeach
                                @endif
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
            </div>
        </div>
    </section>
    @include('stations.partials.register-station-modal')
    <script>
        const $filterStatus = $('#filter_status').select2({
            placeholder: 'All Statuses',
            allowClear: true,
            theme: 'bootstrap4'
        });

        const $filterCity = $('#filter_city').select2({
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
                    d.filter_code = $('#filter_code').val();
                    d.filter_name = $('#filter_name').val();
                    d.filter_status = $filterStatus.val();
                    d.filter_city = $filterCity.val();
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

        $('#btn_filter').on('click', function() {
            table.draw();
        });

        $('#btn_reset').on('click', function() {
            $('#filter_code').val('');
            $('#filter_name').val('');
            $filterStatus.val(null).trigger('change');
            $filterCity.val(null).trigger('change');

            table.draw();
        });

        $("#filter_code").keyup(function(event) {
            if (event.keyCode === 13) {
                $("#btn_filter").click();
            }
        });

        $("#filter_name").keyup(function(event) {
            if (event.keyCode === 13) {
                $("#btn_filter").click();
            }
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
