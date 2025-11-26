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

            <!-- ROW 1 -->
            <div class="row">

                <!-- Usage chart -->
                <div class="col-lg-5 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x23FB;</span> Usage
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="usageChart" style="height:220px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Transactions -->
                <div class="col-lg-3 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x21BB;</span> Transactions
                            </h6>
                            <small class="text-muted">Amount</small>
                        </div>
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="text-muted small text-uppercase">Ongoing</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary">0</a>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small text-uppercase">Finished</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary">0</a>
                            </div>

                        </div>
                        <div class="card-footer bg-white text-right">
                            <a href="#" class="small text-secondary">More</a>
                        </div>
                    </div>
                </div>

                <!-- Map -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x1F5FA;</span> Map
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <!-- ganti div ini dengan Google Maps sesungguhnya -->
                            <div style="height:260px; width:100%; background:#e9ecef;"
                                class="d-flex align-items-center justify-content-center text-muted">
                                Map placeholder
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ROW 2 -->
            <div class="row">

                <!-- Charging stations -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-charging-station mr-2"></i><span>Charging stations</span>
                            </h6>
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <div class="text-muted small mb-1">Administrative:</div>

                                <div class="d-flex flex-wrap">
                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-primary mr-1">In transit</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>

                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-info mr-1">Installed</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>

                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-success mr-1">Active</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>

                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-danger mr-1">Inactive</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="text-muted small mb-1">Live:</div>

                                <div class="d-flex flex-wrap">
                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-success mr-1">Online</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>

                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-danger mr-1">Offline</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>

                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-secondary mr-1">Faulted</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>

                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-dark mr-1">Unknown</span>
                                        <a href="#" class="font-weight-bold text-primary">0</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer bg-white text-right">
                            <a href="#" class="small text-secondary">More</a>
                        </div>
                    </div>
                </div>

                <!-- Connectors -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x25CE;</span> Connectors
                            </h6>
                        </div>
                        <div class="card-body">

                            <div class="text-muted small mb-2">Live:</div>

                            <div class="d-flex flex-wrap">

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-success mr-1">Available</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-warning mr-1">Occupied</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-primary mr-1">Charging</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-info mr-1">Reserved</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-danger mr-1">Not available</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-danger mr-1">Out of order</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-secondary mr-1">Planned</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                                <div class="mr-3 mb-2">
                                    <span class="badge badge-pill badge-dark mr-1">Removed</span>
                                    <a href="#" class="font-weight-bold text-primary">0</a>
                                </div>

                            </div>

                        </div>
                        <div class="card-footer bg-white text-right">
                            <a href="#" class="small text-secondary">More</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

    <script>
        // Contoh data dummy untuk chart
        var ctx = document.getElementById('usageChart').getContext('2d');
        var usageChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['11-2024', '12-2024', '01-2025', '02-2025', '03-2025', '04-2025', '05-2025', '06-2025',
                    '07-2025', '08-2025', '09-2025', '10-2025', '11-2025'
                ],
                datasets: [{
                    label: 'kWh',
                    data: [9000, 10500, 8200, 8800, 9500, 7800, 12300, 12600, 13200, 11500, 11800, 13700,
                        12900
                    ],
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'kWh'
                        }
                    }]
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/echarts@5.3.3/dist/echarts.min.js"></script>
    <script></script>
@endsection
