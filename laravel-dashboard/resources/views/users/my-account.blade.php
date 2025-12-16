@extends('templates/template')
@section('content')
    <section class="content">
        <div class="container-fluid">
            <!-- User Information Card -->
            <div class="row g-4">
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
                        <div class="card-header bg-primary text-white text-center py-3">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="fas fa-info-circle me-2"></i>Account Information
                            </h5>
                        </div>
                        <div class="card-body p-4 bg-white">
                            @if($account)
                                @php
                                    $accountArray = $account->toArray();
                                @endphp
                                <div class="row">
                                    @foreach($accountArray as $key => $value)
                                        @if(!in_array($key, ['deleted_at', 'created_at', 'updated_at']) && $value !== null)
                                            <div class="col-md-12 mb-3">
                                                <div class="pb-2 border-bottom">
                                                    <span class="text-muted small text-uppercase fw-semibold">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                    <div class="fw-medium text-dark mt-1">
                                                        @if(is_bool($value))
                                                            {{ $value ? 'Yes' : 'No' }}
                                                        @elseif(is_array($value))
                                                            {{ json_encode($value) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    <!-- Address from Stations -->
                                    @if($stations && $stations->count() > 0)
                                        @php
                                            $stationsWithAddress = $stations->filter(function($station) {
                                                return !empty($station->address);
                                            });
                                        @endphp

                                        @if($stationsWithAddress->count() > 0)
                                            @foreach($stationsWithAddress as $station)
                                                <div class="col-md-12 mb-3">
                                                    <div class="pb-2 border-bottom">
                                                        <span class="text-muted small text-uppercase fw-semibold">
                                                            @if($station->name)
                                                                Address ({{ $station->name }})
                                                            @elseif($station->code)
                                                                Address (Station {{ $station->code }})
                                                            @else
                                                                Address
                                                            @endif
                                                        </span>
                                                        <div class="fw-medium text-dark mt-1">
                                                            {{ $station->address }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-exclamation-circle text-muted fa-3x mb-3"></i>
                                    <p class="text-muted mb-0">No account information found.</p>
                                    @if(!$user->partner_id)
                                        <small class="text-muted">This user does not have a partner_id assigned.</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

