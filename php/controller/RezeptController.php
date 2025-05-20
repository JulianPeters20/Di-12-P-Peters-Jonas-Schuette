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
?>