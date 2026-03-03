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
                        <div class="col-md-2">

                            <label for="range" class="form-label">Date Range</label>
                            <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control form-control-sm float-right" id="dateRange">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="transactionId" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control form-control-sm" id="transactionId" placeholder="Search by transaction ID...">
                        </div>
                        <div class="col-md-2">
                            <label for="customerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control form-control-sm" id="customerName" placeholder="Search by customer name...">
                        </div>
                        <div class="col-md-2">
                            <label for="paymentStatus" class="form-label">Payment Status</label>
                            <select class="form-control form-control-sm" id="paymentStatus" style="width: 100%;">
                                @if (!empty($data['payment_status']))
                                    @foreach ($data['payment_status'] as $data_status)
                                        <option value="{{ $data_status->lookup_id }}">{{ $data_status->lookup_value }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row g-4">
                        <div class="col-md-12">
                            {{-- <div id="colvis-container" style="display: inline-block; margin-left: 5px; vertical-align: middle;"></div> --}}
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
                                            <th style="width: 80px;">Chargin Station Name</th>
                                            <th>Transaction ID</th>
                                            <th>Connector ID</th>
                                            <th>Customer Name</th>
                                            <th>Payment Status</th>
                                            <th>Date</th>
                                            <th>Start Time</th>
                                            <th>Stop Time</th>
                                            <th>Total Time</th>
                                            <th>Total Kwh</th>
                                            <th>Total Cost</th>
                                            <th style="width: 30px;"><i class="fas fa-cogs"></i></th>
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
        $(function() {
            const $dateRange = $('#dateRange');
            let startDate = null;
            let endDate = null;
            $dateRange.daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });
            $dateRange.on('apply.daterangepicker', function (ev, picker) {
                startDate = picker.startDate.format('YYYY-MM-DD');
                endDate   = picker.endDate.format('YYYY-MM-DD');

                $(this).val(startDate + ' - ' + endDate);
            });
            $dateRange.on('cancel.daterangepicker', function (ev, picker) {
                startDate = null;
                endDate   = null;
                $(this).val('');
            });

            const $paymentStatus = $('#paymentStatus').select2({
                placeholder: 'Payment Status',
                allowClear: true,
                theme: 'bootstrap4'
            });
            $('#paymentStatus').val(null).trigger('change');

            $('#btn_export_excel').on('click', function(e) {
                e.preventDefault();

                let params = {
                    start_date: startDate || '',
                    end_date: endDate || '',
                    transaction_id: $('#transactionId').val() || '',
                    customer_name: $('#customerName').val() || '',
                    payment_status: $paymentStatus.val() || '',
                };

                let url = '{{ route("cpo.transactions.chargepoints.export-excel") }}'
                    + '?' + $.param(params);

                window.location.href = url;
            });

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
                        d.start_date = startDate;
                        d.end_date = endDate;
                        d.transaction_id = $('#transactionId').val();
                        d.customer_name = $('#customerName').val();
                        d.payment_status = $paymentStatus.val();
                        console.log(d);
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'station_name',
                        name: 'Chargin Station Name',
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
                        data: 'customer_name',
                        name: 'Customer Name',
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
                        data: 'created_at',
                        name: 'Date',
                        searchable: true,
                        orderable: true,
                        render: function (data, type) {
                            if (type === 'sort' || type === 'type') return data;
                            return moment(data).format('YYYY-MM-DD HH:mm:ss');
                        }
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
                        orderable: true,
                        render: function(data, type, row) {
                            if (data != null || data != undefined) {
                                return data + ' Minutes';
                            } else {
                                return '-';
                            }
                        }
                    },
                    {
                        data: null,
                        name: 'Total Kwh',
                        searchable: true,
                        orderable: true,
                        render: function(data, type, row) {
                            return '-';
                        }
                    },
                    {
                        data: 'total_cost',
                        name: 'Total Cost',
                        searchable: true,
                        orderable: true,
                        render: function(data, type, row) {
                            return Zora.toRupiah(data);
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
                                        <i class="fas fa-chevron-down"></i>
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

            $('#btn_filter').on('click', function() {
                table.draw();
            });

            $('#btn_reset').on('click', function() {
                startDate = null;
                endDate = null;
                $dateRange.val('');
                $('#transactionId').val('');
                $('#customerName').val('');
                $paymentStatus.val(null).trigger('change');
                table.draw();
            });

            const detailRows = [];
            table.on('click', '#btn-detail-table', function () {
                let btn = $(this);
                let tr = event.target.closest('tr');
                let row = table.row(tr);
                let idx = detailRows.indexOf(tr.id);

                if (row.child.isShown()) {
                    tr.classList.remove('details');
                    row.child.hide();

                    detailRows.splice(idx, 1);
                    btn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                }
                else {
                    tr.classList.add('details');
                    row.child(format(row.data())).show();

                    if (idx === -1) {
                        detailRows.push(tr.id);
                    }
                    btn.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }
            });

            table.on('draw', () => {
                detailRows.forEach((id, i) => {
                    let el = document.querySelector('#' + id + ' td.dt-control');

                    if (el) {
                        el.dispatchEvent(new Event('click', { bubbles: true }));
                    }
                });
            });

            const detailTableUrl = "{{ route('cpo.transactions.chargepoints.detail-table', ['id' => '__ID__']) }}";
            function format(d) {
                let html = '';
                $.ajax({
                    url: detailTableUrl.replace('__ID__', d.id),
                    async: false,
                    success: function(response) {
                        html = response;
                    }
                });
                return html;
            }
        });
    </script>
@endsection
