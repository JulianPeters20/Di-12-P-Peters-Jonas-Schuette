<?php
require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php'; // Nicht vergessen!

// Login direkt verarbeitet (falls über POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['passwort'])) {
    $email = trim($_POST['email']);
    $passwort = $_POST['passwort'];

    $nutzer = NutzerDAO::findeNachEmail($email);

    if ($nutzer && password_verify($passwort, $nutzer->passwort)) {
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

// === Funktionsbasierter Teil ===

function showRezepte()
{
    $alleRezepte = RezeptDAO::findeAlle();

    // Falls Suchbegriff gesetzt → filtern
    $suche = trim($_GET['suche'] ?? '');

    if (!empty($suche)) {
        $suchbegriff = strtolower($suche);
        $alleRezepte = array_filter($alleRezepte, function ($rezept) use ($suchbegriff) {
            return str_contains(strtolower($rezept['titel']), $suchbegriff)
                || str_contains(strtolower($rezept['kategorie']), $suchbegriff)
                || str_contains(strtolower($rezept['autor']), $suchbegriff);
        });
    }

    $rezepte = $alleRezepte;
    require 'php/view/rezepte.php';
}

// Neues Rezeptformular anzeigen
function showRezeptNeu()
{
    require 'php/view/rezept-neu.php';
}

// Einzelnes Rezept anzeigen
function showRezeptDetails($id)
{
    if (!is_numeric($id)) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $rezept = RezeptDAO::findeNachId($id);
    if (!$rezept) {
        $_SESSION["message"] = "Rezept wurde nicht gefunden.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    require 'php/view/rezept.php';
}

// Rezept speichern
function speichereRezept()
{
    if (
        !isset(
            $_POST['titel'],
            $_POST['kategorie'],
            $_POST['zutaten'],
            $_POST['zubereitung'],
            $_POST['portionsgroesse'],
            $_POST['preis'],
            $_FILES['bild']
        )
    ) {
        $_SESSION["message"] = "Fehlende Formulardaten.";
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

    if (empty($titel) || empty($kategorie) || empty($zutaten) || empty($zubereitung) || empty($preis) || $portionsgroesse < 1) {
        $_SESSION["message"] = "Bitte fülle alle Pflichtfelder aus.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    if ($bild['error'] !== 0) {
        $_SESSION["message"] = "Bild-Upload fehlgeschlagen.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    try {
        RezeptDAO::addRezept(
            $titel,
            $kategorie,
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
        $_SESSION["message"] = "Interner Fehler beim Speichern.";
        header("Location: index.php");
        exit;
    }
}

// Rezept löschen
function loescheRezept($id)
{
    if (!is_numeric($id)) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    if (RezeptDAO::loesche($id)) {
        $_SESSION["message"] = "Rezept wurde gelöscht.";
    } else {
        $_SESSION["message"] = "Rezept nicht gefunden oder konnte nicht gelöscht werden.";
    }

    header("Location: index.php?page=rezepte");
    exit;
}
// Rezept bearbeiten
function showRezeptBearbeitenFormular($id) {
    if (!is_numeric($id)) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $rezept = RezeptDAO::findeNachId($id);
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

// Rezept aktualisieren
function aktualisiereRezept($id) {
    if (!is_numeric($id)) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $titel = trim($_POST['titel'] ?? '');
    $kategorie = trim($_POST['kategorie'] ?? '');
    $zutaten = trim($_POST['zutaten'] ?? '');
    $zubereitung = trim($_POST['zubereitung'] ?? '');
    $utensilien = trim($_POST['utensilien'] ?? '');
    $portionsgroesse = intval($_POST['portionsgroesse'] ?? 1);
    $preis = trim($_POST['preis'] ?? '');
    $bild = $_FILES['bild']['name'] ?? null;
    $datum = date('d.m.Y');
    $autor = $_SESSION['email'] ?? '';

    RezeptDAO::aktualisiereRezept(
        $id,
        $titel,
        $kategorie,
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