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

                /* Tinggi section biar nggak kepanjangan */
                .chart-wrap { height: 160px; }
                .map-wrap { height: 320px; border-radius: 12px; overflow: hidden; }

                /* Mini list style untuk angka */
                .stat-row { padding: .55rem 0; }
                .stat-row + .stat-row { border-top: 1px dashed rgba(0,0,0,.12); }
                .stat-label { letter-spacing: .06em; }
            </style>

            <!-- ROW 1 -->
            <div class="row align-items-stretch">

                <!-- Usage chart -->
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
                                <canvas id="usageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions -->
                <div class="col-lg-3 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x21BB;</span> Transactions
                            </h6>
                            {{-- <small class="text-muted">Amount</small> --}}
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Ongoing</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary">{{ $transactions['ongoing'] ?? 0 }}</a>
                            </div>

                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Finished</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary">{{ $transactions['finished'] ?? 0 }}</a>
                            </div>

                            <div class="d-flex justify-content-between align-items-center stat-row">
                                <div>
                                    <div class="text-muted small text-uppercase stat-label">Total Price</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary">{{ $transactions['sum_price'] ?? 0 }}</a>
                            </div>

                            {{-- <div class="mt-auto pt-2">
                                <a href="#" class="small text-secondary d-inline-flex align-items-center">
                                    More <span class="ml-1">&rsaquo;</span>
                                </a>
                            </div> --}}
                        </div>

                    </div>
                </div>
            </div>

            <!-- ROW 2 -->
            <div class="row align-items-stretch">

                <!-- Charging stations -->
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
                                        <span class="text-muted small">Available</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary">{{ $stations['online'] ?? 0 }}</a>
                                </div>

                                <div class="d-flex justify-content-between align-items-center stat-row">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill badge-danger mr-2">Offline</span>
                                        <span class="text-muted small">Disconnected</span>
                                    </div>
                                    <a href="#" class="font-weight-bold text-primary">{{ $stations['offline'] ?? 0 }}</a>
                                </div>
                            </div>

                            {{-- <div class="mt-auto pt-2">
                                <a href="#" class="small text-secondary d-inline-flex align-items-center">
                                    More <span class="ml-1">&rsaquo;</span>
                                </a>
                            </div> --}}
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="col-lg-8 mb-4 d-flex">
                    <div class="card shadow-sm border-0 z-card h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <i class="far fa-map mr-2"></i><span>Location</span>
                            </h6>
                            <small class="text-muted">Stations map</small>
                        </div>

                        <div class="card-body">
                            <div class="map-wrap">
                                <iframe
                                    src="{{ $gmap_url }}"
                                    style="border:0;"
                                    width="100%"
                                    height="100%"
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
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

    <script>
        const txDate = @json($tx_sum['tx_date']);
        const txSum  = @json($tx_sum['tx_sum']);

        const ctx = document.getElementById('usageChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: txDate,
                datasets: [{
                    label: 'Transactions Sum',
                    data: txSum,
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    lineTension: 0.25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // penting: biar ngikutin .chart-wrap height
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/echarts@5.3.3/dist/echarts.min.js"></script>
    <script></script>
@endsection
