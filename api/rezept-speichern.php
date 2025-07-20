<?php
declare(strict_types=1);

/**
 * AJAX-Endpunkt zum Speichern/Entfernen von Rezepten
 * Verarbeitet POST-Anfragen mit CSRF-Schutz und Berechtigungsprüfung
 */

// Sichere HTTP-Headers setzen
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

ini_set('display_errors', '0'); // Keine HTML-Warnungen in JSON-Response
error_reporting(E_ALL);

session_start();
require_once '../php/model/GespeicherteRezepteDAO.php';
require_once '../php/model/RezeptDAO.php';
require_once '../php/include/csrf_protection.php';

// Authentifizierung prüfen
if (empty($_SESSION['nutzerId'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht angemeldet']);
    exit;
}

// CSRF-Token zur Sicherheit prüfen
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'CSRF-Token ungültig']);
    exit;
}

$nutzerId = (int)$_SESSION['nutzerId'];

// Eingabedaten validieren
if (!isset($_POST['rezeptId']) || !is_numeric($_POST['rezeptId']) || (int)$_POST['rezeptId'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Rezept-ID']);
    exit;
}

$rezeptId = (int)$_POST['rezeptId'];
$aktion = $_POST['aktion'] ?? 'speichern'; // 'speichern' oder 'entfernen'

try {
    // Prüfen ob Rezept existiert
    $rezeptDAO = new RezeptDAO();
    $rezept = $rezeptDAO->findeNachId($rezeptId);
    
    if (!$rezept) {
        echo json_encode(['success' => false, 'message' => 'Rezept nicht gefunden']);
        exit;
    }
    
    // Prüfen ob Nutzer nicht der Ersteller ist (eigene Rezepte können nicht gespeichert werden)
    $erstellerId = $rezept['erstellerId'] ?? null;
    if ((int)$erstellerId === $nutzerId) {
        echo json_encode(['success' => false, 'message' => 'Eigene Rezepte können nicht gespeichert werden']);
        exit;
    }
    
    $gespeicherteRezepteDAO = new GespeicherteRezepteDAO();
    
    if ($aktion === 'speichern') {
        $erfolg = $gespeicherteRezepteDAO->speichereRezept($nutzerId, $rezeptId);
        $message = $erfolg ? 'Rezept gespeichert' : 'Fehler beim Speichern';
    } elseif ($aktion === 'entfernen') {
        $erfolg = $gespeicherteRezepteDAO->entferneRezept($nutzerId, $rezeptId);
        $message = $erfolg ? 'Rezept entfernt' : 'Fehler beim Entfernen';
    } else {
        echo json_encode(['success' => false, 'message' => 'Ungültige Aktion']);
        exit;
    }
    
    // Aktuellen Status zurückgeben
    $istGespeichert = $gespeicherteRezepteDAO->istGespeichert($nutzerId, $rezeptId);
    
    echo json_encode([
        'success' => $erfolg,
        'message' => $message,
        'istGespeichert' => $istGespeichert
    ]);

} catch (Throwable $e) {
    // Fehlerbehandlung: Keine technischen Details an Client senden
    echo json_encode([
        'success' => false,
        'message' => 'Interner Fehler beim Verarbeiten der Anfrage.'
    ]);
}
