@extends('templates/template')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <p><a href="{{ url()->previous() }}" class="link-underline-primary"><i class="fas fa-arrow-left"></i>
                            Back</a></p>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Stations</li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="bg-light rounded-3 px-4 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">{{ $station_name }}</h5>
                        <small class="text-muted">Information Overview</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="station-tabs" role="tablist">
                        @foreach ($tabs as $key => $tab)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $tab['tab_name'] }}-tab"
                                    data-toggle="tab" href="#"
                                    data-url="{{ route('cpo.stations.details.tab', ['id' => $station_id, 'tab' => $tab['lookup_code']]) }}"
                                    role="tab">
                                    {{ $tab['lookup_value'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3" id="tab-content">
                        <div id="tab-loader" class="text-center p-4" style="display:none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>

                        <div id="tab-content-body"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        $(function() {
            function loadTabContent(url) {
                $("#tab-loader").show();
                $("#tab-content-body").html('');

                $.get(url, function(data) {
                    $("#tab-content-body").html(data);
                    $("#tab-loader").hide();
                }).fail(function() {
                    $("#tab-content-body").html(
                    "<p class='text-danger p-3'>Error loading tab content.</p>");
                    $("#tab-loader").hide();
                });
            }

            // Load the first tab by default
            let firstTab = $("#station-tabs .nav-link.active");
            if (firstTab.length) {
                loadTabContent(firstTab.data("url"));
            }

            // On tab click
            $("#station-tabs .nav-link").on("click", function(e) {
                e.preventDefault();

                $("#station-tabs .nav-link").removeClass("active");
                $(this).addClass("active");

                let url = $(this).data("url");
                loadTabContent(url);
            });
        });
    </script>
@endsection
