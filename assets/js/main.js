// assets/js/main.js

document.addEventListener('DOMContentLoaded', function () {

    /* ══════════════════════════════════════════════════════════
       1. AOS (animations au scroll)
       ══════════════════════════════════════════════════════════ */
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 900, once: true, offset: 80, easing: 'ease-out-cubic' });
    }

    /* ══════════════════════════════════════════════════════════
       2. Hero Slider (Swiper)
       ══════════════════════════════════════════════════════════ */
    if (document.querySelector('.hero-swiper')) {
        new Swiper('.hero-swiper', {
            loop: true,
            autoplay: { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
            pagination: { el: '.hero-swiper .swiper-pagination', clickable: true },
            navigation: {
                nextEl: '.hero-swiper .swiper-button-next',
                prevEl: '.hero-swiper .swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: { crossFade: true },
            speed: 800,
        });
    }

    /* ══════════════════════════════════════════════════════════
       3. Temoignages Slider (Swiper)
       ══════════════════════════════════════════════════════════ */
    if (document.querySelector('.testimonials-swiper')) {
        new Swiper('.testimonials-swiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.testimonials-swiper .swiper-pagination', clickable: true },
            slidesPerView: 1,
            spaceBetween: 24,
            breakpoints: { 768: { slidesPerView: 2 } },
            speed: 700,
        });
    }

    /* ══════════════════════════════════════════════════════════
       4. Compteurs animes -- AJOUTE (manquait entierement)
       Lit data-count="1200" sur .stat-number et anime 0 -> cible
       des que l'element entre dans le viewport.
       ══════════════════════════════════════════════════════════ */
    (function initCounters() {
        var counters = Array.from(
            document.querySelectorAll('.stat-number[data-count]')
        ).filter(function (el) {
            return !isNaN(parseInt(el.getAttribute('data-count'), 10));
        });
        if (!counters.length) return;

        function fmt(n) { return n.toLocaleString('fr-FR'); }

        function animateCounter(el) {
            var target   = parseInt(el.getAttribute('data-count'), 10);
            var suffix   = el.getAttribute('data-suffix') || '+';
            var duration = 1800;
            var startTs  = null;
            function easeOut(t) { return 1 - Math.pow(1 - t, 3); }
            function step(ts) {
                if (!startTs) startTs = ts;
                var p = Math.min((ts - startTs) / duration, 1);
                el.textContent = fmt(Math.floor(easeOut(p) * target)) + suffix;
                if (p < 1) requestAnimationFrame(step);
                else el.textContent = fmt(target) + suffix;
            }
            requestAnimationFrame(step);
        }

        if ('IntersectionObserver' in window) {
            var counterObs = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) { animateCounter(e.target); counterObs.unobserve(e.target); }
                });
            }, { threshold: 0.5 });
            counters.forEach(function (el) { el.textContent = '0'; counterObs.observe(el); });
        } else {
            counters.forEach(function (el) { el.textContent = fmt(parseInt(el.getAttribute('data-count'), 10)); });
        }
    })();

    /* ══════════════════════════════════════════════════════════
       5. Marquee partenaires -- AJOUTE (manquait entierement)
       Recalcule la largeur reelle de la liste pour un translateX
       pixel-perfect, garantissant le loop sans saut visible.
       ══════════════════════════════════════════════════════════ */
    (function initPartnersMarquee() {
        var track = document.querySelector('.partners-track');
        if (!track) return;

        // Securite : creer la copie aria-hidden si absente
        var lists = track.querySelectorAll('.partners-list');
        if (lists.length < 2) {
            var clone = lists[0].cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            track.appendChild(clone);
        }

        function calibrate() {
            var orig = track.querySelector('.partners-list:not([aria-hidden])');
            if (!orig) return;
            var w = orig.offsetWidth;
            if (!w) return;

            // Injecter un keyframe pixel-exact
            var id = 'gscc-marquee-kf';
            var old = document.getElementById(id);
            if (old) old.remove();
            var s = document.createElement('style');
            s.id = id;
            s.textContent =
                '@keyframes gscc-partners-px{' +
                '0%{transform:translateX(0)}' +
                '100%{transform:translateX(-' + w + 'px)}' +
                '}';
            document.head.appendChild(s);

            track.style.animation = 'none';
            void track.offsetWidth; // flush reflow
            track.style.animation = 'gscc-partners-px 30s linear infinite';
        }

        var wrap = document.querySelector('.partners-track-wrap');
        if (wrap) {
            wrap.addEventListener('mouseenter', function () { track.style.animationPlayState = 'paused'; });
            wrap.addEventListener('mouseleave', function () { track.style.animationPlayState = 'running'; });
        }

        setTimeout(calibrate, 250);
        window.addEventListener('resize', calibrate);
    })();

    /* ══════════════════════════════════════════════════════════
       6. Smooth scroll
       ══════════════════════════════════════════════════════════ */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var href = this.getAttribute('href');
            if (href === '#') return;
            var target = document.querySelector(href);
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    });

    /* ══════════════════════════════════════════════════════════
       7. Newsletter form — géré dans footer.php et index.php
       ══════════════════════════════════════════════════════════ */

    /* ══════════════════════════════════════════════════════════
       8. Toast notification
       CORRIGE : @keyframes gsccSlideIn defini dans style.css
       ══════════════════════════════════════════════════════════ */
    window.showNotification = function (message, type) {
        type = type || 'info';
        var p = {
            success : { bg: '#4CAF50', icon: 'check-circle' },
            error   : { bg: '#DC2626', icon: 'times-circle' },
            warning : { bg: '#D97706', icon: 'exclamation-triangle' },
            info    : { bg: '#003399', icon: 'info-circle' },
        };
        var c = p[type] || p.info;
        var old = document.querySelector('.gscc-notification');
        if (old) old.remove();
        var el = document.createElement('div');
        el.className = 'gscc-notification';
        el.innerHTML =
            '<i class="fas fa-' + c.icon + '" style="font-size:18px;flex-shrink:0"></i>' +
            '<span style="flex:1">' + message + '</span>' +
            '<button onclick="this.parentElement.remove()" aria-label="Fermer" ' +
            'style="background:rgba(255,255,255,.25);border:none;cursor:pointer;color:white;' +
            'width:24px;height:24px;border-radius:50%;display:flex;align-items:center;' +
            'justify-content:center;font-size:14px;flex-shrink:0">&times;</button>';
        Object.assign(el.style, {
            position: 'fixed', top: '20px', right: '20px',
            display: 'flex', alignItems: 'center', gap: '12px',
            padding: '14px 18px', background: c.bg, color: 'white',
            borderRadius: '12px', boxShadow: '0 8px 32px rgba(0,0,0,0.2)',
            zIndex: '9999', fontFamily: 'DM Sans, Inter, sans-serif',
            fontSize: '14.5px', fontWeight: '500',
            animation: 'gsccSlideIn 0.3s ease both',
            maxWidth: '380px', minWidth: '260px',
        });
        document.body.appendChild(el);
        setTimeout(function () {
            if (el.parentElement) {
                el.style.opacity = '0'; el.style.transform = 'translateX(20px)';
                el.style.transition = 'all 0.3s ease';
                setTimeout(function () { if (el.parentElement) el.remove(); }, 300);
            }
        }, 4000);
    };

    /* ══════════════════════════════════════════════════════════
       9. IntersectionObserver .in-view
       ══════════════════════════════════════════════════════════ */
    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('in-view'); obs.unobserve(e.target); }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        document.querySelectorAll('.mission-card, .blog-card, .cta-card, .stat-item').forEach(function (el) {
            obs.observe(el);
        });
    }

    /* ══════════════════════════════════════════════════════════
       10. Protection clic droit sur les images
       ══════════════════════════════════════════════════════════ */
    document.querySelectorAll('img').forEach(function (img) {
        img.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    });

}); // fin DOMContentLoaded