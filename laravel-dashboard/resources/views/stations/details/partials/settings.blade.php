<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Settings
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="form-group">
                    <label class="form-label" for="chargingStationName">Chargin Station Name</label>
                    <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                </div>
                <div class="form-group">
                    <label class="form-label" for="locationType">Location Type</label>
                    <select class="form-control form-control-sm select2 selectLocationType" id="locationType" name="location_type_id" style="width: 100%;">
                        <option value="0">Parking Garage</option>
                        <option value="1">Parking Lot</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary action-save"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
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
                    <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div>
</div>
<div class="row g-4">
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
                    <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div>
</div>
<div class="row g-4">
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
                <a href="{{ route('cpo.stations.details.settings.download-qr', ['station_id' => $station_id, 'station_code' => $station_code]) }}" class="btn btn-sm btn-primary"><i class="fas fa-download mr-2"></i><span>Download QR</span></a>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('.selectLocationType').select2();
        $('.selectOwnerAccount').select2();
    });
</script>
