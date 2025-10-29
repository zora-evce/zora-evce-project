<!-- Main Content (initially hidden) -->
<div class="container mt-4" id="main-content">
    <div class="row g-4">
        <!-- Column 1 -->
        <div class="col-md-4">
            <div class="mb-2">
                <small class="text-muted d-block">Serial Number</small>
                <span class="fw-semibold">{{ $data->serial_number ?? '-' }}</span>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Firmware Version</small>
                <span class="fw-semibold">{{ $data->firmware_version ?? '-' }}</span>
            </div>
        </div>

        <!-- Column 2 -->
        <div class="col-md-4">
            <div class="mb-2">
                <small class="text-muted d-block">Account Name</small>
                <span class="fw-semibold">{{ $data->account_name ?? '-' }}</span>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Location Holder</small>
                <span class="fw-semibold">{{ $data->location_holder_name ?? '-' }}</span>
            </div>
        </div>

        <!-- Column 3 -->
        <div class="col-md-4">
            <div class="mb-2">
                <small class="text-muted d-block">Contract Number</small>
                <span class="fw-semibold">{{ $data->contract_number ?? '-' }}</span>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Tariff</small>
                <span class="fw-semibold">{{ $data->tariff_name ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
