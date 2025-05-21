<?php
session_start();
require_once 'php/model/NutzerDAO.php';

$fehlermeldung = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $passwort = $_POST['passwort'] ?? '';

    $nutzer = NutzerDAO::findeNachEmail($email);

    if ($nutzer && password_verify($passwort, $nutzer->passwort)) {
        $_SESSION['eingeloggt'] = true;
        $_SESSION['benutzername'] = $nutzer->benutzername;
        $_SESSION['email'] = $nutzer->email;
        header('Location: index.php');
        exit();
    } else {
        $fehlermeldung = 'E-Mail oder Passwort ist ungültig.';
    }
}

// Rezept anzeigen
function showRezept()
{
    $id = $_GET['id'];
    $rezept = RezeptDAO::findeNachId($id);
    if ($rezept) {
        require 'php/view/rezept.php';
    } else {
        require 'php/view/rezept-nicht-gefunden.php';
    }
}

// Rezepte anzeigen
function showRezepte()
{
    $rezepte = RezeptDAO::findeAlle();
    require 'php/view/rezepte.php';
}

// Neues Rezept erstellen
function showRezeptNeu()
{
    require 'php/view/rezept-neu.php';
}

// Rezept speichern
function speichereRezept()
{
    $titel = $_POST['titel'];
    $kategorie = $_POST['kategorie'];
    $bild = $_FILES['bild'];
    $datum = date('d.m.Y');
    $autor = $_SESSION['benutzername'];

    if ($bild['error'] == 0) {
        $bildName = $bild['name'];
        $bildTyp = $bild['type'];
        $bildGröße = $bild['size'];
        $bildTemp = $bild['tmp_name'];

        $rezept = RezeptDAO::addRezept($titel, $kategorie, $bildName, $datum, $autor);
        if ($rezept) {
            header('Location: index.php?page=rezepte');
            exit;
        } else {
            require 'php/view/rezept-speichern-fehlgeschlagen.php';
        }
    } else {
        require 'php/view/rezept-speichern-fehlgeschlagen.php';
    }
}
?>