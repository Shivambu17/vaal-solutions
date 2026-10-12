document.addEventListener('DOMContentLoaded', () => {

    /* ========== MOBILE MENU ========== */
    const menuBtn = document.querySelector('.menu-btn');
    const navLinks = document.querySelector('.nav-links');
    if (menuBtn && navLinks) {
        menuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
        // Close menu when a link is clicked
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => navLinks.classList.remove('open'));
        });
    }

    /* ========== HERO TYPING EFFECT ========== */
    const typingEl = document.querySelector('.typing');
    if (typingEl) {
        const words = [
            'Software Solutions',
            'Smart Networks',
            'Cloud Systems',
            'Secure Infrastructure'
        ];
        let wordIndex = 0;
        let charIndex = 0;
        let deleting = false;

        function type() {
            const currentWord = words[wordIndex];
            if (!deleting) {
                typingEl.textContent = currentWord.substring(0, charIndex + 1);
                charIndex++;
                if (charIndex === currentWord.length) {
                    deleting = true;
                    setTimeout(type, 1500);
                    return;
                }
            } else {
                typingEl.textContent = currentWord.substring(0, charIndex - 1);
                charIndex--;
                if (charIndex === 0) {
                    deleting = false;
                    wordIndex = (wordIndex + 1) % words.length;
                }
            }
            setTimeout(type, deleting ? 60 : 120);
        }
        type();
    }

    /* ========== ANIMATED COUNTERS ========== */
    const counters = document.querySelectorAll('.counter');
    if (counters.length) {
        const animateCounter = (el) => {
            const target = +el.dataset.target || 0;
            let count = 0;
            const step = Math.max(1, Math.ceil(target / 60));
            const update = () => {
                count += step;
                if (count >= target) {
                    el.textContent = target;
                } else {
                    el.textContent = count;
                    requestAnimationFrame(update);
                }
            };
            update();
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        counters.forEach(counter => observer.observe(counter));
    }

    /* ========== TESTIMONIAL SLIDER ========== */
    const testimonials = document.querySelectorAll('.testimonial');
    if (testimonials.length > 1) {
        let current = 0;
        setInterval(() => {
            testimonials[current].classList.remove('active');
            current = (current + 1) % testimonials.length;
            testimonials[current].classList.add('active');
        }, 5000);
    }

    /* ========== SMOOTH SCROLL ========== */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const id = this.getAttribute('href');
            if (id.length > 1) {
                const target = document.querySelector(id);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

});