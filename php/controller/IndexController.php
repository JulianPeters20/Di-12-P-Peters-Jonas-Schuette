<?php
// Dateipfad: controller/IndexController.php

function showHome() {
    // Hier ggf. Startseitenlogik/Dummy-Daten
    require 'views/index.php';
}

function showRezepte()
{
    // Hier ggf. Rezept-Logik/Dummy-Daten
    $rezepte = RezeptDAO::findeAlle();
    require 'views/rezepte.php';
}

function showRezeptNeu()
{
    // Hier ggf. Rezept-Neu-Logik/Dummy-Daten
    require 'views/rezept-neu.php';
}

function showImpressum()
{
    // Hier ggf. Impressum-Logik/Dummy-Daten
    require 'views/impressum.php';
}

function showDatenschutz()
{
    // Hier ggf. Datenschutz-Logik/Dummy-Daten
    require 'views/datenschutz.php';
}

function showNutzungsbedingungen()
{
    // Hier ggf. Nutzungsbedingungen-Logik/Dummy-Daten
    require 'views/nutzungsbedingungen.php';
}