<?php

require_once __DIR__ . '/../model/NutzerDAO.php';
require_once __DIR__ . '/../service/UserService.php';
require_once __DIR__ . '/../include/form_utils.php';
require_once __DIR__ . '/../include/rate_limiting.php';
require_once __DIR__ . '/../include/csrf_protection.php';
require_once __DIR__ . '/../model/RezeptDAO.php';

// Relative Pfade für simulierte Mails verwenden (besser für Tests)
// Keine absolute URL mehr nötig

/**
 * Hilfsfunktion: Stellt sicher, dass mails-Verzeichnis existiert.
 */
function ensureMailDir() {
    $dir = __DIR__ . '/../../data/mails/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

/**
 * Temporäre JSON für Vorregistrierung speichern
 */
function speichereVorregistrierung(string $benutzername, string $email, string $passwort): string {
    ensureMailDir();
    $code = bin2hex(random_bytes(16));
    $jsonFile = __DIR__ . "/../../data/mails/registrierung_" . $code . ".json";
    $data = [
        'benutzername' => $benutzername,
        'email' => $email,
        'passwort' => $passwort,
        'created' => time()
    ];
    file_put_contents($jsonFile, json_encode($data, JSON_THROW_ON_ERROR));
    return $code;
}

/**
 * Anzeige und Verarbeitung des (angepassten) Registrierungsformulars
 */
function showRegistrierungsFormular(): void {
    if (!empty($_SESSION["eingeloggt"])) {
        header("Location: index.php");
        exit;
    }

    $error = ""; // Fehler-Variable für die View

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // CSRF-Token prüfen
        checkCSRFToken();

        // Sticky: Formularwerte bereitstellen
        $benutzername = sanitize_text($_POST["benutzername"] ?? '');
        $email = sanitize_email($_POST["email"] ?? '');
        $passwort = sanitize_text($_POST["passwort"] ?? '');
        $passwort_wdh = sanitize_text($_POST["passwort-wdh"] ?? '');
        $agb = isset($_POST['agb']);
        $datenschutz = isset($_POST['datenschutz']);

        if ($email === '' || $passwort === '' || $passwort_wdh === '') {
            $error = "Bitte alle Pflichtfelder ausfüllen.";
        } elseif ($passwort !== $passwort_wdh) {
            $error = "Die Passwörter stimmen nicht überein.";
        } elseif (!$agb || !$datenschutz) {
            $error = "Du musst die Nutzungsbedingungen und Datenschutzerklärung akzeptieren.";
        }

        if (!empty($error)) {
            // Fehler: Lade die View direkt, damit Werte erhalten bleiben
            require 'php/view/registrierung.php';
            exit;
        }

        $dao = new NutzerDAO();
        $nutzer = $dao->findeNachEmail($email);

        $code = speichereVorregistrierung($benutzername, $email, $passwort);
        $mailDatei = "data/mails/registrierung_" . $code . ".html";
        $bestaetigungsLink = "index.php?page=bestaetigeRegistrierung&code=" . urlencode($code);

        if ($nutzer) {
            $inhalt = <<<MAIL
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Registrierung bei Broke & Hungry</title>
</head>
<body>
<p>Jemand hat versucht, diese E-Mail für ein neues Konto zu nutzen.<br>
Du bist jedoch bereits registriert.</p>
<p>Falls du dich nicht mehr an deine Zugangsdaten erinnerst, wende dich bitte an unseren Support.</p>
</body>
</html>
MAIL;
        } else {
            $inhalt = <<<MAIL
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Registrierung bei Broke & Hungry</title>
</head>
<body>
<p>Um deine Registrierung bei Broke &amp; Hungry abzuschließen, klicke folgenden Link:<br>
<a href="{$bestaetigungsLink}">Registrierung bestätigen</a>
</p>
<p>Falls du das nicht wolltest, ignoriere diese Nachricht.</p>
</body>
</html>
MAIL;
        }
        file_put_contents(__DIR__ . "/../../" . $mailDatei, $inhalt);

        flash("success", "Registrierung eingeleitet! Weitere Infos findest du in der Datei: " . $mailDatei);
        header("Location: index.php?page=registrierung");
        exit;
    }

    // GET-Request oder erstmaliger Aufruf
    $benutzername = $email = "";
    require_once 'php/view/registrierung.php';
}

/**
 * Bestätigungslink aus der simulierten E-Mail verarbeiten (Registrierung final!)
 */
function bestaetigeRegistrierung(string $code): void {
    $filePath = __DIR__ . "/../../data/mails/registrierung_" . $code . ".json";
    if (!is_file($filePath)) {
        flash("error", "Ungültiger oder bereits genutzter Bestätigungslink.");
        header("Location: index.php?page=registrierung");
        exit;
    }
    $data = json_decode(file_get_contents($filePath), true);
    if (!$data || empty($data['email']) || empty($data['passwort'])) {
        flash("error", "Ungültige Daten - keine Registrierung möglich.");
        header("Location: index.php?page=registrierung");
        exit;
    }

    $dao = new NutzerDAO();
    if ($dao->findeNachEmail($data['email'])) {
        unlink($filePath);
        flash("warning", "Diese E-Mail ist bereits registriert.");
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $res = $dao->registrieren($data['benutzername'] ?? '', $data['email'], $data['passwort']);
    unlink($filePath);

    if ($res) {
        flash("success", "Registrierung abgeschlossen! Du kannst dich jetzt anmelden.");
        header("Location: index.php?page=anmeldung");
        exit;
    } else {
        flash("error", "Registrierung fehlgeschlagen.");
        header("Location: index.php?page=registrierung");
        exit;
    }
}

/**
 * Anmeldung
 */
function showAnmeldeFormular(): void {
    if ($_SERVER["REQUEST_METHOD"] === "GET" && !empty($_SESSION["eingeloggt"])) {
        header("Location: index.php");
        exit;
    }

    $userService = new UserService();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // CSRF-Token prüfen
        checkCSRFToken();

        $email = sanitize_email($_POST["email"] ?? '');
        $passwort = sanitize_text($_POST["passwort"] ?? '');

        if ($email === '' || $passwort === '') {
            flash("warning","Bitte fülle alle Felder aus.");
            header("Location: index.php?page=anmeldung");
            exit;
        }

        // Rate Limiting prüfen
        if (!checkLoginAttempts($email)) {
            $remainingTime = getRemainingLockTime($email);
            $minutes = ceil($remainingTime / 60);
            flash("error", "Zu viele fehlgeschlagene Login-Versuche. Bitte warte $minutes Minuten.");
            header("Location: index.php?page=anmeldung");
            exit;
        }

        try {
            // UserService für Authentifizierung verwenden (inkl. Passwort-Verifikation)
            $nutzer = $userService->authentifizieren($email, $passwort);

            if (!$nutzer) {
                recordFailedLogin($email);
                flash("warning","E-Mail-Adresse oder Passwort ist falsch.");
                header("Location: index.php?page=anmeldung");
                exit;
            }

            // Erfolgreicher Login - Rate Limiting zurücksetzen
            clearLoginAttempts($email);
            $_SESSION["benutzername"] = $nutzer->benutzername;
            $_SESSION["email"] = $nutzer->email;
            $_SESSION["nutzerId"] = $nutzer->id;
            $_SESSION["istAdmin"] = $nutzer->istAdmin;
            $_SESSION["eingeloggt"] = true;
            $_SESSION["regenerate_session"] = true; // Session-Regeneration für Sicherheit markieren

            // CSRF-Token nach erfolgreichem Login regenerieren
            regenerateCSRFToken();

            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            recordFailedLogin($email);
            error_log("Anmeldefehler: " . $e->getMessage());
            flash("error", "Ein Fehler ist aufgetreten. Bitte versuche es erneut.");
            header("Location: index.php?page=anmeldung");
            exit;
        }
    }

    require_once 'php/view/anmeldung.php';
}

/**
 * AJAX-Prüfung Benutzername
 */
function pruefeBenutzername(): void {
    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');

    $benutzername = trim($_GET['benutzername'] ?? '');
    if ($benutzername === '') {
        echo json_encode(['status' => 'error', 'message' => 'Benutzername fehlt']);
        exit;
    }

    $dao = new NutzerDAO();
    $existiert = $dao->existiertBenutzername($benutzername);

    echo json_encode(['exists' => $existiert]);
    exit;
}

/**
 * Nutzer ausloggen
 */
function logoutUser(): void {
    session_unset();
    session_destroy();

    session_start();
    flash('success', 'Du wurdest erfolgreich abgemeldet.');

    header("Location: index.php");
    exit;
}

/**
 * Nutzerprofil anzeigen
 */
function showNutzerProfil(): void {
    if (empty($_SESSION['nutzerId']) || !is_numeric($_SESSION['nutzerId'])) {
        flash("warning", "Du bist nicht eingeloggt.");
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $nutzerDAO = new NutzerDAO();
    $nutzer = $nutzerDAO->findeNachID((int)$_SESSION['nutzerId']);

    if (!$nutzer) {
        flash("error", "Nutzerprofil konnte nicht geladen werden.");
        header("Location: index.php");
        exit;
    }

    $rezeptDAO = new RezeptDAO();
    $rezepte = $rezeptDAO->findeNachErstellerID($nutzer->id);

    // Bewertungen für jedes Rezept laden
    require_once 'php/model/BewertungDAO.php';
    $bewertungDAO = new BewertungDAO();

    foreach ($rezepte as $rezept) {
        $rezeptId = $rezept->RezeptID;
        $rezept->durchschnitt = $bewertungDAO->berechneDurchschnittRating($rezeptId);
        $rezept->anzahlBewertungen = $bewertungDAO->zaehleBewertungen($rezeptId);
    }

    // Gespeicherte Rezepte laden
    require_once 'php/model/GespeicherteRezepteDAO.php';
    $gespeicherteRezepteDAO = new GespeicherteRezepteDAO();
    $gespeicherteRezepte = $gespeicherteRezepteDAO->findeGespeicherteRezepte($nutzer->id);

    // Bewertungen für gespeicherte Rezepte hinzufügen
    foreach ($gespeicherteRezepte as &$rezept) {
        $rezeptId = $rezept['RezeptID'];
        $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptId);
        $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptId);
    }
    unset($rezept);

    require 'php/view/nutzer.php';
}

/**
 * Nutzerliste (nur Admin)
 */
function showNutzerListe(): void {
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        flash("error", "Nur Administratoren dürfen die Nutzerliste sehen.");
        header("Location: index.php");
        exit;
    }

    $dao = new NutzerDAO();
    $nutzer = $dao->findeAlle();

    require_once 'php/view/nutzerliste.php';
}

/**
 * Löscht Nutzer (nur Admin)
 */
function loescheNutzer(int $id): void {
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        flash("error", "Nur Administratoren dürfen Nutzer löschen.");
        header("Location: index.php");
        exit;
    }

    if (isset($_SESSION['nutzerId']) && (int)$_SESSION['nutzerId'] === $id) {
        flash("warning", "Du kannst deinen eigenen Account nicht löschen.");
        header("Location: index.php?page=nutzerliste");
        exit;
    }

    $dao = new NutzerDAO();

    // Prüfen, ob der zu löschende Nutzer ein Admin ist
    $zuLoeschenderNutzer = $dao->findeNachID($id);
    if ($zuLoeschenderNutzer && $zuLoeschenderNutzer->istAdmin) {
        flash("warning", "Administratoren können nicht gelöscht werden.");
        header("Location: index.php?page=nutzerliste");
        exit;
    }

    $ok = $dao->loesche($id);

    if ($ok) {
        flash("success", "Nutzer erfolgreich gelöscht.");
    } else {
        flash("error", "Fehler beim Löschen des Nutzers.");
    }

    header("Location: index.php?page=nutzerliste");
    exit;
}

/**
 * Nutzer löscht sein eigenes Konto
 */
function loescheEigenesKonto(): void {
    // Prüfen ob Nutzer angemeldet ist
    if (empty($_SESSION['nutzerId']) || !is_numeric($_SESSION['nutzerId'])) {
        flash("error", "Du bist nicht angemeldet.");
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $nutzerId = (int)$_SESSION['nutzerId'];

    // CSRF-Token prüfen
    try {
        require_once 'php/include/csrf_protection.php';
        checkCSRFToken();
    } catch (Exception $e) {
        flash("error", "Sicherheitsfehler. Bitte versuche es erneut.");
        header("Location: index.php?page=nutzer");
        exit;
    }

    $nutzerDAO = new NutzerDAO();

    // Nutzer-Daten vor Löschung abrufen (für Log/Bestätigung)
    $nutzer = $nutzerDAO->findeNachID($nutzerId);
    if (!$nutzer) {
        flash("error", "Nutzer nicht gefunden.");
        header("Location: index.php");
        exit;
    }

    // Admins können sich nicht selbst löschen (Sicherheitsmaßnahme)
    if ($nutzer->istAdmin) {
        flash("warning", "Administratoren können ihr eigenes Konto nicht löschen. Wende dich an einen anderen Administrator.");
        header("Location: index.php?page=nutzer");
        exit;
    }

    try {
        // Konto löschen (CASCADE DELETE löscht automatisch alle verknüpften Daten)
        $ok = $nutzerDAO->loesche($nutzerId);

        if ($ok) {
            // Session beenden
            session_destroy();

            // Erfolgs-Flash für die neue Session setzen
            session_start();
            flash("success", "Dein Konto wurde erfolgreich gelöscht. Wir bedauern, dass du uns verlässt.");

            // Zur Startseite weiterleiten
            header("Location: index.php");
            exit;
        } else {
            flash("error", "Fehler beim Löschen des Kontos. Bitte versuche es erneut oder kontaktiere den Support.");
            header("Location: index.php?page=nutzer");
            exit;
        }
    } catch (Exception $e) {
        error_log("Fehler beim Löschen des eigenen Kontos (Nutzer-ID: $nutzerId): " . $e->getMessage());
        flash("error", "Ein unerwarteter Fehler ist aufgetreten. Bitte versuche es später erneut.");
        header("Location: index.php?page=nutzer");
        exit;
    }
}