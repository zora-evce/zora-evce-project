<div class="row g-4">
    <!-- Information Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Information
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Address</span>
                        <span class="fw-medium text-dark">{{ $station->address }}</span>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">City</span>
                        <span class="fw-medium text-dark">{{ $station->city_name }}</span>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Location Type</span>
                        <span class="fw-medium text-dark">{{ $station->location_type_name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Location
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Latitude</span>
                        <span class="fw-medium text-dark">{{ $station->latitude }}</span>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Longitude</span>
                        <span class="fw-medium text-dark">{{ $station->longitude }}</span>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Elevation</span>
                        <span class="fw-medium text-dark">{{ $station->elevation ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Owner Information Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Owner Information
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Owner Name</span>
                        <span class="fw-medium text-dark">{{ $station->owner_name }}</span>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Contact</span>
                        <span class="fw-medium text-dark">{{ $station->owner_contact }}</span>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase fw-semibold">Email</span>
                        <span class="fw-medium text-dark">{{ $station->owner_email }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connectors Table -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-responsive">
            <table id="connectorsTable" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Connector Number</th>
                        <th>Status</th>
                        <th>Power (KW)</th>
                        <th>Last Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic Rows -->
                </tbody>
            </table>
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
                // url: '/stations/details/get-connectors',
                url: '{{ route("admin.stations.details.get-connectors") }}',
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
