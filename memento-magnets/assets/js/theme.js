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
        var overlay = document.querySelector('.search-overlay');
        if (!overlay) return;
        var openBtn  = document.querySelector('.js-search-open');
        var closeBtn = overlay.querySelector('.js-search-close');
        var form     = overlay.querySelector('.search-form');
        var input    = overlay.querySelector('input[type="search"]');
        var list     = overlay.querySelector('.search-suggest');
        var quick    = overlay.querySelector('.search-quick');
        var status   = overlay.querySelector('.search-suggest__status');
        var data     = (window.mementoSearch && window.mementoSearch.items) || [];
        var S        = (window.mementoSearch && window.mementoSearch.strings) || {};
        var lastFocus = null;
        var active    = -1;
        var closeTimer;

        function openSearch() {
            clearTimeout(closeTimer);
            lastFocus = document.activeElement;
            overlay.hidden = false;
            // Force a style recalculation so the fade/slide transition runs from the hidden state.
            void overlay.offsetWidth;
            overlay.classList.add('is-open');
            document.documentElement.classList.add('search-open');
            if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
            setTimeout(function () { if (input) { input.focus(); input.select(); } }, 60);
        }

        function closeSearch() {
            if (overlay.hidden) return;
            overlay.classList.remove('is-open');
            document.documentElement.classList.remove('search-open');
            if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
            closeTimer = setTimeout(function () { overlay.hidden = true; }, 250);
            if (lastFocus && lastFocus.focus) lastFocus.focus();
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function highlight(text, terms) {
            var html = escapeHtml(text);
            terms.forEach(function (t) {
                if (!t) return;
                var re = new RegExp('(' + t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
                html = html.replace(re, '<mark>$1</mark>');
            });
            return html;
        }

        function options() { return list ? Array.prototype.slice.call(list.querySelectorAll('[role="option"]')) : []; }

        function setActive(i) {
            var opts = options();
            if (!opts.length) { active = -1; return; }
            active = (i + opts.length) % opts.length;
            opts.forEach(function (o, n) { o.setAttribute('aria-selected', n === active ? 'true' : 'false'); });
            opts[active].scrollIntoView({ block: 'nearest' });
            input.setAttribute('aria-activedescendant', opts[active].id);
        }

        function render() {
            if (!list) return;
            var q = input.value.trim().toLowerCase();
            active = -1;
            input.removeAttribute('aria-activedescendant');
            if (q.length < 2) {
                list.hidden = true;
                list.innerHTML = '';
                if (quick) quick.hidden = false;
                if (status) status.textContent = '';
                return;
            }
            var terms = q.split(/\s+/).filter(Boolean);
            var matches = data.filter(function (item) {
                var hay = (item.title + ' ' + item.meta + ' ' + item.keywords).toLowerCase();
                return terms.every(function (t) { return hay.indexOf(t) !== -1; });
            });
            var products = matches.filter(function (m) { return m.type === 'product'; }).slice(0, 4);
            var pages = matches.filter(function (m) { return m.type !== 'product'; }).slice(0, 4);
            var html = '', n = 0;

            function group(label, items) {
                if (!items.length) return;
                html += '<p class="search-suggest__group">' + escapeHtml(label) + '</p>';
                items.forEach(function (item) {
                    var icon = item.type === 'product'
                        ? '<span class="search-suggest__thumb search-suggest__thumb--tiles" data-count="' + (item.count || 3) + '"><i></i><i></i><i></i><i></i></span>'
                        : '<span class="search-suggest__thumb"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg></span>';
                    html += '<a class="search-suggest__item" role="option" aria-selected="false" id="search-opt-' + (n++) + '" href="' + escapeHtml(item.url) + '">' +
                        icon + '<span class="search-suggest__text"><span class="search-suggest__title">' + highlight(item.title, terms) + '</span>' +
                        '<span class="search-suggest__meta">' + escapeHtml(item.meta) + '</span></span></a>';
                });
            }
            group(S.products || 'Products', products);
            group(S.pages || 'Pages', pages);
            if (!n) html += '<p class="search-suggest__empty">' + escapeHtml(S.noMatches || '') + '</p>';
            html += '<a class="search-suggest__item search-suggest__all" role="option" aria-selected="false" id="search-opt-' + (n++) + '" href="' +
                escapeHtml(form.getAttribute('action') + '?s=' + encodeURIComponent(input.value.trim())) + '">' +
                escapeHtml((S.seeAll || 'See all results for “%s”').replace('%s', input.value.trim())) + ' →</a>';

            list.innerHTML = html;
            list.hidden = false;
            if (quick) quick.hidden = true;
            if (status) status.textContent = (S.resultsFor || '%d').replace('%d', n);
        }

        if (input && list) {
            input.setAttribute('role', 'combobox');
            input.setAttribute('aria-autocomplete', 'list');
            input.setAttribute('aria-controls', 'search-suggest-list');
            list.id = 'search-suggest-list';
            input.addEventListener('input', render);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
                else if (e.key === 'Enter' && active > -1) {
                    var opt = options()[active];
                    if (opt) { e.preventDefault(); window.location.href = opt.href; }
                }
            });
        }

        if (openBtn) {
            openBtn.setAttribute('aria-expanded', 'false');
            openBtn.addEventListener('click', openSearch);
        }
        if (closeBtn) closeBtn.addEventListener('click', closeSearch);

        // Click on the dimmed backdrop closes; clicks inside the panel don't.
        overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) closeSearch(); });

        document.addEventListener('keydown', function (e) {
            if (overlay.hidden) {
                // "/" opens search from anywhere (except while typing in a field).
                if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
                    e.preventDefault();
                    openSearch();
                }
                return;
            }
            if (e.key === 'Escape') { e.preventDefault(); closeSearch(); return; }
            if (e.key === 'Tab') {
                // Keep keyboard focus inside the panel.
                var focusable = Array.prototype.filter.call(
                    overlay.querySelectorAll('a[href], button, input'),
                    function (el) { return el.offsetParent !== null && !el.disabled; }
                );
                if (!focusable.length) return;
                var first = focusable[0], last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
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
