<!-- Main Content (initially hidden) -->
<div class="container mt-4" id="main-content">
    <div class="row g-4">
        <!-- Column 1 -->
        <div class="col-md-4">
            <div class="mb-2">
                <small class="text-muted d-block">Customer Email</small>
                <span class="fw-semibold">{{ $data->customer_email ?? '-' }}</span>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Customer Phone</small>
                <span class="fw-semibold">{{ $data->customer_phone ?? '-' }}</span>
            </div>
        </div>

        <!-- Column 2 -->
        <div class="col-md-4">
            <div class="mb-2">
                <small class="text-muted d-block">Midtrans Order Id</small>
                <span class="fw-semibold">{{ $data->midtrans_order_id ?? '-' }}</span>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Tariff</small>
                <span class="fw-semibold">{{ $data->tariff_code ?? '-' }}</span>
            </div>
        </div>

        <!-- Column 3 -->
        <div class="col-md-4">
            <div class="mb-2">
                <small class="text-muted d-block">Station Address</small>
                <span class="fw-semibold">{{ $data->station_address ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
