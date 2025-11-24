<div class="modal fade" id="registerStationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <form action="{{ route('cpo.stations.register-new-station') }}" method="POST">
            @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Register New Station</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="stationName">Station Name</label>
                        <input type="text" class="form-control form-control-sm" id="stationName" name="name" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="stationCode">Station Code</label>
                        <input type="text" class="form-control form-control-sm" id="stationCode" name="code" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="select2Brand">Brand</label>
                        <select class="form-control form-control-sm select2" id="select2Brand" name="brand_id" style="width: 100%;">
                            <option selected="selected" value="">- Brand -</option>
                            @if (!empty($data['brands']))
                                @foreach ($data['brands'] as $data_brands)
                                    <option value="{{ $data_brands->brand_id }}">{{ $data_brands->brand_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="select2Vendor">Vendor</label>
                        <select class="form-control form-control-sm select2" id="select2Vendor" name="vendor_id" style="width: 100%;">
                            <option selected="selected" value="">- Vendor -</option>
                            @if (!empty($data['vendors']))
                                @foreach ($data['vendors'] as $data_vendors)
                                    <option value="{{ $data_vendors->vendor_id }}">{{ $data_vendors->vendor_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="select2Model">Model</label>
                        <select class="form-control form-control-sm select2" id="select2Model" name="model_id" style="width: 100%;">
                            <option selected="selected" value="">- Model -</option>
                            @if (!empty($data['models']))
                                @foreach ($data['models'] as $data_models)
                                    <option value="{{ $data_models->model_id }}">{{ $data_models->model_name }}</option>
                                @endforeach
                            @endif
                        </select>
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
