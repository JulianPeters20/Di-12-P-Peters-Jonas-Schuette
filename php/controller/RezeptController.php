<?php
require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php';
require_once 'php/include/form_utils.php';

function showRezepte()
{
    $dao = new RezeptDAO();
    $alleRezepte = $dao->findeAlle();

    $suche = trim($_GET['suche'] ?? '');
    if ($suche !== '') {
        $suchbegriff = mb_strtolower($suche);
        $alleRezepte = array_filter($alleRezepte, function ($rezept) use ($suchbegriff) {
            return mb_stripos($rezept['Titel'], $suchbegriff) !== false
                || mb_stripos($rezept['Kategorie'] ?? '', $suchbegriff) !== false;
        });
    }

    $rezepte = $alleRezepte;
    require 'php/view/rezepte.php';
}

function showRezeptNeu()
{
    require_once 'php/model/KategorieDAO.php';
    require_once 'php/model/UtensilDAO.php';

    $katDAO = new KategorieDAO();
    $utenDAO = new UtensilDAO();

    $_SESSION['kategorienListe'] = $katDAO->findeAlle();
    $_SESSION['utensilienListe'] = $utenDAO->findeAlle();

    require 'php/view/rezept-neu.php';
}
/**
 * Zeigt die Details eines Rezepts an.
 * Prüft, ob die Rezept-ID gültig ist und ob das Rezept existiert.
 */
function showRezeptDetails($id)
{
    if (!is_numeric($id) || (int)$id < 1) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId((int)$id);
    if (!$rezept) {
        $_SESSION["message"] = "Rezept nicht gefunden.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    require 'php/view/rezept.php';
}
/**
 * Speichert ein neues Rezept.
 * Prüft, ob der Nutzer angemeldet ist und ob die Eingaben gültig sind.
 */
function speichereRezept()
{
    session_start();

    $nutzerID = isset($_SESSION['nutzerId']) ? (int)$_SESSION['nutzerId'] : 0;

    if ($nutzerID < 1) {
        $_SESSION["message"] = "Ungültige Sitzung – bitte erneut anmelden.";
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $titel = sanitize_text($_POST['titel'] ?? '');
    $zubereitung = sanitize_text($_POST['zubereitung'] ?? '');
    $zutaten = build_zutaten_array($_POST['zutatennamen'] ?? [], $_POST['mengen'] ?? [], $_POST['einheiten'] ?? []);
    $kategorien = sanitize_int_array($_POST['kategorien'] ?? []);
    $utensilien = sanitize_int_array($_POST['utensilien'] ?? []);
    $bildPfad = validate_and_store_image($_FILES['bild'] ?? []);

    if ($titel === '' || $zubereitung === '' || empty($zutaten) || !$bildPfad) {
        $_SESSION["message"] = "Bitte alle Pflichtfelder ausfüllen (inkl. gültigem Bild).";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }

    $dao = new RezeptDAO();
    $rezeptID = $dao->addRezept(
        $titel,
        $zubereitung,
        $bildPfad,
        $_SESSION['nutzerId'],
        (int)($_POST['preisklasse'] ?? 1),
        (int)($_POST['portionsgroesse'] ?? 1),
        $kategorien,
        $zutaten,
        $utensilien
    );

    if ($rezeptID) {
        $_SESSION["message"] = "Rezept erfolgreich gespeichert.";
        header("Location: index.php?page=rezepte");
        exit;
    } else {
        $_SESSION["message"] = "Fehler beim Speichern.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
        exit;
    }
}

/**
 * Löscht ein Rezept basierend auf der ID.
 * Prüft, ob der Nutzer angemeldet ist und ob er die Rechte zum Löschen hat.
 */
function loescheRezept($id)
{
    session_start();

    if (!isset($_SESSION['nutzerId'])) {
        $_SESSION["message"] = "Bitte melde dich an.";
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $id = validateId($id);
    if ($id === null) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);

    if (!$rezept) {
        $_SESSION["message"] = "Rezept nicht gefunden.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $istAdmin = $_SESSION['istAdmin'] ?? false;
    $istEigentümer = (int)$rezept['ErstellerID'] === (int)$_SESSION['nutzerId'];

    if (!$istAdmin && !$istEigentümer) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht löschen.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    if ($dao->loesche($id)) {
        $_SESSION["message"] = "Rezept wurde gelöscht.";
    } else {
        $_SESSION["message"] = "Fehler beim Löschen des Rezepts.";
    }

    header("Location: index.php?page=rezepte");
    exit;
}

/**
 * Aktualisiert ein bestehendes Rezept.
 * Prüft, ob der Nutzer angemeldet ist und ob er die Rechte zum Bearbeiten hat.
 */
function aktualisiereRezept($id)
{
    session_start();
    require_once 'php/include/form_utils.php';

    // Zugriffsschutz
    if (!isset($_SESSION['nutzerId'])) {
        $_SESSION["message"] = "Bitte melde dich an.";
        header("Location: index.php?page=anmeldung");
        exit;
    }

    // ID prüfen
    if (!is_numeric($id) || (int)$id < 1) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }
    $id = (int)$id;

    // Rechte prüfen
    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);
    if (!$rezept || (int)$rezept['ErstellerID'] !== (int)$_SESSION['nutzerId']) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht bearbeiten.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    // Eingaben
    $titel = sanitize_text($_POST['titel'] ?? '');
    $zubereitung = sanitize_text($_POST['zubereitung'] ?? '');
    $zutaten = build_zutaten_array($_POST['zutatennamen'] ?? [], $_POST['mengen'] ?? [], $_POST['einheiten'] ?? []);
    $kategorien = sanitize_int_array($_POST['kategorien'] ?? []);
    $utensilien = sanitize_int_array($_POST['utensilien'] ?? []);

    if ($titel === '' || $zubereitung === '') {
        $_SESSION["message"] = "Titel und Zubereitung dürfen nicht leer sein.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-bearbeiten&id=" . urlencode($id));
        exit;
    }

    if (empty($zutaten)) {
        $_SESSION["message"] = "Mindestens eine vollständige Zutat muss angegeben werden.";
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-bearbeiten&id=" . urlencode($id));
        exit;
    }

    // Bild prüfen
    $bildPfad = null;
    if (!empty($_FILES['bild']) && $_FILES['bild']['error'] === UPLOAD_ERR_OK) {
        $bildPfad = validate_and_store_image($_FILES['bild']);
        if ($bildPfad === null) {
            $_SESSION["message"] = "Bild-Upload ungültig. Nur JPG, PNG, GIF, WebP erlaubt.";
            $_SESSION["formdata"] = $_POST;
            header("Location: index.php?page=rezept-bearbeiten&id=" . urlencode($id));
            exit;
        }
    }

    // Speichern
    $ok = $dao->aktualisiere(
        $id,
        $titel,
        $zubereitung,
        $bildPfad,
        $kategorien,
        $zutaten,
        $utensilien
    );

    $_SESSION["message"] = $ok
        ? "Rezept erfolgreich aktualisiert."
        : "Fehler beim Aktualisieren des Rezepts.";

    header("Location: index.php?page=rezept&id=" . urlencode($id));
    exit;
}

/**
 * Zeigt das Formular zum Bearbeiten eines Rezepts an.
 * Prüft, ob die Rezept-ID gültig ist und ob der Nutzer die Rechte zum Bearbeiten hat.
 */
function showRezeptBearbeitenFormular($id)
{
    if (!is_numeric($id) || (int)$id < 1) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId((int)$id);

    if (!$rezept) {
        $_SESSION["message"] = "Rezept nicht gefunden.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    // Rechteprüfung: Ersteller oder Admin
    if (!isset($_SESSION['nutzerId']) ||
        ((int)$_SESSION['nutzerId'] !== (int)($rezept['erstellerId'] ?? -1) && empty($_SESSION['istAdmin']))) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht bearbeiten.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    require 'php/view/rezept-bearbeiten.php';
}