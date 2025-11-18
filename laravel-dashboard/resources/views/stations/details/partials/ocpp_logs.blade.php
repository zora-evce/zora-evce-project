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
                                            <th style="width: 30px;">Chargin Station ID</th>
                                            <th>Transaction ID</th>
                                            <th>Connector ID</th>
                                            <th>Retrieve Date</th>
                                            <th>Process Time</th>
                                            <th>Type</th>
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
    });
</script>
