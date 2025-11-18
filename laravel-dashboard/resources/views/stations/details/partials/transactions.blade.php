<div class="row g-4">
    <!-- Information Card -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Transactions
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="auditTable" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30px;">No</th>
                                        <th style="width: 30px;">Chargin Station ID</th>
                                        <th>Transaction ID</th>
                                        <th>Connector ID</th>
                                        <th>Address</th>
                                        <th>Payment Status</th>
                                        <th>Start Time</th>
                                        <th>Stop Time</th>
                                        <th>Total Time</th>
                                        <th>Total Cost</th>
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

<!-- Connectors Table -->
<script>
    $(function() {
        let stationId = "{{ $station_id }}";
        console.log(stationId);

        let table = $("#auditTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("cpo.stations.details.get-transactions") }}',
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
                    data: 'code',
                    name: 'Chargin Station ID',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'transactionId',
                    name: 'Transaction ID',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'connector_id',
                    name: 'Connector ID',
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
                    data: 'payment_status_name',
                    name: 'Payment Status',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'start_time',
                    name: 'Start Time',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'stop_time',
                    name: 'Stop Time',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'total_time',
                    name: 'Total Time',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'total_cost',
                    name: 'Total Cost',
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
