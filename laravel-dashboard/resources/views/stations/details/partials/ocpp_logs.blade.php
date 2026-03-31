<div class="row g-4">
    <!-- Information Card -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 bg-white">
                <h5>OCPP Logs</h5>
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
                                <th>Payload</th>
                                <th>Response</th>
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
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 bg-white">
                <h5>Heartbeat</h5>
                <div class="table-responsive">
                    <table id="heartbeatTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30px;">No</th>
                                <th style="width: 30px;">Chargin Station Code</th>
                                <th>Type</th>
                                <th>Vendor</th>
                                <th>Model</th>
                                <th>Firmware</th>
                                <th>Timestamp</th>
                                <th>Payload</th>
                                <th>Response</th>
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
@include('stations.details.partials.ocpp-logs-partials.ocpp-logs-detail-modal')
@include('stations.details.partials.ocpp-logs-partials.heartbeat-logs-detail-modal')

<!-- Connectors Table -->
<script>
    /* =========================
   GLOBAL JSON BEAUTIFIER
========================= */
    function beautifyJson(str) {
        if (!str) return '';

        try {
            let obj = JSON.parse(str);

            if (typeof obj === 'string') {
                obj = JSON.parse(obj);
            }

            return JSON.stringify(obj, null, 2);
        } catch (e) {
            return str;
        }
    }


    /* =========================
       OCPP LOGS TABLE
    ========================= */
    $(function() {

        let stationId = "{{ $station_id }}";

        let auditTable = $("#auditTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('cpo.stations.details.get-ocpp-logs') }}',
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
                    name: 'Chargin Station Code'
                },
                {
                    data: 'type',
                    name: 'Type'
                },
                {
                    data: 'vendor',
                    name: 'Vendor'
                },
                {
                    data: 'model',
                    name: 'Model'
                },
                {
                    data: 'firmware',
                    name: 'Firmware'
                },
                {
                    data: 'created_at',
                    name: 'Timestamp'
                },
                {
                    data: 'payload',
                    name: 'payload',
                    searchable: false,
                    orderable: false,
                    visible: false
                },
                {
                    data: 'response',
                    name: 'response',
                    searchable: false,
                    orderable: false,
                    visible: false
                },
                {
                    data: null,
                    render: function(data, type, row) {

                        const payload = row.payload ?? '';
                        const response = row.response ?? '';

                        return `
                        <div class="btn-group" role="group">
                            <button
                                type="button"
                                class="btn btn-primary btn-sm action-detail"
                                data-toggle="modal"
                                data-target="#ocppDetailModal"
                                data-payload='${payload}'
                                data-response='${response}'
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    `;
                    }
                }
            ],
            order: []
        });

    });


    /* =========================
       HEARTBEAT TABLE
    ========================= */
    $(function() {

        let stationId = "{{ $station_id }}";

        let heartbeatTable = $("#heartbeatTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('cpo.stations.details.get-ocpp-logs') }}',
                type: 'GET',
                data: function(d) {
                    d.station_id = stationId;
                    d.type = 'heartbeat';
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
                    name: 'Chargin Station Code'
                },
                {
                    data: 'type',
                    name: 'Type'
                },
                {
                    data: 'vendor',
                    name: 'Vendor'
                },
                {
                    data: 'model',
                    name: 'Model'
                },
                {
                    data: 'firmware',
                    name: 'Firmware'
                },
                {
                    data: 'created_at',
                    name: 'Timestamp'
                },
                {
                    data: 'payload',
                    name: 'payload',
                    searchable: false,
                    orderable: false,
                    visible: false
                },
                {
                    data: 'response',
                    name: 'response',
                    searchable: false,
                    orderable: false,
                    visible: false
                },
                {
                    data: null,
                    render: function(data, type, row) {

                        const payload = row.payload ?? '';
                        const response = row.response ?? '';

                        return `
                        <div class="btn-group" role="group">
                            <button
                                type="button"
                                class="btn btn-primary btn-sm action-detail-heartbeat"
                                data-toggle="modal"
                                data-target="#heartbeatDetailModal"
                                data-payload='${payload}'
                                data-response='${response}'
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    `;
                    }
                }
            ],
            order: []
        });

    });


    /* =========================
       MODAL HANDLERS
    ========================= */

    // OCPP modal
    $(document).on('click', '.action-detail', function() {

        const payload = $(this).attr('data-payload');
        const response = $(this).attr('data-response');

        $('#ocppDetailModal #md_payload').text(beautifyJson(payload));
        $('#ocppDetailModal #md_response').text(beautifyJson(response));

    });

    // HEARTBEAT modal
    $(document).on('click', '.action-detail-heartbeat', function() {

        const payload = $(this).attr('data-payload');
        const response = $(this).attr('data-response');

        $('#heartbeatDetailModal #md_payload').text(beautifyJson(payload));
        $('#heartbeatDetailModal #md_response').text(beautifyJson(response));

    });
</script>
