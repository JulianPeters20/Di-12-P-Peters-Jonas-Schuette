<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/NutzerDAO.php';
require_once 'php/include/form_utils.php';

/**
 * Anmeldung (Login-Formular anzeigen und verarbeiten)
 */
function showAnmeldeFormular(): void {
    $fehler = "";
    $dao = new NutzerDAO();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = sanitize_email($_POST["email"] ?? '');
        $passwort = sanitize_text($_POST["passwort"] ?? '');

        if (empty($email) || empty($passwort)) {
            $fehler = "Bitte alle Felder ausfüllen.";
        } else {
            $nutzer = $dao->findeBenutzer($email, $passwort);
            if ($nutzer) {
                $_SESSION["benutzername"] = $nutzer->benutzername;
                $_SESSION["email"] = $nutzer->email;
                $_SESSION["nutzerId"] = $nutzer->id;
                $_SESSION["istAdmin"] = $nutzer->istAdmin;
                $_SESSION["eingeloggt"] = true;
                header("Location: index.php");
                exit;
            } else {
                $fehler = "Falsche E-Mail oder falsches Passwort.";
            }
        }
    }

    require 'php/view/anmeldung.php';
}

/**
 * Registrierung (Formular anzeigen und verarbeiten)
 */
function showRegistrierungsFormular(): void {
    $fehler = "";
    $dao = new NutzerDAO();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $benutzername = sanitize_text($_POST["benutzername"] ?? '');
        $email = sanitize_email($_POST["email"] ?? '');
        $passwort = sanitize_text($_POST["passwort"] ?? '');
        $passwort_wdh = sanitize_text($_POST["passwort-wdh"] ?? '');

        if (empty($benutzername) || empty($email) || empty($passwort) || empty($passwort_wdh)) {
            $fehler = "Bitte alle Felder ausfüllen.";
        } elseif ($passwort !== $passwort_wdh) {
            $fehler = "Die Passwörter stimmen nicht überein.";
        } elseif ($dao->findeNachEmail($email)) {
            $fehler = "E-Mail ist bereits registriert.";
        } else {
            $erfolg = $dao->registrieren($benutzername, $email, $passwort);
            if ($erfolg) {
                $_SESSION["benutzername"] = $benutzername;
                $_SESSION["email"] = $email;
                $_SESSION["eingeloggt"] = true;
                $_SESSION["message"] = "Registrierung erfolgreich.";
                header("Location: index.php");
                exit;
            } else {
                $fehler = "Fehler bei der Registrierung.";
            }
        }
    }

    require 'php/view/registrierung.php';
}

/**
 * Logout
 */
function logoutUser(): void {
    session_unset();
    session_destroy();
    require 'php/view/abmeldung.php';
}

/**
 * Nutzerprofil anzeigen
 */
function showNutzerProfil($email = null): void {

    $dao = new NutzerDAO();
    $nutzer = null;

    if ($email !== null) {
        $email = sanitize_email($email);
        if ($email === '') {
            $_SESSION["message"] = "Ungültige E-Mail-Adresse.";
            header("Location: index.php");
            exit;
        }

        $nutzer = $dao->findeNachEmail($email);
        if (!$nutzer) {
            $_SESSION["message"] = "Nutzer nicht gefunden.";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION["message"] = "Keine E-Mail übermittelt.";
        header("Location: index.php");
        exit;
    }

    require 'php/view/nutzer.php';
}


/**
 * Nutzerliste anzeigen
 */
function showNutzerListe(): void {
    session_start();

    // Nur Admins dürfen die Nutzerliste sehen
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        $_SESSION["message"] = "Nur Administratoren dürfen die Nutzerliste sehen.";
        header("Location: index.php");
        exit;
    }

    $dao = new NutzerDAO();
    $nutzer = $dao->findeAlle();

    require 'php/view/nutzerliste.php';
}

/**
 * Löscht einen Nutzer anhand seiner ID – nur für Admins erlaubt.
 */
function loescheNutzer(int $id): void {
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        $_SESSION["message"] = "Nur Administratoren dürfen Nutzer löschen.";
        header("Location: index.php");
        exit;
    }

    if ((int)$id === (int)($_SESSION['nutzerId'] ?? 0)) {
        $_SESSION["message"] = "Du kannst deinen eigenen Account nicht löschen.";
        header("Location: index.php?page=nutzerliste");
        exit;
    }

    $dao = new NutzerDAO();
    if ($dao->loesche($id)) {
        $_SESSION["message"] = "Nutzer erfolgreich gelöscht.";
    } else {
        $_SESSION["message"] = "Fehler beim Löschen des Nutzers.";
    }

    header("Location: index.php?page=nutzerliste");
    exit;
}