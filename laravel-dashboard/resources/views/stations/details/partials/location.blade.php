<div class="row g-4">
    <!-- Information Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Location Data
                </h5>
            </div>
            <form action="{{ route('cpo.stations.details.location.save-station-location') }}" method="POST">
            @csrf
                <input type="hidden" value="{{ $station->id }}" name="id">
                <div class="card-body p-4 bg-white">
                    <div class="form-group">
                        <label class="form-label" for="city">City</label>
                        <select class="form-control form-control-sm select2 selectLocationType" id="city" name="city_id" style="width: 100%;">
                            <option value="">Select a City</option>
                            @if (!empty($city))
                                @foreach ($city as $data_city)
                                    <option value="{{ $data_city->city_id }}" {{ !empty($station->city_id) && $data_city->city_id == $station->city_id ? 'selected' : '' }}>{{ $data_city->city_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="address">Address</label>
                        <textarea class="form-control form-control-sm" id="address" name="address">{{ !empty($station->address) ? $station->address : null }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gmap_embed">Google Map Embed</label>
                        <input type="text" class="form-control form-control-sm" id="gmap_embed" name="gmap_embed" value="{{ !empty($station->gmap_embed) ? $station->gmap_embed : null }}" placeholder="">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-primary action-save"><i class="fas fa-save mr-2"></i><span>Save</span></button>
                </div>
            </form>
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
                        src="{{ $gmap_url }}"
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
        const $filterCity = $('#city').select2({
            placeholder: 'All Types',
            allowClear: true,
            theme: 'bootstrap4'
        });
    });
</script>
