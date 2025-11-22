<div class="row g-4">
    <!-- Information Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Location Data
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="form-group">
                    <label class="form-label" for="chargingStationName">City</label>
                    <select class="form-control form-control-sm select2 selectLocationType" id="locationType" name="location_type_id" style="width: 100%;">
                        <option value="0">Bandung</option>
                        <option value="1">Jakarta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="locationType">Country</label>
                    <select class="form-control form-control-sm select2 selectLocationType" id="locationType" name="location_type_id" style="width: 100%;">
                        <option value="0">Indonesia</option>
                        <option value="1">Malaysia</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="form-label" for="chargingStationName">Zipcode</label>
                            <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="chargingStationName">No</label>
                            <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="chargingStationName">Street</label>
                    <textarea class="form-control form-control-sm"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="chargingStationName">Latitude</label>
                            <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="chargingStationName">Longitude</label>
                            <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div>

    <!-- Location Card -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Maps
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="ratio ratio-1x1">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.264535647068!2d107.62815717585183!3d-6.858865067105696!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e0b82dc93d87%3A0xf664c059175258b1!2sTaman%20Hutan%20Raya%20Ir.%20H.%20Djuanda!5e0!3m2!1sen!2sid!4v1762875505053!5m2!1sen!2sid"
                        style="border:0;"
                        width="100%"
                        height="380"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
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
