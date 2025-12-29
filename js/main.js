// Main JavaScript for e-commerce features
document.addEventListener('DOMContentLoaded', function () {

    // Back to Top Button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.id = 'backToTop';
    backToTopBtn.innerHTML = '↑';
    backToTopBtn.title = 'Back to Top';
    document.body.appendChild(backToTopBtn);

    window.addEventListener('scroll', function () {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    backToTopBtn.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Loading overlay
    window.showLoading = function () {
        let overlay = document.getElementById('loadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    };

    window.hideLoading = function () {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    };

    // Newsletter subscription
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;

            // Simple validation
            if (email && email.includes('@')) {
                alert('Thank you for subscribing! You will receive updates at ' + email);
                this.reset();
            } else {
                alert('Please enter a valid email address.');
            }
        });
    }

    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.querySelector('.main-nav');

    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function () {
            mainNav.classList.toggle('mobile-open');
            this.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function (e) {
        if (mainNav && mainNav.classList.contains('mobile-open')) {
            if (!e.target.closest('.main-nav') && !e.target.closest('#mobileMenuBtn')) {
                mainNav.classList.remove('mobile-open');
                if (mobileMenuBtn) {
                    mobileMenuBtn.classList.remove('active');
                }
            }
        }
    });

    // Image zoom on hover (for product images)
    document.querySelectorAll('.product-image').forEach(img => {
        img.addEventListener('mouseenter', function () {
            this.style.transform = 'scale(1.1)';
        });

        img.addEventListener('mouseleave', function () {
            this.style.transform = 'scale(1)';
        });
    });
});
