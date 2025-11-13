<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Settings
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="form-group">
                    <label for="locationType">Tariffs</label>
                    <select class="form-control select2 selectLocationType" id="locationType" name="location_type_id" style="width: 100%;">
                        <option value="0">Parking Garage</option>
                        <option value="1">Parking Lot</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i><span>Save</span></button>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Tariff in Use
                </h5>
            </div>
            <div class="card-body p-4 bg-white">

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
