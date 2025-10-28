@extends('templates/template')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Stations</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Stations</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Stations</h3> <br>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="auditTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">No</th>
                                        <th style="width: 30px;">Online</th>
                                        <th>Charging Stations ID</th>
                                        <th>Charging Stations Name</th>
                                        <th>Last Heartbeat</th>
                                        <th>Connectors</th>
                                        {{-- <th>Serial Number</th>
                                        <th>Contract Number</th>
                                        <th>Account</th>
                                        <th>Location Holder</th> --}}
                                        <th>Roaming Type</th>
                                        <th>Location Type</th>
                                        <th>Address</th>
                                        <th>City</th>
                                        {{-- <th>Tariff</th> --}}
                                        <th>Status</th>
                                        <th style="width: 80px;"><i class="fas fa-cogs"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
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
    <script>
        let table = $("#auditTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '/stations/get-data',
                type: 'GET',
                data: function(d) {

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
                            return `<center><a class="btn btn-success btn-sm" readonly><i class="fas fa-signal"></i></a></center>`;
                        } else {
                            return `<center><a class="btn btn-danger btn-sm" readonly><i class="fas fa-signal"></i></a></center>`;
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
                // {
                //     data: 'serial_number',
                //     name: 'Serial Number',
                //     searchable: true,
                //     orderable: true
                // },
                // {
                //     data: 'contract_number',
                //     name: 'Contract Number',
                //     searchable: true,
                //     orderable: true
                // },
                // {
                //     data: 'account_name',
                //     name: 'Account',
                //     searchable: true,
                //     orderable: true
                // },
                // {
                //     data: 'location_holder_name',
                //     name: 'Location Holder',
                //     searchable: true,
                //     orderable: true
                // },
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
                // {
                //     data: 'tariff_name',
                //     name: 'Tariff',
                //     searchable: true,
                //     orderable: true
                // },
                {
                    data: 'status',
                    name: 'Status',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        let status = row.status;
                        if (status == 'available') {
                            return `<a class="btn btn-success btn-xs action-detail">Available</a>`;
                        }
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let stationId = row.id;
                        return `<a class="btn btn-primary btn-sm action-detail" href="/stations/details?id=${stationId}"><i class="fas fa-eye"></i></a>`;
                    }
                }
            ],
            order: [
                [1, 'asc']
            ],
        });

        const detailRows = [];

        table.on('click', 'tbody', function () {
            let tr = event.target.closest('tr');
            let row = table.row(tr);
            let idx = detailRows.indexOf(tr.id);

            if (row.child.isShown()) {
                tr.classList.remove('details');
                row.child.hide();

                detailRows.splice(idx, 1);
            }
            else {
                tr.classList.add('details');
                row.child(format(row.data())).show();

                if (idx === -1) {
                    detailRows.push(tr.id);
                }
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

        function format(d) {
            console.log(d);

            return (
                'test'
            );
        }

    </script>
@endsection
