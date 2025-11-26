<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Zora EVCE</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{ asset('templates/sb/assets/favicon.ico') }}" />
        <!-- Bootstrap Icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" type="text/css" />
        <!-- SimpleLightbox plugin CSS-->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('templates/sb/css/styles.css') }}" rel="stylesheet" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <style>
            /* Keep main container tall enough even if top text is removed */
            #contact .container { min-height: 80vh; }

            /* Ensure modal content is visible with proper text color */
            .modal-body {
                color: #212529 !important;
            }
            .modal-body h4, .modal-body h5, .modal-body h6 {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                color: #212529 !important;
            }
            .modal-body p, .modal-body li, .modal-body ul {
                color: #212529 !important;
            }

            /* Enhanced Stepper with Icons and 3D Effect */
            .stepper-wrapper {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 40px auto;
                max-width: 600px;
                position: relative;
            }

            .stepper-item {
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                flex: 1;
                text-align: center;
                color: #ccc;
                transition: all 0.3s ease;
            }

            /* Remove connecting lines */
            .stepper-item::before,
            .stepper-item::after {
                content: none !important;
                display: none !important;
            }

            .step-counter {
                width: 70px;
                height: 70px;
                background: #ccc;
                border: 3px solid #ccc;
                border-radius: 12px;
                z-index: 2;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                color: #fff;
                transition: all 0.3s ease;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                position: relative;
            }

            .step-counter i {
                font-size: 28px;
                transition: all 0.3s ease;
            }

            .step-name {
                margin-top: 12px;
                font-size: 11px;
                font-weight: 500;
                color: #fff !important;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }

            /* Mobile responsive for step name */
            @media (max-width: 576px) {
                .step-name {
                    font-size: 9px;
                    letter-spacing: 0.2px;
                }
            }

            /* Active State - 3D Elevated Effect */
            .stepper-item.active .step-counter {
                background: #0c9936;
                border-color: #0c9936;
                color: #ffffff;
                box-shadow:
                    0 8px 16px rgba(12, 153, 54, 0.4),
                    0 4px 8px rgba(12, 153, 54, 0.3),
                    inset 0 2px 4px rgba(255, 255, 255, 0.2);
                transform: translateY(-8px) scale(1.05);
                animation: stepperGlow 2s ease-in-out infinite;
            }

            .stepper-item.active .step-counter i {
                color: #ffffff;
                filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.5));
            }

            .stepper-item.active .step-name {
                color: #0c9936;
            }

            /* Completed State */
            .stepper-item.completed .step-counter {
                background: #0c9936;
                border-color: #0c9936;
                color: #ffffff;
                box-shadow:
                    0 4px 8px rgba(12, 153, 54, 0.3);
            }

            .stepper-item.completed .step-counter i {
                color: #ffffff;
            }

            .stepper-item.completed .step-name {
                color: #0c9936;
            }

            /* Glow Animation for Active State */
            @keyframes stepperGlow {
                0%, 100% {
                    box-shadow:
                        0 8px 16px rgba(12, 153, 54, 0.4),
                        0 4px 8px rgba(12, 153, 54, 0.3),
                        inset 0 2px 4px rgba(255, 255, 255, 0.2);
                }
                50% {
                    box-shadow:
                        0 12px 24px rgba(12, 153, 54, 0.6),
                        0 6px 12px rgba(12, 153, 54, 0.4),
                        inset 0 2px 4px rgba(255, 255, 255, 0.3);
                }
            }

            /* Hover Effect */
            .stepper-item:not(.active):hover .step-counter {
                border-color: #0c9936;
                background: #0c9936;
                color: #fff;
                box-shadow: 0 6px 12px rgba(12, 153, 54, 0.3);
                transform: translateY(-2px);
            }

            /* Unique Connector Number Design with EV Connector Icon */
            .connector-display {
                position: relative;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin: 20px 0;
            }

            .connector-icon-wrapper {
                position: relative;
                width: 180px;
                height: 200px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }

            .connector-svg {
                width: 100%;
                height: 100%;
                object-fit: contain;
                filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
            }

            .connector-number-badge {
                position: absolute;
                top: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                background: linear-gradient(135deg, #00B23C 0%, #00D94F 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                font-weight: 900;
                color: #ffffff;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
                box-shadow:
                    0 4px 8px rgba(0, 178, 60, 0.4),
                    0 0 0 3px rgba(255, 255, 255, 0.3);
                z-index: 10;
                animation: connectorPulse 2s ease-in-out infinite;
            }

            .connector-label {
                margin-top: 10px;
                font-size: 14px;
                font-weight: 600;
                color: #ffffff;
                text-transform: uppercase;
                letter-spacing: 2px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            @keyframes connectorPulse {
                0%, 100% {
                    box-shadow:
                        0 4px 8px rgba(0, 178, 60, 0.4),
                        0 0 0 3px rgba(255, 255, 255, 0.3);
                    transform: scale(1);
                }
                50% {
                    box-shadow:
                        0 6px 12px rgba(0, 178, 60, 0.6),
                        0 0 0 4px rgba(255, 255, 255, 0.4);
                    transform: scale(1.05);
                }
            }

            .connector-icon-wrapper:hover {
                transform: scale(1.05);
            }

            .connector-icon-wrapper:hover .connector-number-badge {
                animation: none;
                transform: scale(1.1);
            }

            /* Arrow Button Styles */
            .btn-next i,
            .btn-prev i {
                font-size: 20px;
            }

            .btn-next,
            .btn-prev {
                min-width: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Dynamic Welcome Section Design */
            #mainForm1 .subsection {
                background: linear-gradient(135deg, rgba(2, 60, 97, 0.95) 0%, rgba(2, 60, 97, 0.85) 100%);
                border-radius: 24px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.2),
                    0 4px 16px rgba(0, 0, 0, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                padding: 2.5rem 2rem !important;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                overflow: hidden;
            }

            #mainForm1 .subsection::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
                animation: welcomeShine 8s ease-in-out infinite;
                pointer-events: none;
            }

            #mainForm1 .subsection:hover {
                transform: translateY(-4px);
                box-shadow:
                    0 12px 40px rgba(0, 0, 0, 0.25),
                    0 6px 20px rgba(0, 0, 0, 0.15),
                    inset 0 1px 0 rgba(255, 255, 255, 0.15);
                border-color: rgba(255, 255, 255, 0.2);
            }

            #mainForm1 .subsection h2 {
                position: relative;
                z-index: 1;
                font-weight: 700;
                letter-spacing: -0.5px;
                text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }

            #mainForm1 .subsection p {
                position: relative;
                z-index: 1;
            }

            #mainForm1 .connector-display {
                position: relative;
                z-index: 1;
            }

            @keyframes welcomeShine {
                0%, 100% {
                    transform: translate(-50%, -50%) rotate(0deg);
                    opacity: 0.3;
                }
                50% {
                    transform: translate(-40%, -40%) rotate(180deg);
                    opacity: 0.5;
                }
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                #mainForm1 .subsection {
                    border-radius: 20px;
                    padding: 2rem 1.5rem !important;
                }
            }

            /* Logo positioning */
            @media (max-width: 768px) {
                body > div[style*="position: fixed"] {
                    top: 10px !important;
                    left: 10px !important;
                }
                body > div[style*="position: fixed"] img {
                    max-width: 120px !important;
                }
            }
        </style>
    </head>
    <body id="page-top">
        <!-- Logo in top left corner -->
        <div style="position: fixed; top: 20px; left: 20px; z-index: 1000;">
            <img src="{{ asset('images/logo-mebi-white.png') }}" alt="Zora EVCE" style="max-width: 150px; height: auto;">
        </div>

        <section class="page-section" id="contact">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 justify-content-center mb-2">
                    <div class="col-lg-8 col-xl-6 text-center">
                        <img src="{{ asset('images/logo-white.png') }}" alt="" style="display:block; margin: 0 auto;width:40%">
                        <hr class="divider" />
                        <br>
                    </div>
                </div>

                <div class="stepper-wrapper">
                    <div class="stepper-item active">
                        <div class="step-counter">
                            <i class="bi bi-house-door"></i>
                        </div>
                        <div class="step-name">Welcome</div>
                    </div>
                    <div class="stepper-item">
                        <div class="step-counter">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="step-name">Personal Detail</div>
                    </div>
                    <div class="stepper-item">
                        <div class="step-counter">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="step-name">Duration</div>
                    </div>
                    <div class="stepper-item">
                        <div class="step-counter">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div class="step-name">Payment</div>
                    </div>
                </div>

            <div class="row gx-4 gx-lg-5 justify-content-center mainForm mb-5 d-none" id="mainForm1">
                    <div class="col-lg-8 col-xl-6 text-center">
                        <div class="subsection px-4 py-4">
                            <h2 class="mt-0">Welcome!</h2>
                            <p class="text-muted">You're at <b><span class="text-highlight">{{ $station->name }}</span></b><br>
                                Connector Number :
                            </p>
                            <div class="row gx-4 gx-lg-5 justify-content-center mb-2">
                                <div class="col-lg-8 col-xl-6 text-center">
                                    <div class="connector-display">
                                        <div class="connector-icon-wrapper">
                                            <div class="connector-number-badge">{{ $connector->connector_number }}</div>
                                            <img src="{{ asset('images/ccs.png') }}" alt="Connector" class="connector-svg">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" data-step="2" class="btn btn-primary btn-md btn-next">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row gx-4 gx-lg-5 justify-content-center mainForm mb-5 d-none" id="mainForm2">
                    <div class="col-lg-6">
                        <div class="subsection px-4 py-4">
                            <div class="mb-3">
                                <label for="" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" aria-describedby="">
                                <div class="invalid-feedback" id="nameError">Please enter your name.</div>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" aria-describedby="">
                                <div id="" class="form-text">We'll send the information about your charging activity through this email.</div>
                                <div class="invalid-feedback" id="emailError">Please enter a valid email address.</div>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Phone</label>
                                <input type="text" id="phone" class="form-control" id="">
                                <div class="invalid-feedback" id="phoneError">Please enter a valid phone number (7–15 digits).</div>
                            </div>
                        </div>
                        <br>
                        <div class="d-flex justify-content-between">
                            <button type="button" data-step="1" class="btn btn-primary btn-md btn-prev">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                            <button type="button" data-step="3" class="btn btn-primary btn-md btn-next">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row gx-4 gx-lg-5 justify-content-center mb-5 mainForm" id="mainForm3">
                    <div class="col-lg-6">
                        <div class="subsection px-4 py-4">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <select id="duration" name="duration" class="form-select">
                                    @if (env('IS_TEST_MINUTE') == true)
                                        <option value="0.05">3 Minute</option>
                                    @endif
                                    <option value="1">1 Hour</option>
                                    <option value="2">2 Hours</option>
                                    <option value="3">3 Hours</option>
                                    <option value="4">4 Hours</option>
                                    <option value="5">5 Hours</option>
                                    <option value="6">6 Hours</option>
                                    <option value="7">7 Hours</option>
                                    <option value="8">8 Hours</option>
                                    <option value="9">9 Hours</option>
                                    <option value="10">10 Hours</option>
                                    <option value="11">11 Hours</option>
                                    <option value="12">12 Hours</option>
                                    <option value="13">13 Hours</option>
                                    <option value="14">14 Hours</option>
                                    <option value="15">15 Hours</option>
                                    <option value="16">16 Hours</option>
                                    <option value="17">17 Hours</option>
                                    <option value="18">18 Hours</option>
                                    <option value="19">19 Hours</option>
                                    <option value="20">20 Hours</option>
                                    <option value="21">21 Hours</option>
                                    <option value="22">22 Hours</option>
                                    <option value="23">23 Hours</option>
                                    <option value="24">24 Hours</option>
                                </select>
                            </div>
                            <div class="text-danger small d-none" id="durationError">Please select a duration.</div>
                        </div>
                        <br>
                        <div class="d-flex justify-content-between">
                            <button type="button" data-step="2" class="btn btn-primary btn-md btn-prev">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                            <button type="button" data-step="4" class="btn btn-primary btn-md btn-next">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row gx-4 gx-lg-5 justify-content-center mainForm mb-5 d-none" id="mainForm4">
                    <div class="col-lg-6">
                        <div class="subsection px-4 py-4">
                            <h1>Payment</h1>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <span>Selected Duration</span>
                                    <span id="summaryDuration">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Price</span>
                                    <span id="summarySubtotal">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Tax</span>
                                    <span id="summaryTax">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Price After Tax</span>
                                    <span id="summaryPrice">-</span>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="d-flex justify-content-between">
                            <button type="button" data-step="3" class="btn btn-primary btn-md btn-prev">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                            <button type="button" data-step="4" class="btn btn-primary btn-md btn-next">Pay</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- User Agreement Modal -->
        <div class="modal fade" id="userAgreementModal" tabindex="-1" aria-labelledby="userAgreementModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userAgreementModalLabel">User Agreement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 20px; color: #212529;">
                        <div class="mb-4">
                            <h4 class="mb-3" style="display: block; font-size: 1.5rem; font-weight: bold; color: #212529;">User Agreement - Zora</h4>
                        </div>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">1. Introduction</h6>
                        <p>This User Agreement governs the use of electric vehicle (EV) charging services and the Zora transaction platform operated by PT Mega Energi Biru Indonesia.</p>
                        <p>By using Zora services, users are deemed to have read, understood, and agreed to all terms and conditions in this document.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">2. Scope of Services</h6>
                        <p>Zora services include:</p>
                        <ul>
                            <li>Access to Zora charger units through QR Scan without login</li>
                            <li>Payment services through Midtrans</li>
                            <li>Charging session management through OCPP system</li>
                            <li>Storage of charging logs in Zora backend system</li>
                        </ul>
                        <p>Zora does not provide location booking, reservation, or vehicle technical services.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">3. Use Without Account</h6>
                        <p>Users do not need to create an account to access services.</p>
                        <p>All processes are conducted through:</p>
                        <ul>
                            <li>QR Scan on charger unit</li>
                            <li>Payment</li>
                            <li>Charging session starts automatically</li>
                        </ul>
                        <p>By using this method, users remain bound by all rules in this User Agreement.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">4. User Obligations</h6>
                        <p>Users must:</p>
                        <ul>
                            <li>Use the charger according to instructions available on the device</li>
                            <li>Use connectors compatible with the vehicle</li>
                            <li>Supervise the vehicle during charging</li>
                            <li>Move the vehicle after the charging session is complete (to avoid idle time)</li>
                            <li>Use valid payment methods</li>
                            <li>Use the charging area in an orderly and safe manner</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">5. Prohibited Use</h6>
                        <p>Users are prohibited from:</p>
                        <ul>
                            <li>Damaging, modifying, or manipulating charger devices</li>
                            <li>Using illegal or uncertified adapters</li>
                            <li>Forcing connectors into place</li>
                            <li>Leaving the charger in idle position after charging is complete</li>
                            <li>Opening charger casing or attempting to access internal components</li>
                            <li>Performing actions that disrupt charger operations or threaten safety</li>
                            <li>Misusing payment systems or conducting fraudulent transactions</li>
                        </ul>
                        <p>If violations occur, Zora reserves the right to terminate charging sessions and take legal action.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">6. User Responsibility</h6>
                        <p>Users are responsible for:</p>
                        <ul>
                            <li>Vehicle and personal property security during charging</li>
                            <li>Accuracy in selecting charger location</li>
                            <li>Vehicle condition and battery suitability for charging</li>
                            <li>Risks resulting from use of unsafe third-party cables, adapters, or devices</li>
                        </ul>
                        <p>Zora is not responsible for vehicle damage resulting from use of third-party devices that do not meet standards.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">7. Zora Responsibility</h6>
                        <p>Zora is responsible for:</p>
                        <ul>
                            <li>Maintaining charger readiness according to technical standards</li>
                            <li>Providing secure payment systems</li>
                            <li>Storing charging logs for audit and technical support purposes</li>
                            <li>Responding to customer complaints during operational hours</li>
                        </ul>
                        <p>However, Zora is not responsible for:</p>
                        <ul>
                            <li>Power disruptions from energy providers</li>
                            <li>Internet network disruptions from telecommunications operators</li>
                            <li>Force majeure causing charger malfunction</li>
                            <li>User errors in device usage</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">8. Transactions & Payment</h6>
                        <ul>
                            <li>All transactions are processed through Midtrans</li>
                            <li>Users must ensure payment is completed before charging begins</li>
                            <li>Transaction receipts will be stored and can be used as basis for complaints</li>
                            <li>If issues occur, refund process follows the Refund Policy</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">9. Data & Privacy</h6>
                        <p>Use of Zora services is subject to the Privacy Policy which explains how user data is collected, processed, and stored.</p>
                        <p>Zora does not collect vehicle data such as license plate numbers or Vehicle ID.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">10. Service Termination</h6>
                        <p>Zora reserves the right to terminate or refuse service if:</p>
                        <ul>
                            <li>There are indications of misuse</li>
                            <li>Users violate User Agreement terms</li>
                            <li>Charger units are under maintenance or experiencing technical issues</li>
                            <li>Payment is invalid or suspicious</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">11. Terms Changes</h6>
                        <p>Terms in this User Agreement may be updated at any time.</p>
                        <p>Changes will be announced through the Zora platform and take effect immediately after publication.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">12. Official Contact</h6>
                        <p>For questions or issues related to service usage:</p>
                        <p><strong>PT Mega Energi Biru Indonesia (Zora)</strong><br>
                        Email: office@mebi.co.id<br>
                        WhatsApp Hotline: +6281110014171<br>
                        Operational Hours: 08:00 – 17:00</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Policy Modal -->
        <div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="privacyPolicyModalLabel">Privacy Policy</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 20px; color: #212529;">
                        <div class="mb-4">
                            <h4 class="mb-3" style="display: block; font-size: 1.5rem; font-weight: bold; color: #212529;">Privacy Policy - Zora</h4>
                        </div>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">1. Introduction</h6>
                        <p>This Privacy Policy explains how PT Mega Energi Biru Indonesia ("Zora", "we", "us") collects, uses, stores, and protects customer personal data when using electric vehicle (EV) charging services through the Zora platform.</p>
                        <p>By using Zora services, customers are deemed to have understood and agreed to the terms in this Privacy Policy.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">2. Personal Data We Collect</h6>
                        <p>Zora collects data necessary to process transactions and provide charging services. Data collected includes:</p>

                        <p><strong>A. User Identity Data</strong></p>
                        <ul>
                            <li>Name</li>
                            <li>Email</li>
                            <li>Phone number</li>
                        </ul>

                        <p><strong>B. Transaction Data</strong></p>
                        <ul>
                            <li>Transaction number / Order ID</li>
                            <li>Payment information from Midtrans</li>
                            <li>Payment status</li>
                            <li>Transaction time</li>
                        </ul>

                        <p><strong>C. Location & Charger Data</strong></p>
                        <ul>
                            <li>Charger location used</li>
                            <li>Charger ID and connector</li>
                            <li>Charger status during transaction</li>
                        </ul>

                        <p><strong>D. Charging Data (Charging Logs - from OCPP system)</strong></p>
                        <ul>
                            <li>StartTransaction and StopTransaction</li>
                            <li>Meter values (kWh)</li>
                            <li>Charging duration</li>
                            <li>Error code (if technical issues occur)</li>
                        </ul>

                        <p><strong>Note:</strong> We do not collect Vehicle ID, vehicle license plate numbers, or other vehicle identification.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">3. How We Collect Data</h6>
                        <p>Zora collects data from several system sources:</p>
                        <ul>
                            <li><strong>Midtrans payment platform</strong> - for transaction verification and payment status</li>
                            <li><strong>OCPP Server system</strong> - to record all charger activities (session logs, meter values, error status)</li>
                            <li><strong>Laravel Backend & Zora Database</strong> - stores transaction data, user input, and charging history</li>
                        </ul>
                        <p>No data is collected from customer devices other than what is necessary for transactions.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">4. Purpose of Data Use</h6>
                        <p>Zora uses personal data to:</p>
                        <ul>
                            <li>Verify and process payments</li>
                            <li>Activate and manage charging sessions</li>
                            <li>Provide transaction receipts and energy bills</li>
                            <li>Handle complaints, errors, and refund processes</li>
                            <li>Maintain system and charger security</li>
                            <li>Operational analysis (non-personalized analytics) to improve services</li>
                        </ul>
                        <p>Zora does not use customer data for marketing without permission.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">5. Data Storage and Security</h6>
                        <p>All data is stored in the Zora system database, managed by Laravel backend and connected to the OCPP server.</p>
                        <p>To maintain data security:</p>
                        <ul>
                            <li>Database access is restricted only to authorized internal personnel</li>
                            <li>All communication between charger ↔ OCPP ↔ backend uses secure protocols</li>
                            <li>Transaction and payment data follows Midtrans security standards</li>
                            <li>Internal audit logs are used to prevent data misuse</li>
                        </ul>
                        <p>We do not sell or share customer personal data with third parties without permission, except as required by law.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">6. Data Sharing with Third Parties</h6>
                        <p>Zora only shares data with:</p>
                        <ul>
                            <li><strong>Midtrans</strong> - for payment processing</li>
                            <li><strong>Vendors or technical partners</strong> that support charger operations (if necessary)</li>
                        </ul>
                        <p>These third parties are required to comply with security and data confidentiality standards in accordance with regulations.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">7. User Rights</h6>
                        <p>Customers have the right to:</p>
                        <ul>
                            <li>Request information regarding stored data</li>
                            <li>Request data correction if there are errors</li>
                            <li>Request deletion of certain data if it is no longer relevant</li>
                            <li>Object to the use of certain data</li>
                        </ul>
                        <p>Requests can be made through Zora customer service contact.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">8. Data Retention</h6>
                        <p>Data is stored for:</p>
                        <ul>
                            <li>Minimum 2 years for audit and payment regulation purposes</li>
                            <li>Or as long as necessary to resolve transactions, complaints, or investigations</li>
                        </ul>
                        <p>After that period, data may be deleted or anonymized.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">9. Policy Changes</h6>
                        <p>This policy may be updated at any time to comply with regulations or service improvements. Any significant changes will be announced through the Zora platform.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">10. Privacy Contact</h6>
                        <p>For questions or requests regarding personal data, customers can contact:</p>
                        <p><strong>PT Mega Energi Biru Indonesia (Zora)</strong><br>
                        Email: office@mebi.co.id<br>
                        WhatsApp Hotline: +6281110014171<br>
                        Operational Hours: 08:00 – 17:00</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refund Policy Modal -->
        <div class="modal fade" id="refundPolicyModal" tabindex="-1" aria-labelledby="refundPolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="refundPolicyModalLabel">Refund Policy</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 20px; color: #212529;">
                        <div class="mb-4">
                            <h4 class="mb-3" style="display: block; font-size: 1.5rem; font-weight: bold; color: #212529;">Refund Policy - Zora</h4>
                        </div>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">1. Refund Scope</h6>
                        <p>This refund policy applies to all payment transactions for electric vehicle (EV) charging services conducted through the Zora platform and processed by PT Mega Energi Biru Indonesia using the Midtrans payment system.</p>
                        <p>Refunds are provided when charging services cannot be performed or do not operate according to operational standards.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">2. Refund Request Conditions</h6>
                        <p>Customers are entitled to request a refund if one of the following conditions occurs:</p>
                        <ul>
                            <li>Charging fails to start, even though payment was successful</li>
                            <li>Charging stops suddenly due to charger technical error or power disruption</li>
                            <li>Energy is not delivered according to the amount paid, based on verification of StartTransaction, StopTransaction, and OCPP meter values</li>
                            <li>Technical issues with the backend system or payment gateway that prevent transaction processing</li>
                        </ul>
                        <p>Refunds do not apply to:</p>
                        <ul>
                            <li>Customer error in selecting charger location</li>
                            <li>Customer error in using illegal connectors or adapters</li>
                            <li>Excessive idle time after charging is complete (not a service failure)</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">3. Types of Refund</h6>
                        <p>Zora provides two types of refunds:</p>

                        <p><strong>1) Full Refund</strong></p>
                        <p>Granted if:</p>
                        <ul>
                            <li>Charging cannot start at all, or</li>
                            <li>No energy was delivered (meter value did not change)</li>
                        </ul>

                        <p><strong>2) Pro-Rate Refund</strong></p>
                        <p>Granted if partial energy has been consumed.</p>
                        <p>Pro-rate calculation is based on:</p>
                        <ul>
                            <li>Actual kWh consumed according to OCPP meter data</li>
                            <li>Applicable tariff (Rupiah/kWh) at the time of transaction</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">4. Refund Process & Method</h6>

                        <p><strong>1) Internal Verification</strong></p>
                        <p>Zora team performs checks on:</p>
                        <ul>
                            <li>Midtrans payment history</li>
                            <li>OCPP logs (StartTransaction, StopTransaction, meter values, error codes)</li>
                            <li>Charger status during transaction</li>
                        </ul>

                        <p><strong>2) Refund Method</strong></p>
                        <p>Refunds are processed through:</p>
                        <ul>
                            <li><strong>Midtrans</strong> (primary method)</li>
                            <li><strong>Manual transfer</strong> if Midtrans process requires more than 7 days, according to internal SLA</li>
                        </ul>

                        <p><strong>3) Refund Duration</strong></p>
                        <p>Processing time: 3-7 business days</p>
                        <p>Duration may vary according to bank or payment provider policies</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">5. Refund Request Requirements</h6>
                        <p>Customers need to provide:</p>
                        <ul>
                            <li>Name and phone number</li>
                            <li>Transaction number / Midtrans Order ID</li>
                            <li>Charger location</li>
                            <li>Transaction time and date</li>
                            <li>Error evidence (if available: charger screen photo or error message)</li>
                        </ul>
                        <p>Zora reserves the right to request additional logs if needed for verification.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">6. Refund Denial</h6>
                        <p>Refunds may be denied if:</p>
                        <ul>
                            <li>Transaction data is invalid or not found</li>
                            <li>Charging was successful and energy was delivered according to OCPP meter</li>
                            <li>Issues originate from use of non-compliant devices (e.g., illegal adapters)</li>
                            <li>Customer commits misuse, manipulation, or violation of service usage terms</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">7. Refund Request Contact</h6>
                        <p>Refund requests can be submitted to:</p>
                        <p><strong>PT Mega Energi Biru Indonesia (Zora)</strong><br>
                        Email: office@mebi.co.id<br>
                        WhatsApp Hotline: +6281110014171<br>
                        Operational Hours: 08:00 – 17:00</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Policy Modal -->
        <div class="modal fade" id="deliveryPolicyModal" tabindex="-1" aria-labelledby="deliveryPolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deliveryPolicyModalLabel">Delivery Policy</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 20px; color: #212529;">
                        <div class="mb-4">
                            <h4 class="mb-3" style="display: block; font-size: 1.5rem; font-weight: bold; color: #212529;">Delivery Policy - Zora</h4>
                        </div>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">1. Service Scope</h6>
                        <p>The Zora platform is used to make payments and access electric vehicle (EV) charging services on charger networks owned by or partnered with PT Mega Energi Biru Indonesia.</p>
                        <p>No physical goods are delivered. The term "delivery" refers to the availability of charging sessions after payment is verified.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">2. Service Delivery Process</h6>
                        <p>After payment is successful (based on valid notification from Midtrans):</p>
                        <ul>
                            <li>Zora backend system will create or activate a charging session on the selected charger</li>
                        </ul>
                        <p>Customers can start charging through:</p>
                        <ul>
                            <li>QR Scan on charger device</li>
                            <li>Instructions on charger screen</li>
                        </ul>
                        <p>Charging status (start, in progress, completed) will be displayed in the system and on the charger screen.</p>
                        <p>Transaction data and charging logs will be recorded in the Zora database.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">3. Delivery Time</h6>
                        <ul>
                            <li>Service is available in real-time, generally within seconds after payment is confirmed by Midtrans</li>
                            <li>Customers are responsible for immediately proceeding to the correct charger location</li>
                            <li>Customer waiting time is not included in the service delivery process</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">4. Conditions Affecting Delivery</h6>
                        <p>Service availability may be affected by:</p>
                        <ul>
                            <li>Charger is offline or experiencing issues</li>
                            <li>Charger is being used by another customer</li>
                            <li>Power disruption at the location</li>
                            <li>Internet communication disruption (WiFi/4G) affecting OCPP connection</li>
                            <li>Charger unit maintenance</li>
                        </ul>
                        <p>If any of these conditions prevent the charging session from starting, customers can contact Zora customer service.</p>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">5. Delivery Failure & Handling</h6>
                        <p>Delivery failure includes:</p>
                        <ul>
                            <li>Payment successful but charging cannot start</li>
                            <li>Charging stops suddenly due to charger technical error or network issues</li>
                        </ul>
                        <p><strong>Handling by Zora team:</strong></p>
                        <ul>
                            <li>Verification of Midtrans transaction</li>
                            <li>Verification of OCPP logs (StartTransaction/StopTransaction, meter values, error codes)</li>
                        </ul>
                        <p>If delivery failure is proven, Zora can:</p>
                        <ul>
                            <li>Provide recharging compensation</li>
                            <li>Full or pro-rate refund (according to energy consumption conditions)</li>
                            <li>Refund process through Midtrans or manual if it exceeds the time limit</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">6. Service Delivery Evidence</h6>
                        <p>Evidence that service has been available or consumed by customers:</p>
                        <ul>
                            <li>StartTransaction and StopTransaction logs in the OCPP system</li>
                            <li>Energy consumption data (meter values)</li>
                            <li>Success status on charger screen</li>
                            <li>Transaction history in Zora backend system</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">7. Customer Responsibility</h6>
                        <ul>
                            <li>Arrive at the charger location as selected</li>
                            <li>Use compatible vehicles and connectors</li>
                            <li>Do not damage chargers or use illegal adapters</li>
                            <li>Move the vehicle after the charging session is complete (do not idle excessively)</li>
                        </ul>

                        <h6 class="mt-3 mb-2" style="display: block; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; color: #212529;">8. Customer Service Contact</h6>
                        <p>For assistance regarding transactions, service delivery, or complaints:</p>
                        <p><strong>PT Mega Energi Biru Indonesia (Zora)</strong><br>
                        Email: office@mebi.co.id<br>
                        WhatsApp Hotline: +6281110014171<br>
                        Operational Hours: 08:00 – 17:00</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer-->
        <footer class="bg-light py-3">
            <div class="container px-4 px-lg-5">
                <div class="small text-center text-muted">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#userAgreementModal">User Agreement</a>
                    &#8226; <a href="#" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">Privacy Policy</a>
                    &#8226; <a href="#" data-bs-toggle="modal" data-bs-target="#refundPolicyModal">Refund Policy</a>
                    &#8226; <a href="#" data-bs-toggle="modal" data-bs-target="#deliveryPolicyModal">Delivery Policy</a>
                </div>
            </div>
            <div class="container px-4 py-2 px-lg-5"><div class="small text-center text-muted">Copyright &copy; {{ date('Y') }} - Zora</div></div>
        </footer>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- SimpleLightbox plugin JS-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('templates/sb/js/scripts.js') }}"></script>
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
        <!-- Midtrans Snap.js (Sandbox) -->
        <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        {{-- <script type="text/javascript" src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script> --}}

        <script>
            $(document).ready(function(){
                var IS_TEST_MINUTE = "{{ env('IS_TEST_MINUTE') }}";
                var allForm = $(".mainForm");
                var form1 = $("#mainForm1");
                var form2 = $("#mainForm2");
                var form3 = $("#mainForm3");
                var form4 = $("#mainForm4");
                var stepperItems = $(".stepper-wrapper .stepper-item");

                var TARIFF_PRICE_PER_MINUTE = {{ (int) ($products->tariff_price ?? 0) }};
                var TAX_RATE = {{ (float) ($products->tax_rate ?? 0) }};
                var MINUTES_PER_HOUR = 60;

                function formatRupiah(value) {
                    return "Rp " + Number(value).toLocaleString("id-ID");
                }

                function isValidEmail(value) {
                    // Simple, pragmatic email validation
                    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
                    return emailPattern.test(String(value).trim());
                }

                function isValidPhone(value) {
                    // Allow digits, spaces, dashes, parentheses, optional leading +, length 7-15 digits
                    var normalized = String(value).replace(/[^0-9]/g, "");
                    return normalized.length >= 7 && normalized.length <= 15;
                }

                function updateStepper(step) {
                    stepperItems.removeClass("active");
                    stepperItems.each(function(index){
                        if (index < step) {
                            $(this).addClass("active");
                        }
                    });
                }

                function showStep(step) {
                    allForm.addClass("d-none");
                    $("#mainForm" + step).removeClass("d-none");
                    updateStepper(step);
                    // Re-evaluate next button state on step change
                    evaluateStepState(step);
                    if (step === 4) {
                        updatePaymentSummary();
                    }
                }

                function evaluateStepState(step) {
                    if (step === 2) {
                        var emailVal = $("#email").val();
                        var phoneVal = $("#phone").val();
                        var canProceed = isValidEmail(emailVal) && isValidPhone(phoneVal);
                        $("#mainForm2 .btn-next").prop("disabled", !canProceed);
                    } else if (step === 3) {
                        var hasSelection = $("#duration").val() !== "";
                        $("#mainForm3 .btn-next").prop("disabled", !hasSelection);
                    } else {
                        $("#mainForm4 .btn-next").prop("disabled", false);
                    }
                }

                function markValidity($input, isValid) {
                    $input.toggleClass("is-invalid", !isValid);
                    $input.toggleClass("is-valid", isValid);
                }

                function showError(selector, show) {
                    var $el = $(selector);
                    if (show) {
                        $el.removeClass("d-none");
                    } else {
                        $el.addClass("d-none");
                    }
                }

                // Initialize: start at step 1
                allForm.addClass("d-none");
                form1.removeClass("d-none");
                updateStepper(1);

                // Initially disable step 2 and 3 Next buttons until valid
                $("#mainForm2 .btn-next").prop("disabled", true);
                $("#mainForm3 .btn-next").prop("disabled", true);

                // Live validation for email and phone on step 2
                $(document).on("input blur", "#email", function(){
                    var value = $(this).val();
                    var isEmpty = value.trim() === "";
                    var valid = isValidEmail(value);
                    if (isEmpty) {
                        $(this).removeClass("is-valid is-invalid");
                        showError("#emailError", false);
                    } else {
                        markValidity($(this), valid);
                        showError("#emailError", !valid);
                    }
                    evaluateStepState(2);
                });
                $(document).on("input blur", "#phone", function(){
                    var value = $(this).val();
                    var isEmpty = value.trim() === "";
                    var valid = isValidPhone(value);
                    if (isEmpty) {
                        $(this).removeClass("is-valid is-invalid");
                        showError("#phoneError", false);
                    } else {
                        markValidity($(this), valid);
                        showError("#phoneError", !valid);
                    }
                    evaluateStepState(2);
                });

                // Duration selection updates step 3 next button
                $(document).on("change", "#duration", function(){
                    var hasSelection = $(this).val() !== "";
                    showError("#durationError", !hasSelection);
                    evaluateStepState(3);
                });

                function getDurationHours() {
                    var durationVal = $("#duration").val();
                    if (IS_TEST_MINUTE) {
                        return durationVal ? parseFloat(durationVal, 10) : null;
                    } else {
                        return durationVal ? parseInt(durationVal, 10) : null;
                    }
                }

                function calculatePrice(hours) {
                    if (!hours || !TARIFF_PRICE_PER_MINUTE) {
                        return {
                            subtotal: 0,
                            tax: 0,
                            total: 0
                        };
                    }

                    var totalMinutes = hours * MINUTES_PER_HOUR;
                    var subtotal = totalMinutes * TARIFF_PRICE_PER_MINUTE;
                    var tax = TAX_RATE > 0 ? Math.round(subtotal * (TAX_RATE / 100)) : 0;

                    return {
                        subtotal: subtotal,
                        tax: tax,
                        total: subtotal + tax
                    };
                }

                function updatePaymentSummary() {
                    var durationHours = getDurationHours();
                    var price = calculatePrice(durationHours);

                    $("#summaryDuration").text(durationHours ? (durationHours + " Hours") : "-");
                    $("#summarySubtotal").text(price.subtotal ? formatRupiah(price.subtotal) : "-");
                    $("#summaryTax").text(price.tax ? formatRupiah(price.tax) : (TAX_RATE > 0 ? "Rp 0" : "-"));
                    $("#summaryPrice").text(price.total ? formatRupiah(price.total) : "-");
                }

                function computePaymentAmount() {
                    var durationHours = getDurationHours();
                    var price = calculatePrice(durationHours);
                    return price.total;
                }

                // Pay button handler (Midtrans Snap)
                $(document).on("click", "#mainForm4 .btn-next", function(e){
                    e.preventDefault();
                    var $btn = $(this);
                    function getQueryParam(name) {
                        var params = new URLSearchParams(window.location.search);
                        return params.get(name);
                    }
                    var sessionToken = getQueryParam('token');
                    var amount = computePaymentAmount();
                    if (!amount) { return; }

                    $btn.prop("disabled", true).text("Processing...");

                    $.ajax({
                        url: "{{ route('zora.checkout') }}",
                        method: "POST",
                        data: {
                            _token: $("meta[name='csrf-token']").attr("content"),
                            quantity: 1,
                            duration: $("#duration").val(),
                            name: $("#name").val(),
                            email: $("#email").val(),
                            phone_number: $("#phone").val(),
                            station_id: {{ $station->id }},
                            connector_id: {{ $connector->id }},
                            tariff_code: "{{ $products->tariff_code }}",
                        }
                    }).done(function(response){
                        if (response && response.snap_token && window.snap) {
                            var orderId = response.transaction && response.transaction.midtrans_order_id
                                ? response.transaction.midtrans_order_id
                                : null;
                            var pollIntervalId = null;
                            function startPollingIfPossible() {
                                if (!orderId) return;
                                var statusUrl = "{{ route('zora.checkout.status', ['orderId' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', encodeURIComponent(orderId));
                                pollIntervalId = setInterval(function(){
                                    $.getJSON(statusUrl)
                                        .done(function(res){
                                            if (res && res.payment_status === 1) {
                                                clearInterval(pollIntervalId);
                                                var redirectUrl = "{{ route('zora.checkout.after') }}";
                                                if (sessionToken) {
                                                    redirectUrl += ("?token=" + encodeURIComponent(sessionToken));
                                                }
                                                window.location.href = redirectUrl;
                                            }
                                        })
                                        .fail(function(){ /* ignore until next tick */ });
                                }, 3000);
                            }
                            window.snap.pay(response.snap_token, {
                                onSuccess: function(result){
                                    // Start polling for server-side settlement notification
                                    startPollingIfPossible();
                                },
                                onPending: function(result){
                                    alert("Waiting payment...");
                                    startPollingIfPossible();
                                    $btn.prop("disabled", false).text("Pay");
                                },
                                onError: function(result){
                                    alert("Error on payment!");
                                    $btn.prop("disabled", false).text("Pay");
                                },
                                onClose: function(){
                                    alert("Prompt closed without completing payment");
                                    $btn.prop("disabled", false).text("Pay");
                                }
                            });
                        } else {
                            alert("Unable to start payment.");
                            $btn.prop("disabled", false).text("Pay");
                        }
                    }).fail(function(xhr){
                        alert("Failed to create transaction.");
                        $btn.prop("disabled", false).text("Pay");
                    });
                });

                // Next button handler with validation gates
                $(document).on("click", ".btn-next", function(){
                    var targetStep = parseInt($(this).data("step"), 10);
                    var currentForm = $(this).closest(".mainForm");
                    var currentStep = currentForm.attr("id").replace("mainForm", "");
                    currentStep = parseInt(currentStep, 10);

                    if (currentStep === 2) {
                        var emailVal = $("#email").val();
                        var phoneVal = $("#phone").val();
                        var emailOk = isValidEmail(emailVal);
                        var phoneOk = isValidPhone(phoneVal);
                        markValidity($("#email"), emailOk);
                        markValidity($("#phone"), phoneOk);
                        showError("#emailError", !emailOk);
                        showError("#phoneError", !phoneOk);
                        if (!(emailOk && phoneOk)) {
                            evaluateStepState(2);
                            return; // Block navigation
                        }
                    }

                    if (currentStep === 3) {
                        var hasSelection = $("#duration").val() !== "";
                        showError("#durationError", !hasSelection);
                        if (!hasSelection) {
                            evaluateStepState(3);
                            return; // Block navigation
                        }
                    }

                    // Passed validation; navigate
                    showStep(targetStep);
                });

                // Prev button handler (no validation)
                $(document).on("click", ".btn-prev", function(){
                    var targetStep = parseInt($(this).data("step"), 10);
                    showStep(targetStep);
                });

                // On load, compute initial state
                evaluateStepState(2);

                // Prevent default link behavior for policy links
                $('a[data-bs-toggle="modal"]').on('click', function(e) {
                    e.preventDefault();
                });
            });
        </script>
    </body>
</html>
