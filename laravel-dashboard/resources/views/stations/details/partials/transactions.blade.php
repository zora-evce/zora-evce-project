<div class="row g-4">
    <div class="col-md-12">
        <div id="colvis-container" style="display: inline-block; margin-left: 5px; vertical-align: middle;"></div>
        {{-- <button type="button" class="btn btn-sm btn-primary" id="btn_filter"><i class="fas fa-search"></i></button>
        <button type="button" class="btn btn-sm btn-primary" id="btn_reset"><i class="fas fa-redo-alt"></i></button> --}}
        <button type="button" class="btn btn-sm btn-primary" id="btn_export_excel"><i class="fas fa-file-excel mr-2"></i>Export Excel</button>
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

<!-- Connectors Table -->
<script>
    $(function() {
        let stationId = "{{ $station_id }}";

        $('#btn_export_excel').on('click', function(e) {
            e.preventDefault();
            let params = {
                station_id: stationId || '',
            };

            let url = '{{ route("cpo.stations.details.transactions.export-excel-transactions") }}'
                + '?' + $.param(params);

            window.location.href = url;
        });

        let table = $("#auditTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("cpo.stations.details.transactions.get-transactions") }}',
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
                    data: 'transaction_id',
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
                    orderable: true,
                    render: function(data, type, row) {
                        if (data != null || data != undefined) {
                            return data + ' Minutes';
                        } else {
                            return '-';
                        }
                    }
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
