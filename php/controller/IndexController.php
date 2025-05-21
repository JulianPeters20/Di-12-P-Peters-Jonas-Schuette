<?php
// Dateipfad: controller/IndexController.php

function showHome() {
    require_once 'php/model/RezeptDAO.php';
    $rezepte = RezeptDAO::findeNeueste();
    require 'php/view/index.php';
}

function showRezepte()
{
    // Hier ggf. Rezept-Logik/Dummy-Daten
    $rezepte = RezeptDAO::findeAlle();
    require 'php/view/rezepte.php';
}

function showRezeptNeu()
{
    // Hier ggf. Rezept-Neu-Logik/Dummy-Daten
    require 'php/view/rezept-neu.php';
}

function showImpressum()
{
    // Hier ggf. Impressum-Logik/Dummy-Daten
    require 'php/view/impressum.php';
}

function showDatenschutz()
{
    // Hier ggf. Datenschutz-Logik/Dummy-Daten
    require 'php/view/datenschutz.php';
}

function showNutzungsbedingungen()
{
    // Hier ggf. Nutzungsbedingungen-Logik/Dummy-Daten
    require 'php/view/nutzungsbedingungen.php';
}