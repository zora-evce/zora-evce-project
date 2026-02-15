<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Zora EV Charger</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/fonts-googleapis/sans-pro.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/fontawesome-pro/css/all.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/toastr/toastr.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/code-ionicframework/ionicons.min.css') }}">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte//plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/jqvmap/jqvmap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/summernote/summernote-bs4.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('templates/adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('templates/adminlte/plugins/fonts-googleapis/poppins.css') }}">
    <link rel="stylesheet" href="{{ asset('templates/admin/css/style.css') }}">
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="" src="{{ asset('images/logo.png') }}"
                alt="Zora Logo" height="8%" width="">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-user"></i>
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <!-- Menu Body -->
                        <li class="user-body" style="background-color:#012b46;color:#fff">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <p>
                                        {{ Auth::user()->name }} / {{ (Auth::user()->id_role == 1) ? 'Admin' : 'Partner' }}
                                    </p>
                                </div>
                            </div>
                            <!-- /.row -->
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="{{ route('cpo.change-password') }}" class="btn btn-sm btn-primary">Change Password</a>
                            <form action="{{ route('cpo.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger float-right">Sign Out</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- /.navbar -->

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('cpo.dashboard') }}" class="brand-link">
                <img src="{{ asset('images/logo-white.png') }}" alt="AdminLTE Logo" class="brand-image elevation-3">
                <span class="brand-text font-weight-bold">EV Charger</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar" style="display: flex; flex-direction: column;">
                <!-- Sidebar user panel (optional) -->
                <!-- Sidebar Menu -->
                <nav class="mt-2" style="flex: 1;">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('cpo.dashboard') }}"
                                class="nav-link {{ Route::is('cpo.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('cpo.stations') }}"
                                class="nav-link {{ Route::is('cpo.stations*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-charging-station"></i>
                                <p>
                                    Stations
                                </p>
                            </a>
                        </li>
                        <li class="nav-item menu-closed">
                            <a href="#"
                                class="nav-link {{ Route::is('cpo.transactions*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-exchange-alt"></i>
                                <p>
                                    Transactions
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('cpo.transactions.chargepoints') }}" class="nav-link {{ Route::is('cpo.transactions.chargepoints') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Chargepoints</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @if (Auth::user()->id_role == 1)
                            <li class="nav-item">
                                <a href="{{ route('cpo.users') }}"
                                    class="nav-link {{ Route::is('cpo.users*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>
                                        Accounts
                                    </p>
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('cpo.my-account') }}"
                                class="nav-link {{ Route::is('cpo.my-account') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    My Account
                                </p>
                            </a>
                        </li>
                        @if (Auth::user()->id_role == 1)
                            <li class="nav-item menu-closed">
                                <a href="#"
                                    class="nav-link {{ Route::is('cpo.master*') ? 'active' : '' }} }}">
                                    <i class="nav-icon fas fa-server"></i>
                                    <p>
                                        Master
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('cpo.master.tariff') }}" class="nav-link {{ Route::is('cpo.master.tariff') ? 'active' : '' }}">
                                            <i class="fas fa-usd-circle nav-icon"></i>
                                            <p>Tariff</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->

                <!-- Charging Icon at Bottom -->
                <div class="text-center p-5 mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <img src="{{ asset('images/charging-icon.png') }}" alt="Charging Icon" style="max-width: 120px; height: auto; opacity: 0.8;">
                </div>
            </div>
            <!-- /.sidebar -->
        </aside>
        <div class="content-wrapper">
            <!-- jQuery -->
            <script src="{{ asset('templates/adminlte/plugins/jquery/jquery.min.js') }}"></script>
            <!-- jQuery UI 1.11.4 -->
            <script src="{{ asset('templates/adminlte/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
            <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
            <script>
                $.widget.bridge('uibutton', $.ui.button)
            </script>
            <!-- Bootstrap 4 -->
            <script src="{{ asset('templates/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
            <!-- Select2 -->
            <script src="{{ asset('templates/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
            <!-- SweetAlert2 -->
            <script src="{{ asset('templates/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
            <!-- Toastr -->
            <script src="{{ asset('templates/adminlte/plugins/toastr/toastr.min.js') }}"></script>
            <script>
                var Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                const messages = {
                    success: @json(session('success')),
                    error: @json(session('error')),
                    warning: @json(session('warning'))
                };
                @if (session('success'))
                    toastr.success(messages.success);
                @elseif (session('error'))
                    if (Array.isArray(messages.error)) {
                        $.each(messages.error, function(index, value) {
                            toastr.error(value)
                        })
                    } else {
                        toastr.error(messages.error)
                    }
                @elseif (session('warning'))
                    toastr.warning(messages.warning);
                @endif
            </script>
            <!-- ChartJS -->
            {{-- <script src="{{ asset('templates/adminlte/plugins/chart.js/Chart.min.js') }}"></script> --}}
            <!-- Sparkline -->
            {{-- <script src="{{ asset('templates/adminlte/plugins/sparklines/sparkline.js') }}"></script> --}}
            <!-- JQVMap -->
            {{-- <script src="{{ asset('templates/adminlte/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script> --}}
            <!-- jQuery Knob Chart -->
            <script src="{{ asset('templates/adminlte/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
            <!-- daterangepicker -->
            <script src="{{ asset('templates/adminlte/plugins/moment/moment.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>
            <!-- Tempusdominus Bootstrap 4 -->
            <script
                src="{{ asset('templates/adminlte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}">
            </script>
            <!-- Summernote -->
            <script src="{{ asset('templates/adminlte/plugins/summernote/summernote-bs4.min.js') }}"></script>
            <!-- overlayScrollbars -->
            <script src="{{ asset('templates/adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}">
            </script>
            <!-- AdminLTE App -->
            <script src="{{ asset('templates/adminlte/dist/js/adminlte.js') }}"></script>
            <!-- AdminLTE for demo purposes -->
            {{-- <script src="{{ asset('templates/adminlte/dist/js/demo.js') }}"></script> --}}
            <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
            {{-- <script src="{{ asset('templates/adminlte/dist/js/pages/dashboard.js') }}"></script> --}}
            <!-- DataTables  & Plugins -->
            <script src="{{ asset('templates/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}">
            </script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}">
            </script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/jszip/jszip.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/pdfmake/pdfmake.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/pdfmake/vfs_fonts.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/echarts/echarts.min.js') }}"></script>
            <script src="{{ asset('templates/adminlte/plugins/xlsx/xlsx.full.min.js') }}"></script>
            @if (Route::is('cpo.dashboard'))
            @else
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <p><a href="{{ url()->previous() }}" class="link-underline-primary"><i class="fas fa-arrow-left mr-2"></i>Back</a></p>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    @foreach ($breadcrumbs as $breadcrumb)
                                        @if ($breadcrumb['url'])
                                            <li class="breadcrumb-item">
                                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                                            </li>
                                        @else
                                            <li class="breadcrumb-item active">
                                                {{ $breadcrumb['title'] }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</body>

<!-- /.content-wrapper -->
<script>
    $(document).ready(function() {
        var login_success = @json(session('login_success', ''));
        if (login_success) {
            toastr.success(login_success);
        }

        // Global AJAX error handler for session expiration
        $(document).ajaxError(function(event, xhr, settings) {
            if (xhr.status === 401 || xhr.status === 403) {
                // Session expired or unauthorized
                let response = xhr.responseJSON;
                if (response && response.redirect) {
                    toastr.error(response.message || 'Session expired. Please login again.');
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    toastr.error('Session expired. Please login again.');
                    setTimeout(function() {
                        window.location.href = '{{ route("cpo.login") }}';
                    }, 1500);
                }
            }
        });

        $(document).on("click", ".action-save", function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Attention!',
                text: "Are you sure you want to save this?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.value) {
                    form.submit();
                }
            });
        });

        $(document).on("click", ".action-delete", function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: "Are you sure you want to delete this?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.value) {
                    const form = e.target.closest('form');
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'delete';
                    form.appendChild(actionInput);
                    form.submit();
                }
            });
        });
    });
</script>

</html>
