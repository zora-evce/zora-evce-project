<div class="row g-4">
    <div class="col-md-9">
        <div class="card border-0 shadow-sm rounded-4">
            <form action="{{ route('cpo.stations.details.settings.save-settings-section') }}" method="POST">
            @csrf
                <input type="hidden" value="{{ $station->id }}" name="id">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        Settings
                    </h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="form-group">
                        <label class="form-label" for="chargingStationName">Chargin Station Name</label>
                        <input type="text" class="form-control form-control-sm" id="chargingStationName" name="name" placeholder="" value="{{ !empty($data['settings']['name']) ? $data['settings']['name'] : '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="select2LocationType">Location Type</label>
                        <select class="form-control form-control-sm select2" id="select2LocationType" name="location_type_id" style="width: 100%;">
                            <option value="">- Location Type -</option>
                            @if (!empty($data['dropdown_select']['location_type']))
                                @foreach ($data['dropdown_select']['location_type'] as $location_type)
                                    <option value="{{ $location_type->lookup_id }}" {{ !empty($data['settings']['location_type_id'])  && $location_type->lookup_id == $data['settings']['location_type_id'] ? 'selected' : '' }}>{{ $location_type->lookup_value }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="select2Brand">Brand</label>
                        <select class="form-control form-control-sm select2" id="select2Brand" name="brand_id" style="width: 100%;">
                            <option selected="selected" value="">- Brand -</option>
                            @if (!empty($data['dropdown_select']['brands']))
                                @foreach ($data['dropdown_select']['brands'] as $data_brands)
                                    <option value="{{ $data_brands->brand_id }}" {{ !empty($data['settings']['brand_id']) && $data_brands->brand_id == $data['settings']['brand_id'] ? 'selected' : '' }}>{{ $data_brands->brand_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="select2Vendor">Vendor</label>
                        <select class="form-control form-control-sm select2" id="select2Vendor" name="vendor_id" style="width: 100%;">
                            <option selected="selected" value="">- Vendor -</option>
                            @if (!empty($data['dropdown_select']['vendors']))
                                @foreach ($data['dropdown_select']['vendors'] as $data_vendors)
                                    <option value="{{ $data_vendors->vendor_id }}" {{ !empty($data['settings']['vendor_id']) && $data_vendors->vendor_id == $data['settings']['vendor_id'] ? 'selected' : '' }}>{{ $data_vendors->vendor_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="select2Model">Model</label>
                        <select class="form-control form-control-sm select2" id="select2Model" name="model_id" style="width: 100%;">
                            <option selected="selected" value="">- Model -</option>
                            @if (!empty($data['dropdown_select']['models']))
                                @foreach ($data['dropdown_select']['models'] as $data_models)
                                    <option value="{{ $data_models->model_id }}" {{ !empty($data['settings']['model_id']) && $data_models->model_id == $data['settings']['model_id'] ? 'selected' : '' }}>{{ $data_models->model_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="authKey">Auth Key</label>
                        <input type="text" class="form-control form-control-sm" id="authKey" name="auth_key" placeholder="" value="{{ !empty($data['settings']['auth_key']) ? $data['settings']['auth_key'] : '' }}">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-primary action-save"><i class="fas fa-save mr-2"></i><span>Save</span></button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    QR Code
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="text-center">
                    {{ $qr }}
                </div>
            </div>
            <div class="card-footer text center">
                <a type="button" href="{{ route('cpo.stations.details.settings.download-qr', ['station_id' => $station_id, 'station_code' => $station_code]) }}" class="btn btn-sm btn-primary" {{ empty($qr) ? 'disabled' : '' }}><i class="fas fa-download mr-2"></i><span>Download QR</span></a>
            </div>
        </div>
    </div>
    {{-- <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Charging Station Holder Settings
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="form-group">
                    <label class="form-label" for="ownerAccount">Owner Account</label>
                    <select class="form-control form-control-sm select2 selectOwnerAccount" id="ownerAccount" name="location_type_id" style="width: 100%;">
                        <option value="0">Eka Ramdani</option>
                        <option value="1">Ilman</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="chargingStationName">Reference</label>
                    <input type="text" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div> --}}
</div>
{{-- <div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Administrative Status
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="form-group">
                    <label class="form-label" for="locationType">Status</label>
                    <select class="form-control form-control-sm select2 selectLocationType" id="locationType" name="location_type_id" style="width: 100%;">
                        <option value="0">Active</option>
                        <option value="1">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Activation Code
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="form-group">
                    <label class="form-label" for="chargingStationName">Activation Code</label>
                    <input type="text" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div>
</div> --}}
{{-- <div class="row g-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    QR Code
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="text-center">
                    {{ $qr }}
                </div>
            </div>
            <div class="card-footer text center">
                <a type="button" href="{{ route('cpo.stations.details.settings.download-qr', ['station_id' => $station_id, 'station_code' => $station_code]) }}" class="btn btn-sm btn-primary" {{ empty($qr) ? 'disabled' : '' }}><i class="fas fa-download mr-2"></i><span>Download QR</span></a>
            </div>
        </div>
    </div>
</div> --}}
<script>
    $(function() {
        $('#select2LocationType').select2();
        $('#select2Brand').select2();
        $('#select2Vendor').select2();
        $('#select2Model').select2();
        // $('.selectOwnerAccount').select2();
    });
</script>
