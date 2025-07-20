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
    // Sortier-Parameter aus URL oder Session holen
    $sortBy = $_GET['sort'] ?? $_SESSION['rezepte_sort'] ?? 'datum';

    // Gültige Sortier-Parameter validieren
    $validSortOptions = ['bewertung', 'beliebtheit', 'datum'];

    if (!in_array($sortBy, $validSortOptions)) {
        $sortBy = 'datum';
    }

    // Sortierung in Session speichern
    $_SESSION['rezepte_sort'] = $sortBy;

    $dao = new RezeptDAO();
    $alleRezepte = $dao->findeAlleMitSortierung($sortBy, 'desc'); // Immer absteigend

    $bewertungDAO = new BewertungDAO();

    foreach ($alleRezepte as &$rezept) {
        $rezeptID = $rezept['RezeptID'] ?? 0;
        $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
        $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
    }
    unset($rezept);

    // Parameter für View verfügbar machen
    $currentSort = $sortBy;

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
    // CSRF-Token prüfen
    checkCSRFToken();

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
    // CSRF-Token prüfen
    checkCSRFToken();

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

    // Nährwerte löschen, da sich die Zutaten geändert haben könnten
    if ($ok) {
        try {
            require_once 'php/model/NaehrwerteDAO.php';
            $naehrwerteDAO = new NaehrwerteDAO();

            // Prüfen ob Nährwerte vorhanden waren
            $hatteNaehrwerte = $naehrwerteDAO->hatNaehrwerte($id);

            if ($hatteNaehrwerte) {
                $naehrwerteGeloescht = $naehrwerteDAO->loescheNaehrwerte($id);

                // Flag setzen, wenn Nährwerte tatsächlich gelöscht wurden
                if ($naehrwerteGeloescht) {
                    $_SESSION['naehrwerte_zurueckgesetzt'] = true;
                }
            }
        } catch (Exception $e) {
            error_log("Fehler beim Löschen der Nährwerte nach Rezept-Update: " . $e->getMessage());
        }
    }

    if ($ok) {
        $nachricht = "Rezept erfolgreich aktualisiert.";

        // Zusätzlicher Hinweis wenn Nährwerte zurückgesetzt wurden
        if (isset($_SESSION['naehrwerte_zurueckgesetzt']) && $_SESSION['naehrwerte_zurueckgesetzt'] === true) {
            $nachricht .= " Nährwerte wurden zurückgesetzt und müssen neu berechnet werden.";
        }

        flash("success", $nachricht);
    } else {
        flash("error", "Fehler beim Aktualisieren des Rezepts.");
    }

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

/**
 * Berechnet Nährwerte für ein Rezept über die Spoonacular API
 */
function berechneNaehrwerte(): void {
    // Output Buffer leeren und JSON-Header setzen
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Fehlerausgabe für API-Calls unterdrücken
    $originalErrorReporting = error_reporting(0);
    ini_set('display_errors', '0');

    header('Content-Type: application/json');

    // Hilfsfunktion für saubere JSON-Antwort mit Error Reporting Reset
    $sendJsonAndExit = function($data) use ($originalErrorReporting) {
        error_reporting($originalErrorReporting);
        echo json_encode($data);
        exit;
    };

    // DSGVO-konforme Prüfung: Nur mit Einwilligung
    if (empty($_SESSION['naehrwerte_einwilligung'])) {
        $sendJsonAndExit([
            'success' => false,
            'error' => 'Bitte stimme der Datenübertragung an Spoonacular zu.',
            'consent_required' => true
        ]);
    }

    $rezeptId = validateId($_POST['rezeptId'] ?? null);
    if ($rezeptId === null) {
        $sendJsonAndExit(['success' => false, 'error' => 'Ungültige Rezept-ID']);
    }

    require_once 'php/model/SpoonacularAPI.php';
    require_once 'php/model/NaehrwerteDAO.php';
    require_once 'php/config/api-config.php';

    // API-Konfiguration prüfen
    if (!isApiConfigured()) {
        $sendJsonAndExit([
            'success' => false,
            'error' => 'Spoonacular API ist nicht konfiguriert. Bitte kontaktiere den Administrator.'
        ]);
    }

    $dao = new RezeptDAO();
    $rezept = $dao->findeNachId($rezeptId);

    if (!$rezept) {
        $sendJsonAndExit(['success' => false, 'error' => 'Rezept nicht gefunden']);
    }

    // Prüfen ob bereits Nährwerte vorhanden sind
    $naehrwerteDAO = new NaehrwerteDAO();
    $vorhandeneNaehrwerte = $naehrwerteDAO->holeNaehrwerte($rezeptId);

    if ($vorhandeneNaehrwerte) {
        $sendJsonAndExit([
            'success' => true,
            'naehrwerte' => $vorhandeneNaehrwerte,
            'cached' => true
        ]);
    }

    // API-Key aus Konfiguration holen
    $apiKey = getSpoonacularApiKey();

    $spoonacularAPI = new SpoonacularAPI($apiKey);

    // Prüfen ob API verfügbar ist
    if (!$spoonacularAPI->istAPIVerfuegbar()) {
        $sendJsonAndExit([
            'success' => false,
            'error' => 'Spoonacular API ist momentan nicht verfügbar. Bitte versuche es später erneut.'
        ]);
    }

    // Portionsgröße ermitteln (Standard: 1)
    $portionen = 1;
    if (!empty($rezept['portionsgroesseName'])) {
        // Versuche Zahl aus Portionsgröße zu extrahieren
        preg_match('/(\d+)/', $rezept['portionsgroesseName'], $matches);
        if (!empty($matches[1])) {
            $portionen = (int)$matches[1];
        }
    }

    $naehrwerte = $spoonacularAPI->berechneNaehrwerte($rezept['zutaten'], $portionen);

    if ($naehrwerte === null) {
        $sendJsonAndExit([
            'success' => false,
            'error' => 'Nährwerte konnten nicht berechnet werden. Möglicherweise sind die Zutaten nicht erkennbar oder das API-Limit wurde erreicht.'
        ]);
    }

    // Nährwerte in Datenbank speichern
    $gespeichert = $naehrwerteDAO->speichereNaehrwerte($rezeptId, $naehrwerte);

    // Erfolgreiche Antwort senden
    $sendJsonAndExit([
        'success' => true,
        'naehrwerte' => $naehrwerte,
        'saved' => $gespeichert
    ]);
}



/**
 * Setzt die Einwilligung für Nährwert-Berechnung
 */
function setzeNaehrwerteEinwilligung(): void {
    // Output Buffer leeren und JSON-Header setzen
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');

    $einwilligung = filter_var($_POST['einwilligung'] ?? false, FILTER_VALIDATE_BOOLEAN);

    if ($einwilligung) {
        $_SESSION['naehrwerte_einwilligung'] = true;
        $_SESSION['naehrwerte_einwilligung_datum'] = date('Y-m-d H:i:s');
    } else {
        unset($_SESSION['naehrwerte_einwilligung']);
        unset($_SESSION['naehrwerte_einwilligung_datum']);
    }

    echo json_encode(['success' => true, 'einwilligung' => $einwilligung]);
    exit;
}