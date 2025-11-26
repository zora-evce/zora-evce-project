<form id="user-form" action="{{ $user ? route('cpo.users.update', $user->id) : route('cpo.users.store') }}" method="POST">
    @csrf
    @if($user)
        @method('PUT')
    @endif

    <div class="form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
               value="{{ old('name', $user->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
               value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="id_role">Role <span class="text-danger">*</span></label>
        <select class="form-control @error('id_role') is-invalid @enderror" id="id_role" name="id_role" required>
            <option value="">Select Role</option>
            <option value="1" {{ old('id_role', $user->id_role ?? '') == 1 ? 'selected' : '' }}>Admin</option>
            <option value="2" {{ old('id_role', $user->id_role ?? '') == 2 ? 'selected' : '' }}>Partner</option>
        </select>
        @error('id_role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group" id="partner-group" style="display: none;">
        <label for="partner_id">Partner <span class="text-danger">*</span></label>
        @if(isset($account_id) && $account_id)
            <input type="hidden" id="partner_id" name="partner_id" value="{{ $account_id }}">
            <input type="text" class="form-control" value="Account ID: {{ $account_id }}" readonly>
            <small class="form-text text-muted">Partner ID is automatically set to Account ID</small>
        @else
            <select class="form-control @error('partner_id') is-invalid @enderror" id="partner_id" name="partner_id">
                <option value="">Select Partner</option>
                @foreach($partners ?? [] as $partner)
                    <option value="{{ $partner->partner_id }}" {{ old('partner_id', $user->partner_id ?? '') == $partner->partner_id ? 'selected' : '' }}>
                        {{ $partner->partner_name }} ({{ $partner->partner_code }})
                    </option>
                @endforeach
            </select>
        @endif
        @error('partner_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password">{{ $user ? 'New Password' : 'Password' }} <span class="text-danger">*</span></label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
               {{ $user ? '' : 'required' }}>
        @if($user)
            <small class="form-text text-muted">Leave blank to keep current password</small>
        @endif
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
               id="password_confirmation" name="password_confirmation" {{ $user ? '' : 'required' }}>
        @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<script>
    // Show/hide partner dropdown based on role selection
    function togglePartnerField() {
        var role = $('#id_role').val();
        if (role == '2') { // Partner role
            $('#partner-group').show();
            var partnerInput = $('#partner_id');
            if (partnerInput.is('select')) {
                partnerInput.prop('required', true);
            }
        } else {
            $('#partner-group').hide();
            var partnerInput = $('#partner_id');
            if (partnerInput.is('select')) {
                partnerInput.prop('required', false);
                partnerInput.val('');
            }
        }
    }

    // Initialize on page load
    $(document).ready(function() {
        togglePartnerField();
    });

    // Toggle when role changes
    $('#id_role').on('change', function() {
        togglePartnerField();
    });

    // Make password confirmation required only if password is filled
    @if($user)
        $('#password').on('input', function() {
            if ($(this).val().length > 0) {
                $('#password_confirmation').prop('required', true);
            } else {
                $('#password_confirmation').prop('required', false);
            }
        });
    @endif
</script>

