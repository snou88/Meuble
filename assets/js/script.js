// ============================================
// HEADER SCROLL EFFECT
// ============================================

let header, scrollTopBtn, navLogo;

const initializeHeaderElements = () => {
    header = document.getElementById('header');
    scrollTopBtn = document.getElementById('scrollTop');
    navLogo = document.querySelector('.nav-logo img');
};

const updateLogo = () => {
    if (!header) return;
    
    const isScrolled = window.scrollY > 50;
    
    if (isScrolled) {
        header.classList.add('scrolled');
        if (scrollTopBtn) scrollTopBtn.classList.add('show');
        if (navLogo) navLogo.src = 'assets/images/LOGO.png';
    } else {
        header.classList.remove('scrolled');
        if (scrollTopBtn) scrollTopBtn.classList.remove('show');
        if (navLogo) navLogo.src = 'assets/images/LOGO-blanc.png';
    }
    
    // Forcer la mise à jour du hamburger menu
    const navToggleSpans = document.querySelectorAll('.nav-toggle span');
    navToggleSpans.forEach(span => {
        if (isScrolled) {
            span.style.background = 'var(--color-gray)';
        } else {
            span.style.background = 'var(--color-white)';
        }
    });
    
    // Forcer la mise à jour des liens de navigation
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        if (isScrolled) {
            link.style.color = 'var(--color-gray)';
        } else {
            link.style.color = 'var(--color-white)';
        }
    });
    
    // Forcer la mise à jour du panier
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        if (isScrolled) {
            cartIcon.style.color = 'var(--color-gray)';
            const cartSvg = cartIcon.querySelector('svg');
            if (cartSvg) cartSvg.style.fill = 'var(--color-gray)';
        } else {
            cartIcon.style.color = 'var(--color-wood-dark)';
            const cartSvg = cartIcon.querySelector('svg');
            if (cartSvg) cartSvg.style.fill = 'var(--color-wood-dark)';
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeHeaderElements();
    initializeMobileMenu();
    initializeScrollTop();
    updateLogo();
});

window.addEventListener('scroll', updateLogo);

// ============================================
// MOBILE MENU TOGGLE
// ============================================

let navToggle, navMenu, navLinks;

const initializeMobileMenu = () => {
    navToggle = document.getElementById('navToggle');
    navMenu = document.getElementById('navMenu');
    navLinks = document.querySelectorAll('.nav-link');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
        });
    }
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navToggle) navToggle.classList.remove('active');
            if (navMenu) navMenu.classList.remove('active');
            document.body.style.overflow = '';
            
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });
};

// ============================================
// SMOOTH SCROLL TO TOP
// ============================================

const initializeScrollTop = () => {
    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
};

// ============================================
// SCROLL ANIMATIONS (AOS)
// ============================================

const observerOptions = {
    root: null,
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const animateOnScroll = (entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const delay = entry.target.getAttribute('data-aos-delay') || 0;

            setTimeout(() => {
                entry.target.classList.add('aos-animate');
            }, delay);

            observer.unobserve(entry.target);
        }
    });
};

const observer = new IntersectionObserver(animateOnScroll, observerOptions);

const elementsToAnimate = document.querySelectorAll('[data-aos]');
elementsToAnimate.forEach(el => observer.observe(el));

// ============================================
// PRODUCT CARDS HOVER EFFECT
// ============================================

const productCards = document.querySelectorAll('.product-card');

productCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.zIndex = '10';
    });

    card.addEventListener('mouseleave', function() {
        this.style.zIndex = '1';
    });
});

// ============================================
// CATEGORY CARDS INTERACTION
// ============================================

const categoryCards = document.querySelectorAll('.category-card');

categoryCards.forEach(card => {
    card.addEventListener('click', function() {
        const categoryName = this.querySelector('h3').textContent;
        console.log(`Navigating to category: ${categoryName}`);
    });
});


// ============================================
// PARALLAX EFFECT ON HERO
// ============================================

window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');

    if (hero) {
        const parallaxSpeed = 0.5;
        hero.style.backgroundPositionY = -(scrolled * parallaxSpeed) + 'px';
    }
});

// ============================================
// BUTTON CLICK EFFECTS
// ============================================

const allButtons = document.querySelectorAll('.btn');

allButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        ripple.style.cssText = `
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: translate(-50%, -50%) scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
            left: ${x}px;
            top: ${y}px;
        `;

        this.style.position = 'relative';
        this.style.overflow = 'hidden';
        this.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
});

const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: translate(-50%, -50%) scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ============================================
// IMAGE LAZY LOADING OPTIMIZATION
// ============================================

if ('loading' in HTMLImageElement.prototype) {
    const images = document.querySelectorAll('img[loading="lazy"]');
    images.forEach(img => {
        img.src = img.dataset.src || img.src;
    });
} else {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
    document.body.appendChild(script);
}

// ============================================
// ACTIVE SECTION HIGHLIGHTING
// ============================================

const sections = document.querySelectorAll('section[id]');

const highlightNavOnScroll = () => {
    const scrollY = window.pageYOffset;

    sections.forEach(section => {
        const sectionHeight = section.offsetHeight;
        const sectionTop = section.offsetTop - 150;
        const sectionId = section.getAttribute('id');
        const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);

        if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
            navLinks.forEach(link => link.classList.remove('active'));
            if (navLink) {
                navLink.classList.add('active');
            }
        }
    });
};

window.addEventListener('scroll', highlightNavOnScroll);

// ============================================
// SMOOTH SCROLL FOR ALL ANCHOR LINKS
// ============================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        if (href !== '#' && href.length > 1) {
            e.preventDefault();

            const target = document.querySelector(href);
            if (target) {
                const headerHeight = header.offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });
});

// ============================================
// PRODUCT BUTTON INTERACTIONS
// ============================================

const productButtons = document.querySelectorAll('.product-card .btn-secondary');

productButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();

        const productCard = this.closest('.product-card');
        const productName = productCard.querySelector('.product-name').textContent;
        const productPrice = productCard.querySelector('.product-price').textContent;

        console.log(`Product clicked: ${productName} - ${productPrice}`);
    });
});

// ============================================
// FEATURE CARDS STAGGER ANIMATION
// ============================================

const featureCards = document.querySelectorAll('.feature-card');

const staggerObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, index * 100);

            staggerObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

featureCards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    staggerObserver.observe(card);
});

// ============================================
// CURSOR EFFECT ON CATEGORY CARDS
// ============================================

categoryCards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;

        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
    });
});

// ============================================
// COUNTER ANIMATION FOR STATS (if added)
// ============================================

function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);

    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

// ============================================
// LOAD COMPLETE
// ============================================
