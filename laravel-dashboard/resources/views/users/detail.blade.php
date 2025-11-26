@extends('templates/template')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Account Detail</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('cpo.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cpo.users') }}">Accounts</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- User Information Card -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-primary text-white text-center py-3">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="fas fa-user me-2"></i>User Information
                            </h5>
                        </div>
                        <div class="card-body p-4 bg-white">
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="text-muted small text-uppercase fw-semibold">Name</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="fw-medium text-dark">{{ $user->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="text-muted small text-uppercase fw-semibold">Email</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="fw-medium text-dark">{{ $user->email ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="text-muted small text-uppercase fw-semibold">Role</span>
                                    </div>
                                    <div class="col-8">
                                        @if($user->id_role == 1)
                                            <span class="badge badge-primary">Admin</span>
                                        @elseif($user->id_role == 2)
                                            <span class="badge badge-info">Partner</span>
                                        @else
                                            <span class="badge badge-secondary">Unknown</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="text-muted small text-uppercase fw-semibold">Partner ID</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="fw-medium text-dark">{{ $user->partner_id ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="text-muted small text-uppercase fw-semibold">Created At</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="fw-medium text-dark">
                                            {{ $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information Card -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-info text-white text-center py-3">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="fas fa-info-circle me-2"></i>Account Information
                            </h5>
                        </div>
                        <div class="card-body p-4 bg-white">
                            @if($account)
                                @php
                                    $accountArray = $account->toArray();
                                @endphp
                                @foreach($accountArray as $key => $value)
                                    @if(!in_array($key, ['deleted_at', 'created_at', 'updated_at']) && $value !== null)
                                        <div class="mb-3 pb-2 border-bottom">
                                            <div class="row">
                                                <div class="col-4">
                                                    <span class="text-muted small text-uppercase fw-semibold">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                </div>
                                                <div class="col-8">
                                                    <span class="fw-medium text-dark">
                                                        @if(is_bool($value))
                                                            {{ $value ? 'Yes' : 'No' }}
                                                        @elseif(is_array($value))
                                                            {{ json_encode($value) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-exclamation-circle text-muted fa-3x mb-3"></i>
                                    <p class="text-muted mb-0">No account information found for this user.</p>
                                    @if(!$user->partner_id)
                                        <small class="text-muted">This user does not have a partner_id assigned.</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stations Section -->
            @if($account)
            <div class="row g-4 mt-2">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-success text-white text-center py-3">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="fas fa-charging-station me-2"></i>Stations
                            </h5>
                        </div>
                        <div class="card-body p-4 bg-white">
                            @if($stations && $stations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30px;">No</th>
                                                <th>Station Code</th>
                                                <th>Station Name</th>
                                                <th>Status</th>
                                                <th>Connectivity</th>
                                                <th>Connectors</th>
                                                <th>Last Heartbeat</th>
                                                <th style="width: 80px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stations as $index => $station)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $station->code ?? '-' }}</td>
                                                    <td>{{ $station->name ?? '-' }}</td>
                                                    <td>
                                                        @if($station->status == 'available')
                                                            <span class="badge badge-primary">Available</span>
                                                        @elseif($station->status == 'charging')
                                                            <span class="badge badge-warning">Charging</span>
                                                        @elseif($station->status == 'faulted')
                                                            <span class="badge badge-danger">Faulted</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ ucfirst($station->status ?? '-') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($station->connectivity_status == 'online')
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-signal-alt"></i> Online
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-signal-alt-slash"></i> Offline
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $station->connectors_count ?? 0 }}</td>
                                                    <td>
                                                        @if($station->last_heartbeat_at)
                                                            {{ \Carbon\Carbon::parse($station->last_heartbeat_at)->format('Y-m-d H:i:s') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('cpo.stations.details') }}?id={{ $station->id }}" 
                                                           class="btn btn-primary btn-sm" 
                                                           title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-charging-station text-muted fa-3x mb-3"></i>
                                    <p class="text-muted mb-0">No stations found for this account.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Back Button -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <a href="{{ route('cpo.users') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Accounts
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

