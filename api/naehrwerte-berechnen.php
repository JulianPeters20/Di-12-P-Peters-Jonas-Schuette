<?php
declare(strict_types=1);

// Sichere Headers setzen
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

session_start();
require_once '../php/model/NaehrwerteDAO.php';
require_once '../php/include/form_utils.php';
require_once '../php/include/csrf_protection.php';

// CSRF-Token prüfen
try {
    checkCSRFToken();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF-Token ungültig']);
    exit;
}

// Rezept-ID validieren
$rezeptId = validateId($_POST['rezept_id'] ?? null);
if (!$rezeptId) {
    echo json_encode(['success' => false, 'error' => 'Ungültige Rezept-ID']);
    exit;
}

try {
    $naehrwerteDAO = new NaehrwerteDAO();
    $naehrwerte = $naehrwerteDAO->holeNaehrwerte($rezeptId);
    
    if (!$naehrwerte) {
        echo json_encode(['success' => false, 'error' => 'Keine Nährwerte gefunden']);
        exit;
    }
    
    // HTML-Template generieren
    ob_start();
    include '../php/view/naehrwerte-display.php';
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'naehrwerte' => $naehrwerte
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Fehler beim Laden der Nährwerte']);
}
