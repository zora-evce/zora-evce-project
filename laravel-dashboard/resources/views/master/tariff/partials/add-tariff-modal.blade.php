<div class="modal fade" id="addTariff" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <form action="{{ route('cpo.master.tariff.add-tariff') }}" method="POST">
            @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Register New Station</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="tariffCode">Code</label>
                        <input type="text" class="form-control form-control-sm" id="tariffCode" name="tariff_code" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tariffName">Name</label>
                        <input type="text" class="form-control form-control-sm" id="tariffName" name="tariff_name" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tariffType">Type</label>
                        <select class="form-control form-control-sm select2" id="tariffType" name="tariff_type" style="width: 100%;">
                            <option value="minute" selected>Minute</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tariffValue">Value (minute)</label>
                        <input type="text" class="form-control form-control-sm" id="tariffValue" name="tariff_value" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tariffPrice">Price</label>
                        <input type="number" class="form-control form-control-sm" id="tariffPrice" name="tariff_price" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tariffTaxRate">Tax Rate</label>
                        <input type="number" class="form-control form-control-sm" id="tariffTaxRate" name="tax_rate" placeholder="">
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
    $('#tariffType').select2({
        placeholder: 'Model',
        allowClear: true,
        theme: 'bootstrap4'
    });
</script>
