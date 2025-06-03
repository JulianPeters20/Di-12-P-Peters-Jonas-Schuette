<?php
require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['passwort'])) {
    $email = strtolower(trim($_POST['email'])); // email normalisieren
    $passwort = $_POST['passwort'];

    $nutzer = NutzerDAO::findeNachEmail($email);

    if ($nutzer && password_verify($passwort, $nutzer->passwort)) {
        // Session-Regeneration zur Vermeidung von Session-Fixation
        session_regenerate_id(true);

        $_SESSION['eingeloggt'] = true;
        $_SESSION['benutzername'] = $nutzer->benutzername;
        $_SESSION['email'] = $nutzer->email;
        $_SESSION['message'] = "Willkommen, {$nutzer->benutzername}!";
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['message'] = "E-Mail oder Passwort ist ungültig.";
        header('Location: index.php?page=anmeldung');
        exit();
    }
}

function showRezepte()
{
    $alleRezepte = RezeptDAO::findeAlle();

    $suche = trim($_GET['suche'] ?? '');
    if ($suche !== '') {
        $suchbegriff = mb_strtolower($suche); // mb_ für UTF-8 Sicherheit
        $alleRezepte = array_filter($alleRezepte, function ($rezept) use ($suchbegriff) {
            return mb_stripos($rezept['titel'], $suchbegriff) !== false
                || (is_string($rezept['kategorie']) && mb_stripos($rezept['kategorie'], $suchbegriff) !== false)
                || (is_string($rezept['autor']) && mb_stripos($rezept['autor'], $suchbegriff) !== false);
        });
    }

    $rezepte = $alleRezepte;
    require 'php/view/rezepte.php';
}

function showRezeptNeu()
{
    require 'php/view/rezept-neu.php';
}

function showRezeptDetails($id)
{
    // ID Validierung: numerisch und >0
    if (!is_numeric($id) || (int)$id < 1) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $rezept = RezeptDAO::findeNachId((int)$id);
    if (!$rezept) {
        $_SESSION["message"] = "Rezept wurde nicht gefunden.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    require 'php/view/rezept.php';
}

function speichereRezept()
{
    $requiredFields = ['titel', 'kategorie', 'zutaten', 'zubereitung', 'portionsgroesse', 'preis'];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION["message"] = "Bitte alle Pflichtfelder ausfüllen.";
            $_SESSION["formdata"] = $_POST;
            header("Location: index.php?page=rezept-neu");
            exit;
        }
    }
    if (!isset($_FILES['bild'])) {
        $_SESSION["message"] = "Bild-Upload wurde nicht gefunden.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    $titel = trim($_POST['titel']);
    $kategorie = trim($_POST['kategorie']);
    $zutaten = trim($_POST['zutaten']);
    $zubereitung = trim($_POST['zubereitung']);
    $utensilien = trim($_POST['utensilien'] ?? '');
    $portionsgroesse = intval($_POST['portionsgroesse']);
    $preis = trim($_POST['preis']);
    $bild = $_FILES['bild'];
    $autor = $_SESSION['benutzername'] ?? "Anonym";
    $datum = date('d.m.Y');

    if ($portionsgroesse < 1) {
        $_SESSION["message"] = "Portionsgröße muss mindestens 1 sein.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    if ($bild['error'] !== UPLOAD_ERR_OK) {
        $_SESSION["message"] = "Bild-Upload fehlgeschlagen.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    // Optional: Hier kann noch Bildtyp/Größe validiert und sicher verarbeitet werden

    try {
        RezeptDAO::addRezept(
            $titel,
            (array)$kategorie,
            $bild['name'],
            $datum,
            $autor,
            $zutaten,
            $zubereitung,
            $utensilien,
            $portionsgroesse,
            $preis
        );
        $_SESSION["message"] = "Rezept erfolgreich gespeichert.";
        header("Location: index.php?page=rezepte");
        exit;
    } catch (Exception $e) {
        $_SESSION["message"] = "Interner Fehler beim Speichern des Rezepts.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }
}

function loescheRezept($id)
{
    if (!is_numeric($id) || (int)$id < 1) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    if (RezeptDAO::loesche((int)$id)) {
        $_SESSION["message"] = "Rezept wurde gelöscht.";
    } else {
        $_SESSION["message"] = "Rezept nicht gefunden oder konnte nicht gelöscht werden.";
    }

    header("Location: index.php?page=rezepte");
    exit;
}

function showRezeptBearbeitenFormular($id)
{
    if (!is_numeric($id) || (int)$id < 1) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $rezept = RezeptDAO::findeNachId((int)$id);
    if (!$rezept) {
        $_SESSION["message"] = "Rezept nicht gefunden.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    // Rechteprüfung: darf der aktuelle Nutzer das Rezept bearbeiten?
    if (!isset($_SESSION['email']) || $rezept['autor'] !== $_SESSION['email']) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht bearbeiten.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    require 'php/view/rezept-bearbeiten.php';
}

function aktualisiereRezept($id)
{
    if (!is_numeric($id) || (int)$id < 1) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $titel = trim($_POST['titel'] ?? '');
    $kategorie = trim($_POST['kategorie'] ?? '');
    $zutaten = trim($_POST['zutaten'] ?? '');
    $zubereitung = trim($_POST['zubereitung'] ?? '');
    $utensilien = trim($_POST['utensilien'] ?? '');
    $portionsgroesse = max(1, intval($_POST['portionsgroesse'] ?? 1));
    $preis = trim($_POST['preis'] ?? '');
    $bild = $_FILES['bild']['name'] ?? null;
    $datum = date('d.m.Y');
    $autor = $_SESSION['email'] ?? '';

    if ($titel === '' || $kategorie === '' || $zutaten === '' || $zubereitung === '' || $preis === '') {
        $_SESSION["message"] = "Bitte fülle alle Pflichtfelder aus.";
        header("Location: index.php?page=rezept-bearbeiten&id=" . urlencode($id));
        exit;
    }

    // Optional: Validierung und Upload-Handling des Bildes hier ergänzen

    RezeptDAO::aktualisiereRezept(
        (int)$id,
        $titel,
        (array)$kategorie,
        $bild,
        $datum,
        $autor,
        $zutaten,
        $zubereitung,
        $utensilien,
        $portionsgroesse,
        $preis
    );

    $_SESSION["message"] = "Rezept wurde aktualisiert.";
    header("Location: index.php?page=nutzer&email=" . urlencode($autor));
    exit;
}
?>