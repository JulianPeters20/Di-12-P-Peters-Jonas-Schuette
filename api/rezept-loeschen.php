<?php
declare(strict_types=1);

// Sichere Headers setzen
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

ini_set('display_errors', '0'); // Keine HTML-Warnings
error_reporting(E_ALL);

session_start();
require_once '../php/model/RezeptDAO.php';
require_once '../php/include/csrf_protection.php';
require_once '../php/include/form_utils.php';

// Authentifizierung prüfen
if (empty($_SESSION['nutzerId'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht angemeldet']);
    exit;
}

// CSRF-Token prüfen
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'CSRF-Token ungültig']);
    exit;
}

$nutzerId = $_SESSION['nutzerId'] ?? null;
$istAdmin = $_SESSION['istAdmin'] ?? false;

// Input-Validierung
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || (int)$_POST['id'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Rezept-ID']);
    exit;
}

$id = (int)$_POST['id'];

try {
    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);

    if (!$rezept) {
        echo json_encode(['success' => false, 'message' => 'Rezept nicht gefunden']);
        exit;
    }

    // ACHTUNG: Zugriff auf Objekt-Eigenschaften!
    $erstellerId = $rezept instanceof Rezept ? $rezept->ErstellerID : $rezept['ErstellerID'] ?? null;

    if ((int)$erstellerId !== (int)$nutzerId && !$istAdmin) {
        echo json_encode(['success' => false, 'message' => 'Nicht berechtigt']);
        exit;
    }

    $ok = $dao->loesche($id);
    echo json_encode(['success' => $ok]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Interner Fehler beim Löschen.'
        // 'error' => $e->getMessage() // Nur zu Debugzwecken anzeigen
    ]);
}