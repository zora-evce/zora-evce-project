<div class="row g-4">
    <div class="col-md-4">
        <form action="{{ route('cpo.stations.details.tariff.save-tariff') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        Settings
                    </h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="form-group">
                        <label for="locationType">Tariffs</label>
                        <input type="hidden" name="station_id" value="{{ $station_id }}">
                        <select class="form-control select2 selectLocationType" id="locationType" name="tariff_id" style="width: 100%;">
                            <option selected="selected" value="">- Tariff -</option>
                            @if (!empty($tariff))
                                @foreach ($tariff as $t)
                                    <option value="{{ $t->tariff_id }}">{{ $t->tariff_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary action-save"><i class="fas fa-save mr-2"></i><span>Save</span></button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h5 class="card-title mb-0 fw-semibold">
                    Tariff in Use
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <h6>{{ $station->tariff_name }}</h6>
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
