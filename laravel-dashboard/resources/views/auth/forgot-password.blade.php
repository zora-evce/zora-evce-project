<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zora CPO - Forgot Password</title>
    <link rel="stylesheet" href="{{ asset('templates/login/style.css') }}">
</head>
<body>
    <div class="soft-background">
        <div class="floating-shapes">
            <div class="soft-blob blob-1"></div>
            <div class="soft-blob blob-2"></div>
            <div class="soft-blob blob-3"></div>
            <div class="soft-blob blob-4"></div>
        </div>
    </div>

    <div class="login-container">
        <div class="soft-card">
            <div class="comfort-header">
                <div class="gentle-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="" style="display:block; margin: 0 auto;width:200%">
                </div>
                <h1 class="comfort-title">Forgot Password</h1>
                <p class="gentle-subtitle">Enter your email address and we'll send you a link to reset your password</p>
            </div>

            <form class="comfort-form" id="forgotPasswordForm" novalidate>
                <div class="soft-field">
                    <div class="field-container">
                        <input type="email" id="email" name="email" required autocomplete="email">
                        <label for="email">Email address</label>
                        <div class="field-accent"></div>
                    </div>
                    <span class="gentle-error" id="emailError"></span>
                </div>

                <button type="submit" class="comfort-button">
                    <div class="button-background"></div>
                    <span class="button-text">Send Reset Link</span>
                    <div class="button-loader">
                        <div class="gentle-spinner">
                            <div class="spinner-circle"></div>
                        </div>
                    </div>
                    <div class="button-glow"></div>
                </button>
            </form>

            <div class="comfort-options" style="justify-content: center; margin-top: 20px;">
                <a href="{{ route('cpo.login') }}" class="comfort-link">Back to Login</a>
            </div>

            <div class="gentle-success" id="successMessage">
                <div class="success-bloom">
                    <div class="bloom-rings">
                        <div class="bloom-ring ring-1"></div>
                        <div class="bloom-ring ring-2"></div>
                        <div class="bloom-ring ring-3"></div>
                    </div>
                    <div class="success-icon">
                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                            <path d="M8 14l5 5 11-11" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <h3 class="success-title">Email Sent!</h3>
                <p class="success-desc">Please check your email for password reset instructions</p>
            </div>
        </div>
    </div>

    <script>
        // Define routes for JavaScript
        window.routes = {
            forgotPassword: '{{ route("cpo.forgot-password.post") }}',
            login: '{{ route("cpo.login") }}'
        };

        // Forgot Password Form Handler
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('forgotPasswordForm');
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            const submitButton = form.querySelector('.comfort-button');
            const successMessage = document.getElementById('successMessage');

            // Add placeholder for label animation
            emailInput.setAttribute('placeholder', ' ');

            // Update label state
            function updateLabelState() {
                const hasValue = emailInput.value && emailInput.value.trim() !== '';
                const isFocused = document.activeElement === emailInput;
                const label = emailInput.closest('.soft-field').querySelector('label');

                if (isFocused || hasValue) {
                    label.style.top = '12px';
                    label.style.fontSize = '12px';
                    label.style.color = '#012b46';
                    label.style.transform = 'translateY(0)';
                    label.style.fontWeight = '500';
                } else {
                    label.style.top = '50%';
                    label.style.fontSize = '15px';
                    label.style.color = 'rgba(1, 44, 70, 0.387)';
                    label.style.transform = 'translateY(-50%)';
                    label.style.fontWeight = '400';
                }
            }

            emailInput.addEventListener('focus', updateLabelState);
            emailInput.addEventListener('blur', updateLabelState);
            emailInput.addEventListener('input', () => {
                clearError();
                updateLabelState();
            });

            function showError(message) {
                const softField = emailInput.closest('.soft-field');
                softField.classList.add('error');
                emailError.textContent = message;
                emailError.classList.add('show');
            }

            function clearError() {
                const softField = emailInput.closest('.soft-field');
                softField.classList.remove('error');
                emailError.classList.remove('show');
                setTimeout(() => {
                    emailError.textContent = '';
                }, 300);
            }

            function setLoading(loading) {
                submitButton.classList.toggle('loading', loading);
                submitButton.disabled = loading;
            }

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!email) {
                    showError('Please enter your email address');
                    return false;
                }

                if (!emailRegex.test(email)) {
                    showError('Please enter a valid email address');
                    return false;
                }

                clearError();
                return true;
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!validateEmail()) {
                    return;
                }

                setLoading(true);

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch(window.routes.forgotPassword, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            email: emailInput.value.trim()
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Show success message
                        form.style.display = 'none';
                        successMessage.classList.add('show');
                    } else {
                        setLoading(false);
                        const errors = data.errors || {};
                        if (errors.email) {
                            showError(Array.isArray(errors.email) ? errors.email[0] : errors.email);
                        } else if (data.message) {
                            showError(data.message);
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    setLoading(false);
                    showError('Something went wrong. Please try again.');
                }
            });
        });
    </script>
</body>
</html>

