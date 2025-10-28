<div class="card">
    <div class="card-header">
        <h3 class="card-title">Overview</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-4">
                                        <h6>Address</h6>
                                    </div>
                                    <div class="col-8">
                                        <h56>{{ $station->address }}</h6>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <h6>City</h6>
                                    </div>
                                    <div class="col-8">
                                        <h56>{{ $station->city_name }}</h6>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <h6>Location Type</h6>
                                    </div>
                                    <div class="col-8">
                                        <h56>{{ $station->location_type_name }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Maps</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Chargin Station Owner</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Connectors</h3>
                    </div>
                    <div class="card-body">
                        <table id="connectorsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">No</th>
                                    <th>Connector Number</th>
                                    <th>Status</th>
                                    <th>Power KW</th>
                                    <th>Last Status</th>
                                    {{-- <th style="width: 80px;"><i class="fas fa-cogs"></i></th> --}}
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        let stationId = "{{ $station_id }}";
        let table = $("#connectorsTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: true,
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            paging: false,
            info: false,
            ajax: {
                url: '/stations/details/get-connectors',
                type: 'GET',
                data: function(d) {
                    d.station_id = stationId;
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
                    data: 'connector_number',
                    name: 'Connector Number',
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
                            return `<a class="btn btn-success btn-xs action-detail">Available</a>`;
                        }
                    }
                },
                {
                    data: 'power_kw',
                    name: 'Power KW',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'last_status_at',
                    name: 'Last Status',
                    searchable: true,
                    orderable: true
                }
            ],
            order: [
                [1, 'asc']
            ],
        });
    });
</script>
