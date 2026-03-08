<?php
// admin/articles/upload.php
require_once '../includes/admin_auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    echo json_encode(['error' => 'Requête invalide']);
    exit;
}

$file = $_FILES['file'];

$upload = uploadFile($file, UPLOADS_PATH, ['jpg', 'jpeg', 'png', 'gif']);

if ($upload['success']) {
    echo json_encode([
        'location' => UPLOADS_URL . $upload['filename']
    ]);
} else {
    echo json_encode(['error' => $upload['error']]);
}
?>