import './bootstrap';
import AOS from 'aos';
import 'aos/dist/aos.css';
import './form-loading-states'; // Smart form loading states

let headerScrollBound = false;
let parallaxScrollBound = false;
let mobileMenuDocBound = false;
let buttonInteractionBound = false;
let parallaxElements = [];

const AOS_OPTIONS = {
    duration: 900,
    easing: 'ease-out-cubic',
    offset: 80,
    once: false,
    disable: false,
    mirror: true,
    anchorPlacement: 'top-bottom'
};

function initPageEnhancements() {
    initMobileMenu();
    initSmoothScroll();
    initHeaderScroll();
    initScrollParallax();
    initButtonInteractions();
    initFaqAccordion();
    initAutoDismissAlerts();
}

// Use livewire:navigated to support wire:navigate (SPA mode)
document.addEventListener('livewire:navigated', () => {
    AOS.init(AOS_OPTIONS);
    initPageEnhancements();
});

// For compatibility with non-livewire pages
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Livewire) {
        AOS.init(AOS_OPTIONS);
    }
    initPageEnhancements();
});

function initAutoDismissAlerts() {
    const alerts = Array.from(document.querySelectorAll('.alert')).filter((alert) => alert.dataset.autoDismissBound !== '1');

    if (alerts.length === 0) return;

    alerts.forEach((alert) => {
        alert.dataset.autoDismissBound = '1';
    });

    setTimeout(() => {
        alerts.forEach((alert) => {
            if (!alert.isConnected) return;

            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                if (alert.isConnected) {
                    alert.remove();
                }
            }, 500);
        });
    }, 5000); // 5 seconds
}

function initMobileMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.querySelector('.nav-menu');

    if (!menuToggle || !navMenu) return;

    if (menuToggle.dataset.menuToggleBound !== '1') {
        menuToggle.dataset.menuToggleBound = '1';
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            animateHamburger(menuToggle, navMenu);
        });
    }

    if (!mobileMenuDocBound) {
        mobileMenuDocBound = true;
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.header-container')) {
                document.querySelector('.nav-menu')?.classList.remove('active');
            }
        });
    }

    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach((link) => {
        if (link.dataset.navLinkBound === '1') return;

        link.dataset.navLinkBound = '1';
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
        });
    });
}

function animateHamburger(toggle, navMenu) {
    const spans = toggle.querySelectorAll('span');
    if (spans.length < 3) return;

    if (navMenu.classList.contains('active')) {
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

    links.forEach((link) => {
        if (link.dataset.smoothScrollBound === '1') return;

        const href = link.getAttribute('href');

        if (link.hasAttribute('data-external') || href === '#' || href === '#home') {
            return;
        }

        link.dataset.smoothScrollBound = '1';
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
    if (headerScrollBound) return;

    headerScrollBound = true;
    let ticking = false;
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const header = document.querySelector('.landing-header');
        if (!header) return;

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
    parallaxElements = Array.from(document.querySelectorAll('[data-parallax]'));
    if (parallaxElements.length === 0) return;

    if (parallaxScrollBound) return;
    parallaxScrollBound = true;

    let ticking = false;

    const handleScroll = () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                parallaxElements.forEach((el) => {
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
    if (buttonInteractionBound) return;

    buttonInteractionBound = true;
    document.addEventListener('mouseover', (e) => {
        const button = e.target.closest('.btn');
        if (!button) return;

        if (e.relatedTarget && button.contains(e.relatedTarget)) {
            return;
        }

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

        setTimeout(() => {
            if (ripple.isConnected) {
                ripple.remove();
            }
        }, 600);
    });
}

function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach((item) => {
        const header = item.querySelector('.faq-header');
        if (!header || header.dataset.faqBound === '1') return;

        header.dataset.faqBound = '1';
        header.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            const allFaqItems = document.querySelectorAll('.faq-item');

            allFaqItems.forEach((otherItem) => {
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
if (!document.getElementById('ripple-effect-style')) {
    style.id = 'ripple-effect-style';
    document.head.appendChild(style);
}
