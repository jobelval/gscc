<?php
/**
 * GSCC CMS — admin/dashboard.php
 * Alias vers le tableau de bord principal
 */
header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/index.php', true, 301);
exit;
