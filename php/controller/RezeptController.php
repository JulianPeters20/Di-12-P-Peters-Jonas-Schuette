<?php

// Modellklassen einbinden
require_once 'php/model/RezeptDAO.php';
require_once 'php/model/KategorieDAO.php';
require_once 'php/model/UtensilDAO.php';
require_once 'php/model/PortionsgroesseDAO.php';
require_once 'php/model/PreisklasseDAO.php';
require_once 'php/model/BewertungDAO.php';
require_once 'php/include/form_utils.php';

/**
 * Alle Rezepte anzeigen, optional mit Suchfilter
 */
function showRezepte(): void {
    $dao = new RezeptDAO();
    $alleRezepte = $dao->findeAlle();

    $bewertungDAO = new BewertungDAO();

    foreach ($alleRezepte as &$rezept) {
        $rezeptID = $rezept['RezeptID'] ?? 0;
        $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
        $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
    }
    unset($rezept);

    // Filter je nach Suche etc.

    $rezepte = $alleRezepte;
    require 'php/view/rezepte.php';
}

/**
 * Formular zum Anlegen eines neuen Rezepts anzeigen,
 * inkl. Laden aller Listen wie Kategorien, Utensilien, etc. in Sessions
 */
function showRezeptNeu(): void {
    $katDAO = new KategorieDAO();
    $utenDAO = new UtensilDAO();
    $portDAO = new PortionsgroesseDAO();
    $preisDAO = new PreisklasseDAO();

    // Kategorien laden und in assoziatives Array speichern (ID => Objekt)
    $kategorienListeRaw = $katDAO->findeAlle();
    $kategorienListe = [];
    foreach ($kategorienListeRaw as $kat) {
        $kategorienListe[$kat->KategorieID] = $kat;
    }
    $_SESSION['kategorienListe'] = $kategorienListe;

    // Utensilien laden
    $utensilienListeRaw = $utenDAO->findeAlle();
    $utensilienListe = [];
    foreach ($utensilienListeRaw as $uten) {
        $utensilienListe[$uten->UtensilID] = $uten;
    }
    $_SESSION['utensilienListe'] = $utensilienListe;

    // Portionsgrößen laden
    $portionsgroesseListeRaw = $portDAO->findeAlle();
    $portionsgroesseListe = [];
    foreach ($portionsgroesseListeRaw as $pg) {
        $portionsgroesseListe[$pg->PortionsgroesseID] = $pg;
    }
    $_SESSION['portionsgroesseListe'] = $portionsgroesseListe;

    // Preisklassen laden
    $preisklasseListeRaw = $preisDAO->findeAlle();
    $preisklasseListe = [];
    foreach ($preisklasseListeRaw as $pl) {
        $preisklasseListe[$pl->PreisklasseID] = $pl;
    }
    $_SESSION['preisklasseListe'] = $preisklasseListe;

    require 'php/view/rezept-neu.php';
}

/**
 * Details zu einem Rezept anzeigen,
 * inkl. Durchschnittsbewertung, Nutzerbewertung (falls angemeldet)
 * und Prüfung, ob der angemeldete Nutzer der Ersteller ist (für UI-Entscheidungen)
 */
function showRezeptDetails($id): void {
    $id = validateId($id);
    if ($id === null) {
        flash("error", "Ungültige Rezept-ID.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);

    if (!$rezept) {
        flash("error", "Rezept nicht gefunden.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    $bewertungDAO = new BewertungDAO();
    $durchschnitt = $bewertungDAO->berechneDurchschnittRating($id);
    $anzahlBewertungen = $bewertungDAO->zaehleBewertungen($id);

    $nutzerBewertung = null;
    $istEigenerErsteller = false;
    if (!empty($_SESSION['nutzerId'])) {
        $nutzerBewertung = $bewertungDAO->findeNachRezeptUndNutzer($id, (int)$_SESSION['nutzerId']);
        $istEigenerErsteller = (int)$rezept['erstellerId'] === (int)$_SESSION['nutzerId'];
    }

    require 'php/view/rezept.php';
}

/**
 * Neues Rezept speichern, mit Validierung,
 * Speicherung von Bild, Zutaten, Kategorien und Utensilien
 */
function speichereRezept(): void {
    require_once 'php/include/form_utils.php'; // sicherstellen, dass flash() verfügbar ist

    if (empty($_SESSION['nutzerId'])) {
        flash("error", "Nur angemeldete Nutzer können Rezepte speichern.");
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
        flash("warning", "Bitte alle Pflichtfelder ausfüllen.");
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
        flash("success", "Rezept erfolgreich gespeichert.");
        header("Location: index.php?page=rezepte");
    } else {
        flash("error", "Fehler beim Speichern.");
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-neu");
    }
    exit;
}

/**
 * Rezept löschen,
 * nur erlaubt für Admins oder Ersteller des Rezepts
 */
function loescheRezept($id): void {
    require_once 'php/include/form_utils.php'; // sicherstellen, dass flash() verfügbar ist

    $id = validateId($id);
    if ($id === null) {
        flash("error", "Ungültige Rezept-ID.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    if (!isset($_SESSION['nutzerId'])) {
        flash("error", "Bitte melde dich an.");
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);

    if (!$rezept) {
        flash("error", "Rezept nicht gefunden.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    $istAdmin = $_SESSION['istAdmin'] ?? false;
    $istErsteller = (int)$rezept['erstellerId'] === (int)$_SESSION['nutzerId'];

    if (!$istAdmin && !$istErsteller) {
        flash("warning", "Du darfst dieses Rezept nicht löschen.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    $ok = $dao->loesche($id);
    flash(
        $ok ? "success" : "error",
        $ok ? "Rezept erfolgreich gelöscht." : "Fehler beim Löschen."
    );

    header("Location: index.php?page=rezepte");
    exit;
}

/**
 * Rezept aktualisieren,
 * mit Validierung und Berechtigung prüfen (Admin oder Ersteller),
 * optional Bild aktualisieren
 */
function aktualisiereRezept($id): void {
    require_once 'php/include/form_utils.php';
    $id = validateId($id);
    if ($id === null) {
        flash("error", "Ungültige Rezept-ID.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    if (!isset($_SESSION['nutzerId'])) {
        flash("warning", "Bitte melde dich an.");
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);
    if (!$rezept || ((int)$rezept['erstellerId'] !== (int)$_SESSION['nutzerId'] && empty($_SESSION['istAdmin']))) {
        flash("error", "Du darfst dieses Rezept nicht bearbeiten.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    $titel = sanitize_text($_POST['titel'] ?? '');
    $zubereitung = sanitize_text($_POST['zubereitung'] ?? '');
    $zutaten = build_zutaten_array($_POST['zutatennamen'] ?? [], $_POST['mengen'] ?? [], $_POST['einheiten'] ?? []);
    $kategorien = sanitize_int_array($_POST['kategorien'] ?? []);
    $utensilien = sanitize_int_array($_POST['utensilien'] ?? []);
    $preisklasseID = (int)($_POST['preisklasse'] ?? 1);
    $portionsgroesseID = (int)($_POST['portionsgroesse'] ?? 1);

    // Validierung
    if ($titel === '' || $zubereitung === '') {
        flash("warning", "Titel und Zubereitung dürfen nicht leer sein.");
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-bearbeiten&id=" . urlencode($id));
        exit;
    }

    if (empty($zutaten)) {
        flash("warning", "Mindestens eine vollständige Zutat muss angegeben werden.");
        $_SESSION["formdata"] = $_POST;
        header("Location: index.php?page=rezept-bearbeiten&id=" . urlencode($id));
        exit;
    }

    $bildPfad = null;
    if (!empty($_FILES['bild']) && $_FILES['bild']['error'] === UPLOAD_ERR_OK) {
        $bildPfad = validate_and_store_image($_FILES['bild']);
        if ($bildPfad === null) {
            flash("warning", "Ungültiges Bildformat.");
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
        $preisklasseID,
        $portionsgroesseID,
        $kategorien,
        $zutaten,
        $utensilien
    );

    flash($ok ? "success" : "error", $ok
        ? "Rezept erfolgreich aktualisiert."
        : "Fehler beim Aktualisieren des Rezepts.");

    header("Location: index.php?page=rezept&id=" . urlencode($id));
    exit;
}

/**
 * Formular zum Bearbeiten eines Rezepts (inkl. Laden aller notwendigen Listen)
 */
function showRezeptBearbeitenFormular($id): void {
    require_once 'php/include/form_utils.php'; // sicherstellen, dass flash() verfügbar ist

    $id = validateId($id);
    if ($id === null) {
        flash("error", "Ungültige Rezept-ID.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($id);

    if (!$rezept) {
        flash("error", "Rezept nicht gefunden.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    if (!isset($_SESSION['nutzerId']) ||
        ((int)$_SESSION['nutzerId'] !== (int)$rezept['erstellerId'] && empty($_SESSION['istAdmin']))) {
        flash("warning", "Du darfst dieses Rezept nicht bearbeiten.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    // Kategorien mit IDs laden (für Vorauswahl der Checkboxes)
    $rezept['kategorienMitIds'] = $dao->findeKategorienMitIdsNachRezeptId($id);

    // Kategorien-Liste vorbereiten (ID => Objekt)
    $katDAO = new KategorieDAO();
    $kategorienListeRaw = $katDAO->findeAlle();
    $kategorienListe = [];
    foreach ($kategorienListeRaw as $kat) {
        $kategorienListe[$kat->KategorieID] = $kat;
    }
    $_SESSION['kategorienListe'] = $kategorienListe;

    // Utensilien vorbereiten
    $utenDAO = new UtensilDAO();
    $utensilienListe = [];
    foreach ($utenDAO->findeAlle() as $uten) {
        $utensilienListe[$uten->UtensilID] = $uten;
    }
    $_SESSION['utensilienListe'] = $utensilienListe;

    // Portionsgrößen vorbereiten
    $portDAO = new PortionsgroesseDAO();
    $portionsgroesseListe = [];
    foreach ($portDAO->findeAlle() as $pg) {
        $portionsgroesseListe[$pg->PortionsgroesseID] = $pg;
    }
    $_SESSION['portionsgroesseListe'] = $portionsgroesseListe;

    // Preisklassen vorbereiten
    $preisDAO = new PreisklasseDAO();
    $preisklasseListe = [];
    foreach ($preisDAO->findeAlle() as $pl) {
        $preisklasseListe[$pl->PreisklasseID] = $pl;
    }
    $_SESSION['preisklasseListe'] = $preisklasseListe;

    require 'php/view/rezept-bearbeiten.php';
}

/**
 * Bewertungsfunktion:
 * Ermöglicht angemeldeten Nutzern, ein Rezept zu bewerten (einmalig oder Update)
 * Validiert Eingaben und verhindert Selbstevaluation
 */
function bewerteRezept(): void {
    require_once 'php/include/form_utils.php'; // sicherstellen, dass flash() verfügbar ist

    if (empty($_SESSION['nutzerId'])) {
        flash("warning", "Bitte melde dich an, um zu bewerten.");
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $nutzerId = (int)$_SESSION['nutzerId'];
    $rezeptId = validateId($_POST['rezeptId'] ?? null);
    $punkte = (int)($_POST['punkte'] ?? 0);

    if ($rezeptId === null) {
        flash("error", "Ungültige Rezept-ID.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    if ($punkte < 1 || $punkte > 5) {
        flash("warning", "Bitte eine Bewertung zwischen 1 und 5 Sternen abgeben.");
        header("Location: index.php?page=rezept&id=" . urlencode($rezeptId));
        exit;
    }

    $rezeptDAO = new RezeptDAO();
    $rezept = $rezeptDAO->findeNachId($rezeptId);

    if (!$rezept) {
        flash("error", "Rezept existiert nicht.");
        header("Location: index.php?page=rezepte");
        exit;
    }

    if ((int)$rezept['erstellerId'] === $nutzerId) {
        flash("warning", "Du kannst dein eigenes Rezept nicht bewerten.");
        header("Location: index.php?page=rezept&id=" . urlencode($rezeptId));
        exit;
    }

    $bewertungDAO = new BewertungDAO();
    $bestehendeBewertung = $bewertungDAO->findeNachRezeptUndNutzer($rezeptId, $nutzerId);
    $heute = date('Y-m-d');

    if ($bestehendeBewertung) {
        $bestehendeBewertung->Punkte = $punkte;
        $bestehendeBewertung->Bewertungsdatum = $heute;
        $ok = $bewertungDAO->aktualisiereBewertung($bestehendeBewertung);
    } else {
        $neu = new Bewertung($rezeptId, $nutzerId, $punkte, $heute);
        $ok = $bewertungDAO->fuegeBewertungHinzu($neu);
    }

    flash($ok ? "success" : "error", $ok ? "Bewertung gespeichert." : "Fehler beim Speichern der Bewertung.");
    header("Location: index.php?page=rezept&id=" . urlencode($rezeptId));
    exit;
}