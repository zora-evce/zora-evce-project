<div class="modal fade" id="addStationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Station</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="chargingStationName">Station Name</label>
                    <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                </div>
                <div class="form-group">
                    <label class="form-label" for="chargingStationName">Station Code</label>
                    <input type="email" class="form-control form-control-sm" id="chargingStationName" placeholder="">
                </div>
                <div class="form-group">
                    <label class="form-label" for="ownerAccount">Brand</label>
                    <select class="form-control form-control-sm select2 selectOwnerAccount" id="ownerAccount" name="location_type_id" style="width: 100%;">
                        <option value="0">1</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ownerAccount">Vendor</label>
                    <select class="form-control form-control-sm select2 selectOwnerAccount" id="ownerAccount" name="location_type_id" style="width: 100%;">
                        <option value="0">1</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ownerAccount">Model</label>
                    <select class="form-control form-control-sm select2 selectOwnerAccount" id="ownerAccount" name="location_type_id" style="width: 100%;">
                        <option value="0">1</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-primary action-save">Save</button>
                <button class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
