<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>404 - Page Not Found | Zora EVCE</title>
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
                margin-bottom: 2rem;
                position: relative;
                z-index: 1;
            }

            .error-button {
                display: inline-block;
                padding: 12px 32px;
                background: linear-gradient(135deg, #00B23C 0%, #00D94F 100%);
                color: #fff;
                border-radius: 12px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 12px rgba(0, 178, 60, 0.3);
                position: relative;
                z-index: 1;
            }

            .error-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 178, 60, 0.4);
                color: #fff;
                text-decoration: none;
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
                        <img src="{{ asset('images/logo-white.png') }}" alt="Zora EVCE" style="display:block; margin: 0 auto;width:40%">
                        <hr class="divider" />
                        <br>
                    </div>
                </div>

                <div class="error-container">
                    <div class="error-content">
                        <i class="bi bi-exclamation-triangle error-icon"></i>
                        <h1>404</h1>
                        <p>The page you are looking for could not be found.</p>
                        <a href="{{ url('/') }}" class="error-button">Go to Home</a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Footer-->
        <footer class="bg-light py-3">
            <div class="container px-4 px-lg-5">
                <div class="small text-center text-muted">Copyright &copy; {{ date('Y') }} - Zora</div>
            </div>
        </footer>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- SimpleLightbox plugin JS-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('templates/sb/js/scripts.js') }}"></script>
    </body>
</html>
