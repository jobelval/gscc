<?php
// recherche.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Recherche';
$q       = trim($_GET['q'] ?? '');
$q_safe  = htmlspecialchars($q);
$results = [];
$total   = 0;

if (strlen($q) >= 2) {
    $like = '%' . $q . '%';
    try {
        // Articles publiés
        $stmt = $pdo->prepare(
            "SELECT 'article' AS type, id, titre,
                    COALESCE(resume, SUBSTRING(contenu,1,200)) AS extrait,
                    slug, date_publication AS date_item, image_couverture AS image, NULL AS lieu
             FROM articles WHERE statut='publie'
             AND (titre LIKE ? OR resume LIKE ? OR contenu LIKE ? OR tags LIKE ?)
             ORDER BY date_publication DESC LIMIT 10"
        );
        $stmt->execute([$like, $like, $like, $like]);
        $results['articles'] = $stmt->fetchAll();

        // Événements
        $stmt = $pdo->prepare(
            "SELECT 'evenement' AS type, id, titre, description AS extrait,
                    slug, date_debut AS date_item, image, lieu
             FROM evenements WHERE statut != 'annule'
             AND (titre LIKE ? OR description LIKE ? OR lieu LIKE ?)
             ORDER BY date_debut DESC LIMIT 8"
        );
        $stmt->execute([$like, $like, $like]);
        $results['evenements'] = $stmt->fetchAll();

        // Campagnes & projets
        $stmt = $pdo->prepare(
            "SELECT 'campagne' AS type, id, titre, description AS extrait,
                    slug, date_debut AS date_item, image_couverture AS image, lieu
             FROM campagnes_projets WHERE est_actif=1
             AND (titre LIKE ? OR description LIKE ?)
             ORDER BY date_creation DESC LIMIT 8"
        );
        $stmt->execute([$like, $like]);
        $results['campagnes'] = $stmt->fetchAll();

        // Forum sujets
        $stmt = $pdo->prepare(
            "SELECT 'forum' AS type, s.id, s.titre,
                    SUBSTRING(s.contenu,1,160) AS extrait,
                    NULL AS slug, s.date_creation AS date_item,
                    NULL AS image, c.nom AS lieu
             FROM forum_sujets s
             LEFT JOIN forum_categories c ON s.categorie_id = c.id
             WHERE s.titre LIKE ? OR s.contenu LIKE ?
             ORDER BY s.date_creation DESC LIMIT 8"
        );
        $stmt->execute([$like, $like]);
        $results['forum'] = $stmt->fetchAll();

        // Témoignages
        $stmt = $pdo->prepare(
            "SELECT 'temoignage' AS type, id, nom AS titre,
                    SUBSTRING(temoignage,1,160) AS extrait,
                    NULL AS slug, date_creation AS date_item,
                    NULL AS image, NULL AS lieu
             FROM temoignages WHERE statut='approuve'
             AND (nom LIKE ? OR temoignage LIKE ?)
             ORDER BY date_creation DESC LIMIT 6"
        );
        $stmt->execute([$like, $like]);
        $results['temoignages'] = $stmt->fetchAll();

        foreach ($results as $g) $total += count($g);
    } catch (PDOException $e) {
        logError('recherche.php: ' . $e->getMessage());
    }
}

function typeInfo(array $row): array
{
    switch ($row['type']) {
        case 'article':
            return [
                'icon' => 'fa-newspaper',
                'label' => 'Article',
                'color' => '#003399',
                'href' => 'article.php?slug=' . urlencode($row['slug'] ?? '')
            ];
        case 'evenement':
            return [
                'icon' => 'fa-calendar-alt',
                'label' => 'Événement',
                'color' => '#D94F7A',
                'href' => 'evenement.php?slug=' . urlencode($row['slug'] ?? '')
            ];
        case 'campagne':
            return [
                'icon' => 'fa-bullhorn',
                'label' => 'Campagne',
                'color' => '#2A7F7F',
                'href' => 'campagne.php?slug=' . urlencode($row['slug'] ?? '')
            ];
        case 'forum':
            return [
                'icon' => 'fa-comments',
                'label' => 'Forum',
                'color' => '#7C3AED',
                'href' => 'forum-sujet.php?id=' . (int)$row['id']
            ];
        case 'temoignage':
            return [
                'icon' => 'fa-quote-left',
                'label' => 'Témoignage',
                'color' => '#F59E0B',
                'href' => 'temoignages.php'
            ];
        default:
            return ['icon' => 'fa-file', 'label' => 'Page', 'color' => '#6B7280', 'href' => '#'];
    }
}

function highlight(string $text, string $q): string
{
    if (!$q) return htmlspecialchars($text);
    return preg_replace(
        '/(' . preg_quote(htmlspecialchars($q), '/') . ')/iu',
        '<mark>$1</mark>',
        htmlspecialchars($text)
    );
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $q ? "Résultats pour « $q_safe »" : 'Recherche' ?> — <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --blue: #003399;
            --blue-soft: rgba(0, 51, 153, 0.08);
            --rose: #D94F7A;
            --dark: #1A2240;
            --gray-bg: #F4F6FB;
            --gray-light: #EEF1F8;
            --gray-text: #6B7280;
            --border: #E5E9F2;
            --white: #fff;
            --radius: 12px;
            --shadow: 0 4px 24px rgba(0, 51, 153, 0.08);
        }

        /* Hero */
        .search-hero {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            padding: 56px 0 76px;
            text-align: center;
            position: relative;
            overflow: hidden;
            color: white;
        }

        .search-hero::before {
            content: '';
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            opacity: 0.07;
            background: white;
            top: -140px;
            right: -60px;
            pointer-events: none;
        }

        .search-hero-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            line-height: 0;
        }

        .search-hero-wave svg {
            display: block;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: white;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 18px;
        }

        .search-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 700;
            margin-bottom: 28px;
        }

        .search-hero h1 em {
            font-style: italic;
            color: rgba(255, 255, 255, 0.85);
        }

        /* Champ de recherche */
        .search-form-big {
            display: flex;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .search-form-big input {
            flex: 1;
            padding: 16px 22px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            border: none;
            border-radius: 14px 0 0 14px;
            outline: none;
            color: var(--dark);
            background: #fff;
        }

        .search-form-big button {
            background: var(--rose);
            border: none;
            padding: 16px 26px;
            border-radius: 0 14px 14px 0;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background .2s;
        }

        .search-form-big button:hover {
            background: #C0306A;
        }

        /* Section résultats */
        .results-section {
            padding: 48px 0 80px;
            background: var(--gray-bg);
        }

        .results-summary {
            font-size: 15px;
            color: var(--gray-text);
            margin-bottom: 32px;
        }

        .results-summary strong {
            color: var(--dark);
        }

        .results-summary mark {
            background: rgba(217, 79, 122, 0.12);
            color: var(--rose);
            border-radius: 3px;
            padding: 1px 4px;
            font-style: normal;
        }

        /* Groupes */
        .result-group {
            margin-bottom: 40px;
        }

        .group-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .group-count {
            background: var(--blue-soft);
            color: var(--blue);
            font-size: 11px;
            padding: 2px 9px;
            border-radius: 20px;
            font-weight: 700;
            letter-spacing: 0;
        }

        /* Carte */
        .result-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 20px 24px;
            margin-bottom: 12px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
            text-decoration: none;
            color: inherit;
            transition: transform .22s, box-shadow .22s, border-color .22s;
        }

        .result-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 36px rgba(0, 51, 153, 0.13);
            border-color: rgba(0, 51, 153, 0.18);
        }

        .result-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .result-body {
            flex: 1;
            min-width: 0;
        }

        .result-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 2px 9px;
            border-radius: 20px;
            margin-bottom: 7px;
        }

        .result-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .result-excerpt {
            font-size: 13.5px;
            color: var(--gray-text);
            line-height: 1.65;
            margin-bottom: 8px;
        }

        .result-title mark,
        .result-excerpt mark {
            background: rgba(217, 79, 122, 0.15);
            color: var(--rose);
            border-radius: 3px;
            padding: 0 2px;
        }

        .result-meta {
            font-size: 12px;
            color: var(--gray-text);
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .result-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .result-meta i {
            font-size: 10px;
            color: var(--blue);
        }

        /* États vides */
        .no-results {
            text-align: center;
            padding: 70px 20px;
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .no-results-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--blue-soft);
            color: var(--blue);
            font-size: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .no-results h3 {
            color: var(--dark);
            font-size: 20px;
            margin-bottom: 10px;
        }

        .no-results p {
            color: var(--gray-text);
            font-size: 15px;
            line-height: 1.7;
        }

        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
        }

        .suggestion-tag {
            background: var(--blue-soft);
            color: var(--blue);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background .2s;
        }

        .suggestion-tag:hover {
            background: var(--blue);
            color: white;
        }

        .search-empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .search-empty-state i {
            font-size: 56px;
            color: var(--border);
            display: block;
            margin-bottom: 18px;
        }

        .search-empty-state p {
            color: var(--gray-text);
            font-size: 15px;
        }

        @media(max-width:640px) {
            .result-card {
                padding: 16px;
            }

            .search-hero {
                padding: 44px 0 64px;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Hero -->
    <div class="search-hero">
        <div class="container" style="position:relative;z-index:1;">
            <div class="hero-eyebrow" data-aos="fade-down">
                <i class="fas fa-search"></i> Recherche
            </div>
            <h1 data-aos="fade-up">
                <?= $q ? "Résultats pour « <em>$q_safe</em> »" : 'Que recherchez-vous ?' ?>
            </h1>
            <form class="search-form-big" action="recherche.php" method="GET"
                data-aos="fade-up" data-aos-delay="80">
                <input type="text" name="q" value="<?= $q_safe ?>"
                    placeholder="Articles, événements, forum…"
                    autofocus autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="search-hero-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" />
            </svg>
        </div>
    </div>

    <!-- Résultats -->
    <section class="results-section">
        <div class="container">

            <?php if (strlen($q) === 1): ?>
                <div class="no-results" data-aos="fade-up">
                    <div class="no-results-icon"><i class="fas fa-keyboard"></i></div>
                    <h3>Mot-clé trop court</h3>
                    <p>Veuillez saisir au moins 2 caractères.</p>
                </div>

            <?php elseif ($q === ''): ?>
                <div class="search-empty-state" data-aos="fade-up">
                    <i class="fas fa-search"></i>
                    <p>Saisissez un mot-clé pour rechercher dans les articles,<br>
                        événements, campagnes, forum et témoignages.</p>
                    <div class="suggestions" style="margin-top:24px;">
                        <?php foreach (['dépistage', 'cancer', 'événement', 'soutien', 'marche', 'formation', 'témoignage'] as $s): ?>
                            <a href="recherche.php?q=<?= urlencode($s) ?>" class="suggestion-tag"><?= $s ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ($total === 0): ?>
                <div class="no-results" data-aos="fade-up">
                    <div class="no-results-icon"><i class="fas fa-search-minus"></i></div>
                    <h3>Aucun résultat pour « <?= $q_safe ?> »</h3>
                    <p>Essayez un autre mot-clé ou parcourez nos rubriques.</p>
                    <div class="suggestions">
                        <a href="blog.php" class="suggestion-tag"><i class="fas fa-newspaper"></i> Blog</a>
                        <a href="evenements.php" class="suggestion-tag"><i class="fas fa-calendar"></i> Événements</a>
                        <a href="campagnes.php" class="suggestion-tag"><i class="fas fa-bullhorn"></i> Campagnes</a>
                        <a href="forum.php" class="suggestion-tag"><i class="fas fa-comments"></i> Forum</a>
                    </div>
                </div>

            <?php else: ?>
                <p class="results-summary" data-aos="fade-up">
                    <strong><?= $total ?></strong> résultat<?= $total > 1 ? 's' : '' ?> pour
                    <mark><?= $q_safe ?></mark>
                </p>

                <?php
                $groups = [
                    'articles'    => ['label' => 'Articles',           'icon' => 'fa-newspaper',   'color' => '#003399'],
                    'evenements'  => ['label' => 'Événements',         'icon' => 'fa-calendar-alt', 'color' => '#D94F7A'],
                    'campagnes'   => ['label' => 'Campagnes & projets', 'icon' => 'fa-bullhorn',     'color' => '#2A7F7F'],
                    'forum'       => ['label' => 'Forum',              'icon' => 'fa-comments',     'color' => '#7C3AED'],
                    'temoignages' => ['label' => 'Témoignages',        'icon' => 'fa-quote-left',   'color' => '#F59E0B'],
                ];
                foreach ($groups as $key => $meta):
                    if (empty($results[$key])) continue;
                ?>
                    <div class="result-group" data-aos="fade-up">
                        <div class="group-title" style="color:<?= $meta['color'] ?>;">
                            <i class="fas <?= $meta['icon'] ?>"></i>
                            <?= $meta['label'] ?>
                            <span class="group-count"><?= count($results[$key]) ?></span>
                        </div>

                        <?php foreach ($results[$key] as $row):
                            $info = typeInfo($row);
                        ?>
                            <a href="<?= $info['href'] ?>" class="result-card">
                                <div class="result-icon"
                                    style="background:<?= $info['color'] ?>18;color:<?= $info['color'] ?>;">
                                    <i class="fas <?= $info['icon'] ?>"></i>
                                </div>
                                <div class="result-body">
                                    <div class="result-badge"
                                        style="background:<?= $info['color'] ?>12;color:<?= $info['color'] ?>;">
                                        <i class="fas <?= $info['icon'] ?>"></i> <?= $info['label'] ?>
                                    </div>
                                    <div class="result-title">
                                        <?= highlight($row['titre'] ?? 'Sans titre', $q) ?>
                                    </div>
                                    <?php if (!empty($row['extrait'])): ?>
                                        <div class="result-excerpt">
                                            <?= highlight(mb_strimwidth(strip_tags($row['extrait']), 0, 180, '…'), $q) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="result-meta">
                                        <?php if (!empty($row['date_item'])): ?>
                                            <span><i class="far fa-clock"></i>
                                                <?= date('d/m/Y', strtotime($row['date_item'])) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($row['lieu'])): ?>
                                            <span><i class="fas fa-map-marker-alt"></i>
                                                <?= htmlspecialchars($row['lieu']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span style="color:<?= $info['color'] ?>;margin-left:auto;
                                         font-weight:600;font-size:12px;">
                                            Voir <i class="fas fa-arrow-right" style="font-size:9px;"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 500,
            once: true,
            offset: 40
        });
    </script>
</body>

</html>