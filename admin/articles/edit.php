<?php
/**
 * GSCC CMS — admin/articles/edit.php
 * Redirige vers create.php qui gère les deux modes (création + édition).
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
header('Location: create.php?id=' . $id);
exit;
