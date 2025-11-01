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
            /* Payment Success Modal */
            #paymentSuccessModal {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.8); z-index: 9999; display: none;
                justify-content: center; align-items: center;
            }
            #paymentSuccessContent {
                background: #fff; padding: 40px; border-radius: 12px; text-align: center;
                max-width: 400px; margin: 0 auto;
            }
            #paymentSuccessContent h2 { color: #28a745; margin-bottom: 20px; }
            #paymentSuccessContent p { margin-bottom: 20px; }
            #countdownText { font-size: 18px; font-weight: bold; color: #007bff; }
        </style>
    </head>
    <body id="page-top">
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
                        <div class="step-counter">1</div>
                        <div class="step-name">Welcome</div>
                    </div>
                    <div class="stepper-item">
                        <div class="step-counter">2</div>
                        <div class="step-name">Personal Details</div>
                    </div>
                    <div class="stepper-item">
                        <div class="step-counter">3</div>
                        <div class="step-name">Duration</div>
                    </div>
                    <div class="stepper-item">
                        <div class="step-counter">4</div>
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
                                    <div class="circle mx-auto text-center">{{ $connector->connector_number }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" data-step="2" class="btn btn-primary btn-md btn-next">Next</button>
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
                            <button type="button" data-step="1" class="btn btn-primary btn-md btn-prev">Prev</button>
                            <button type="button" data-step="3" class="btn btn-primary btn-md btn-next">Next</button>
                        </div>
                    </div>
                </div>

                <div class="row gx-4 gx-lg-5 justify-content-center mb-5 mainForm" id="mainForm3">
                    <div class="col-lg-6">
                        <div class="subsection px-4 py-4">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <select id="duration" name="duration" class="form-select">
                                    @foreach(($products ?? []) as $product)
                                    @php
                                        $minutes = (int) (data_get($product, 'tariff_value', 0));
                                    @endphp
                                        <option value="{{ $minutes }}">{{ $minutes }} Minutes</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-danger small d-none" id="durationError">Please select a duration.</div>
                        </div>
                        <br>
                        <div class="d-flex justify-content-between">
                            <button type="button" data-step="2" class="btn btn-primary btn-md btn-prev">Prev</button>
                            <button type="button" data-step="4" class="btn btn-primary btn-md btn-next">Next</button>
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
                                    <span>Price</span>
                                    <span id="summaryPrice">-</span>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="d-flex justify-content-between">
                            <button type="button" data-step="3" class="btn btn-primary btn-md btn-prev">Prev</button>
                            <button type="button" data-step="4" class="btn btn-primary btn-md btn-next">Pay</button>
                        </div>
                    </div>
                </div>

                <!-- Payment Success Modal -->
                <div id="paymentSuccessModal">
                    <div id="paymentSuccessContent">
                        <h2>✅ Payment Success!</h2>
                        <p>Your payment has been processed successfully.</p>
                        <p id="countdownText">Redirecting to test page in 30 seconds...</p>
                    </div>
                </div>

            </div>
        </section>
        <!-- Footer-->
        <footer class="bg-light py-3">
            <div class="container px-4 px-lg-5"><div class="small text-center text-muted">Copyright &copy; {{ date('Y') }} - Zora</div></div>
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

        <script>
            $(document).ready(function(){
                var allForm = $(".mainForm");
                var form1 = $("#mainForm1");
                var form2 = $("#mainForm2");
                var form3 = $("#mainForm3");
                var form4 = $("#mainForm4");
                var stepperItems = $(".stepper-wrapper .stepper-item");

                var PRODUCTS = @json(($products ?? collect())->map(function($p){
                    return [
                        'value' => (int) ($p->tariff_value ?? 0),
                        'price' => (int) ($p->tariff_price ?? 0),
                    ];
                }));
                var durationToPrice = {};
                (PRODUCTS || []).forEach(function(p){
                    if (p && p.value) {
                        durationToPrice[p.value] = p.price || 0;
                    }
                });
                // Prices from backend are final (tax-included)

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

                function updatePaymentSummary() {
                    var durationVal = $("#duration").val();
                    var durationMinutes = durationVal ? parseInt(durationVal, 10) : null;
                    var price = durationMinutes ? (durationToPrice[durationMinutes] || 0) : 0;

                    $("#summaryDuration").text(durationMinutes ? (durationMinutes + " Minutes") : "-");
                    $("#summaryPrice").text(price ? formatRupiah(price) : "-");
                }

                function computePaymentAmount() {
                    var durationVal = $("#duration").val();
                    var durationMinutes = durationVal ? parseInt(durationVal, 10) : null;
                    var price = durationMinutes ? (durationToPrice[durationMinutes] || 0) : 0;
                    return price;
                }

                // Pay button handler (Midtrans Snap)
                $(document).on("click", "#mainForm4 .btn-next", function(e){
                    e.preventDefault();
                    var $btn = $(this);
                    var amount = computePaymentAmount();
                    if (!amount) { return; }

                    $btn.prop("disabled", true).text("Processing...");

                    $.ajax({
                        url: "{{ route('checkout') }}",
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
                            tariff_code: (function(){
                                var val = $("#duration").val();
                                var code = null;
                                @foreach(($products ?? []) as $product)
                                    if ({{ (int)($product->tariff_value) }} == val) code = '{{ $product->tariff_code }}';
                                @endforeach
                                return code;
                            })()
                        }
                    }).done(function(response){
                        if (response && response.snap_token && window.snap) {
                            window.snap.pay(response.snap_token, {
                                onSuccess: function(result){
                                    // Show payment success modal
                                    $("#paymentSuccessModal").css("display", "flex");

                                    // Trigger OCPP RemoteStartTransaction call
                                    $.ajax({
                                        url: "https://zora.apenable.com/api/ocpp/commands",
                                        method: "POST",
                                        headers: { "X-OCPP-Key": "ZORA_SUPER_SECRET" },
                                        contentType: "application/json",
                                        data: JSON.stringify({
                                            station_code: "Zora1",
                                            connector: 1,
                                            command: "RemoteStartTransaction",
                                            payload: { idTag: "TEST123" }
                                        })
                                    }).always(function(){
                                        // Start 30s countdown then redirect to /teststop
                                        var remaining = 30;
                                        var timer = setInterval(function(){
                                            // $("#countdownText").text("Redirecting to test page in " + remaining + " seconds...");
                                            remaining -= 1;
                                            if (remaining <= 0) {
                                                clearInterval(timer);
                                                window.location.href = "/teststop";
                                            }
                                        }, 1000);
                                    });
                                },
                                onPending: function(result){
                                    alert("Menunggu pembayaran...");
                                    $btn.prop("disabled", false).text("Pay");
                                },
                                onError: function(result){
                                    alert("Terjadi error pembayaran!");
                                    $btn.prop("disabled", false).text("Pay");
                                },
                                onClose: function(){
                                    alert("Popup ditutup tanpa menyelesaikan pembayaran");
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
            });
        </script>
    </body>
</html>
