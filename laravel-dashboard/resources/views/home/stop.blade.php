<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>Force Stop Charging | Zora EVCE</title>
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
        <style>
            #contact .container { min-height: 80vh; }

            .error-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 70vh;
            }

            .error-content {
                background: linear-gradient(135deg, rgba(2, 60, 97, 0.95) 0%, rgba(2, 60, 97, 0.85) 100%);
                border-radius: 24px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.2),
                    0 4px 16px rgba(0, 0, 0, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                padding: 3rem 2.5rem;
                text-align: center;
                max-width: 600px;
                width: 100%;
                position: relative;
                overflow: hidden;
            }

            .error-content::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
                animation: errorShine 8s ease-in-out infinite;
                pointer-events: none;
            }

            .error-icon {
                font-size: 80px;
                color: #fff;
                margin-bottom: 1.5rem;
                position: relative;
                z-index: 1;
                opacity: 0.9;
            }

            .error-content h1 {
                font-size: 2.5rem;
                font-weight: 700;
                color: #fff;
                margin-bottom: 1rem;
                position: relative;
                z-index: 1;
                text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }

            .error-content p {
                font-size: 1.1rem;
                color: rgba(255, 255, 255, 0.9);
                margin-bottom: 1.5rem;
                position: relative;
                z-index: 1;
            }

            .error-content .form-group {
                margin-bottom: 1.5rem;
                text-align: left;
                position: relative;
                z-index: 1;
            }

            .error-content label {
                color: rgba(255, 255, 255, 0.95);
                font-weight: 600;
                margin-bottom: 0.5rem;
                display: block;
            }

            .error-content input {
                width: 100%;
                padding: 12px 16px;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                font-size: 1rem;
                transition: all 0.3s ease;
            }

            .error-content input::placeholder {
                color: rgba(255, 255, 255, 0.5);
            }

            .error-content input:focus {
                outline: none;
                border-color: rgba(255, 255, 255, 0.4);
                background: rgba(255, 255, 255, 0.15);
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            }

            .error-button {
                display: inline-block;
                padding: 12px 32px;
                background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
                color: #fff;
                border: none;
                border-radius: 12px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
                position: relative;
                z-index: 1;
                cursor: pointer;
                width: 100%;
                font-size: 1rem;
            }

            .error-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
                color: #fff;
            }

            .error-button:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }

            #forceStopMsg {
                margin-top: 1rem;
                padding: 12px 16px;
                border-radius: 12px;
                position: relative;
                z-index: 1;
            }

            #forceStopMsg.alert-success {
                background: rgba(0, 178, 60, 0.2);
                border: 1px solid rgba(0, 178, 60, 0.4);
                color: #fff;
            }

            #forceStopMsg.alert-danger {
                background: rgba(220, 53, 69, 0.2);
                border: 1px solid rgba(220, 53, 69, 0.4);
                color: #fff;
            }

            .form-control::placeholder {
                color: lightgrey;
            }

            /* Chrome, Edge, Safari */
            .form-control::-webkit-input-placeholder {
                color: lightgrey;
            }

            /* Firefox */
            .form-control::-moz-placeholder {
                color: lightgrey;
            opacity: 1;
            }

            /* Internet Explorer */
            .form-control:-ms-input-placeholder {
                color: lightgrey;
            }

            @keyframes errorShine {
                0%, 100% {
                    transform: translate(-50%, -50%) rotate(0deg);
                    opacity: 0.3;
                }
                50% {
                    transform: translate(-40%, -40%) rotate(180deg);
                    opacity: 0.5;
                }
            }

            @media (max-width: 768px) {
                .error-content {
                    border-radius: 20px;
                    padding: 2.5rem 2rem;
                }
                .error-icon {
                    font-size: 60px;
                }
                .error-content h1 {
                    font-size: 2rem;
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
                        <img src="{{ asset('images/logo-white.png') }}" alt="Zora EVCE" style="display:block; margin: 0 auto;width:40%">
                        <hr class="divider" />
                        <br>
                    </div>
                </div>

                <div class="error-container">
                    <div class="error-content">
                        <i class="bi bi-stop-circle-fill error-icon"></i>
                        <h1>Force Stop Charging</h1>
                        <p>Enter your Transaction ID to request a force stop.</p>
                        <div class="form-group">
                            <label for="transactionId">Transaction ID</label>
                            <input type="text" id="transactionId" style="color:#fff" class="form-control" placeholder="e.g. 12345">
                        </div>
                        <button id="forceStopBtn" class="error-button">Force Stop</button>
                        <div id="forceStopMsg" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer-->
        <footer class="bg-light py-1">
            <div class="container px-4 py-2 px-lg-5"><div class="small text-center text-muted">Copyright &copy; {{ date('Y') }} - Zora</div></div>
        </footer>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- SimpleLightbox plugin JS-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('templates/sb/js/scripts.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const btn = document.getElementById('forceStopBtn');
            const input = document.getElementById('transactionId');
            const msg = document.getElementById('forceStopMsg');

            function showMessage(text, ok) {
                msg.style.display = 'block';
                msg.className = ok ? 'alert alert-success' : 'alert alert-danger';
                msg.textContent = text;
            }

            btn.addEventListener('click', function(){
                const val = (input.value || '').trim();
                if (!val) {
                    showMessage('Please enter a valid Transaction ID.', false);
                    return;
                }
                Swal.fire({
                    title: 'Force Stop Charging?',
                    text: 'Are you sure you want to stop this charging session?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, stop it',
                    cancelButtonText: 'Cancel'
                }).then(function(result){
                    if (result.isConfirmed) {
                        btn.disabled = true;
                        fetch("{{ route('zora.stop.action') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ transactionId: val })
                        }).then(async function(resp){
                            const data = await resp.json().catch(() => ({}));
                            if (resp.ok && data && data.ok) {
                                showMessage('Request submitted. If valid, the session will be stopped shortly.', true);
                                Swal.fire('Submitted', 'Force stop request submitted.', 'success');
                            } else {
                                showMessage('Failed to submit request. Please try again.', false);
                                Swal.fire('Error', 'Failed to submit request.', 'error');
                            }
                            btn.disabled = false;
                        }).catch(function(){
                            showMessage('Network error. Please try again.', false);
                            Swal.fire('Error', 'Network error.', 'error');
                            btn.disabled = false;
                        });
                    }
                });
            });
        });
        </script>
    </body>
</html>
