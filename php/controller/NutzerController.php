<?php

require_once __DIR__ . '/../model/NutzerDAO.php';
require_once __DIR__ . '/../include/form_utils.php';
require_once __DIR__ . '/../model/RezeptDAO.php';

function showAnmeldeFormular(): void {
    if ($_SERVER["REQUEST_METHOD"] === "GET" && !empty($_SESSION["eingeloggt"])) {
        header("Location: index.php");
        exit;
    }

    $dao = new NutzerDAO();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = sanitize_email($_POST["email"] ?? '');
        $passwort = sanitize_text($_POST["passwort"] ?? '');

        if ($email === '' || $passwort === '') {
            flash("warning","Bitte fülle alle Felder aus.");
            header("Location: index.php?page=anmeldung");
            exit;
        }

        $nutzer = $dao->findeNachEmail($email);

        if (!$nutzer) {
            flash("warning","Es existiert kein Konto mit dieser E-Mail-Adresse.");
            header("Location: index.php?page=anmeldung");
            exit;
        } elseif (!password_verify($passwort, $nutzer->passwortHash)) {
            flash("warning","Das Passwort ist falsch.");
            header("Location: index.php?page=anmeldung");
            exit;
        } else {
            $_SESSION["benutzername"] = $nutzer->benutzername;
            $_SESSION["email"] = $nutzer->email;
            $_SESSION["nutzerId"] = $nutzer->id;
            $_SESSION["istAdmin"] = $nutzer->istAdmin;
            $_SESSION["eingeloggt"] = true;

            header("Location: index.php");
            exit;
        }
    }

    require_once 'php/view/anmeldung.php';
}

function showRegistrierungsFormular(): void {
    if (!empty($_SESSION["eingeloggt"])) {
        header("Location: index.php");
        exit;
    }

    $dao = new NutzerDAO();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $benutzername = sanitize_text($_POST["benutzername"] ?? '');
        $email = sanitize_email($_POST["email"] ?? '');
        $passwort = sanitize_text($_POST["passwort"] ?? '');
        $passwort_wdh = sanitize_text($_POST["passwort-wdh"] ?? '');

        if ($benutzername === '' || $email === '' || $passwort === '' || $passwort_wdh === '') {
            flash("warning","Bitte alle Felder ausfüllen.");
            header("Location: index.php?page=registrierung");
            exit;
        } elseif ($passwort !== $passwort_wdh) {
            flash("warning","Die Passwörter stimmen nicht überein.");
            header("Location: index.php?page=registrierung");
            exit;
        } elseif ($dao->findeNachEmail($email)) {
            flash("warning","Diese E-Mail ist bereits registriert.");
            header("Location: index.php?page=registrierung");
            exit;
        } else {
            $erfolg = $dao->registrieren($benutzername, $email, $passwort);

            if ($erfolg) {
                flash("success","Registrierung erfolgreich. Bitte melde dich nun an.");
                header("Location: index.php?page=anmeldung");
                exit;
            } else {
                flash("error","Fehler bei der Registrierung");
                header("Location: index.php?page=registrierung");
                exit;
            }
        }
    }

    require_once 'php/view/registrierung.php';
}

function pruefeBenutzername(): void {
    // Da sonst html mit übergeben wird, was zum Fehler führt
    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');

    // Überprüfung, ob aufgerufen wird
    error_log("AJAX Prüfe Benutzername wurde aufgerufen");

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
        $_SESSION["message"] = "Du bist nicht eingeloggt.";
        header("Location: index.php?page=anmeldung");
        exit;
    }

    $nutzerDAO = new NutzerDAO();
    $nutzer = $nutzerDAO->findeNachID((int)$_SESSION['nutzerId']);

    if (!$nutzer) {
        $_SESSION["message"] = "Nutzerprofil konnte nicht geladen werden.";
        header("Location: index.php");
        exit;
    }

    // Eigene Rezepte laden
    $rezeptDAO = new RezeptDAO();
    $rezepte = $rezeptDAO->findeNachErstellerID($nutzer->id);

    // Sichtbar machen für View
    require 'php/view/nutzer.php';
}

/**
 * Nutzerliste anzeigen (nur Admin)
 */
function showNutzerListe(): void {
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        $_SESSION["message"] = "Nur Administratoren dürfen die Nutzerliste sehen.";
        header("Location: index.php");
        exit;
    }

    $dao = new NutzerDAO();
    $nutzer = $dao->findeAlle();

    require_once 'php/view/nutzerliste.php';
}

/**
 * Löscht einen Nutzer anhand seiner ID (nur Admin)
 */
function loescheNutzer(int $id): void {
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        $_SESSION["message"] = "Nur Administratoren dürfen Nutzer löschen.";
        header("Location: index.php");
        exit;
    }

    if (isset($_SESSION['nutzerId']) && (int)$_SESSION['nutzerId'] === $id) {
        $_SESSION["message"] = "Du kannst deinen eigenen Account nicht löschen.";
        header("Location: index.php?page=nutzerliste");
        exit;
    }

    $dao = new NutzerDAO();
    $ok = $dao->loesche($id);

    $_SESSION["message"] = $ok
        ? "Nutzer erfolgreich gelöscht."
        : "Fehler beim Löschen des Nutzers.";

    header("Location: index.php?page=nutzerliste");
    exit;
}