<?php
// faire-un-don.php — redirige directement vers le lien Stripe
require_once 'includes/config.php';

try {
    $stmt = $pdo->query("SELECT valeur FROM parametres WHERE cle = 'stripe_payment_link'");
    $link = $stmt->fetchColumn() ?: 'https://donate.stripe.com/dRm8wIaG307H2byfrR1Nu00';
} catch (Exception $e) {
    $link = 'https://donate.stripe.com/dRm8wIaG307H2byfrR1Nu00';
}

header('Location: ' . $link);
exit;
