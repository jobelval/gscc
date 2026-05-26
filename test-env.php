<?php
require_once 'includes/config.php';
echo '<pre>';
echo 'SITE_URL = ' . SITE_URL . "\n";
echo 'DB_NAME  = ' . DB_NAME  . "\n";
echo '.env lu  = ' . (getenv('SITE_URL') ? 'OUI' : 'NON') . "\n";
echo '</pre>';
