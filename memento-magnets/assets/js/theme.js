/**
 * Memento Magnets — Theme JavaScript
 */

(function () {
    'use strict';

    // ============================================================
    // DOM READY
    // ============================================================
    document.addEventListener('DOMContentLoaded', function () {
        initStickyHeader();
        initMobileMenu();
        initSearchOverlay();
        initFAQAccordion();
        initScrollAnimations();
        initScrollToTop();
        initNewsletterForms();
    });

    // ============================================================
    // STICKY HEADER
    // ============================================================
    function initStickyHeader() {
        var header = document.querySelector('.site-header');
        if (!header) return;

        var lastScroll = 0;

        function onScroll() {
            var currentScroll = window.pageYOffset;

            if (currentScroll > 60) {
                header.classList.add('is-scrolled');
            } else {
                header.classList.remove('is-scrolled');
            }

            lastScroll = currentScroll;
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // ============================================================
    // MOBILE MENU
    // ============================================================
    function initMobileMenu() {
        var toggle = document.querySelector('.menu-toggle');
        var nav = document.querySelector('.primary-nav');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('is-open');
            toggle.classList.toggle('is-active', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('is-open');
                toggle.classList.remove('is-active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });

        // Close on nav link click
        var navLinks = nav.querySelectorAll('a');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                nav.classList.remove('is-open');
                toggle.classList.remove('is-active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                nav.classList.remove('is-open');
                toggle.classList.remove('is-active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    }

    // ============================================================
    // SEARCH OVERLAY
    // ============================================================
    function initSearchOverlay() {
        var openBtn = document.querySelector('.js-search-open');
        var closeBtn = document.querySelector('.js-search-close');
        var overlay = document.querySelector('.search-overlay');
        if (!overlay) return;

        function openSearch() {
            overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            var input = overlay.querySelector('input[type="search"]');
            if (input) {
                setTimeout(function () { input.focus(); }, 100);
            }
        }

        function closeSearch() {
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openSearch);
        if (closeBtn) closeBtn.addEventListener('click', closeSearch);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeSearch();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSearch();
        });
    }

    // ============================================================
    // FAQ ACCORDION
    // ============================================================
    function initFAQAccordion() {
        var faqItems = document.querySelectorAll('.faq-item');
        if (!faqItems.length) return;

        faqItems.forEach(function (item) {
            var question = item.querySelector('.faq-question');
            var answer = item.querySelector('.faq-answer');
            if (!question || !answer) return;

            question.setAttribute('aria-expanded', 'false');
            var answerId = 'faq-answer-' + Math.random().toString(36).substr(2, 9);
            answer.id = answerId;
            question.setAttribute('aria-controls', answerId);

            question.addEventListener('click', function () {
                var isOpen = item.classList.toggle('is-open');
                question.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                // Close others (optional — remove for multi-open)
                faqItems.forEach(function (other) {
                    if (other !== item && other.classList.contains('is-open')) {
                        other.classList.remove('is-open');
                        var otherQ = other.querySelector('.faq-question');
                        if (otherQ) otherQ.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    }

    // ============================================================
    // SCROLL ANIMATIONS (Intersection Observer)
    // ============================================================
    function initScrollAnimations() {
        if (!('IntersectionObserver' in window)) return;

        var elements = document.querySelectorAll('.fade-in-up');
        if (!elements.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        elements.forEach(function (el) {
            observer.observe(el);
        });
    }

    // ============================================================
    // SCROLL TO TOP
    // ============================================================
    function initScrollToTop() {
        var btn = document.querySelector('.scroll-to-top');
        if (!btn) return;

        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 400) {
                btn.classList.add('is-visible');
            } else {
                btn.classList.remove('is-visible');
            }
        }, { passive: true });

        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ============================================================
    // NEWSLETTER FORM SUBMIT (basic AJAX-free handling)
    // ============================================================
    function initNewsletterForms() {
        var forms = document.querySelectorAll('.newsletter-form, .footer-newsletter-mini');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var input = form.querySelector('input[type="email"]');
                var btn = form.querySelector('button[type="submit"]');
                if (!input || !input.value) return;

                // Visual feedback
                if (btn) {
                    var original = btn.textContent;
                    btn.textContent = '✓ Subscribed!';
                    btn.disabled = true;
                    setTimeout(function () {
                        btn.textContent = original;
                        btn.disabled = false;
                        input.value = '';
                    }, 3000);
                }
            });
        });
    }

    // ============================================================
    // HERO PARALLAX (subtle, performance-safe)
    // ============================================================
    window.addEventListener('scroll', function () {
        var hero = document.querySelector('.hero');
        if (!hero) return;
        var decorators = hero.querySelectorAll('.hero-decorator');
        var scroll = window.pageYOffset;
        decorators.forEach(function (el, i) {
            var speed = 0.05 + (i * 0.02);
            el.style.transform = 'translateY(' + (scroll * speed) + 'px)';
        });
    }, { passive: true });

})();
