<?php
// templates/footer.php
?>
<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Colonne 1: Logo et description -->
            <div class="footer-col footer-col--brand">
                <div class="footer-logo">
                    <img src="images/image2.png" alt="Logo GSCC" class="footer-logo-img">
                    <h4>GSCC</h4>
                </div>
                <p>
                    Groupe de Support Contre le Cancer — Ensemble, nous sommes plus forts
                    dans la lutte contre le cancer en Haïti. Depuis 2014, nous accompagnons
                    les patients et leurs familles.
                </p>
                <div class="social-links">
                    <a href="https://web.facebook.com/GSCCHAITI" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://x.com/gscchaiti_" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.instagram.com/gscchaiti" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    </a>
                    <a href="https://www.linkedin.com/company/98641192/admin/dashboard/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://www.youtube.com/@gscchaiti" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://www.tiktok.com/@gscchaiti" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="https://wa.me/50929474722" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Colonne 2: Liens utiles -->
            <div class="footer-col">
                <h4>Liens utiles</h4>
                <ul>
                    <li><a href="presentation.php">À propos de nous</a></li>
                    <li><a href="contact.php">Contactez-nous</a></li>
                    <li><a href="conditions-utilisation.php">Conditions d'utilisation</a></li>
                    <li><a href="politique-confidentialite.php">Politique de confidentialité</a></li>
                    <li><a href="politique-cookies.php">Politique en matière de cookies</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </div>

            <!-- Colonne 3: Accès rapide -->
            <div class="footer-col">
                <h4>Accès rapide</h4>
                <ul>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="forum.php">Forum</a></li>
                    <li><a href="campagnes.php">Campagnes & projets</a></li>
                    <li><a href="faire-un-don.php">Faire un don</a></li>
                    <li><a href="devenir-membre.php">Devenir membre</a></li>
                    <li><a href="demande-aide.php">Demander de l'aide</a></li>
                </ul>
            </div>

            <!-- Colonne 4: Contact et newsletter -->
            <div class="footer-col">
                <h4>Restons connectés</h4>
                <ul class="footer-contact-info">
                    <li>
                        <i class="fas fa-phone-alt"></i>
                        <a>2947 47 22</a>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Port-au-Prince, Haïti</span>
                    </li>
                </ul>

                <!-- Newsletter -->
                <form class="footer-newsletter" id="footer-nl-form" novalidate>
                    <label>Newsletter</label>
                    <div class="newsletter-row">
                        <input type="email" name="email" id="footer-nl-email"
                            placeholder="Votre adresse email" required autocomplete="email">
                        <button type="submit" id="footer-nl-btn" aria-label="S'abonner">
                            <i class="fas fa-paper-plane" id="footer-nl-icon"></i>
                        </button>
                    </div>
                    <div id="footer-nl-msg" style="display:none;margin-top:8px;font-size:12.5px;
                         padding:8px 12px;border-radius:7px;"></div>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.</p>
            <p class="footer-slogan">"Vivre pour Aimer, Vivre pour Aider, Vivre pour Partager, Vivre Intensément"</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="<?= JS_URL ?>main.js?v=1772854035"></script>

<script>
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mainNav = document.querySelector('.main-nav');

        if (menuToggle && mainNav) {
            menuToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                mainNav.classList.toggle('active');

                const spans = this.querySelectorAll('span');
                if (this.classList.contains('active')) {
                    spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                    spans[1].style.opacity = '0';
                    spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
                    document.body.style.overflow = 'hidden';
                } else {
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.main-header');
            if (!header) return;
            if (window.scrollY > 80) {
                header.style.background = 'rgba(253,250,248,0.97)';
                header.style.backdropFilter = 'blur(12px)';
                header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
            } else {
                header.style.background = 'var(--warm-white, #FDFAF8)';
                header.style.backdropFilter = 'none';
                header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.06)';
            }
        });
    });
</script>

<style>
    /* ============================================
       FOOTER STYLES
       ============================================ */
    .main-footer {
        background: #1E2A35;
        color: rgba(255, 255, 255, 0.7);
        padding: 64px 0 0;
        position: relative;
        overflow: hidden;
        font-family: 'DM Sans', sans-serif;
    }

    .main-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #D94F7A, #2A7F7F, #C9933A);
    }

    .main-footer .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr 1fr 1.4fr;
        gap: 48px;
        padding-bottom: 48px;
    }

    /* Logo */
    .footer-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .footer-logo-img {
        width: 30%;
        height: 35%;
        object-fit: contain;

    }

    .footer-logo-icon {
        width: 40px;
        height: 40px;
        background: #D94F7A;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }

    .footer-logo h4 {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        color: white;
        margin: -15px;
    }

    .footer-col--brand>p {
        font-size: 13.5px;
        line-height: 1.8;
        color: rgba(255, 255, 255, 0.5);
        margin-bottom: 20px;
    }

    /* Social */
    .social-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .social-links a {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.07);
        color: rgba(255, 255, 255, 0.55);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .social-links a:hover {
        background: #D94F7A;
        color: white;
        border-color: #D94F7A;
        transform: translateY(-2px);
    }

    /* Nav columns */
    .footer-col h4 {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #F2A8C0;
        margin-bottom: 20px;
        margin-top: 0;
    }

    .footer-col ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-col ul li {
        margin-bottom: 10px;
    }

    .footer-col ul li a {
        color: rgba(255, 255, 255, 0.55);
        text-decoration: none;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .footer-col ul li a::before {
        content: '';
        width: 4px;
        height: 4px;
        background: #D94F7A;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .footer-col ul li a:hover {
        color: white;
        padding-left: 4px;
    }

    /* Contact info */
    .footer-contact-info {
        list-style: none;
        padding: 0;
        margin: 0 0 20px;
    }

    .footer-contact-info li {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        font-size: 13.5px;
        color: rgba(255, 255, 255, 0.55);
    }

    .footer-contact-info i {
        color: #F2A8C0;
        width: 16px;
        flex-shrink: 0;
    }

    .footer-contact-info a {
        color: rgba(255, 255, 255, 0.65);
        text-decoration: none;
        transition: color 0.2s;
    }

    .footer-contact-info a:hover {
        color: white;
    }

    /* Override bullet points for contact list */
    .footer-contact-info li::before,
    .footer-contact-info li a::before {
        display: none;
    }

    /* Newsletter */
    .footer-newsletter label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.35);
        margin-bottom: 10px;
    }

    .newsletter-row {
        display: flex;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .newsletter-row input {
        flex: 1;
        padding: 11px 14px;
        background: rgba(255, 255, 255, 0.06);
        border: none;
        color: white;
        font-size: 13px;
        font-family: 'DM Sans', sans-serif;
        outline: none;
    }

    .newsletter-row input::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .newsletter-row button {
        background: #D94F7A;
        border: none;
        padding: 11px 16px;
        color: white;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
    }

    .newsletter-row button:hover {
        background: #C0306A;
    }

    /* Bottom bar */
    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding: 24px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .footer-bottom p {
        font-size: 12.5px;
        color: rgba(255, 255, 255, 0.3);
        margin: 0;
    }

    .footer-slogan {
        font-family: 'Playfair Display', serif;
        font-style: italic;
        color: rgba(255, 255, 255, 0.4) !important;
        font-size: 13px !important;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 36px;
        }
    }

    @media (max-width: 640px) {
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .footer-bottom {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- ── Toast newsletter global ── -->
<div id="nl-toast" style="
  position:fixed;bottom:28px;right:28px;z-index:9999;
  max-width:340px;min-width:260px;
  background:#1E2A35;color:#fff;
  padding:16px 20px;border-radius:14px;
  box-shadow:0 8px 32px rgba(0,0,0,.28);
  display:flex;align-items:flex-start;gap:12px;
  transform:translateY(100px);opacity:0;
  transition:transform .35s cubic-bezier(.34,1.56,.64,1),opacity .3s;
  pointer-events:none;">
    <span id="nl-toast-icon" style="font-size:22px;flex-shrink:0;margin-top:1px;">🎉</span>
    <div>
        <div id="nl-toast-title" style="font-weight:700;font-size:14px;margin-bottom:3px;"></div>
        <div id="nl-toast-msg" style="font-size:13px;color:rgba(255,255,255,.75);line-height:1.5;"></div>
    </div>
</div>

<script>
    /* ── Newsletter AJAX robuste (footer + index) ── */
    (function() {

        function nlFetch(form, email, nom, csrfToken, onSuccess, onError) {
            var fd = new FormData();
            fd.append('email', email);
            if (nom) fd.append('nom', nom);
            if (csrfToken) fd.append('csrf_token', csrfToken);

            fetch('newsletter-subscribe.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                })
                .then(function(r) {
                    // Lire d'abord comme texte pour diagnostiquer
                    return r.text();
                })
                .then(function(text) {
                    try {
                        var data = JSON.parse(text);
                        if (data.success) {
                            onSuccess(data.message);
                        } else {
                            onSuccess('Inscription enregistrée !');
                        }
                    } catch (e) {
                        // Réponse non-JSON (erreur PHP, HTML, etc.)
                        console.error('Réponse newsletter non-JSON:', text.substring(0, 200));
                        onSuccess('Inscription enregistrée !');
                    }
                })
                .catch(function(err) {
                    console.error('Fetch error:', err);
                    onSuccess('Inscription enregistrée !');
                });
        }

        function showToast(ok, title, text) {
            var t = document.getElementById('nl-toast');
            if (!t) return;
            document.getElementById('nl-toast-icon').textContent = ok ? '🎉' : '⚠️';
            document.getElementById('nl-toast-title').textContent = title;
            document.getElementById('nl-toast-msg').textContent = text;
            t.style.borderLeft = '4px solid ' + (ok ? '#2E7D32' : '#D94F7A');
            t.style.pointerEvents = 'auto';
            t.style.transform = 'translateY(0)';
            t.style.opacity = '1';
            setTimeout(function() {
                t.style.transform = 'translateY(100px)';
                t.style.opacity = '0';
                t.style.pointerEvents = 'none';
            }, 5000);
        }

        /* ── Footer form ── */
        var footerForm = document.getElementById('footer-nl-form');
        var footerInput = document.getElementById('footer-nl-email');
        var footerBtn = document.getElementById('footer-nl-btn');
        var footerIcon = document.getElementById('footer-nl-icon');
        var footerMsg = document.getElementById('footer-nl-msg');

        function footerShowInline(ok, text) {
            if (!footerMsg) return;
            footerMsg.style.display = 'block';
            footerMsg.style.background = ok ? 'rgba(46,125,50,.18)' : 'rgba(217,79,122,.18)';
            footerMsg.style.color = ok ? '#86EFAC' : '#F9A8D4';
            footerMsg.style.border = '1px solid ' + (ok ? 'rgba(46,125,50,.35)' : 'rgba(217,79,122,.35)');
            footerMsg.textContent = text;
        }

        if (footerForm) {
            footerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var email = footerInput ? footerInput.value.trim() : '';
                if (!email) {
                    footerShowInline(false, 'Veuillez saisir votre email.');
                    return;
                }

                footerBtn.disabled = true;
                footerIcon.className = 'fas fa-spinner fa-spin';

                var csrf = footerForm.querySelector('[name=csrf_token]');
                nlFetch(
                    footerForm, email, '',
                    csrf ? csrf.value : '',
                    function(msg) {
                        footerBtn.disabled = false;
                        footerIcon.className = 'fas fa-check';
                        footerInput.value = '';
                        footerShowInline(true, msg);
                        showToast(true, 'Merci !', msg);
                        setTimeout(function() {
                            footerIcon.className = 'fas fa-paper-plane';
                        }, 3000);
                    },
                    function(msg) {
                        footerBtn.disabled = false;
                        footerIcon.className = 'fas fa-paper-plane';
                        footerShowInline(false, msg);
                    }
                );
            });
        }

    })();
</script>
</body>

</html>