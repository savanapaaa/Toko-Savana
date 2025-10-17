/* Advanced JavaScript Effects untuk Toko Savana */

// Initialize all effects when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initParticleSystem();
    initMagneticButtons();
    initSmoothScrolling();
    initTypingEffect();
    initIntersectionObserver();
    initCountUpAnimation();
    initThemeToggle();
    initAdvancedAnimations();
});

// Particle System
function initParticleSystem() {
    const container = document.createElement('div');
    container.className = 'particles-container';
    document.body.appendChild(container);
    
    function createParticle() {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        const size = Math.random() * 5 + 2;
        const left = Math.random() * 100;
        const duration = Math.random() * 3 + 2;
        
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = left + '%';
        particle.style.animationDuration = duration + 's';
        
        container.appendChild(particle);
        
        setTimeout(() => {
            particle.remove();
        }, duration * 1000);
    }
    
    // Create particles every 300ms
    setInterval(createParticle, 300);
}

// Magnetic Button Effect
function initMagneticButtons() {
    const magneticButtons = document.querySelectorAll('.magnetic-btn, .btn');
    
    magneticButtons.forEach(button => {
        button.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            const moveX = x * 0.1;
            const moveY = y * 0.1;
            
            this.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translate(0, 0)';
        });
    });
}

// Smooth Scrolling
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Typing Effect
function initTypingEffect() {
    const typingElements = document.querySelectorAll('.typing-text');
    
    typingElements.forEach(element => {
        const text = element.textContent;
        element.textContent = '';
        element.style.borderRight = '2px solid #667eea';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            } else {
                element.style.borderRight = 'none';
            }
        };
        
        typeWriter();
    });
}

// Intersection Observer for Animations
function initIntersectionObserver() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                
                // Add specific animations based on class
                if (entry.target.classList.contains('slide-in-trigger')) {
                    entry.target.classList.add('slide-in-up');
                }
                if (entry.target.classList.contains('bounce-in-trigger')) {
                    entry.target.classList.add('bounce-in');
                }
                if (entry.target.classList.contains('fade-in-trigger')) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            }
        });
    }, observerOptions);
    
    // Observe elements
    document.querySelectorAll('.card, .stat-card, .stat-card-2, .stat-card-3, .stat-card-4').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(50px)';
        el.style.transition = 'all 0.6s ease';
        el.classList.add('fade-in-trigger');
        observer.observe(el);
    });
}

// Count Up Animation
function initCountUpAnimation() {
    function countUp(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            // Format numbers
            if (element.dataset.format === 'currency') {
                element.textContent = formatCurrency(Math.floor(current));
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }
    
    // Apply to stat numbers
    const statNumbers = document.querySelectorAll('.stat-number, .h3, .h4');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.animated) {
                const target = parseInt(entry.target.textContent.replace(/[^\d]/g, ''));
                if (!isNaN(target) && target > 0) {
                    entry.target.dataset.animated = 'true';
                    countUp(entry.target, target);
                }
            }
        });
    });
    
    statNumbers.forEach(el => observer.observe(el));
}

// Theme Toggle (Dark/Light Mode)
function initThemeToggle() {
    const themeToggle = document.createElement('button');
    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    themeToggle.className = 'btn btn-sm position-fixed';
    themeToggle.style.cssText = `
        bottom: 20px; 
        right: 20px; 
        z-index: 1000; 
        border-radius: 50%; 
        width: 50px; 
        height: 50px;
        background: #7A7A99;
        border: none;
        color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(themeToggle);
    
    themeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-theme');
        const icon = this.querySelector('i');
        icon.className = document.body.classList.contains('dark-theme') 
            ? 'fas fa-sun' 
            : 'fas fa-moon';
    });
}

// Advanced Animations
function initAdvancedAnimations() {
    // Parallax effect
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax-element');
        
        parallaxElements.forEach(element => {
            const speed = element.dataset.speed || 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
    
    // Ripple effect on click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('ripple') || e.target.closest('.ripple')) {
            const button = e.target.classList.contains('ripple') ? e.target : e.target.closest('.ripple');
            const rect = button.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.5);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            `;
            
            button.style.position = 'relative';
            button.style.overflow = 'hidden';
            button.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        }
    });
    
    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .dark-theme {
            filter: invert(1) hue-rotate(180deg);
        }
        
        .dark-theme img,
        .dark-theme video {
            filter: invert(1) hue-rotate(180deg);
        }
    `;
    document.head.appendChild(style);
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Loading Animation
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    loading.innerHTML = `
        <div style="
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        "></div>
    `;
    
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// Advanced Form Validation
function initAdvancedFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearError(this);
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Mohon periksa kembali form yang Anda isi', 'error');
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    let isValid = true;
    let message = '';
    
    // Clear previous errors
    clearError(field);
    
    // Required validation
    if (required && !value) {
        isValid = false;
        message = 'Field ini wajib diisi';
    }
    
    // Type-specific validation
    if (value && type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Format email tidak valid';
        }
    }
    
    if (value && type === 'number') {
        if (isNaN(value)) {
            isValid = false;
            message = 'Harus berupa angka';
        }
    }
    
    // Show error if not valid
    if (!isValid) {
        showFieldError(field, message);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.appendChild(errorDiv);
    }
    
    errorDiv.textContent = message;
}

function clearError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Notification System
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification-toast`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slide-in-right 0.5s ease;
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentNode.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slide-out-right 0.5s ease';
            setTimeout(() => notification.remove(), 500);
        }
    }, duration);
}

// Initialize advanced form validation
document.addEventListener('DOMContentLoaded', initAdvancedFormValidation);

// Service Worker for PWA (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}