<?php
// coordonnees-bancaires.php
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Coordonnées Bancaires';
$page_description = 'Faites un don au GSCC par virement bancaire.';

require_once 'templates/header.php';
?>

<style>
    :root {
        --rose:       #D94F7A;
        --rose-dark:  #B83565;
        --rose-pale:  #FDE8EF;
        --teal:       #2A7F7F;
        --teal-pale:  #E6F3F3;
        --charcoal:   #1E2A35;
        --grey:       #6B7A8D;
        --grey-light: #F0F4F8;
        --warm-white: #FDFAF8;
    }

    /* ══════════════════════════════
       HERO
    ══════════════════════════════ */
    .bank-hero {
        position: relative;
        background: linear-gradient(135deg, #1E2A35 0%, #7B1535 50%, #D94F7A 100%);
        padding: 64px 0 110px;
        overflow: hidden;
        text-align: center;
    }

    .bank-hero::before {
        content: '';
        position: absolute;
        width: 420px; height: 420px;
        border-radius: 50%;
        top: -150px; right: -80px;
        background: rgba(255,255,255,0.04);
        pointer-events: none;
    }

    .bank-hero::after {
        content: '';
        position: absolute;
        width: 260px; height: 260px;
        border-radius: 50%;
        bottom: -100px; left: -60px;
        background: rgba(42,127,127,0.16);
        pointer-events: none;
    }

    .bank-hero-inner { position: relative; z-index: 2; }

    .bank-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.9);
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: 0.3px;
        margin-bottom: 22px;
    }

    .bank-hero h1 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        color: white;
        margin-bottom: 14px;
        text-shadow: 0 2px 20px rgba(0,0,0,0.2);
    }

    .bank-hero p {
        font-size: 1rem;
        color: rgba(255,255,255,0.82);
        max-width: 520px;
        margin: 0 auto;
        line-height: 1.7;
    }

    .bank-hero p strong { color: #FBCFE8; }

    /* ══════════════════════════════
       MAIN WRAP
    ══════════════════════════════ */
    .bank-wrap {
        background: var(--grey-light);
        padding: 0 0 80px;
    }

    .bank-inner {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* ══════════════════════════════
       FLOATING CURRENCY TABS
    ══════════════════════════════ */
    .currency-tabs {
        display: flex;
        gap: 14px;
        justify-content: center;
        margin-top: -28px;
        margin-bottom: 44px;
        position: relative;
        z-index: 5;
    }

    .currency-tab {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 13px 28px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 700;
        box-shadow: 0 8px 28px rgba(0,0,0,0.13);
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: transform 0.25s, box-shadow 0.25s;
    }

    .currency-tab:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    .currency-tab.htg {
        background: white;
        color: var(--rose);
        border: 2px solid var(--rose-pale);
    }

    .currency-tab.usd {
        background: white;
        color: var(--teal);
        border: 2px solid var(--teal-pale);
    }

    /* ══════════════════════════════
       SECTION HEADER
    ══════════════════════════════ */
    .bank-section { margin-bottom: 40px; }

    .section-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }

    .section-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 18px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        flex-shrink: 0;
    }

    .section-pill.htg {
        background: var(--rose-pale);
        color: var(--rose-dark);
    }

    .section-pill.usd {
        background: var(--teal-pale);
        color: var(--teal);
    }

    .section-holder {
        font-size: 13px;
        color: var(--grey);
        font-weight: 500;
    }

    .section-holder strong { color: var(--charcoal); }

    .section-line {
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(0,0,0,0.08), transparent);
    }

    /* ══════════════════════════════
       BANK CARDS GRID
    ══════════════════════════════ */
    .bank-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .bank-card {
        background: white;
        border-radius: 18px;
        padding: 20px 22px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.055);
        border: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.22s, box-shadow 0.22s;
    }

    .bank-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 32px rgba(0,0,0,0.10);
    }

    .bank-card-icon {
        width: 46px; height: 46px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        font-weight: 900;
        font-family: 'Montserrat', sans-serif;
        font-size: 15px;
    }

    .bank-card-icon.htg { background: var(--rose-pale); color: var(--rose); }
    .bank-card-icon.usd { background: var(--teal-pale); color: var(--teal); }
    .bank-card-icon.special { background: #EDE9FE; color: #7C3AED; }

    .bank-card-body { flex: 1; min-width: 0; }

    .bank-card-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--charcoal);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bank-card-num {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        font-weight: 700;
        color: var(--grey);
        letter-spacing: 0.3px;
    }

    .bank-card-num.is-email {
        font-family: inherit;
        font-size: 12.5px;
        color: #7C3AED;
        word-break: break-all;
    }

    .copy-btn {
        background: var(--grey-light);
        border: none;
        border-radius: 9px;
        padding: 8px 10px;
        cursor: pointer;
        color: var(--grey);
        font-size: 13px;
        flex-shrink: 0;
        transition: all 0.2s;
        line-height: 1;
    }

    .copy-btn:hover { background: var(--rose-pale); color: var(--rose); }
    .copy-btn.ok    { background: #DCFCE7; color: #16A34A; }

    /* Second titulaire */
    .bank-sep {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 6px 0 2px;
    }

    .bank-sep-line { flex: 1; height: 1px; background: #E8ECF0; }

    .bank-sep-label {
        font-size: 11.5px;
        color: var(--grey);
        font-weight: 600;
        white-space: nowrap;
        background: var(--grey-light);
        padding: 3px 12px;
        border-radius: 20px;
    }

    .bank-sep-label strong { color: var(--charcoal); }

    /* QR / special card */
    .bank-card.wide {
        grid-column: 1 / -1;
    }

    /* ══════════════════════════════
       DOCUMENTS
    ══════════════════════════════ */
    .docs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 18px;
    }

    .doc-card {
        background: white;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.22s, box-shadow 0.22s;
    }

    .doc-card:hover { transform: translateY(-4px); box-shadow: 0 12px 36px rgba(0,0,0,0.11); }

    .doc-card img { width: 100%; display: block; object-fit: cover; }

    .doc-caption {
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 700;
        color: var(--charcoal);
        display: flex;
        align-items: center;
        gap: 8px;
        border-top: 1px solid #F0F2F5;
        background: var(--warm-white);
    }

    .doc-caption i { font-size: 14px; }
    .doc-caption i.htg-icon { color: var(--rose); }
    .doc-caption i.usd-icon { color: var(--teal); }

    /* ══════════════════════════════
       NOTICE
    ══════════════════════════════ */
    .bank-notice {
        background: white;
        border-radius: 18px;
        padding: 22px 24px;
        margin-top: 32px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .notice-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        background: rgba(217,79,122,0.1);
        color: var(--rose);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .notice-text {
        font-size: 14px;
        color: #4B5563;
        line-height: 1.75;
    }

    .notice-text strong { color: var(--charcoal); }

    /* ══════════════════════════════
       CTA
    ══════════════════════════════ */
    .bank-cta {
        margin-top: 28px;
        background: white;
        border-radius: 18px;
        padding: 24px 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        flex-wrap: wrap;
    }

    .cta-text p {
        font-size: 14.5px;
        color: var(--charcoal);
        font-weight: 600;
        margin-bottom: 3px;
    }

    .cta-text span {
        font-size: 13px;
        color: var(--grey);
    }

    .cta-btns {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.22s;
        white-space: nowrap;
    }

    .cta-btn.whatsapp {
        background: #25D366;
        color: white;
    }
    .cta-btn.whatsapp:hover { background: #1ebe5d; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,211,102,0.35); }

    .cta-btn.contact {
        background: var(--rose-pale);
        color: var(--rose-dark);
        border: 1.5px solid rgba(217,79,122,0.18);
    }
    .cta-btn.contact:hover { background: #FCD5E4; transform: translateY(-2px); }

    /* ══════════════════════════════
       TOAST
    ══════════════════════════════ */
    .bank-toast {
        position: fixed;
        bottom: 28px; left: 50%;
        transform: translateX(-50%) translateY(60px);
        background: var(--charcoal);
        color: white;
        padding: 11px 22px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 9px;
        z-index: 9999;
        opacity: 0;
        transition: all 0.28s ease;
        pointer-events: none;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
    .bank-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    .bank-toast i { color: #4ADE80; font-size: 15px; }

    /* ══════════════════════════════
       RESPONSIVE
    ══════════════════════════════ */
    @media (max-width: 640px) {
        .bank-cards { grid-template-columns: 1fr; }
        .docs-grid  { grid-template-columns: 1fr; }
        .currency-tabs { flex-direction: column; align-items: center; }
        .bank-cta { flex-direction: column; align-items: flex-start; }
        .bank-hero { padding: 50px 0 90px; }
        .bank-card.wide { grid-column: auto; }
    }
</style>

<!-- ═══════════════════════════════
     HERO
═══════════════════════════════ -->
<section class="bank-hero">
    <div class="container">
        <div class="bank-hero-inner">
            <div class="bank-hero-badge">
                <i class="fas fa-university"></i>
                Virement bancaire sécurisé
            </div>
            <h1>Coordonnées bancaires</h1>
            <p>
                Pour effectuer un don par virement, utilisez l'un des comptes ci-dessous.<br>
                Mentionnez <strong>« Don GSCC »</strong> dans le motif du virement.
            </p>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════
     CONTENU
═══════════════════════════════ -->
<div class="bank-wrap">
    <div class="bank-inner">

        <!-- Tabs de navigation -->
        <div class="currency-tabs">
            <a href="#htg" class="currency-tab htg">
                <i class="fas fa-coins"></i> Gourdes — HTG
            </a>
            <a href="#usd" class="currency-tab usd">
                <i class="fas fa-dollar-sign"></i> Dollars — USD
            </a>
        </div>

        <!-- ══ GOURDES ══ -->
        <div class="bank-section" id="htg">
            <div class="section-header">
                <div class="section-pill htg">
                    <i class="fas fa-coins"></i> Gourdes HTG
                </div>
                <div class="section-holder">
                    Titulaire : <strong>Groupe de Support Contre le Cancer</strong>
                </div>
                <div class="section-line"></div>
            </div>

            <div class="bank-cards">
                <div class="bank-card">
                    <div class="bank-card-icon htg">BUH</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Banque de l'Union Haïtienne</div>
                        <div class="bank-card-num">#5500 0000565</div>
                    </div>
                    <button class="copy-btn" data-copy="5500 0000565" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon htg">CAP</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Capital Bank</div>
                        <div class="bank-card-num">#1909973</div>
                    </div>
                    <button class="copy-btn" data-copy="1909973" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon htg">SGB</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Sogebank</div>
                        <div class="bank-card-num">#2606047476</div>
                    </div>
                    <button class="copy-btn" data-copy="2606047476" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon htg">UNI</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Unibank</div>
                        <div class="bank-card-num">#103-1021-01016229</div>
                    </div>
                    <button class="copy-btn" data-copy="103-1021-01016229" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card wide">
                    <div class="bank-card-icon htg"><i class="fas fa-mobile-alt"></i></div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">MonCash</div>
                        <div class="bank-card-num">+509 2947-4722</div>
                    </div>
                    <button class="copy-btn" data-copy="+50929474722" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>
            </div>
        </div>

        <!-- ══ DOLLARS ══ -->
        <div class="bank-section" id="usd">
            <div class="section-header">
                <div class="section-pill usd">
                    <i class="fas fa-dollar-sign"></i> Dollars USD
                </div>
                <div class="section-holder">
                    Titulaire : <strong>Groupe de Support Contre le Cancer</strong>
                </div>
                <div class="section-line"></div>
            </div>

            <div class="bank-cards">
                <div class="bank-card">
                    <div class="bank-card-icon usd">UNI</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Unibank</div>
                        <div class="bank-card-num">#103-1022-01016237</div>
                    </div>
                    <button class="copy-btn" data-copy="103-1022-01016237" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon usd">CAP</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Capital Bank</div>
                        <div class="bank-card-num">#1909974</div>
                    </div>
                    <button class="copy-btn" data-copy="1909974" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon usd">SGB</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Sogebank</div>
                        <div class="bank-card-num">#2616029373</div>
                    </div>
                    <button class="copy-btn" data-copy="2616029373" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon usd">SBL</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Sogebel</div>
                        <div class="bank-card-num">#1530101961</div>
                    </div>
                    <button class="copy-btn" data-copy="1530101961" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <!-- Second titulaire -->
                <div class="bank-sep">
                    <div class="bank-sep-line"></div>
                    <div class="bank-sep-label">Titulaire : <strong>Groupe de Support Contre le Cancer Inc</strong></div>
                    <div class="bank-sep-line"></div>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon usd">CTI</div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Citibank</div>
                        <div class="bank-card-num">#3290440516</div>
                    </div>
                    <button class="copy-btn" data-copy="3290440516" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card">
                    <div class="bank-card-icon special"><i class="fas fa-bolt"></i></div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Zelle</div>
                        <div class="bank-card-num is-email">gsccht1999@gmail.com</div>
                    </div>
                    <button class="copy-btn" data-copy="gsccht1999@gmail.com" aria-label="Copier"><i class="fas fa-copy"></i></button>
                </div>

                <div class="bank-card wide">
                    <div class="bank-card-icon usd"><i class="fas fa-qrcode"></i></div>
                    <div class="bank-card-body">
                        <div class="bank-card-name">Paiement par QR code</div>
                        <div class="bank-card-num" style="font-family:inherit;font-size:12.5px;color:var(--grey);">Disponible sur nos affiches et lors de nos événements</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ DOCUMENTS OFFICIELS ══ -->
        <div class="bank-section">
            <div class="section-header">
                <div class="section-pill htg">
                    <i class="fas fa-file-image"></i> Documents officiels
                </div>
                <div class="section-holder">Fiches bancaires à télécharger ou partager</div>
                <div class="section-line"></div>
            </div>

            <div class="docs-grid">
                <div class="doc-card">
                    <img src="images/site/comptes-gourdes.jpg" alt="Comptes en Gourdes GSCC">
                    <div class="doc-caption">
                        <i class="fas fa-coins htg-icon"></i>
                        Comptes en Gourdes (HTG)
                    </div>
                </div>
                <div class="doc-card">
                    <img src="images/site/comptes-dollars.jpg" alt="Comptes en Dollars GSCC">
                    <div class="doc-caption">
                        <i class="fas fa-dollar-sign usd-icon"></i>
                        Comptes en Dollars US (USD)
                    </div>
                </div>
            </div>
        </div>

        <!-- Notice -->
        <div class="bank-notice">
            <div class="notice-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="notice-text">
                Pour tout virement international, contactez-nous d'abord pour obtenir les informations <strong>SWIFT / BIC</strong> nécessaires.
                Un reçu vous sera envoyé par email dans les <strong>48 heures</strong> suivant réception de votre don.
            </div>
        </div>

        <!-- CTA -->
        <div class="bank-cta">
            <div class="cta-text">
                <p>Une question sur votre virement ?</p>
                <span>Notre équipe répond rapidement.</span>
            </div>
            <div class="cta-btns">
                <a href="https://wa.me/50929474722" target="_blank" rel="noopener" class="cta-btn whatsapp">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="contact.php" class="cta-btn contact">
                    <i class="fas fa-envelope"></i> Nous écrire
                </a>
            </div>
        </div>

    </div>
</div>

<!-- Toast -->
<div id="bankToast" class="bank-toast">
    <i class="fas fa-check-circle"></i>
    <span id="bankToastMsg">Copié !</span>
</div>

<script>
document.querySelectorAll('.copy-btn[data-copy]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var val = this.getAttribute('data-copy');
        var self = this;
        navigator.clipboard.writeText(val).catch(function() {
            var t = document.createElement('textarea');
            t.value = val; document.body.appendChild(t);
            t.select(); document.execCommand('copy');
            document.body.removeChild(t);
        });
        self.classList.add('ok');
        self.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(function() {
            self.classList.remove('ok');
            self.innerHTML = '<i class="fas fa-copy"></i>';
        }, 1800);
        var toast = document.getElementById('bankToast');
        document.getElementById('bankToastMsg').textContent = '"' + val + '" copié.';
        toast.classList.add('show');
        setTimeout(function() { toast.classList.remove('show'); }, 2800);
    });
});

/* Smooth scroll pour les tabs */
document.querySelectorAll('.currency-tab').forEach(function(tab) {
    tab.addEventListener('click', function(e) {
        var target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>
