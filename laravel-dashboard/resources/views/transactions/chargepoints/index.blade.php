@extends('templates/template')
@section('content')
    <style>
        /* .btn-group .btn-divider {
            width: 2px;
            margin: 0 0px;
            height: 24px;
            align-self: center;
        } */
    </style>
    <section class="content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-filter me-1"></i> Chargepoints
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- <div class="col-md-2">
                            <label for="filter_name" class="form-label">Charging Station ID</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder="">
                        </div> --}}
                        <div class="col-md-2">
                            <label for="filter_transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control form-control-sm" id="filter_transaction_id" placeholder=".">
                        </div>
                        {{-- <div class="col-md-2">
                            <label for="filter_name" class="form-label">Connector ID</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder=".">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_name" class="form-label">Status</label>
                            <input type="text" class="form-control form-control-sm" id="filter_name" placeholder="">
                        </div> --}}
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-primary" id="btn_filter"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_reset"><i class="fas fa-redo-alt"></i></button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_export_excel"><i class="fas fa-file-excel mr-2"></i>Export Excel</button>
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="auditTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th style="width: 30px;">Chargin Station ID</th>
                                            <th>Transaction ID</th>
                                            <th>Connector ID</th>
                                            <th>Address</th>
                                            <th>Payment Status</th>
                                            <th>Start Time</th>
                                            <th>Stop Time</th>
                                            <th>Total Time</th>
                                            <th>Total Cost</th>
                                            {{-- <th style="width: 80px;"><i class="fas fa-cogs"></i></th> --}}
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
            </div>
        </div>
    </section>
    <script>
        let table = $("#auditTable").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("cpo.transactions.chargepoints.get-data") }}',
                type: 'GET',
                data: function(d) {
                    d.transaction_id = $('#filter_transaction_id').val();
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
                    data: 'code',
                    name: 'Chargin Station ID',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'transaction_id',
                    name: 'Transaction ID',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'connector_id',
                    name: 'Connector ID',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'address',
                    name: 'Address',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'payment_status_name',
                    name: 'Payment Status',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'start_time',
                    name: 'Start Time',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'stop_time',
                    name: 'Stop Time',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'total_time',
                    name: 'Total Time',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'total_cost',
                    name: 'Total Cost',
                    searchable: true,
                    orderable: true
                },
                // {
                //     data: null,
                //     render: function(data, type, row) {
                //         let stationId = row.id;
                //         return `
                //             <div class="btn-group align-items-center" role="group" aria-label="Station Actions">
                //                 <a href="" class="btn btn-primary btn-sm action-detail">
                //                     <i class="fas fa-eye"></i>
                //                 </a>
                //             </div>
                //         `;
                //     }
                // }
            ],
            order: [
                [1, 'asc']
            ],
        });

        $('#btn_filter').on('click', function() {
            table.draw();
        });

        $('#btn_reset').on('click', function() {
            $('#filter_transaction_id').val('');

            table.draw();
        });

        $('#btn_export_excel').on('click', function(e) {
            e.preventDefault();
            let params = {
                filter_transaction_id: $('#filter_transaction_id').val() || ''
            };

            let url = '{{ route("cpo.transactions.chargepoints.export-excel") }}'
                + '?' + $.param(params);

            window.location.href = url;
        });
    </script>
@endsection
