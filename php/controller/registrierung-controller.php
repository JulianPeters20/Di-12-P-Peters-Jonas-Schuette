<?php
// php/controller/registrierung-controller.php

session_start();

require_once __DIR__ . '/../model/NutzerDAO.php';

$fehler = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $benutzername = trim($_POST["benutzername"]);
    $passwort = trim($_POST["passwort"]);

    if (empty($benutzername) || empty($passwort)) {
        $fehler = "Bitte alle Felder ausfüllen.";
    } elseif (NutzerDAO::findeNutzer($benutzername)) {
        $fehler = "Benutzername existiert bereits.";
    } else {
        $nutzer = new Nutzer($benutzername, $passwort);
        NutzerDAO::addNutzer($nutzer);
        $_SESSION["benutzername"] = $benutzername;
        header("Location: index.html");
        exit;
    }
}

require_once 'php/controller/registrierung-controller.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrierung</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Registrieren</h1>

    <?php if (!empty($fehler)) echo "<p style='color:red;'>$fehler</p>"; ?>

    <form action="registrierung.php" method="post">
        <label>Benutzername: <input type="text" name="benutzername"></label><br><br>
        <label>Passwort: <input type="password" name="passwort"></label><br><br>
        <button type="submit">Registrieren</button>
    </form>

    <p><a href="../index.php">Zurück zur Startseite</a></p>
</body>
</html>

