// Soft Minimalism Login Form JavaScript
class SoftMinimalismLoginForm {
    constructor() {
        this.form = document.getElementById('loginForm');
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.passwordToggle = document.getElementById('passwordToggle');
        this.submitButton = this.form.querySelector('.comfort-button');
        this.successMessage = document.getElementById('successMessage');
        this.socialButtons = document.querySelectorAll('.social-soft');

        this.init();
    }

    init() {
        this.bindEvents();
        this.setupPasswordToggle();
        this.setupSocialButtons();
        this.setupGentleEffects();
    }

    bindEvents() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.emailInput.addEventListener('blur', () => {
            this.validateEmail();
            this.updateLabelState(this.emailInput);
        });
        this.passwordInput.addEventListener('blur', () => {
            this.validatePassword();
            this.updateLabelState(this.passwordInput);
        });
        this.emailInput.addEventListener('focus', () => {
            this.updateLabelState(this.emailInput);
        });
        this.passwordInput.addEventListener('focus', () => {
            this.updateLabelState(this.passwordInput);
        });
        this.emailInput.addEventListener('input', () => {
            this.clearError('email');
            this.updateLabelState(this.emailInput);
        });
        this.passwordInput.addEventListener('input', () => {
            this.clearError('password');
            this.updateLabelState(this.passwordInput);
        });

        // Add placeholder for label animations (empty space)
        this.emailInput.setAttribute('placeholder', ' ');
        this.passwordInput.setAttribute('placeholder', ' ');

        // Check initial state
        this.updateLabelState(this.emailInput);
        this.updateLabelState(this.passwordInput);
    }

    updateLabelState(input) {
        const fieldContainer = input.closest('.soft-field');
        const label = fieldContainer.querySelector('label');

        if (!label) return;

        const hasValue = input.value && input.value.trim() !== '';
        const isFocused = document.activeElement === input;

        // Always raise label when focused or has value
        if (isFocused || hasValue) {
            input.setAttribute('placeholder', ' ');
            label.style.top = '12px';
            label.style.fontSize = '12px';
            label.style.color = '#012b46';
            label.style.transform = 'translateY(0)';
            label.style.fontWeight = '500';
        } else {
            // Reset label position when empty and not focused
            input.setAttribute('placeholder', ' ');
            label.style.top = '50%';
            label.style.fontSize = '15px';
            label.style.color = 'rgba(1, 44, 70, 0.387)';
            label.style.transform = 'translateY(-50%)';
            label.style.fontWeight = '400';
        }
    }

    setupPasswordToggle() {
        this.passwordToggle.addEventListener('click', () => {
            const type = this.passwordInput.type === 'password' ? 'text' : 'password';
            this.passwordInput.type = type;

            this.passwordToggle.classList.toggle('toggle-active', type === 'text');

            // Add gentle transition effect
            this.triggerGentleRipple(this.passwordToggle);
        });
    }

    setupSocialButtons() {
        this.socialButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const provider = button.querySelector('span').textContent.trim();
                this.handleSocialLogin(provider, button);
            });
        });
    }

    setupGentleEffects() {
        // Add soft hover effects on inputs
        [this.emailInput, this.passwordInput].forEach(input => {
            input.addEventListener('focus', (e) => {
                this.triggerSoftFocus(e.target.closest('.field-container'));
            });

            input.addEventListener('blur', (e) => {
                this.releaseSoftFocus(e.target.closest('.field-container'));
            });
        });

        // Add gentle click effects to buttons
        this.addGentleClickEffects();
    }

    triggerSoftFocus(container) {
        // Add subtle glow animation
        container.style.transition = 'all 0.3s ease';
        container.style.transform = 'translateY(-1px)';
    }

    releaseSoftFocus(container) {
        // Remove focus effects
        container.style.transform = 'translateY(0)';
    }

    triggerGentleRipple(element) {
        // Create gentle ripple effect
        element.style.transform = 'scale(0.95)';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 150);
    }

    addGentleClickEffects() {
        // Add gentle click animations to all interactive elements
        const interactiveElements = document.querySelectorAll('.comfort-button, .social-soft, .gentle-checkbox');

        interactiveElements.forEach(element => {
            element.addEventListener('mousedown', () => {
                element.style.transform = 'scale(0.98)';
            });

            element.addEventListener('mouseup', () => {
                element.style.transform = 'scale(1)';
            });

            element.addEventListener('mouseleave', () => {
                element.style.transform = 'scale(1)';
            });
        });
    }

    validateEmail() {
        const email = this.emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
            this.showError('email', 'Please enter your email address');
            return false;
        }

        if (!emailRegex.test(email)) {
            this.showError('email', 'Please enter a valid email address');
            return false;
        }

        this.clearError('email');
        return true;
    }

    validatePassword() {
        const password = this.passwordInput.value;

        if (!password) {
            this.showError('password', 'Please enter your password');
            return false;
        }

        if (password.length < 6) {
            this.showError('password', 'Password must be at least 6 characters');
            return false;
        }

        this.clearError('password');
        return true;
    }

    showError(field, message) {
        const softField = document.getElementById(field).closest('.soft-field');
        const errorElement = document.getElementById(`${field}Error`);

        softField.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.add('show');

        // Add gentle shake effect
        this.triggerGentleShake(softField);
    }

    clearError(field) {
        const softField = document.getElementById(field).closest('.soft-field');
        const errorElement = document.getElementById(`${field}Error`);

        softField.classList.remove('error');
        errorElement.classList.remove('show');
        setTimeout(() => {
            errorElement.textContent = '';
        }, 300);
    }

    triggerGentleShake(element) {
        // Subtle shake animation for errors
        element.style.animation = 'none';
        element.style.transform = 'translateX(2px)';

        setTimeout(() => {
            element.style.transform = 'translateX(-2px)';
        }, 100);

        setTimeout(() => {
            element.style.transform = 'translateX(0)';
        }, 200);
    }

    async handleSubmit(e) {
        e.preventDefault();

        // Set loading immediately when button is clicked
        this.setLoading(true);

        const isEmailValid = this.validateEmail();
        const isPasswordValid = this.validatePassword();

        if (!isEmailValid || !isPasswordValid) {
            this.setLoading(false);
            return;
        }

        try {
            // Get form data
            const formData = new FormData(this.form);
            const email = formData.get('email');
            const password = formData.get('password');
            const remember = formData.get('remember') === 'on' ? true : false;

            // Submit to backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const loginUrl = window.routes?.login || '/login';

            const response = await fetch(loginUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    email: email,
                    password: password,
                    remember: remember
                })
            });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                // If response is not JSON, try to get text
                const text = await response.text();
                throw new Error('Something went wrong on the server.');
            }

            if (response.ok) {
                // Keep loading state during success animation
                // Show soft success
                this.showGentleSuccess();

                // Redirect to dashboard after success animation
                setTimeout(() => {
                    window.location.href = data.redirect || window.routes?.dashboard || '/dashboard';
                }, 2000);
                // Don't set loading to false here, let it redirect
                return;
            } else {
                // Handle errors
                this.setLoading(false); // Stop loading on error
                const errors = data.errors || {};
                if (errors.email) {
                    this.showError('email', Array.isArray(errors.email) ? errors.email[0] : errors.email);
                }
                if (errors.password) {
                    this.showError('password', Array.isArray(errors.password) ? errors.password[0] : errors.password);
                }
                if (!errors.email && !errors.password && data.message) {
                    this.showError('password', data.message);
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showError('password', 'Something went wrong. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }

    async handleSocialLogin(provider, button) {
        console.log(`Signing in with ${provider}...`);

        // Gentle loading state
        const originalHTML = button.innerHTML;
        button.style.pointerEvents = 'none';
        button.style.opacity = '0.7';

        const loadingHTML = `
            <div class="social-background"></div>
            <div class="gentle-spinner">
                <div class="spinner-circle"></div>
            </div>
            <span>Connecting...</span>
            <div class="social-glow"></div>
        `;

        button.innerHTML = loadingHTML;

        try {
            await new Promise(resolve => setTimeout(resolve, 2000));
            console.log(`Redirecting to ${provider}...`);
            // window.location.href = `/auth/${provider.toLowerCase()}`;
        } catch (error) {
            console.error(`${provider} sign in error:`, error.message);
        } finally {
            button.style.pointerEvents = 'auto';
            button.style.opacity = '1';
            button.innerHTML = originalHTML;
        }
    }

    setLoading(loading) {
        this.submitButton.classList.toggle('loading', loading);
        this.submitButton.disabled = loading;

        // Disable social buttons during loading
        this.socialButtons.forEach(button => {
            button.style.pointerEvents = loading ? 'none' : 'auto';
            button.style.opacity = loading ? '0.5' : '1';
        });
    }

    showGentleSuccess() {
        // Change title and subtitle text
        const titleElement = document.querySelector('.comfort-title');
        const subtitleElement = document.querySelector('.gentle-subtitle');

        if (titleElement) {
            titleElement.textContent = 'Login Success';
        }
        if (subtitleElement) {
            subtitleElement.textContent = 'redirecting';
        }

        // Hide form with soft transition
        this.form.style.transform = 'scale(0.95)';
        this.form.style.opacity = '0';
        this.form.style.filter = 'blur(1px)';

        setTimeout(() => {
            this.form.style.display = 'none';
            const socialElement = document.querySelector('.comfort-social');
            const signupElement = document.querySelector('.comfort-signup');
            const dividerElement = document.querySelector('.gentle-divider');

            if (socialElement) socialElement.style.display = 'none';
            if (signupElement) signupElement.style.display = 'none';
            if (dividerElement) dividerElement.style.display = 'none';

            // Show gentle success
            this.successMessage.classList.add('show');

            // Add success glow to card
            this.triggerSuccessGlow();

        }, 300);

        // Redirect after success
        setTimeout(() => {
            console.log('Welcome! Taking you to your dashboard...');
            // window.location.href = '/dashboard';
        }, 3500);
    }

    triggerSuccessGlow() {
        // Add gentle glow effect to the entire card
        const card = document.querySelector('.soft-card');
        card.style.boxShadow = `
            0 20px 40px rgba(240, 206, 170, 0.2),
            0 8px 24px rgba(240, 206, 170, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.8)
        `;

        setTimeout(() => {
            card.style.boxShadow = `
                0 20px 40px rgba(0, 0, 0, 0.03),
                0 8px 24px rgba(0, 0, 0, 0.02),
                inset 0 1px 0 rgba(255, 255, 255, 0.8)
            `;
        }, 2000);
    }
}

// Initialize the soft form when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SoftMinimalismLoginForm();
});
