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
            <div class="row align-items-stretch">

                <!-- Usage chart -->
                <div class="col-lg-5 mb-4 d-flex">
                    <div class="card shadow-sm border-0 h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x23FB;</span> Usage
                            </h6>
                        </div>

                        <div class="card-body flex-grow-1">
                            <canvas id="usageChart" style="height:220px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Transactions -->
                <div class="col-lg-3 mb-4 d-flex">
                    <div class="card shadow-sm border-0 h-100 w-100 d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <span class="mr-2">&#x21BB;</span> Transactions
                            </h6>
                            <small class="text-muted">Amount</small>
                        </div>

                        <div class="card-body flex-grow-1">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="text-muted small text-uppercase">Ongoing</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary">{{ $transactions['ongoing'] ?? 0 }}</a>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small text-uppercase">Finished</div>
                                </div>
                                <a href="#" class="font-weight-bold text-primary">{{ $transactions['finished'] ?? 0 }}</a>
                            </div>

                        </div>

                        <div class="card-footer bg-white text-right mt-auto">
                            <a href="#" class="small text-secondary">More</a>
                        </div>
                    </div>
                </div>

                <!-- Charging stations -->
                <div class="col-lg-4 mb-4 d-flex">
                    <div class="card shadow-sm border-0 h-100 w-100 d-flex flex-column">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-charging-station mr-2"></i><span>Charging stations</span>
                            </h6>
                        </div>

                        <div class="card-body flex-grow-1">
                            <div>
                                <div class="text-muted small mb-1">Live:</div>

                                <div class="d-flex flex-wrap">
                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-success mr-1">Online</span>
                                        <a href="#" class="font-weight-bold text-primary">{{ $stations['online'] ?? 0 }}</a>
                                    </div>
                                    <div class="mr-3 mb-2">
                                        <span class="badge badge-pill badge-danger mr-1">Offline</span>
                                        <a href="#" class="font-weight-bold text-primary">{{ $stations['offline'] ?? 0 }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white text-right mt-auto">
                            <a href="#" class="small text-secondary">More</a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

    <script>
        // Contoh data dummy untuk chart
        var ctx = document.getElementById('usageChart').getContext('2d');
        var usageChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['01-2024','02-2024','03-2024'],
                datasets: [{
                    label: 'kWh',
                    data: [0,1000,10000],
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
