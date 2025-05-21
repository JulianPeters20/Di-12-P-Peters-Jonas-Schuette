<main>
    <h2>Du wurdest abgemeldet</h2>
    <p>Du hast dich erfolgreich abgemeldet.</p>
    <p><a href="index.php?page=login">Zurück zur Anmeldung</a></p>
</main>

<?php
// Dateipfad: abmeldung.php

require_once 'php/controller/NutzerController.php';

// Aufrufen der Funktion logoutUser()
logoutUser();