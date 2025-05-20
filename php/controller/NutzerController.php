<?php
// php/controller/NutzerController.php

require_once __DIR__ . '/../model/NutzerDAO.php';

// Anmeldung (Login-Formular anzeigen und verarbeiten)
function showAnmeldeFormular() {
    $fehler = "";
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST["email"]);
        $passwort = trim($_POST["passwort"]);

        if (empty($email) || empty($passwort)) {
            $fehler = "Bitte alle Felder ausfüllen.";
        } else {
            $nutzer = NutzerDAO::findeBenutzer($email, $passwort);
            if ($nutzer) {
                $_SESSION["benutzername"] = $nutzer['benutzername'];
                $_SESSION["email"] = $nutzer['email'];
                header("Location: index.php");
                exit;
            } else {
                $fehler = "Falsche E-Mail oder falsches Passwort.";
            }
        }
    }
    require 'php/view/anmeldung.php';
}

// Registrierung (Registrierungsformular anzeigen und verarbeiten)
function showRegistrierungsFormular() {
    $fehler = "";
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $benutzername = trim($_POST["benutzername"]);
        $email = trim($_POST["email"]);
        $passwort = trim($_POST["passwort"]);
        $passwort_wdh = trim($_POST["passwort-wdh"]);

        if (empty($benutzername) || empty($email) || empty($passwort) || empty($passwort_wdh)) {
            $fehler = "Bitte alle Felder ausfüllen.";
        } elseif ($passwort !== $passwort_wdh) {
            $fehler = "Die Passwörter stimmen nicht überein.";
        } elseif (NutzerDAO::findeBenutzerNachEmail($email)) {
            $fehler = "E-Mail ist bereits registriert.";
        } else {
            NutzerDAO::addBenutzer($benutzername, $email, $passwort);
            $_SESSION["benutzername"] = $benutzername;
            $_SESSION["email"] = $email;
            header("Location: index.php");
            exit;
        }
    }
    require 'php/view/registrierung.php';
}

// Nutzer abmelden (Logout)
function logoutUser() {
    session_unset();
    session_destroy();
    require 'php/view/abmeldung.php';
}

// Nutzerliste anzeigen
function showNutzerListe() {
    $nutzer = NutzerDAO::getAlleBenutzer();
    require 'php/view/nutzerliste.php';
}

// Einzelnes Nutzerprofil anzeigen (optional)
function showNutzerProfil($email = null) {
    $nutzer = null;
    if ($email !== null) {
        $nutzer = NutzerDAO::findeBenutzerNachEmail($email);
    }
    require 'php/view/nutzer.php';
}