// Enhanced custom.js với form validation và animations
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for navigation links
    document.querySelectorAll('a.nav-link[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update active nav link
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });

    // Navbar background on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        // Back to top button
        const backToTop = document.querySelector('.back-to-top');
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });

    // Enhanced animations for cards with stagger effect
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, observerOptions);

    // Observe all cards for animation
    document.querySelectorAll('.feature-card, .course-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });

    // Enhanced Form Validation with real-time feedback
    const contactForm = document.querySelector('#contact form');
    if (contactForm) {
        const inputs = contactForm.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            // Real-time validation
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
        
        // Form submission
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const requiredFields = contactForm.querySelectorAll('[required]');
            
            // Validate all required fields
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            if (isValid) {
                submitForm(contactForm);
            } else {
                showNotification('Vui lòng kiểm tra lại thông tin đã nhập!', 'error');
            }
        });
    }
});

function validateField(field) {
    const value = field.value.trim();
    const feedback = field.parentNode.querySelector('.validation-feedback') || createFeedbackElement(field);
    
    // Remove previous validation classes
    field.classList.remove('is-valid', 'is-invalid');
    feedback.classList.remove('show', 'valid-feedback', 'invalid-feedback');
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        field.classList.add('is-invalid');
        feedback.classList.add('invalid-feedback', 'show');
        feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Trường này là bắt buộc';
        return false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            field.classList.add('is-invalid');
            feedback.classList.add('invalid-feedback', 'show');
            feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Email không hợp lệ';
            return false;
        }
    }
    
    // Phone validation
    if (field.type === 'tel' && value) {
        const phoneRegex = /^(0|\+84)(\d{9,10})$/;
        if (!phoneRegex.test(value)) {
            field.classList.add('is-invalid');
            feedback.classList.add('invalid-feedback', 'show');
            feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Số điện thoại không hợp lệ';
            return false;
        }
    }
    
    // If all validations pass
    if (field.hasAttribute('required') || value) {
        field.classList.add('is-valid');
        feedback.classList.add('valid-feedback', 'show');
        feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>Hợp lệ';
    }
    
    return true;
}

function createFeedbackElement(field) {
    const feedback = document.createElement('div');
    feedback.className = 'validation-feedback';
    field.parentNode.appendChild(feedback);
    return feedback;
}

function submitForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.classList.add('loading');
    submitBtn.innerHTML = 'Đang gửi...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Cảm ơn bạn! Chúng tôi sẽ liên hệ trong vòng 24h.', 'success');
        form.reset();
        
        // Remove validation classes
        form.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });
        form.querySelectorAll('.validation-feedback').forEach(el => {
            el.classList.remove('show');
        });
        
        // Restore button
        submitBtn.classList.remove('loading');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 2000);
}

// Enhanced Notification System
function showNotification(message, type = 'success') {
    const container = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                'fa-exclamation-triangle'
            } me-3 text-${type}"></i>
            <div class="flex-grow-1">
                <strong>${type === 'success' ? 'Thành công!' : type === 'error' ? 'Lỗi!' : 'Cảnh báo!'}</strong>
                <div class="small">${message}</div>
            </div>
            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 500);
        }
    }, 5000);
}