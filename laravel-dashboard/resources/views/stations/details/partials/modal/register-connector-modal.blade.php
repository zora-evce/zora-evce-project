<div class="modal fade" id="registerConnectorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <form action="{{ route('cpo.stations.details.overview.register-new-connector') }}" method="POST">
            @csrf
                <input type="hidden" value="{{ $station->id }}" name="station_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Register New Connector</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="connectorCode">Connector Code</label>
                        <input type="text" class="form-control form-control-sm" id="connectorCode" name="connector_code" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="connectorNumber">Conector Number</label>
                        <input type="text" class="form-control form-control-sm" id="connectorNumber" name="connector_number" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="powerKw">Power (kw)</label>
                        <input type="text" class="form-control form-control-sm" id="powerKw" name="power_kw" placeholder="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-primary action-save">Save</button>
                    <button class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('#select2Brand').select2({
        placeholder: 'Brands',
        allowClear: true,
        theme: 'bootstrap4'
    });
    $('#select2Vendor').select2({
        placeholder: 'Vendors',
        allowClear: true,
        theme: 'bootstrap4'
    });
    $('#select2Model').select2({
        placeholder: 'Model',
        allowClear: true,
        theme: 'bootstrap4'
    });
</script>
