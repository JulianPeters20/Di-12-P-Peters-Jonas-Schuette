<?php
declare(strict_types=1);

require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php';

session_start();

/**
 * Zeigt alle Rezepte (mit optionaler Suche)
 */
function showRezepte(): void
{
    $alleRezepte = RezeptDAO::findeAlle();
    $suche = trim($_GET['suche'] ?? '');

    if ($suche !== '') {
        $suchbegriff = mb_strtolower($suche);
        $alleRezepte = array_filter($alleRezepte, function ($rezept) use ($suchbegriff) {
            // Suche in Titel, Kategorie (Array!) und Autor
            return mb_stripos($rezept['titel'], $suchbegriff) !== false
                || (is_array($rezept['kategorie']) && array_filter($rezept['kategorie'], fn($k) => mb_stripos($k, $suchbegriff) !== false))
                || mb_stripos($rezept['autor'], $suchbegriff) !== false;
        });
    }

    $rezepte = $alleRezepte;
    require 'php/view/rezepte.php';
}

/**
 * Zeigt das Formular zum Hinzufügen eines Rezepts
 */
function showRezeptNeu(): void
{
    require 'php/view/rezept-neu.php';
}

/**
 * Zeigt Details zu einem Rezept
 */
function showRezeptDetails($id): void
{
    if (!is_numeric($id) || $id <= 0) {
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

/**
 * Speichert ein neues Rezept (POST)
 */
function speichereRezept(): void
{
    if (
        !isset($_POST['titel'], $_POST['kategorie'], $_POST['zutaten'], $_POST['zubereitung'], $_POST['portionsgroesse'], $_POST['preis'])
    ) {
        $_SESSION["message"] = "Fehlende Formulardaten.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    $titel = trim($_POST['titel']);
    $kategorien = $_POST['kategorie']; // Mehrfachauswahl als Array!
    if (!is_array($kategorien)) { $kategorien = [$kategorien]; }
    $zutaten = trim($_POST['zutaten']);
    $zubereitung = trim($_POST['zubereitung']);
    $utensilien = trim($_POST['utensilien'] ?? '');
    $portionsgroesse = max(1, intval($_POST['portionsgroesse']));
    $preis = trim($_POST['preis']);
    $autor = $_SESSION['email'] ?? "anonym";
    $datum = date('d.m.Y');

    $bild = ''; // Hier ggf. einfache Upload-Logik mit move_uploaded_file
    if (isset($_FILES['bild']) && $_FILES['bild']['error'] === 0) {
        $bild = 'images/' . basename($_FILES['bild']['name']);
        move_uploaded_file($_FILES['bild']['tmp_name'], $bild);
    }

    if (empty($titel) || empty($kategorien) || empty($zutaten) || empty($zubereitung) || empty($preis) || $portionsgroesse < 1) {
        $_SESSION["message"] = "Bitte fülle alle Pflichtfelder aus.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    try {
        RezeptDAO::addRezept(
            $titel,
            $kategorien,
            $bild,
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

/**
 * Löscht ein Rezept
 */
function loescheRezept($id): void
{
    if (!is_numeric($id)) {
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

/**
 * Zeigt das Bearbeiten-Formular an
 */
function showRezeptBearbeitenFormular($id): void
{
    if (!is_numeric($id)) {
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
    // Berechtigungsprüfung: Selber Nutzer?
    if (!isset($_SESSION['email']) || $rezept['autor'] !== $_SESSION['email']) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht bearbeiten.";
        header("Location: index.php?page=rezepte");
        exit;
    }
    require 'php/view/rezept-bearbeiten.php';
}

/**
 * Rezept aktualisieren
 */
function aktualisiereRezept($id): void
{
    if (!is_numeric($id)) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }
    $titel = trim($_POST['titel'] ?? '');
    $kategorien = $_POST['kategorie'] ?? [];
    if (!is_array($kategorien)) { $kategorien = [$kategorien]; }
    $zutaten = trim($_POST['zutaten'] ?? '');
    $zubereitung = trim($_POST['zubereitung'] ?? '');
    $utensilien = trim($_POST['utensilien'] ?? '');
    $portionsgroesse = intval($_POST['portionsgroesse'] ?? 1);
    $preis = trim($_POST['preis'] ?? '');
    $bild = '';
    if (isset($_FILES['bild']) && $_FILES['bild']['error'] === 0) {
        $bild = 'images/' . basename($_FILES['bild']['name']);
        move_uploaded_file($_FILES['bild']['tmp_name'], $bild);
    }
    $datum = date('d.m.Y');
    $autor = $_SESSION['email'] ?? '';

    RezeptDAO::aktualisiereRezept(
        (int)$id,
        $titel,
        $kategorien,
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