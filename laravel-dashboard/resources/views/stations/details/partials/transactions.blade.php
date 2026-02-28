<div class="row g-4">
    <div class="col-md-2">
        <label for="startDate" class="form-label">Start Date</label>
        <div class="input-group date" id="startDate" data-target-input="nearest">
            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#startDate" placeholder="YYYY-MM-DD"/>
            <div class="input-group-append" data-target="#startDate" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <label for="endDate" class="form-label">End Date</label>
        <div class="input-group date" id="endDate" data-target-input="nearest">
            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#endDate" placeholder="YYYY-MM-DD"/>
            <div class="input-group-append" data-target="#endDate" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
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
                    <option value="{{ $data_status->lookup_code }}">{{ $data_status->lookup_value }}
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

<script>
    $(function() {
        $('#startDate').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#endDate').datetimepicker({
            format: 'YYYY-MM-DD'
        });

        const $filterStatus = $('#paymentStatus').select2({
            placeholder: 'Payment Status',
            allowClear: true,
            theme: 'bootstrap4'
        });
        $('#paymentStatus').val(null).trigger('change');

        let stationId = "{{ $station_id }}";

        $('#btn_export_excel').on('click', function(e) {
            e.preventDefault();
            let params = {
                station_id: stationId || '',
            };

            let url = '{{ route("cpo.stations.details.transactions.export-excel-transactions") }}'
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
                url: '{{ route("cpo.stations.details.transactions.get-transactions") }}',
                type: 'GET',
                data: function(d) {
                    d.station_id = stationId;
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
                    orderable: true
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

        const detailTableUrl = "{{ route('cpo.stations.details.transactions.detail-table', ['id' => '__ID__']) }}";
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
