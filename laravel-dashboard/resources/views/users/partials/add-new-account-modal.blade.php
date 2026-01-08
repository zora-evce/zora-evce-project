<div class="modal fade" id="addAccount" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <form action="{{ route('cpo.users.create-account') }}" method="POST">
            @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Register New Station</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="accountCode">Code</label>
                        <input type="text" class="form-control form-control-sm" id="accountCode" name="account_code" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="accountName">Name</label>
                        <input type="text" class="form-control form-control-sm" id="accountName" name="account_name" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contractNumber">Contract Number</label>
                        <input type="text" class="form-control form-control-sm" id="contractNumber" name="contract_number" placeholder="">
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
