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
                        <select class="form-control form-control-sm select2 selectLocationType" id="locationType" name="tariff_id" style="width: 100%;">
                            <option selected="selected" value="">- Tariff -</option>
                            @if (!empty($tariff))
                                @foreach ($tariff as $t)
                                    <option value="{{ $t->tariff_id }}" {{ !empty($tariff_id) && $t->tariff_id == $tariff_id ? 'selected' : '' }}>{{ $t->tariff_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-primary action-save"><i class="fas fa-save mr-2"></i><span>Save</span></button>
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
                {{-- <h6>{{ $station->tariff_name }}</h6> --}}
                <div class="table-responsive">
                    <table id="tariffTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Tariff Code</th>
                                <th>Tariff Name</th>
                                <th>Tarif Type</th>
                                <th>Tarif Value (minute/kwh)</th>
                                <th>Tariff Price</th>
                                <th>Tax (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic Rows -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('.selectLocationType').select2();
        $('.selectOwnerAccount').select2();
    });

    $(function() {
        let tariffId = "{{ $tariff_id }}";
        console.log(tariffId);

        let table = $("#tariffTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: true,
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            paging: false,
            info: false,
            ajax: {
                // url: '/stations/details/get-connectors',
                url: '{{ route("cpo.stations.details.tariff.get-tariff-in-use") }}',
                type: 'GET',
                data: function(d) {
                    d.tariff_id = tariffId;
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'tariff_code',
                    name: 'Tariff Code',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'tariff_name',
                    name: 'Tariff Number',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'tariff_type',
                    name: 'Tariff Type',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'tariff_value',
                    name: 'Tariff Value (minute/kwh)',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'tariff_price',
                    name: 'Tariff Price',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        let price = row.tariff_price;
                        return `Rp. ${price}`;
                    }
                },
                {
                    data: 'tax_rate',
                    name: 'Tax (%)',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        let tax = row.tax_rate;
                        return `${tax} %`;
                    }
                }
            ],
            order: [
                [1, 'asc']
            ],
        });
    });
</script>
