<main>
    <h2>Willkommen bei Broke & Hungry</h2>
    <p>Hier findest du günstige, kreative und leckere Rezepte für den studentischen Alltag.</p>
    <a href="index.php?page=rezepte" class="btn">Zu den Rezepten</a>
</main>

<?php
// Dateipfad: index.php

require_once 'php/controller/IndexController.php';

// Aufrufen der Funktion showHome()
showHome();

// Aufrufen der Funktion showRezepte()
showRezepte();