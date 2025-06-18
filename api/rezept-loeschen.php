<?php
declare(strict_types=1);
header('Content-Type: application/json');
ini_set('display_errors', '0'); // Keine HTML-Warnings
error_reporting(E_ALL);

session_start();
require_once '../php/model/RezeptDAO.php';

$nutzerId = $_SESSION['nutzerId'] ?? null;
$istAdmin = $_SESSION['istAdmin'] ?? false;

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
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