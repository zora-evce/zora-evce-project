<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zora CPO - Reset Password</title>
    <link rel="stylesheet" href="{{ asset('templates/login/style.css') }}">
    <link rel="stylesheet" href="{{ asset('templates/adminlte/dist/css/adminlte.min.css') }}">
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
                <h1 class="comfort-title">Reset Password</h1>
                <p class="gentle-subtitle">Enter your new password below</p>
            </div>

            <form class="comfort-form" id="resetPasswordForm" action="{{ route('cpo.reset-password.post') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                @if(session('success'))
                    <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; border-radius: 5px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 15px; border-radius: 5px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 15px; border-radius: 5px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                        <ul class="mb-0" style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="soft-field">
                    <div class="field-container">
                        <input type="email" id="email" name="email_display" value="{{ $email }}" disabled>
                        <label for="email">Email address</label>
                        <div class="field-accent"></div>
                    </div>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">This is the email address associated with your account.</small>
                </div>

                <div class="soft-field">
                    <div class="field-container">
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                        <label for="password">New Password</label>
                        <div class="field-accent"></div>
                    </div>
                    <span class="gentle-error" id="passwordError"></span>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Password must be at least 6 characters.</small>
                </div>

                <div class="soft-field">
                    <div class="field-container">
                        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        <label for="password_confirmation">Confirm New Password</label>
                        <div class="field-accent"></div>
                    </div>
                    <span class="gentle-error" id="passwordConfirmationError"></span>
                </div>

                <button type="submit" class="comfort-button">
                    <div class="button-background"></div>
                    <span class="button-text">Reset Password</span>
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
        </div>
    </div>

    <script>
        // Form validation and submission
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('resetPasswordForm');
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const passwordError = document.getElementById('passwordError');
            const passwordConfirmationError = document.getElementById('passwordConfirmationError');
            const submitButton = form.querySelector('.comfort-button');

            // Add placeholders for label animations
            passwordInput.setAttribute('placeholder', ' ');
            passwordConfirmationInput.setAttribute('placeholder', ' ');

            // Update label state function
            function updateLabelState(input) {
                const hasValue = input.value && input.value.trim() !== '';
                const isFocused = document.activeElement === input;
                const label = input.closest('.soft-field').querySelector('label');

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

            [passwordInput, passwordConfirmationInput].forEach(input => {
                input.addEventListener('focus', () => updateLabelState(input));
                input.addEventListener('blur', () => updateLabelState(input));
                input.addEventListener('input', () => {
                    clearError(input);
                    updateLabelState(input);
                });
            });

            function showError(input, message) {
                const softField = input.closest('.soft-field');
                const errorElement = input.id === 'password' ? passwordError : passwordConfirmationError;
                softField.classList.add('error');
                errorElement.textContent = message;
                errorElement.classList.add('show');
            }

            function clearError(input) {
                const softField = input.closest('.soft-field');
                const errorElement = input.id === 'password' ? passwordError : passwordConfirmationError;
                softField.classList.remove('error');
                errorElement.classList.remove('show');
                setTimeout(() => {
                    errorElement.textContent = '';
                }, 300);
            }

            function setLoading(loading) {
                submitButton.classList.toggle('loading', loading);
                submitButton.disabled = loading;
            }

            function validatePassword() {
                const password = passwordInput.value;

                if (!password) {
                    showError(passwordInput, 'Please enter a new password');
                    return false;
                }

                if (password.length < 6) {
                    showError(passwordInput, 'Password must be at least 6 characters');
                    return false;
                }

                clearError(passwordInput);
                return true;
            }

            function validatePasswordConfirmation() {
                const password = passwordInput.value;
                const passwordConfirmation = passwordConfirmationInput.value;

                if (!passwordConfirmation) {
                    showError(passwordConfirmationInput, 'Please confirm your password');
                    return false;
                }

                if (password !== passwordConfirmation) {
                    showError(passwordConfirmationInput, 'Passwords do not match');
                    return false;
                }

                clearError(passwordConfirmationInput);
                return true;
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const isPasswordValid = validatePassword();
                const isPasswordConfirmationValid = validatePasswordConfirmation();

                if (!isPasswordValid || !isPasswordConfirmationValid) {
                    return;
                }

                setLoading(true);

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const formData = new FormData(form);

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Redirect to login on success
                        window.location.href = data.redirect || '{{ route("cpo.login") }}';
                    } else {
                        setLoading(false);
                        const errors = data.errors || {};
                        if (errors.password) {
                            showError(passwordInput, Array.isArray(errors.password) ? errors.password[0] : errors.password);
                        }
                        if (errors.password_confirmation) {
                            showError(passwordConfirmationInput, Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : errors.password_confirmation);
                        }
                        if (!errors.password && !errors.password_confirmation && data.message) {
                            showError(passwordInput, data.message);
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    setLoading(false);
                    // Fallback to regular form submission if AJAX fails
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>

