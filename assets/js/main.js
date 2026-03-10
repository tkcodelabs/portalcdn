/**
 * main.js — Correio do Norte Theme
 * Mobile menu, search overlay, back-to-top, newsletter AJAX
 */
(function () {
    'use strict';

    /* ==============================
       MOBILE MENU
    ============================== */
    const btnOpen = document.getElementById('btn-mobile-menu');
    const btnClose = document.getElementById('btn-mobile-close');
    const mobileNav = document.getElementById('mobile-nav');
    const overlay = document.getElementById('mobile-nav-overlay');

    function openMobileMenu() {
        mobileNav.classList.add('is-open');
        overlay.classList.add('is-open');
        mobileNav.setAttribute('aria-hidden', 'false');
        overlay.setAttribute('aria-hidden', 'false');
        btnOpen && btnOpen.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        mobileNav.classList.remove('is-open');
        overlay.classList.remove('is-open');
        mobileNav.setAttribute('aria-hidden', 'true');
        overlay.setAttribute('aria-hidden', 'true');
        btnOpen && btnOpen.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    btnOpen && btnOpen.addEventListener('click', openMobileMenu);
    btnClose && btnClose.addEventListener('click', closeMobileMenu);
    overlay && overlay.addEventListener('click', closeMobileMenu);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
            closeSearch();
        }
    });

    /* ==============================
       SEARCH OVERLAY
    ============================== */
    const btnSearchOpen = document.getElementById('btn-search-open');
    const btnSearchClose = document.getElementById('btn-search-close');
    const searchOverlay = document.getElementById('search-overlay');
    const searchInput = document.getElementById('search-overlay-input');

    function openSearch() {
        searchOverlay.classList.add('is-open');
        searchOverlay.setAttribute('aria-hidden', 'false');
        btnSearchOpen && btnSearchOpen.setAttribute('aria-expanded', 'true');
        setTimeout(function () {
            searchInput && searchInput.focus();
        }, 100);
        document.body.style.overflow = 'hidden';
    }

    function closeSearch() {
        searchOverlay && searchOverlay.classList.remove('is-open');
        searchOverlay && searchOverlay.setAttribute('aria-hidden', 'true');
        btnSearchOpen && btnSearchOpen.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    btnSearchOpen && btnSearchOpen.addEventListener('click', openSearch);
    btnSearchClose && btnSearchClose.addEventListener('click', closeSearch);
    searchOverlay && searchOverlay.addEventListener('click', function (e) {
        if (e.target === searchOverlay) closeSearch();
    });

    /* ==============================
       BACK TO TOP
    ============================== */
    var backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 400) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        }, { passive: true });

        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ==============================
       NEWSLETTER FORM
    ============================== */
    var newsletterForm = document.getElementById('newsletter-form');
    var newsletterMsg = document.getElementById('newsletter-msg');

    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var email = newsletterForm.querySelector('[name="email"]').value;
            var website = newsletterForm.querySelector('[name="website"]') ? newsletterForm.querySelector('[name="website"]').value : '';
            var nonce = newsletterForm.querySelector('[name="_cdn_nonce"]') ? newsletterForm.querySelector('[name="_cdn_nonce"]').value : '';

            if (!email) return;

            var formData = new FormData();
            formData.append('action', 'cdn_newsletter');
            formData.append('email', email);
            formData.append('website', website);
            formData.append('nonce', typeof cdnData !== 'undefined' ? cdnData.nonce : nonce);

            var btn = newsletterForm.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Aguarde...'; }

            fetch(typeof cdnData !== 'undefined' ? cdnData.ajaxUrl : '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (newsletterMsg) {
                        newsletterMsg.style.display = 'block';
                        newsletterMsg.textContent = data.data || (data.success ? 'Inscrição realizada!' : 'Erro. Tente novamente.');
                        newsletterMsg.style.color = data.success ? 'var(--color-primary)' : 'var(--color-accent-red)';
                    }
                    if (data.success) newsletterForm.reset();
                })
                .catch(function () {
                    if (newsletterMsg) {
                        newsletterMsg.style.display = 'block';
                        newsletterMsg.textContent = 'Erro de conexão. Tente novamente.';
                        newsletterMsg.style.color = 'var(--color-accent-red)';
                    }
                })
                .finally(function () {
                    if (btn) { btn.disabled = false; btn.textContent = 'INSCREVER'; }
                });
        });
    }

    /* ==============================
       LAZY LOAD (IntersectionObserver)
    ============================== */
    if ('IntersectionObserver' in window) {
        var lazyImages = document.querySelectorAll('img[loading="lazy"]');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    observer.unobserve(entry.target);
                }
            });
        });
        lazyImages.forEach(function (img) { observer.observe(img); });
    }

    /* ==============================
       SMOOTH ANCHOR SCROLL
    ============================== */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                var header = document.getElementById('site-header');
                var offset = header ? header.offsetHeight + 16 : 80;
                window.scrollTo({
                    top: target.getBoundingClientRect().top + window.scrollY - offset,
                    behavior: 'smooth',
                });
            }
        });
    });

    /* ==============================
       AJAX LOAD MORE POSTS (HOMEPAGE)
    ============================== */
    const btnLoadMore = document.getElementById('cdn-load-more');
    if (btnLoadMore && typeof cdnData !== 'undefined') {
        const loadMoreContainer = document.getElementById('load-more-container');
        btnLoadMore.addEventListener('click', function () {
            const btn = this;
            let offset = parseInt(btn.getAttribute('data-offset'));
            let ppp = parseInt(btn.getAttribute('data-ppp'));

            btn.textContent = 'Carregando...';
            btn.style.opacity = '0.7';
            btn.disabled = true;

            let formData = new FormData();
            formData.append('action', 'cdn_load_more');
            formData.append('offset', offset);
            formData.append('ppp', ppp);
            formData.append('nonce', cdnData.nonce);

            fetch(cdnData.ajaxUrl, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const grid = document.getElementById('cdn-news-grid');
                        grid.insertAdjacentHTML('beforeend', res.data.html);

                        if (res.data.has_more) {
                            btn.setAttribute('data-offset', offset + ppp);
                            btn.textContent = 'Carregar Mais Matérias';
                            btn.style.opacity = '1';
                            btn.disabled = false;
                        } else {
                            btn.textContent = 'Fim das Notícias';
                            btn.style.opacity = '0.5';
                            setTimeout(() => {
                                if (loadMoreContainer) loadMoreContainer.style.display = 'none';
                            }, 2000);
                        }
                    } else {
                        btn.textContent = 'Erro ao carregar';
                        btn.style.opacity = '1';
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    btn.textContent = 'Tentar Novamente';
                    btn.style.opacity = '1';
                    btn.disabled = false;
                });
        });
    }

    /* ==============================
       THEME SWITCHER
    ============================== */
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');

    if (themeToggle && themeIcon) {
        // Init icon based on current theme
        const currentTheme = document.documentElement.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            themeIcon.textContent = 'light_mode';
            themeToggle.setAttribute('aria-checked', 'true');
        }

        themeToggle.addEventListener('click', function () {
            let activeTheme = document.documentElement.getAttribute('data-theme');
            let newTheme = activeTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('cdn_theme', newTheme);

            themeIcon.textContent = newTheme === 'dark' ? 'light_mode' : 'dark_mode';
            themeToggle.setAttribute('aria-checked', newTheme === 'dark' ? 'true' : 'false');
        });
    }

    /* ==============================
       NEWSLETTER FORM
    ============================== */
    const nlForm = document.getElementById('newsletter-form');
    const nlMsg = document.getElementById('newsletter-msg');

    if (nlForm && typeof cdnData !== 'undefined') {
        nlForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const emailInput = nlForm.querySelector('input[type="email"]');
            const submitBtn = nlForm.querySelector('button[type="submit"]');
            const nonce = nlForm.querySelector('input[name="_cdn_nonce"]');

            if (!emailInput || !emailInput.value.trim()) return;

            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Aguarde...';
            submitBtn.disabled = true;

            const fd = new FormData();
            fd.append('action', 'cdn_newsletter_subscribe');
            fd.append('email', emailInput.value.trim());
            fd.append('nonce', nonce ? nonce.value : '');
            // Honeypot
            const hp = nlForm.querySelector('input[name="website"]');
            if (hp) fd.append('website', hp.value);

            fetch(cdnData.ajaxUrl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    nlMsg.style.display = 'block';
                    if (res.success) {
                        nlMsg.style.color = 'var(--color-primary, #2ecc71)';
                        nlMsg.textContent = res.data.message;
                        emailInput.value = '';
                        submitBtn.textContent = '✓ Inscrito!';
                    } else {
                        nlMsg.style.color = '#e74c3c';
                        nlMsg.textContent = res.data.message;
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(() => {
                    nlMsg.style.display = 'block';
                    nlMsg.style.color = '#e74c3c';
                    nlMsg.textContent = 'Ocorreu um erro. Tente novamente.';
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });
    }

})();
