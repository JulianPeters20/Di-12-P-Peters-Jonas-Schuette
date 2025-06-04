<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php';
require_once 'php/model/KategorieDAO.php';
require_once 'php/model/UtensilDAO.php';
require_once 'php/include/form_utils.php';

function showRezepte(): void {
    $dao = new RezeptDAO();
    $alleRezepte = $dao->findeAlle();

    $suche = trim($_GET['suche'] ?? '');
    if ($suche !== '') {
        $suchbegriff = mb_strtolower($suche);
        $alleRezepte = array_filter($alleRezepte, function ($rezept) use ($suchbegriff) {
            return mb_stripos($rezept['titel'] ?? '', $suchbegriff) !== false
                || mb_stripos($rezept['kategorie'] ?? '', $suchbegriff) !== false;
        });
    }

    $rezepte = $alleRezepte;
    require 'php/view/rezepte.php';
}

function showRezeptNeu(): void {
    $katDAO = new KategorieDAO();
    $utenDAO = new UtensilDAO();

    $_SESSION['kategorienListe'] = $katDAO->findeAlle();
    $_SESSION['utensilienListe'] = $utenDAO->findeAlle();

    require 'php/view/rezept-neu.php';
}

function showRezeptDetails($id): void {
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

    require 'php/view/rezept.php';
}

function speichereRezept(): void {
    if (empty($_SESSION['nutzerId'])) {
        $_SESSION["message"] = "Nur angemeldete Nutzer können Rezepte speichern.";
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
        $_SESSION["message"] = "Bitte alle Pflichtfelder ausfüllen.";
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

    $_SESSION["message"] = $rezeptID
        ? "Rezept erfolgreich gespeichert."
        : "Fehler beim Speichern.";

    header("Location: index.php?page=" . ($rezeptID ? "rezepte" : "rezept-neu"));
    exit;
}

function loescheRezept($id): void {
    $id = validateId($id);
    if ($id === null) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    if (!isset($_SESSION['nutzerId'])) {
        $_SESSION["message"] = "Bitte melde dich an.";
        header("Location: index.php?page=anmeldung");
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
    $istErsteller = (int)$rezept['erstellerId'] === (int)$_SESSION['nutzerId'];

    if (!$istAdmin && !$istErsteller) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht löschen.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    $ok = $dao->loesche($id);
    $_SESSION["message"] = $ok
        ? "Rezept erfolgreich gelöscht."
        : "Fehler beim Löschen.";

    header("Location: index.php?page=rezepte");
    exit;
}

function aktualisiereRezept($id): void {
    $id = validateId($id);
    if ($id === null) {
        $_SESSION["message"] = "Ungültige Rezept-ID.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    if (!isset($_SESSION['nutzerId'])) {
        $_SESSION["message"] = "Bitte melde dich an.";
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);
    if (!$rezept || ((int)$rezept['erstellerId'] !== (int)$_SESSION['nutzerId'] && empty($_SESSION['istAdmin']))) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht bearbeiten.";
        header("Location: index.php?page=rezepte");
        exit;
    }

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

    $bildPfad = null;
    if (!empty($_FILES['bild']) && $_FILES['bild']['error'] === UPLOAD_ERR_OK) {
        $bildPfad = validate_and_store_image($_FILES['bild']);
        if ($bildPfad === null) {
            $_SESSION["message"] = "Ungültiges Bildformat.";
            $_SESSION["formdata"] = $_POST;
            header("Location: index.php?page=rezept-bearbeiten&id=" . urlencode($id));
            exit;
        }
    }

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

function showRezeptBearbeitenFormular($id): void {
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

    if (!isset($_SESSION['nutzerId']) ||
        ((int)$_SESSION['nutzerId'] !== (int)$rezept['erstellerId'] && empty($_SESSION['istAdmin']))) {
        $_SESSION["message"] = "Du darfst dieses Rezept nicht bearbeiten.";
        header("Location: index.php?page=rezepte");
        exit;
    }

    require 'php/view/rezept-bearbeiten.php';
}