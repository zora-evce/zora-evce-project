@extends('templates/template')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h3 class="d-flex align-items-center">
                        Dashboard
                        <span class="ml-2 small text-muted">
                            {{ $partner->account_name ?? 'Admin' }}
                        </span>
                    </h3>
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

                .chart-wrap { height: 320px; }
                .chart-wrap-all-time { height: 150px; }
                .map-wrap { height: 320px; border-radius: 12px; overflow: hidden; }

                .stat-row { padding: .55rem 0; }
                .stat-row + .stat-row { border-top: 1px dashed rgba(0,0,0,.12); }
                .stat-label { letter-spacing: .06em; }
            </style>
            <div class="row align-items-stretch">
                <div class="col-lg-4 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center">
                            <h6 class="mb-0 flex-shrink-0">
                                <span class="mr-2"><i class="far fa-battery-bolt"></i></span> Ongoing
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="chart-wrap-all-time">
                                <canvas id="chartOngoing"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center">
                            <h6 class="mb-0 flex-shrink-0">
                                <span class="mr-2"><i class="fas fa-battery-full"></i></span> Finished
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="chart-wrap-all-time">
                                <canvas id="chartFinished"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center">
                            <h6 class="mb-0 flex-shrink-0">
                                <span class="mr-2"><i class="fas fa-money-bill"></i></span> Total Revenue
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="text-center">
                                <div class="text-muted small text-uppercase mb-2">
                                    All Time
                                </div>
                                <div class="font-weight-bold text-primary" id="revenueCounter" style="font-size: 30px;">
                                    Rp 0
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center align-items-center mb-4">

                <div class="col-lg-8">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="flex-grow-1 border-top mr-3" style="height:1px;"></div>
                        <div class="d-flex align-items-center">
                            <div class="mx-2" style="min-width:180px;">
                                <select class="form-control form-control-sm" id="filterStations">
                                    @if (!empty($stations))
                                        @foreach ($stations as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mx-2" style="min-width:150px;">
                                <select class="form-control form-control-sm" id="filterMonth">
                                    @if (!empty($months))
                                        @foreach ($months as $number => $name)
                                            <option value="{{ $number }}">{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mx-2">
                                <button type="button" class="btn btn-sm btn-primary" id="btnReset">
                                    <i class="fas fa-redo-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex-grow-1 border-top ml-3" style="height:1px;"></div>
                    </div>
                </div>
            </div>
            <div class="row align-items-stretch">
                <div class="col-lg-9 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x23FB;</span> Usage
                            </h6>
                            <small class="text-muted" id="headerChartStation"></small>
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
                        <div class="card-header d-flex align-items-center">
                            <h6 class="mb-0 flex-shrink-0">
                                <span class="mr-2">&#x21BB;</span> Transactions
                            </h6>
                            <small class="text-muted ml-auto text-truncate" id="headerTransactionStation" style="max-width: 50%; white-space: nowrap; overflow: hidden;"></small>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Ongoing</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsOngoingStation">0</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Finished</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsFinishedStation">0</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Total Revenue</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary" id="transactionsSumPriceStation">Rp 0</a>
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
        // CHART ALL TIME

        Chart.pluginService.register({
            beforeDraw: function(chart) {
                if (!chart.config.options.centerText) return;
                var ctx = chart.chart.ctx;
                var text = chart.config.options.centerText;
                ctx.save();
                ctx.font = "600 20px \"Source Sans Pro\", -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif, \"Apple Color Emoji\", \"Segoe UI Emoji\", \"Segoe UI Symbol\"";
                ctx.fillStyle = "#333";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";

                var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;

                ctx.fillText(text, centerX, centerY);
                ctx.restore();
            }
        });

        function createDoughnutProgress(canvasId, value, maxValue, color) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [value, maxValue - value],
                        backgroundColor: [
                            color,
                            '#e9ecef'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 75,
                    legend: { display: false },
                    tooltips: { enabled: false },
                    centerText: value + " / " + maxValue
                }
            });
        }

        $.ajax({
            url: "{{ route('cpo.dashboard.get-chart-all') }}",
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.data.transactions != null || response.data.transactions != undefined) {
                    createDoughnutProgress('chartOngoing', response.data.transactions.ongoing, response.data.transactions.total, '#28a745');
                    createDoughnutProgress('chartFinished', response.data.transactions.finished, response.data.transactions.total, '#007bff');
                    animateCounter('revenueCounter', response.data.transactions.sum_price, true, 1800);
                }
            },
            error: function (xhr) {
                console.error('Failed load usage chart:', xhr.responseText);
            }
        });

        function animateCounter(id, target, isCurrency, duration = 1500) {

            const el = document.getElementById(id);

            let start = 0;
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(easeOut * target);
                if (isCurrency) {
                    el.innerText = 'Rp ' + current.toLocaleString('id-ID');
                } else {
                    el.innerText = current.toLocaleString('id-ID');
                }
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            }
            requestAnimationFrame(updateCounter);
        }

        // CHART STATION
        let transactionChart = null;
        const $filterStations = $('#filterStations').select2({
            placeholder: 'Stations',
            theme: 'bootstrap4'
        });
        $('#filterStations').val(null).trigger('change');

        const $filterMonth = $('#filterMonth').select2({
            placeholder: 'Months',
            theme: 'bootstrap4'
        });
        $('#filterMonth').val(null).trigger('change');

        $filterStations.on('select2:select', function (e) {
            loadChartStation();
        });

        const currentMonth = new Date().getMonth() + 1;
        $filterMonth.val(currentMonth).trigger('change');
        $filterMonth.on('select2:select', function (e) {
            loadChartStation();
        });
        loadChartStation();

        $('#btnReset').on('click', function() {
            $filterStations.val(null).trigger('change');
            $filterMonth.val(currentMonth).trigger('change');
            loadChartStation();
        });

        function loadChartStation() {

            let filterStationVal = $filterStations.val();
            let filterStationText = $('#filterStations option:selected').text();
            if (!filterStationText) {
                filterStationText = 'All Stations';
            }

            let filterMonthVal = $filterMonth.val();
            let filterMonthText = $('#filterMonth option:selected').text();

            let headerChartStation = filterStationText + ' on ' + filterMonthText

            $('#headerChartStation').text(headerChartStation);
            $('#headerTransactionStation').text(headerChartStation);

            $.ajax({
                url: "{{ route('cpo.dashboard.get-chart-station') }}",
                type: "GET",
                dataType: "json",
                data: {
                    station_id: $filterStations.val(),
                    month: $filterMonth.val()

                },
                success: function (response) {
                    if (response.data.tx_sum.tx_date != null || response.data.tx_sum.tx_date != undefined) {
                        renderChartStation(response.data.tx_sum.tx_date, response.data.tx_sum.tx_sum);
                    }
                    if (response.data.transactions) {
                        animateCounter('transactionsOngoingStation', response.data.transactions.ongoing, false, 1800);
                        animateCounter('transactionsFinishedStation', response.data.transactions.finished, false, 1800);
                        animateCounter('transactionsSumPriceStation', response.data.transactions.sum_price, true, 1800);
                    }
                },
                error: function (xhr) {
                    console.error('Failed load usage chart:', xhr.responseText);
                }
            });
        }

        function renderChartStation(labels, data) {
            const ctx = document.getElementById('usageChartStation').getContext('2d');
            if (transactionChart !== null) {
                transactionChart.destroy();
            }
            transactionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Transactions Sum',
                        data: data,
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false
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
                            scaleLabel: {
                                display: true,
                                labelString: 'Date'
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                maxTicksLimit: 5
                            },
                            gridLines: {
                                color: 'rgba(0,0,0,.06)'
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Summary'
                            }
                        }]
                    }
                }
            });
        }

        // DASHBOARD
        $.ajax({
            url: "{{ route('cpo.dashboard.get-data-dashboard') }}",
            type: "GET",
            dataType: "json",
            success: function (response) {
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

        function loadMap(url) {
            if (!url) return;
            const $frame = $('#gmapFrame');
            const finalUrl = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
            $frame.attr('src', finalUrl);
        }
    </script>
@endsection
