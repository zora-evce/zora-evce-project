@extends('templates/template')
@section('content')
    <style>
        .btn-group .btn-divider {
            width: 2px;
            margin: 0 0px;
            height: 24px;
            align-self: center;
        }
    </style>
    <section class="content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-usd-circle me-1"></i> Tariff
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-2">
                            <label for="filter_name" class="form-label form-label">Code</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder="">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_name" class="form-label">Name</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder=".">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_name" class="form-label">Type</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder=".">
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-primary" id="btn_filter"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_reset"><i class="fas fa-redo-alt"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_register_new_station" data-toggle="modal" data-target="#addTariff"><i class="fas fa-plus mr-2"></i>Add New Tariff</button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_export"><i class="fas fa-file-excel mr-2"></i>Export Excel</button>
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="tariffTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;">#</th>
                                            <th>Tariff Code</th>
                                            <th>Tariff Name</th>
                                            <th>Tarif Type</th>
                                            <th>Tarif Value (minute/kwh)</th>
                                            <th>Tariff Price</th>
                                            <th>Tax (%)</th>
                                            <th style="width: 80px;"><i class="fas fa-cogs"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end bg-white border-0 pt-0">
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal-add">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detail</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </section>
    @include('master.tariff.partials.add-tariff-modal')
    <script>
        // Standard Select2 for Status
        const $filterStatus = $('#filter_status').select2({
            placeholder: 'All Statuses',
            allowClear: true,
            theme: 'bootstrap4' // Assuming you use Bootstrap 4 with AdminLTE
        });

        // Standard Select2 for Roaming Type
        const $filterRoaming = $('#filter_roaming').select2({
            placeholder: 'All Types',
            allowClear: true,
            theme: 'bootstrap4'
        });
        let table = $("#tariffTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                // url: '/stations/details/get-connectors',
                url: '{{ route("cpo.master.tariff.get-data") }}',
                type: 'GET',
                data: function(d) {
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
                },
                {
                    data: null,
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row) {
                        let stationId = row.id;
                        return `
                            <div class="btn-group align-items-center" role="group" aria-label="Station Actions">
                                <a href="#" class="btn btn-primary btn-sm action-detail" id="btn-detail-table">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <div class="btn-divider"></div>
                                <a href="#" class="btn btn-primary btn-sm action-detail">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <div class="btn-divider"></div>
                                <a href="#" class="btn btn-primary btn-sm action-detail action-delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        `;
                    }
                }
            ],
            order: [
                [1, 'asc']
            ],
        });

        // Filter button click event
        $('#btn_filter').on('click', function() {
            table.draw(); // Redraw the table, which re-triggers the AJAX call with new data
        });

        // Reset button click event
        $('#btn_reset').on('click', function() {
            // Reset all filter inputs to their default state
            $('#filter_name').val('');
            $filterStatus.val(null).trigger('change'); // Reset Select2
            $filterRoaming.val(null).trigger('change');
            $filterCity.val(null).trigger('change'); // Reset AJAX Select2

            // Redraw the table
            table.draw();
        });
    </script>
@endsection
