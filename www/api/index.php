<?php

require __DIR__ . '/config.php';

// Normalize a passphrase for fuzzy comparison:
// lowercase, strip Czech/Slovak diacritics, collapse/trim whitespace.
function normalize_code(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $s = str_replace(
        ['á','č','ď','é','ě','í','ň','ó','ř','š','ť','ú','ů','ý','ž'],
        ['a','c','d','e','e','i','n','o','r','s','t','u','u','y','z'],
        $s
    );
    $s = preg_replace('/[^\p{L}\p{N}]/u', '', $s);  // strip punctuation, spaces, everything non-alphanumeric
    return $s;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($body['code'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON or missing code field']);
    exit;
}

if (normalize_code((string)$body['code']) !== normalize_code(VALID_CODE)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid code']);
    exit;
}

if (!file_exists(MP3_PATH)) {
    http_response_code(500);
    echo json_encode(['error' => 'Audio file not found']);
    exit;
}

header('Content-Type: audio/mpeg');
header('Content-Length: ' . filesize(MP3_PATH));
header('Content-Disposition: attachment; filename="audio.mp3"');
readfile(MP3_PATH);
