import './bootstrap';
import AOS from 'aos';
import 'aos/dist/aos.css';
import './form-loading-states'; // Smart form loading states

// Alpine.js - Import and initialize for Livewire 3 compatibility
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';
import mask from '@alpinejs/mask';
import persist from '@alpinejs/persist';

// Register Alpine plugins
Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(intersect);
Alpine.plugin(mask);
Alpine.plugin(persist);

// Expose Alpine globally for Livewire
window.Alpine = Alpine;

/**
 * Alpine.js & Livewire 3 Initialization Logic
 * 
 * In Livewire 3, Alpine is usually started by Livewire.
 * If we call Alpine.start() and Livewire also calls it, we get:
 * "Uncaught TypeError: directiveStorageMap[stage] is not a function"
 */
function startAlpine() {
    if (window.Alpine && !window.Alpine.initialized) {
        if (!window.Livewire) {
            console.log('[Alpine] Manual Start (Livewire not detected)');
            window.Alpine.start();
        } else {
            console.log('[Alpine] Skip Manual Start (Livewire will handle it)');
        }
        window.Alpine.initialized = true;
    }
}

// Initial start attempt
startAlpine();

// Secondary attempt on window load to ensure Livewire detection is accurate
window.addEventListener('load', startAlpine);

// Use livewire:navigated to support wire:navigate (SPA mode)
document.addEventListener('livewire:navigated', () => {
    AOS.init({
        duration: 900,
        easing: 'ease-out-cubic',
        offset: 80,
        once: false,
        disable: false,
        mirror: true,
        anchorPlacement: 'top-bottom'
    });

    initMobileMenu();
    initSmoothScroll();
    initHeaderScroll();
    initScrollParallax();
    initButtonInteractions();
    initFaqAccordion();
    initAutoDismissAlerts();
});

// For compatibility with non-livewire pages
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Livewire) {
        AOS.init({
            duration: 900,
            easing: 'ease-out-cubic',
            offset: 80,
            once: false,
            disable: false,
            mirror: true,
            anchorPlacement: 'top-bottom'
        });

        initMobileMenu();
        initSmoothScroll();
        initHeaderScroll();
        initScrollParallax();
        initButtonInteractions();
        initFaqAccordion();
        initAutoDismissAlerts();
    }
});

function initAutoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert');

    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                // Add fade out class
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';

                // Remove from DOM after animation
                setTimeout(() => {
                    alert.remove();
                }, 500);
            });
        }, 5000); // 5 seconds
    }
}

function initMobileMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.querySelector('.nav-menu');

    if (!menuToggle) return;

    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        animateHamburger(menuToggle);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.header-container')) {
            navMenu.classList.remove('active');
        }
    });

    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
        });
    });
}

function animateHamburger(toggle) {
    const spans = toggle.querySelectorAll('span');
    if (toggle.querySelector('.nav-menu').classList.contains('active')) {
        spans[0].style.transform = 'rotate(45deg) translateY(15px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translateY(-15px)';
    } else {
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
    }
}

function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        const href = link.getAttribute('href');

        if (link.hasAttribute('data-external') || href === '#' || href === '#home') {
            return;
        }

        link.addEventListener('click', (e) => {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

function initHeaderScroll() {
    const header = document.querySelector('.landing-header');
    if (!header) return;

    let ticking = false;
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        lastScroll = window.pageYOffset;

        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (lastScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
}

function initScrollParallax() {
    const elements = document.querySelectorAll('[data-parallax]');
    let ticking = false;

    const handleScroll = () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                elements.forEach(el => {
                    const speed = parseFloat(el.dataset.parallax) || 0.5;
                    const rect = el.getBoundingClientRect();

                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        const elementCenter = rect.top + rect.height / 2;
                        const windowCenter = window.innerHeight / 2;
                        const scrollOffset = (elementCenter - windowCenter) * speed * 0.04;

                        el.style.transform = `translate3d(0, ${scrollOffset}px, 0)`;
                    }
                });
                ticking = false;
            });
            ticking = true;
        }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
}



function initButtonInteractions() {
    const buttons = document.querySelectorAll('.btn');

    buttons.forEach(button => {
        button.addEventListener('mouseenter', (e) => {
            const x = e.clientX - button.getBoundingClientRect().left;
            const y = e.clientY - button.getBoundingClientRect().top;

            const ripple = document.createElement('div');
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.width = '10px';
            ripple.style.height = '10px';
            ripple.style.background = 'rgba(255, 255, 255, 0.5)';
            ripple.style.borderRadius = '50%';
            ripple.style.position = 'absolute';
            ripple.style.pointerEvents = 'none';
            ripple.style.transform = 'scale(1)';
            ripple.style.animation = 'ripple 0.6s ease-out';

            button.style.position = 'relative';
            button.style.overflow = 'hidden';
            button.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });
}

function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const header = item.querySelector('.faq-header');

        header.addEventListener('click', () => {
            const isActive = item.classList.contains('active');

            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
            });

            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}



const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

