<div class="row g-4">
    <!-- Information Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Information
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row">
                    <img src="{{ asset('images/ev-charger-img.jpg') }}" alt="" class="img-fluid w-50 mx-auto d-block">
                    <span class="position-absolute top-0 end-0 badge {{ $station->connectivity_status == 'online' ? 'bg-success' : 'bg-danger' }}">
                        {{ $station->connectivity_status == 'online' ? 'ONLINE' : 'OFFLINE' }}
                    </span>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Name</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Code</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->code }}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Address</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->address }}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">City</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->city_name }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Location Type</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->location_type_name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Location
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="ratio ratio-1x1">
                    <iframe
                        src="{{ $gmap_url }}"
                        style="border:0;"
                        width="100%"
                        height="500"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Owner Information Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Owner Information
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <h6>Subscription</h6>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Name</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->subscription_name }}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase w-semibold">Start Date</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->subscription_start_date }}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">End Date</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->subscription_end_date }}</span>
                        </div>
                    </div>
                </div>
                <h6>Account</h6>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Name</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->account_name }}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Contract Number</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->account_contract_number }}</span>
                        </div>
                    </div>
                </div>
                <h6>Location Holder</h6>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Name</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->location_holder_name }}</span>
                        </div>
                    </div>
                </div>
                <h6>Reseller</h6>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Name</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->reseller_name }}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-4">
                            <span class="text-muted small text-uppercase fw-semibold">Contract Number</span>
                        </div>
                        <div class="col-8">
                            <span class="fw-medium text-dark">{{ $station->reseller_contract_number }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<div class="row g-4">
    <div class="col-md-12">
        @if (Auth::user()->id_role == 1)
            <button type="button" class="btn btn-sm btn-primary" id="btn_register_new_connector" data-toggle="modal" data-target="#registerConnectorModal"><i class="fas fa-plus mr-2"></i>Register New Connector</button>
        @endif
    </div>
</div>
@include('stations.details.partials.modal.register-connector-modal')
<!-- Connectors Table -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-responsive">
            <table id="connectorsTable" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Connector Code</th>
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
                url: '{{ route("cpo.stations.details.get-connectors") }}',
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
                    data: 'connector_code',
                    name: 'Connector Code',
                    searchable: true,
                    orderable: true
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
                            return `<a class="btn btn-success btn-xs action-detail">${status}</a>`;
                        } else {
                            return `<a class="btn btn-warning btn-xs action-detail">${status}</a>`;
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
