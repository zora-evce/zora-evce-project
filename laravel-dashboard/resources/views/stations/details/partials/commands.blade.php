<style>
    /* ============= SIDEBAR NAV ============= */
    .action-sidebar-nav .list-group-item {
        border: none;
        padding: 0.75rem 1rem;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .action-sidebar-nav .list-group-item i {
        font-size: 0.95rem;
    }

    .action-sidebar-nav .list-group-item.active {
        background: #f5f7fb;
        color: #1f2933;
        box-shadow: inset 3px 0 0 #2563eb;
    }

    /* ============= CARD DESIGN ============= */
    .action-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        transition: 0.15s ease;
    }

    .action-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 38px rgba(15, 23, 42, 0.1);
    }

    .badge-step {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 600;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #fff;
    }

    .action-title {
        font-weight: 600;
    }

    .action-form {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-left: auto !important;   /* <-- RATANYA KANAN */
    }

    @media (max-width: 768px) {
        .action-form {
            width: 100%;
            justify-content: flex-start;
            margin-left: 0 !important;
            margin-top: 10px;
        }
    }
</style>
<div class="container-fluid py-4">
    <div class="row">

        <!-- ========== SIDEBAR ========== -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush action-sidebar-nav" id="menu-tabs" role="tablist">

                        <a class="list-group-item list-group-item-action active"
                           id="core-tab" data-toggle="list" href="#core-panel" role="tab">
                            <i class="fas fa-cog mr-2"></i> Core
                        </a>

                        <a class="list-group-item list-group-item-action"
                           id="transaction-tab" data-toggle="list" href="#transaction-panel" role="tab">
                            <i class="fas fa-exchange-alt mr-2"></i> Transaction
                        </a>

                        <a class="list-group-item list-group-item-action"
                           id="firmware-tab" data-toggle="list" href="#firmware-panel" role="tab">
                            <i class="fas fa-microchip mr-2"></i> Firmware
                        </a>

                        <a class="list-group-item list-group-item-action"
                           id="locallist-tab" data-toggle="list" href="#locallist-panel" role="tab">
                            <i class="fas fa-list-ul mr-2"></i> Local list
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <!-- ========== CONTENT AREA ========== -->
        <div class="col-lg-9">
            <div class="tab-content" id="menu-tabContent">


                <!-- ======================================================
                     PANEL: CORE
                ====================================================== -->
                <div class="tab-pane fade show active" id="core-panel" role="tabpanel">

                    <!-- RESET STATION -->
                    <div class="card action-card mb-3">
                        <div class="card-body d-flex flex-wrap align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="badge-step mr-3">01</div>
                                <div>
                                    <h6 class="action-title mb-1">Reset station</h6>
                                    <small class="text-muted">Hard/soft reset all units</small>
                                </div>
                            </div>

                            <div class="action-form">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-danger">Hard reset</button>
                                    <button class="btn btn-sm btn-outline-secondary">Soft reset</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESET CONNECTOR -->
                    <div class="card action-card mb-3">
                        <div class="card-body d-flex flex-wrap align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="badge-step mr-3">02</div>
                                <div>
                                    <h6 class="action-title mb-1">Reset connector</h6>
                                    <small class="text-muted">Reset specific connector</small>
                                </div>
                            </div>

                            <div class="action-form">
                                <label class="mr-2 small text-muted">Connector</label>
                                <select class="form-control form-control-sm mr-3" style="width:80px;">
                                    <option>1</option>
                                    <option>2</option>
                                </select>

                                <button class="btn btn-sm btn-outline-primary">Reset</button>
                            </div>
                        </div>
                    </div>

                    <!-- CLEAR CACHE -->
                    <div class="card action-card mb-3">
                        <div class="card-body d-flex flex-wrap align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="badge-step mr-3">03</div>
                                <div>
                                    <h6 class="action-title mb-1">Clear cache</h6>
                                    <small class="text-muted">Clear charger local cache</small>
                                </div>
                            </div>

                            <div class="action-form">
                                <button class="btn btn-sm btn-outline-secondary">Clear cache</button>
                            </div>
                        </div>
                    </div>

                    <!-- CHANGE AVAILABILITY -->
                    <div class="card action-card mb-3">
                        <div class="card-body d-flex flex-wrap align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="badge-step mr-3">04</div>
                                <div>
                                    <h6 class="action-title mb-1">Change availability</h6>
                                    <small class="text-muted">Set connector operative/inoperative</small>
                                </div>
                            </div>

                            <div class="action-form">
                                <label class="mr-2 small text-muted">Connector</label>
                                <select class="form-control form-control-sm mr-2" style="width:80px;">
                                    <option>0</option><option>1</option>
                                </select>

                                <label class="mr-2 small text-muted">Availability</label>
                                <select class="form-control form-control-sm mr-3" style="width:130px;">
                                    <option>Operative</option>
                                    <option>Inoperative</option>
                                </select>

                                <button class="btn btn-sm btn-outline-primary">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- UNLOCK CONNECTOR -->
                    <div class="card action-card mb-3">
                        <div class="card-body d-flex flex-wrap align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="badge-step mr-3">05</div>
                                <div>
                                    <h6 class="action-title mb-1">Unlock connector</h6>
                                    <small class="text-muted">Unlock specific connector</small>
                                </div>
                            </div>

                            <div class="action-form">
                                <label class="mr-2 small text-muted">Connector</label>
                                <select class="form-control form-control-sm mr-3" style="width:80px;">
                                    <option>1</option><option>2</option>
                                </select>

                                <button class="btn btn-sm btn-outline-primary">Unlock</button>
                            </div>
                        </div>
                    </div>

                </div>


                <!-- ======================================================
                     PANEL: TRANSACTION
                ====================================================== -->
                <div class="tab-pane fade" id="transaction-panel" role="tabpanel">

                    <!-- REMOTE START -->
                    <div class="card action-card mb-3">
                        <div class="card-body d-flex flex-wrap align-items-center">

                            <div class="d-flex align-items-center">
                                <div class="badge-step mr-3">01</div>
                                <div>
                                    <h6 class="action-title mb-1">Remote Start</h6>
                                    <small class="text-muted">Start session charging stations</small>
                                </div>
                            </div>

                            <div class="action-form">
                                <select class="form-control form-control-sm mr-2" style="width:80px;">
                                    <option>1</option><option>2</option>
                                </select>

                                <select class="form-control form-control-sm mr-2" style="width:120px;">
                                    <option>1223</option><option>2333</option>
                                </select>

                                <input type="text" class="form-control form-control-sm mr-2"
                                       placeholder="RFID / Card" style="width:150px;">

                                <button class="btn btn-sm btn-primary">Start</button>
                            </div>

                        </div>
                    </div>

                    <!-- REMOTE STOP -->
                    <div class="card action-card mb-3">
                        <div class="card-body d-flex flex-wrap align-items-center">

                            <div class="d-flex align-items-center">
                                <div class="badge-step mr-3">02</div>
                                <div>
                                    <h6 class="action-title mb-1">Remote Stop</h6>
                                    <small class="text-muted">Stop session charging stations</small>
                                </div>
                            </div>

                            <div class="action-form">
                                <select class="form-control form-control-sm mr-3" style="width:120px;">
                                    <option>1</option><option>2</option>
                                </select>

                                <button class="btn btn-sm btn-danger">Stop</button>
                            </div>

                        </div>
                    </div>

                </div>


                <!-- ======================================================
                     PANEL: FIRMWARE
                ====================================================== -->
                <div class="tab-pane fade" id="firmware-panel" role="tabpanel">

                    <div class="card action-card mb-3">
                        <div class="card-body">
                            <h6 class="action-title mb-1">Firmware update</h6>
                            <small class="text-muted">Available soon</small>
                        </div>
                    </div>

                </div>


                <!-- ======================================================
                     PANEL: LOCAL LIST
                ====================================================== -->
                <div class="tab-pane fade" id="locallist-panel" role="tabpanel">

                    <div class="card action-card mb-3">
                        <div class="card-body">
                            <h6 class="action-title mb-1">Local list</h6>
                            <small class="text-muted">Available soon</small>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <!-- END CONTENT COLUMN -->
    </div>
</div>
