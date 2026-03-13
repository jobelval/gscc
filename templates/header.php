<?php
// templates/header.php
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?><?= SITE_NAME ?></title>
    <meta name="description" content="<?= isset($page_description) ? e($page_description) : 'GSCC - Groupe de Support Contre le Cancer' ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= isset($page_title) ? e($page_title) : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($page_description) ? e($page_description) : 'Ensemble contre le cancer en Haïti' ?>">
    <meta property="og:image" content="<?= IMAGES_URL ?>og-image.jpg">
    <meta property="og:url" content="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">

    <!-- CSS principal -->
    <link rel="stylesheet" href="<?= CSS_URL ?>style.css">

    <!-- AOS & Swiper -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

    <style>
        /* ================================================
       VARIABLES
       ================================================ */
        :root {
            --rose: #D94F7A;
            --rose-light: #F2A8C0;
            --rose-pale: #FDE8EF;
            --teal: #2A7F7F;
            --warm-white: #FDFAF8;
            --charcoal: #1E2A35;
            --grey: #6B7A8D;
            --grey-light: #E8ECF0;
        }

        /* ================================================
       BODY PADDING pour compenser le header fixe
       ================================================ */
        body {
            padding-top: 121px;
            /* 44px (top-bar) + 74px (header) + 3px (accent) */
            margin: 0;
        }

        /* ================================================
       TOP BAR — fixe en haut
       ================================================ */
        .top-bar {
            background: var(--charcoal);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            padding: 9px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 101;
        }

        .top-bar .container {
            max-width: 100%;
            padding: 0 16px;
            margin: 0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 14px;
        }

        /* Recherche */
        .search-bar form {
            display: flex;
        }

        .search-bar input {
            padding: 7px 15px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-right: none;
            border-radius: 20px 0 0 20px;
            color: white;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            width: 190px;
            transition: background 0.2s;
        }

        .search-bar input:focus {
            background: rgba(255, 255, 255, 0.13);
        }

        .search-bar input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .search-bar button {
            padding: 7px 14px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-left: none;
            border-radius: 0 20px 20px 0;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }

        .search-bar button:hover {
            background: var(--rose);
            color: white;
        }

        /* Séparateur */
        .top-bar-sep {
            width: 1px;
            height: 18px;
            background: rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }

        /* ── Termes non traduisibles ── */
        .notranslate {
            unicode-bidi: plaintext;
        }

        /* ── Sélecteur de langue ── */
        .language-selector {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .lang-sep {
            color: rgba(255, 255, 255, 0.2);
            font-size: 13px;
            padding: 0 2px;
        }

        .lang-btn {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.5);
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            letter-spacing: 0.3px;
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.9);
        }

        .lang-btn.active {
            background: var(--rose);
            border-color: var(--rose);
            color: #fff;
        }

        .lang-flag {
            font-size: 14px;
            line-height: 1;
        }

        /* Cacher la barre Google Translate */
        .goog-te-banner-frame,
        .goog-te-balloon-frame {
            display: none !important;
        }

        body {
            top: 0 !important;
        }

        .skiptranslate {
            display: none !important;
        }

        /* Bouton connexion */
        .login-btn {
            background: var(--rose) !important;
            color: white !important;
            padding: 7px 18px !important;
            border-radius: 20px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 7px !important;
            text-decoration: none !important;
            transition: background 0.2s !important;
            white-space: nowrap;
            margin-right: 0 !important;
        }

        .login-btn:hover {
            background: #C0306A !important;
            color: white !important;
        }

        /* Dropdown utilisateur */
        .user-menu {
            position: relative;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            min-width: 210px;
            padding: 8px;
            border: 1px solid var(--grey-light);
            z-index: 999;
        }

        .user-menu.open .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            display: flex !important;
            align-items: center;
            gap: 10px;
            padding: 10px 14px !important;
            border-radius: 8px;
            font-size: 15px !important;
            color: var(--charcoal) !important;
            text-decoration: none;
            transition: background 0.15s !important;
        }

        .user-dropdown a:hover {
            background: var(--rose-pale) !important;
            color: var(--rose) !important;
        }

        .user-dropdown i {
            width: 16px;
            color: var(--grey);
        }

        /* ================================================
       HEADER PRINCIPAL — fixe sous la top-bar
       ================================================ */
        .main-header {
            background: var(--warm-white);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            position: fixed;
            top: 44px;
            left: 0;
            width: 100%;
            z-index: 100;
            transition: box-shadow 0.3s;
            font-family: 'Montserrat', sans-serif;
        }

        .main-header .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .header-content {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            column-gap: 24px;
            padding: 16px 0;
        }

        /* ── LOGO ─────────────────────────────────────── */
        .logo a {
            display: flex;
            align-items: center;
            gap: 18px;
            /* ogmante espas ant logo ak tèks */
            text-decoration: none;
        }

        .logo-icon {
            width: 85px;
            /* Pi gwo */
            height: 85px;
            /* Pi gwo */
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* IMAJ LOGO A */
        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            /* pou logo a pa defòme */
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
        }

        .logo-name {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            /* Ti ogmantasyon */
            font-weight: 700;
            color: var(--charcoal);
            line-height: 1;
            font-family: 'Montserrat', sans-serif;
        }

        .logo-slogan {
            font-size: 14px;
            /* ti ogmantasyon */
            color: var(--grey);
            font-weight: 400;
            margin-top: 6px;
            white-space: nowrap;
        }

        /* ── NAV ──────────────────────────────────────── */
        .main-nav {
            display: flex;
            justify-content: center;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            align-items: center;
            padding: 0;
            margin: 0;
            gap: 4px;
        }

        .menu-item>a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            font-size: 15.5px;
            font-weight: 500;
            color: var(--charcoal);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .menu-item>a:hover,
        .menu-item.active>a {
            color: var(--rose);
            background: var(--rose-pale);
        }

        .menu-item>a .fa-chevron-down {
            font-size: 11px;
            opacity: 0.45;
            transition: transform 0.2s;
        }

        .menu-item.open>a .fa-chevron-down {
            transform: rotate(180deg);
        }

        /* Dropdown */
        .menu-item {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.14);
            list-style: none;
            min-width: 240px;
            padding: 8px;
            border: 1px solid var(--grey-light);
            z-index: 500;
        }

        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 10px;
        }

        .menu-item.open>.dropdown-menu {
            display: block;
        }

        .dropdown-menu li a {
            display: block;
            padding: 12px 18px;
            font-size: 15px;
            font-weight: 500;
            color: var(--charcoal);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.15s;
        }

        .dropdown-menu li a:hover {
            background: var(--rose-pale);
            color: var(--rose);
            padding-left: 22px;
        }

        /* ── BOUTON DON ───────────────────────────────── */
        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-donate {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: linear-gradient(135deg, var(--rose), #C0306A);
            color: white;
            padding: 14px 30px;
            border-radius: 30px;
            font-size: 17px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 4px 16px rgba(217, 79, 122, 0.35);
            transition: all 0.2s;
        }

        .btn-donate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 22px rgba(217, 79, 122, 0.5);
            color: white;
        }

        /* Ligne accent — fixe sous le header */
        .header-accent {
            height: 3px;
            background: linear-gradient(90deg, var(--rose), var(--teal), var(--rose-light));
            position: fixed;
            top: 118px;
            /* 44px + 74px (hauteur approximative du header) */
            left: 0;
            width: 100%;
            z-index: 99;
        }

        /* Burger mobile */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: 1.5px solid var(--grey-light);
            border-radius: 8px;
            cursor: pointer;
            padding: 9px 11px;
        }

        .mobile-menu-toggle span {
            display: block;
            width: 23px;
            height: 2px;
            background: var(--charcoal);
            border-radius: 2px;
            transition: all 0.3s;
        }

        /* Bouton fermer menu mobile */
        .mob-close-btn {
            display: none;
            position: fixed;
            top: 130px;
            right: 20px;
            z-index: 9999999;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--rose);
            color: #fff;
            border: none;
            font-size: 18px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
            transition: background 0.2s;
        }
        .mob-close-btn:hover { background: #C0306A; }
        .mob-close-btn.visible { display: flex; }
        @media (max-width: 768px) {
            .mob-close-btn { top: 120px; }
        }
        @media (max-width: 480px) {
            .mob-close-btn { top: 110px; }
        }

        /* Flash */
        .flash-message {
            position: fixed;
            top: 140px;
            /* Ajusté pour être sous le header fixe */
            right: 20px;
            padding: 14px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            animation: gsccSlideIn 0.3s ease;
        }

        .flash-success {
            background: #2A7F7F;
            color: white;
        }

        .flash-error {
            background: #DC2626;
            color: white;
        }

        .flash-message button {
            background: none;
            border: none;
            color: white;
            margin-left: 10px;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            opacity: 0.8;
        }

        .flash-message button:hover {
            opacity: 1;
        }

        @keyframes gsccSlideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 1280px) {
            .main-header .container {
                padding: 0 24px;
            }

            .menu-item>a {
                padding: 10px 12px;
                font-size: 15px;
            }

            .btn-donate {
                padding: 11px 20px;
                font-size: 15px;
            }
        }

        @media (max-width: 1100px) {
            .logo-name {
                font-size: 26px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
            }

            .menu-item>a {
                padding: 9px 10px;
                font-size: 14px;
            }

            .btn-donate {
                padding: 10px 16px;
                font-size: 14px;
            }

            .header-content {
                column-gap: 18px;
            }
        }

        @media (max-width: 960px) {
            body {
                padding-top: 115px;
            }

            .main-nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .header-content {
                grid-template-columns: 1fr auto;
                gap: 12px;
                padding: 12px 0;
            }

            .header-accent {
                top: 112px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 107px;
            }

            .top-bar .container {
                padding: 0 12px;
                gap: 8px;
            }

            .search-bar input {
                width: 120px;
            }

            .logo-slogan {
                display: none;
            }

            .main-header .container {
                padding: 0 14px;
            }

            .header-content {
                padding: 10px 0;
            }

            .logo-icon {
                width: 52px;
                height: 52px;
            }

            .logo-name {
                font-size: 24px;
            }

            .btn-donate {
                padding: 9px 14px;
                font-size: 13px;
            }

            .header-accent {
                top: 104px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding-top: 100px;
            }

            .search-bar {
                display: none;
            }

            .lang-flag {
                display: none;
            }

            .lang-btn {
                padding: 4px 8px;
                font-size: 12px;
            }

            .logo-icon {
                width: 44px;
                height: 44px;
            }

            .logo-name {
                font-size: 20px;
            }

            .btn-donate {
                padding: 8px 12px;
                font-size: 12px;
            }

            .header-accent {
                top: 97px;
            }
        }
    </style>

    <!-- ── Google Translate API ── -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'fr',
                includedLanguages: 'fr,en',
                autoDisplay: false,
                gaTrack: false
            }, 'google_translate_element');
        }

        /* ── Protéger les termes GSCC contre la traduction ── */
        function gsccProtectTerms() {
            // Sélectionner tous les noeuds texte dans le body
            var walker = document.createTreeWalker(
                document.body,
                NodeFilter.SHOW_TEXT, {
                    acceptNode: function(node) {
                        // Ignorer les scripts, styles, l'élément Google Translate
                        var p = node.parentElement;
                        if (!p) return NodeFilter.FILTER_REJECT;
                        var tag = p.tagName.toLowerCase();
                        if (['script', 'style', 'noscript', 'iframe'].includes(tag)) return NodeFilter.FILTER_REJECT;
                        if (p.closest('#google_translate_element')) return NodeFilter.FILTER_REJECT;
                        // Seulement les noeuds qui contiennent nos termes
                        if (/(GSCC|Groupe de Support Contre le Cancer)/i.test(node.textContent))
                            return NodeFilter.FILTER_ACCEPT;
                        return NodeFilter.FILTER_SKIP;
                    }
                }
            );
            var nodes = [];
            while (walker.nextNode()) nodes.push(walker.currentNode);

            nodes.forEach(function(node) {
                // Remplacer GSCC et la phrase complète par un <span translate="no">
                var parent = node.parentElement;
                if (parent && parent.getAttribute('translate') === 'no') return; // déjà protégé
                var html = node.textContent
                    .replace(/(Groupe de Support Contre le Cancer)/g,
                        '<span class="notranslate" translate="no">$1</span>')
                    .replace(/GSCC/g,
                        '<span class="notranslate" translate="no">GSCC</span>');
                if (html !== node.textContent) {
                    var span = document.createElement('span');
                    span.innerHTML = html;
                    parent.replaceChild(span, node);
                }
            });
        }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <script>
        /* ── Contrôle FR / EN ── */
        function gsccSetLang(lang) {
            // Activer le bon bouton
            document.getElementById('btn-fr').classList.toggle('active', lang === 'fr');
            document.getElementById('btn-en').classList.toggle('active', lang === 'en');

            // Mémoriser le choix
            localStorage.setItem('gscc_lang', lang);

            if (lang === 'fr') {
                // Restaurer la langue d'origine
                var restore = document.querySelector('.goog-te-menu-value');
                if (restore) restore.click();

                var sel = document.querySelector('select.goog-te-combo');
                if (sel) {
                    sel.value = 'fr';
                    sel.dispatchEvent(new Event('change'));
                }
                // Fallback : recharger sans cookie de traduction
                var ck = document.cookie.split(';');
                for (var i = 0; i < ck.length; i++) {
                    if (ck[i].trim().indexOf('googtrans') === 0) {
                        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + location.hostname + ';';
                        break;
                    }
                }
                window.location.reload();
            } else {
                // Passer en anglais via le select caché de Google
                var tryTranslate = function(attempt) {
                    var sel = document.querySelector('select.goog-te-combo');
                    if (sel) {
                        sel.value = 'en';
                        sel.dispatchEvent(new Event('change'));
                    } else if (attempt < 20) {
                        setTimeout(function() {
                            tryTranslate(attempt + 1);
                        }, 250);
                    }
                };
                tryTranslate(0);
            }
        }

        /* ── Restaurer le choix mémorisé au chargement ── */
        document.addEventListener('DOMContentLoaded', function() {
            // Protéger les termes GSCC au chargement
            gsccProtectTerms();
            // Re-protéger après chaque traduction (Google modifie le DOM)
            var observer = new MutationObserver(function(mutations) {
                var relevant = mutations.some(function(m) {
                    return m.target.id !== 'google_translate_element' &&
                        !m.target.closest('#google_translate_element');
                });
                if (relevant) {
                    clearTimeout(window._gsccProtectTimer);
                    window._gsccProtectTimer = setTimeout(gsccProtectTerms, 300);
                }
            });
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                characterData: true
            });
            var saved = localStorage.getItem('gscc_lang');
            var hasTrans = document.cookie.indexOf('googtrans') !== -1;

            if (saved === 'en' || hasTrans) {
                document.getElementById('btn-fr').classList.remove('active');
                document.getElementById('btn-en').classList.add('active');
                if (!hasTrans) {
                    // Déclencher la traduction
                    var tryT = function(n) {
                        var s = document.querySelector('select.goog-te-combo');
                        if (s) {
                            s.value = 'en';
                            s.dispatchEvent(new Event('change'));
                        } else if (n < 20) setTimeout(function() {
                            tryT(n + 1);
                        }, 250);
                    };
                    tryT(0);
                }
            } else {
                document.getElementById('btn-fr').classList.add('active');
                document.getElementById('btn-en').classList.remove('active');
            }
        });
    </script>
</head>

<body>

    <!-- ════════════════════════════════════════
         TOP BAR — fixe en haut
         ════════════════════════════════════════ -->
    <div class="top-bar">
        <div class="container">

            <div class="search-bar">
                <form action="recherche.php" method="GET">
                    <input type="text" name="q" placeholder="Rechercher...">
                    <button type="submit" aria-label="Rechercher"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="top-bar-sep"></div>

            <!-- Sélecteur de langue Google Translate -->
            <div class="language-selector" id="lang-switcher">
                <button class="lang-btn active" id="btn-fr" onclick="gsccSetLang('fr')" title="Français">
                    <span class="lang-flag">🇫🇷</span> FR
                </button>
                <span class="lang-sep">|</span>
                <button class="lang-btn" id="btn-en" onclick="gsccSetLang('en')" title="English">
                    <span class="lang-flag">🇺🇸</span> EN
                </button>
            </div>
            <!-- Google Translate (invisible) -->
            <div id="google_translate_element" style="display:none;"></div>

            <div class="top-bar-sep"></div>

            <?php if (isLoggedIn()): ?>
                <div class="user-menu" id="userMenu">
                    <button type="button" class="login-btn" id="userMenuBtn" style="border:none;cursor:pointer;background:var(--rose);">
                        <i class="fas fa-user-circle"></i>
                        <?= e($_SESSION['user_prenom'] ?? 'Mon compte') ?>
                        <i class="fas fa-chevron-down" style="font-size:10px;opacity:0.6;"></i>
                    </button>
                    <div class="user-dropdown">
                        <a href="mon-compte.php"><i class="fas fa-user-circle"></i> Mon profil</a>
                        <!-- <a href="mes-dons.php"><i class="fas fa-heart"></i> Mes dons</a>
                        <a href="mes-demandes.php"><i class="fas fa-hand-holding-heart"></i> Mes demandes</a>
                        <a href="mes-inscriptions.php"><i class="fas fa-calendar-check"></i> Mes événements</a> -->
                        <?php if (isAdmin() || isModerator()): ?>
                            <a href="admin/index.php"><i class="fas fa-tachometer-alt"></i> Administration</a>
                        <?php endif; ?>
                        <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="login-btn">
                    <i class="fas fa-user"></i> Connexion
                </a>
            <?php endif; ?>

        </div>
    </div>

    <!-- ════════════════════════════════════════════════
         HEADER — fixe sous top-bar
         ════════════════════════════════════════════════ -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">

                <!-- LOGO -->
                <div class="logo">
                    <a href="index.php" aria-label="Retour à l'accueil GSCC">

                        <div class="logo-icon">
                            <img src="images/image2.png" alt="Logo GSCC" class="logo-img">
                        </div>

                        <div class="logo-text">
                            <span class="logo-name" translate="no">GSCC</span>
                            <span class="logo-slogan" translate="no">Groupe de Support Contre le Cancer</span>
                        </div>

                    </a>
                </div>

                <!-- NAVIGATION -->
                <nav class="main-nav" role="navigation" aria-label="Navigation principale">
                    <ul class="nav-menu">

                        <li class="menu-item has-dropdown">
                            <a href="#"> Présentation<i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="presentation.php#propos">À propos du GSCC</a></li>
                                <li><a href="presentation.php#mission">Mission & Vision</a></li>
                                <!-- <li><a href="presentation.php#vision">Vision</a></li> -->
                                <li><a href="presentation.php#historique">Historique</a></li>
                                <li><a href="presentation.php#equipe">Équipe</a></li>
                                <li><a href="presentation.php#valeurs">Valeurs & engagements</a></li>
                            </ul>
                        </li>

                        <li class="menu-item has-dropdown">
                            <a href="#">Agissons maintenant <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="partenaires.php">Partenaires</a></li>
                                <li><a href="devenir-membre.php">Devenir membre</a></li>
                                <li><a href="temoignages.php">Témoignages</a></li>
                                <li><a href="benevolat.php">Devenir bénévole</a></li>
                            </ul>
                        </li>

                        <li class="menu-item has-dropdown">
                            <a href="#">Soins et soutien <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="survivants.php">Nos survivants</a></li>
                                <li><a href="ressources.php">S'informer et comprendre</a></li>
                                <li><a href="demande-aide.php">Accompagnement personnalisé</a></li>
                            </ul>
                        </li>

                        <li class="menu-item has-dropdown">
                            <a href="#">Activité <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="sensibilisation.php">Sensibilisation</a></li>
                                <li><a href="levees-fonds.php">Levées de Fonds</a></li>
                                <li><a href="unir-agir.php">Unir et agir</a></li>
                                <li><a href="foire-annuelle.php">Grande Foire Annuelle</a></li>
                                <li><a href="marche-contre-cancer.php">Marche Contre le Cancer</a></li>
                            </ul>
                        </li>

                        <li class="menu-item has-dropdown">
                            <a href="#">Événements <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="blog.php">Blog</a></li>
                                <li><a href="forum.php">Forum</a></li>
                                <li><a href="campagnes.php">Campagnes & projets</a></li>
                                <li><a href="galerie.php">Galerie</a></li>
                            </ul>
                        </li>

                    </ul>
                </nav>

                <!-- BOUTON DON + BURGER -->
                <div class="header-right">
                    <a href="faire-un-don.php" class="btn-donate">
                        <i class="fas fa-heart"></i> Faire un don
                    </a>
                    <button class="mobile-menu-toggle" aria-label="Ouvrir le menu">
                        <span></span><span></span><span></span>
                    </button>
                </div>

            </div>
        </div>
    </header>
    <div class="header-accent"></div>
    <button class="mob-close-btn" id="mobCloseBtn" aria-label="Fermer le menu">&#x2715;</button>

    <!-- Flash Messages -->
    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
        <div class="flash-message flash-<?= $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= e($flash['message']) ?>
            <button onclick="this.parentElement.remove()" aria-label="Fermer">&times;</button>
        </div>
        <script>
            setTimeout(function() {
                var msg = document.querySelector('.flash-message');
                if (msg) msg.remove();
            }, 5000);
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── Dropdown utilisateur (clic) ───────────── */
            var userMenuBtn = document.getElementById('userMenuBtn');
            var userMenu = document.getElementById('userMenu');
            if (userMenuBtn && userMenu) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('open');
                });
                document.addEventListener('click', function(e) {
                    if (!userMenu.contains(e.target)) {
                        userMenu.classList.remove('open');
                    }
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') userMenu.classList.remove('open');
                });
            }

            /* ── Dropdowns ─────────────────────────────── */
            var dropdownItems = document.querySelectorAll('.menu-item.has-dropdown');
            dropdownItems.forEach(function(item) {
                var t = null;
                item.addEventListener('mouseenter', function() {
                    clearTimeout(t);
                    dropdownItems.forEach(function(o) {
                        if (o !== item) o.classList.remove('open');
                    });
                    item.classList.add('open');
                });
                item.addEventListener('mouseleave', function() {
                    t = setTimeout(function() {
                        item.classList.remove('open');
                    }, 180);
                });
            });
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.menu-item.has-dropdown'))
                    dropdownItems.forEach(function(i) {
                        i.classList.remove('open');
                    });
            });

            /* ── Burger mobile ── */
            var burger = document.querySelector('.mobile-menu-toggle');
            var nav = document.querySelector('.main-nav');
            if (burger && nav) {

                var closeBtn = document.getElementById('mobCloseBtn');

                function openMob() {
                    /* Panneau principal */
                    nav.style.cssText = [
                        'display:block',
                        'position:fixed',
                        'top:0','left:0',
                        'width:100%','height:100%',
                        'background:#FDFAF8',
                        'z-index:999999',
                        'overflow-y:auto',
                        'padding:130px 20px 60px',
                        'box-sizing:border-box'
                    ].join(';');

                    /* Liste des items */
                    var menu = nav.querySelector('.nav-menu');
                    menu.style.cssText = [
                        'display:flex','flex-direction:column',
                        'align-items:stretch','width:100%',
                        'padding:0','margin:0',
                        'list-style:none','gap:4px'
                    ].join(';');

                    /* Style de chaque item principal (mobile uniquement) */
                    nav.querySelectorAll('.menu-item > a').forEach(function(a) {
                        a.style.cssText = [
                            'display:flex','justify-content:space-between',
                            'align-items:center',
                            'padding:14px 18px',
                            'font-size:16px','font-weight:600',
                            'color:#1E2A35','text-decoration:none',
                            'border-radius:10px','background:transparent',
                            'border-bottom:1px solid #E8ECF0'
                        ].join(';');
                    });

                    document.body.style.overflow = 'hidden';
                    burger.style.display = 'none';
                    if (closeBtn) closeBtn.classList.add('visible');
                }

                function closeMob() {
                    /* Effacer TOUS les styles inline ajoutés par openMob */
                    nav.removeAttribute('style');
                    var menu = nav.querySelector('.nav-menu');
                    if (menu) menu.removeAttribute('style');
                    nav.querySelectorAll('.menu-item').forEach(function(i) {
                        i.removeAttribute('style');
                    });
                    nav.querySelectorAll('.menu-item > a').forEach(function(a) {
                        a.removeAttribute('style');
                    });
                    nav.querySelectorAll('.dropdown-menu').forEach(function(d) {
                        d.removeAttribute('style');
                    });
                    nav.querySelectorAll('.dropdown-menu a').forEach(function(a) {
                        a.removeAttribute('style');
                    });
                    document.body.style.overflow = '';
                    burger.style.display = '';
                    if (closeBtn) closeBtn.classList.remove('visible');
                }

                burger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    nav.style.display === 'block' ? closeMob() : openMob();
                });

                /* Clic sur item avec dropdown → accordéon */
                nav.querySelectorAll('.menu-item > a').forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        if (nav.style.display !== 'block') return;
                        var item = this.closest('.menu-item');

                        /* Lien sans dropdown → naviguer et fermer */
                        if (!item.classList.contains('has-dropdown')) {
                            closeMob();
                            return;
                        }
                        e.preventDefault();

                        var dd     = item.querySelector('.dropdown-menu');
                        var isOpen = dd.style.display === 'block';

                        /* Fermer tous les autres sous-menus */
                        nav.querySelectorAll('.dropdown-menu').forEach(function(d) {
                            d.style.cssText = 'display:none;position:static;';
                        });
                        nav.querySelectorAll('.menu-item > a').forEach(function(a) {
                            a.style.background = 'transparent';
                            a.style.color      = '#1E2A35';
                        });

                        if (!isOpen) {
                            /* Ouvrir ce sous-menu */
                            dd.style.cssText = [
                                'display:block',
                                'position:static',
                                'background:#FDE8EF',
                                'border:none',
                                'box-shadow:none',
                                'border-radius:10px',
                                'padding:8px 0',
                                'margin:4px 0 8px',
                                'min-width:unset',
                                'width:100%',
                                'list-style:none'
                            ].join(';');

                            /* Styler les liens du sous-menu */
                            dd.querySelectorAll('a').forEach(function(a) {
                                a.style.cssText = [
                                    'display:block',
                                    'padding:12px 24px',
                                    'font-size:15px',
                                    'font-weight:500',
                                    'color:#1E2A35',
                                    'text-decoration:none',
                                    'border-radius:8px',
                                    'background:transparent'
                                ].join(';');
                            });

                            this.style.background = '#FDE8EF';
                            this.style.color      = '#D94F7A';
                        }
                    });
                });

                /* Hover sur liens du sous-menu */
                nav.querySelectorAll('.dropdown-menu a').forEach(function(a) {
                    a.addEventListener('mouseenter', function() {
                        if (nav.style.display === 'block') {
                            this.style.background = 'rgba(217,79,122,0.15)';
                            this.style.color = '#D94F7A';
                        }
                    });
                    a.addEventListener('mouseleave', function() {
                        if (nav.style.display === 'block') {
                            this.style.background = 'transparent';
                            this.style.color = '#1E2A35';
                        }
                    });
                });

                /* Clic sur lien final → fermer */
                nav.querySelectorAll('.dropdown-menu a').forEach(function(a) {
                    a.addEventListener('click', closeMob);
                });

                /* Bouton X */
                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        closeMob();
                    });
                }

                /* Escape */
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') closeMob();
                });
            }

            /* ── Scroll shadow ── */
            window.addEventListener('scroll', function() {
                var h = document.querySelector('.main-header');
                if (h) h.style.boxShadow = window.scrollY > 10 ?
                    '0 4px 24px rgba(0,0,0,0.12)' :
                    '0 2px 20px rgba(0,0,0,0.06)';
            });

            /* ── Ancre avec offset header fixe ── */
            /* Quand la page se charge avec un #hash, le scroll natif
               ne tient pas compte du header fixe (121px).
               On repositionne après chargement complet. */
            if (window.location.hash) {
                window.addEventListener('load', function() {
                    var id  = window.location.hash.substring(1);
                    var el  = document.getElementById(id);
                    if (!el) return;
                    setTimeout(function() {
                        var offset = 160; /* hauteur header + marge */
                        var top    = el.getBoundingClientRect().top + window.pageYOffset - offset;
                        window.scrollTo({ top: top, behavior: 'smooth' });
                    }, 100);
                });
            }
        });
    </script>