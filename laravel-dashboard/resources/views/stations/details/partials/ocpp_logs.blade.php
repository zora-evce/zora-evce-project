<div class="row g-4">
    <!-- Information Card -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    OCPP Logs
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="card-body p-4 bg-white">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="auditTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th style="width: 30px;">Chargin Station Code</th>
                                            <th>Type</th>
                                            <th>Vendor</th>
                                            <th>Model</th>
                                            <th>Firmware</th>
                                            <th>Timestamp</th>
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
            </div>
        </div>
    </div>
</div>

<!-- Connectors Table -->
<script>
    $(function() {
        let stationId = "{{ $station_id }}";
        let table = $("#auditTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("cpo.stations.details.get-ocpp-logs") }}',
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
                    data: 'station_code',
                    name: 'Chargin Station Code',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'type',
                    name: 'Type',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'vendor',
                    name: 'Vendor',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'model',
                    name: 'Model',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'firmware',
                    name: 'Firmware',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'device_timestamp',
                    name: 'Timestamp',
                    searchable: true,
                    orderable: true
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let stationId = row.id;
                        return `
                            <div class="btn-group align-items-center" role="group" aria-label="Station Actions">
                                <a href="" class="btn btn-primary btn-sm action-detail">
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
    });
</script>
