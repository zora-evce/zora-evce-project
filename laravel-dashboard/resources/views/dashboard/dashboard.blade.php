@extends('templates/template')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-4">
                    <h1>Dashboard</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid py-4">
            <style>
                .z-card { border-radius: 14px; }
                .z-card .card-header {
                    background: #fff;
                    border-bottom: 1px solid rgba(0,0,0,.06);
                    padding: .75rem 1rem;
                }
                .z-card .card-body { padding: 1rem; }
                .z-card .card-footer {
                    background: #fff;
                    border-top: 1px solid rgba(0,0,0,.06);
                    padding: .6rem 1rem;
                }

                .chart-wrap { height: 160px; }
                .map-wrap { height: 320px; border-radius: 12px; overflow: hidden; }

                .stat-row { padding: .55rem 0; }
                .stat-row + .stat-row { border-top: 1px dashed rgba(0,0,0,.12); }
                .stat-label { letter-spacing: .06em; }
            </style>
            <div class="row align-items-stretch">
                <div class="col-lg-9 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x23FB;</span> Usage
                            </h6>
                            <small class="text-muted">Last period</small>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="chart-wrap">
                                <canvas id="usageChartAll"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x21BB;</span> Transactions
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Ongoing</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsOngoing">0</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Finished</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsFinished">0</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Total Price</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsSumPrice">Rp 0</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row align-items-stretch">
                <div class="col-lg-2 mb-4 d-flex">
                    <select class="form-control form-control-sm" id="filterStations" style="width: 100%;">
                        @if (!empty($stations))
                            @foreach ($stations as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-2 mb-4 d-flex">
                    <select class="form-control form-control-sm" id="filterMonth" style="width: 100%;">
                        @if (!empty($months))
                            @foreach ($months as $number => $name)
                                <option value="{{ $number }}">{{ $name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="row align-items-stretch">
                <div class="col-lg-9 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x23FB;</span> Usage
                            </h6>
                            <small class="text-muted">Last period</small>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="chart-wrap">
                                <canvas id="usageChartStation"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x21BB;</span> Transactions
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Ongoing</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsStationOngoing">0</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Finished</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsStationFinished">0</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Total Price</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsStationSumPrice">Rp 0</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row align-items-stretch">
                <div class="col-lg-4 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-charging-station mr-2"></i><span>Charging stations</span>
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="text-muted small mb-2">Live</div>
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center stat-row">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill badge-success mr-2">Online</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary" id="stationsOnline">0</a>
                                </div>
                                <div class="d-flex justify-content-between align-items-center stat-row">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill badge-danger mr-2">Offline</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary" id="stationsOffline">0</a>
                                </div>
                            </div>
                            <br>
                            <div class="text-muted small mb-2">Status</div>
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center stat-row">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill badge-success mr-2">Available</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary" id="stationsAvailable">0</a>
                                </div>
                                <div class="d-flex justify-content-between align-items-center stat-row">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill badge-danger mr-2">Unavailable</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary" id="stationsUnavailable">0</a>
                                </div>
                                <div class="d-flex justify-content-between align-items-center stat-row">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill badge-success mr-2">Charging</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary" id="stationsCharging">0</a>
                                </div>
                                <div class="d-flex justify-content-between align-items-center stat-row">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill badge-warning mr-2">Preparing</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary" id="stationsPreparing">0</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <i class="far fa-map mr-2"></i><span>Location</span>
                            </h6>
                            <small class="text-muted">Stations map</small>
                        </div>

                        <div class="card-body">
                            <div class="map-wrap position-relative">
                                <iframe
                                    id="gmapFrame"
                                    style="border:0;"
                                    width="100%"
                                    height="100%"
                                    allowfullscreen
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        const $filterStations = $('#filterStations').select2({
            placeholder: 'Stations',
            allowClear: true,
            theme: 'bootstrap4'
        });
        $('#filterStations').val(null).trigger('change');

        const $filterMonth = $('#filterMonth').select2({
            placeholder: 'Months',
            allowClear: true,
            theme: 'bootstrap4'
        });
        $('#filterMonth').val(null).trigger('change');

        $.ajax({
            url: "{{ route('cpo.dashboard.get-data-dashboard') }}",
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.data.tx_sum.tx_date != null || response.data.tx_sum.tx_date != undefined) {
                    loadChartAll(response.data.tx_sum.tx_date, response.data.tx_sum.tx_sum);
                }

                if (response.data.transactions) {
                    $('#transactionsOngoing').text(response.data.transactions.ongoing);
                    $('#transactionsFinished').text(response.data.transactions.finished);
                    $('#transactionsSumPrice').text(response.data.transactions.sum_price);
                }

                if (response.data.stations) {
                    $('#stationsOnline').text(response.data.stations.online);
                    $('#stationsOffline').text(response.data.stations.offline);
                    $('#stationsAvailable').text(response.data.stations.available);
                    $('#stationsUnavailable').text(response.data.stations.unavailable);
                    $('#stationsCharging').text(response.data.stations.charging);
                    $('#stationsPreparing').text(response.data.stations.preparing);
                }

                if (response.data.gmap_url) {
                    loadMap(response.data.gmap_url);
                }
            },
            error: function (xhr) {
                console.error('Failed load usage chart:', xhr.responseText);
            }
        });

        function loadChartAll(labels, data) {
            const ctx = document.getElementById('usageChartAll').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Transactions Sum',
                        data: data,
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false,
                        lineTension: 0.25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    tooltips: { mode: 'index', intersect: false },
                    hover: { mode: 'nearest', intersect: false },
                    scales: {
                        xAxes: [{
                            gridLines: { display: false },
                            ticks: { maxTicksLimit: 8 }
                        }],
                        yAxes: [{
                            ticks: { beginAtZero: true, maxTicksLimit: 5 },
                            gridLines: { color: 'rgba(0,0,0,.06)' }
                        }]
                    }
                }
            });
        }

        function loadMap(url) {
            if (!url) return;
            const $frame = $('#gmapFrame');
            const finalUrl = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
            $frame.attr('src', finalUrl);
        }
    </script>
@endsection
