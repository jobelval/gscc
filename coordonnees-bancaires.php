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
.bank-page {
    padding: 56px 0 72px;
    background: #f5f7fa;
}
.bank-page .container {
    max-width: 760px;
    margin: 0 auto;
}
.bank-page-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.9rem;
    font-weight: 700;
    color: #1E2A35;
    margin-bottom: 6px;
    text-align: center;
}
.bank-page-sub {
    font-size: 0.97rem;
    color: #6B7A8D;
    margin-bottom: 36px;
    max-width: 520px;
    line-height: 1.7;
    text-align: center;
    margin-left: auto;
    margin-right: auto;
}

/* Tables */
.bank-section { margin-bottom: 36px; }
.bank-section-title {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: #003399;
    border-bottom: 2px solid #003399;
    padding-bottom: 8px;
    margin-bottom: 0;
    text-align: center;
}
.bank-holder {
    font-size: 0.82rem;
    color: #6B7A8D;
    padding: 8px 0 0 2px;
    margin-bottom: 0;
    text-align: center;
}
.bank-holder strong { color: #1E2A35; }

table.bank-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #E2E6EA;
    margin-top: 10px;
    font-size: 0.92rem;
}
table.bank-table td {
    padding: 13px 16px;
    border-bottom: 1px solid #F0F2F5;
    vertical-align: middle;
    color: #1E2A35;
    text-align: center;
}
table.bank-table tr:last-child td { border-bottom: none; }
table.bank-table tr:hover td { background: #FAFBFF; }
table.bank-table td:first-child {
    width: 180px;
    font-weight: 600;
    color: #3A4A5A;
    text-align: center;
}
table.bank-table td:last-child {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.93rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    color: #1E2A35;
    text-align: center;
}
table.bank-table td.is-email {
    font-family: inherit;
    color: #6D1ED4;
    font-weight: 600;
    font-size: 0.9rem;
}
.bank-copy-btn {
    background: none;
    border: 1px solid #E2E6EA;
    border-radius: 5px;
    padding: 4px 8px;
    cursor: pointer;
    color: #6B7A8D;
    font-size: 11px;
    margin-left: 10px;
    transition: all .18s;
    vertical-align: middle;
}
.bank-copy-btn:hover { background: #EEF2FF; color: #003399; border-color: #C7D7FF; }
.bank-copy-btn.ok    { background: #DCFCE7; color: #16A34A; border-color: #86EFAC; }

/* Séparateur second titulaire */
.bank-sep-row td {
    padding: 8px 16px 4px !important;
    background: #F9FAFB;
    font-size: 0.79rem;
    color: #6B7A8D;
    border-top: 1px dashed #E2E6EA;
    font-weight: 400;
    text-align: center;
}
.bank-sep-row td strong { color: #1E2A35; }

/* Note en bas */
.bank-notice {
    margin-top: 28px;
    background: #fff;
    border: 1px solid #E2E6EA;
    border-left: 3px solid #003399;
    border-radius: 8px;
    padding: 16px 20px;
    font-size: 0.88rem;
    color: #3A4A5A;
    line-height: 1.75;
}
.bank-notice strong { color: #003399; }

/* Contact */
.bank-contact {
    margin-top: 28px;
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}
.bank-contact p {
    font-size: 0.88rem;
    color: #6B7A8D;
    margin: 0;
}
.bank-contact a {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 7px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .18s;
    white-space: nowrap;
}
.bank-contact a.primary {
    background: #003399;
    color: #fff;
}
.bank-contact a.primary:hover { background: #002277; color: #fff; }
.bank-contact a.secondary {
    border: 1.5px solid #003399;
    color: #003399;
}
.bank-contact a.secondary:hover { background: #EEF2FF; }

/* Images officielles */
.bank-images-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 10px;
}
.bank-image-card {
    border: 1px solid #E2E6EA;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}
.bank-image {
    width: 100%;
    display: block;
    object-fit: cover;
}
.bank-image-caption {
    padding: 10px 14px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #3A4A5A;
    border-top: 1px solid #F0F2F5;
    background: #F9FAFB;
    display: flex;
    align-items: center;
    gap: 7px;
}
.bank-image-caption i { color: #003399; font-size: 13px; }

@media (max-width: 640px) {
    .bank-images-grid { grid-template-columns: 1fr; }
}

/* Toast */
.bank-toast {
    position: fixed;
    bottom: 22px; left: 50%;
    transform: translateX(-50%) translateY(50px);
    background: #1E2A35; color: #fff;
    padding: 9px 18px; border-radius: 7px;
    font-size: 13px; font-weight: 500;
    display: flex; align-items: center; gap: 8px;
    z-index: 9999; opacity: 0;
    transition: all .25s ease;
    pointer-events: none;
}
.bank-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.bank-toast i { color: #4CAF50; }

@media (max-width: 640px) {
    table.bank-table td:first-child { width: 120px; }
    .bank-contact { flex-direction: column; align-items: flex-start; }
}
</style>

<div class="bank-page">
    <div class="container">

        <h1 class="bank-page-title">Coordonnées bancaires</h1>
        <p class="bank-page-sub">
            Pour effectuer un don par virement, utilisez l'un des comptes ci-dessous.
            Mentionnez <strong>« Don GSCC »</strong> dans le motif du virement.
        </p>

        <!-- ══ GOURDES ══ -->
        <div class="bank-section">
            <div class="bank-section-title">Comptes en Gourdes — HTG</div>
            <p class="bank-holder"><strong>Titulaire :</strong> Groupe de Support Contre le Cancer</p>

            <table class="bank-table">
                <tbody>
                    <tr>
                        <td>Banque de l'Union Haïtienne</td>
                        <td>#5500 0000565 <button class="bank-copy-btn" data-copy="5500 0000565"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Capital Bank</td>
                        <td>#1909973 <button class="bank-copy-btn" data-copy="1909973"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Sogebank</td>
                        <td>#2606047476 <button class="bank-copy-btn" data-copy="2606047476"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Unibank</td>
                        <td>#103-1021-01016229 <button class="bank-copy-btn" data-copy="103-1021-01016229"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>MonCash</td>
                        <td>+509 2947-4722 <button class="bank-copy-btn" data-copy="+50929474722"><i class="fas fa-copy"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ══ DOLLARS ══ -->
        <div class="bank-section">
            <div class="bank-section-title">Comptes en Dollars US — USD</div>
            <p class="bank-holder"><strong>Titulaire :</strong> Groupe de Support Contre le Cancer</p>

            <table class="bank-table">
                <tbody>
                    <tr>
                        <td>Unibank</td>
                        <td>#103-1022-01016237 <button class="bank-copy-btn" data-copy="103-1022-01016237"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Capital Bank</td>
                        <td>#1909974 <button class="bank-copy-btn" data-copy="1909974"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Sogebank</td>
                        <td>#2616029373 <button class="bank-copy-btn" data-copy="2616029373"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Sogebel</td>
                        <td>#1530101961 <button class="bank-copy-btn" data-copy="1530101961"><i class="fas fa-copy"></i></button></td>
                    </tr>

                    <!-- Second titulaire -->
                    <tr class="bank-sep-row">
                        <td colspan="2"><strong>Titulaire :</strong> Groupe de Support Contre le Cancer Inc</td>
                    </tr>
                    <tr>
                        <td>Citibank</td>
                        <td>#3290440516 <button class="bank-copy-btn" data-copy="3290440516"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Zelle</td>
                        <td class="is-email">gsccht1999@gmail.com <button class="bank-copy-btn" data-copy="gsccht1999@gmail.com"><i class="fas fa-copy"></i></button></td>
                    </tr>
                    <tr>
                        <td>Paiement par QR code</td>
                        <td><i class="fas fa-qrcode" style="color:#003399;margin-right:7px;"></i>Disponible sur nos affiches et lors de nos événements</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ══ VISUELS OFFICIELS ══ -->
        <div class="bank-section">
            <div class="bank-section-title">Documents officiels</div>
            <p class="bank-holder" style="margin-bottom:14px;">
                Retrouvez ci-dessous nos fiches bancaires officielles — à télécharger ou à partager.
            </p>
            <div class="bank-images-grid">
                <div class="bank-image-card">
                    <img src="images/comptes-gourdes.jpg"
                         alt="Comptes bancaires en Gourdes — GSCC"
                         class="bank-image">
                    <div class="bank-image-caption">
                        <i class="fas fa-file-image"></i>
                        Comptes en Gourdes (HTG)
                    </div>
                </div>
                <div class="bank-image-card">
                    <img src="images/comptes-dollars.jpg"
                         alt="Comptes bancaires en Dollars US — GSCC"
                         class="bank-image">
                    <div class="bank-image-caption">
                        <i class="fas fa-file-image"></i>
                        Comptes en Dollars US (USD)
                    </div>
                </div>
            </div>
        </div>

        <!-- Notice -->
        <div class="bank-notice">
            <i class="fas fa-info-circle" style="color:#003399;margin-right:7px;"></i>
            Pour tout virement international, contactez-nous d'abord pour obtenir les informations <strong>SWIFT/BIC</strong> nécessaires.
            Un reçu vous sera envoyé par email dans les <strong>48 heures</strong> suivant réception de votre don.
        </div>

        <!-- Contact -->
        <div class="bank-contact">
            <p>Une question ? Notre équipe est disponible :</p>
            <a href="https://wa.me/50929474722" target="_blank" rel="noopener" class="primary">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a href="contact.php" class="secondary">
                <i class="fas fa-envelope"></i> Nous écrire
            </a>
        </div>

    </div>
</div>

<!-- Toast -->
<div id="bankToast" class="bank-toast">
    <i class="fas fa-check-circle"></i>
    <span id="bankToastMsg">Copié !</span>
</div>

<script>
document.querySelectorAll('.bank-copy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var val = this.getAttribute('data-copy');
        navigator.clipboard.writeText(val).catch(function() {
            var t = document.createElement('textarea');
            t.value = val; document.body.appendChild(t);
            t.select(); document.execCommand('copy');
            document.body.removeChild(t);
        });
        btn.classList.add('ok');
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(function() {
            btn.classList.remove('ok');
            btn.innerHTML = '<i class="fas fa-copy"></i>';
        }, 1800);
        var toast = document.getElementById('bankToast');
        document.getElementById('bankToastMsg').textContent = '"' + val + '" copié.';
        toast.classList.add('show');
        setTimeout(function() { toast.classList.remove('show'); }, 2500);
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>