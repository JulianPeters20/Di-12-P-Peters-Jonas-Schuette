<?php
// Dateipfad: controller/IndexController.php

declare(strict_types=1);

require_once 'php/model/RezeptDAO.php';
require_once 'php/model/BewertungDAO.php';

function showHome(): void
{
    $dao = new RezeptDAO();
    $bewertungDAO = new BewertungDAO();

    // Beliebte Rezepte (nach Anzahl Bewertungen sortiert) - 3 Stück
    $beliebteste = $dao->findeBeliebteste(3);
    foreach ($beliebteste as &$rezept) {
        $rezeptID = $rezept['RezeptID'] ?? 0;
        $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
        $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
    }
    unset($rezept);

    // Bestbewertete Rezepte (min. 3 Bewertungen, nach Durchschnitt sortiert) - 3 Stück
    $bestBewertete = $dao->findeBestBewertete(3);
    foreach ($bestBewertete as &$rezept) {
        $rezeptID = $rezept['RezeptID'] ?? 0;
        $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
        $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
    }
    unset($rezept);

    // Neueste Rezepte - 3 Stück
    $alle = $dao->findeAlle();
    $neuesteRezepte = array_slice($alle, 0, 3);
    foreach ($neuesteRezepte as &$rezept) {
        $rezeptID = $rezept['RezeptID'] ?? 0;
        $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
        $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
    }
    unset($rezept);

    require 'php/view/index.php';
}

function showRezepte(): void
{
    $dao = new RezeptDAO();
    // Nur die neuesten 9 Rezepte für die Rezepte-Seite
    $alleRezepte = $dao->findeNeuesteLimitiert(9);

    $bewertungDAO = new BewertungDAO();

    foreach ($alleRezepte as &$rezept) {
        $rezeptID = $rezept['RezeptID'] ?? 0;
        $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
        $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
    }
    unset($rezept);

    $rezepte = $alleRezepte;
    require 'php/view/rezepte.php';
}

function showRezeptNeu(): void
{
    require_once 'php/model/KategorieDAO.php';
    require_once 'php/model/UtensilDAO.php';

    $katDAO = new KategorieDAO();
    $utenDAO = new UtensilDAO();

    $_SESSION['kategorienListe'] = $katDAO->findeAlle();
    $_SESSION['utensilienListe'] = $utenDAO->findeAlle();

    require 'php/view/rezept-neu.php';
}

function showImpressum(): void
{
    require 'php/view/impressum.php';
}

function showDatenschutz(): void
{
    require 'php/view/datenschutz.php';
}

function showNutzungsbedingungen(): void
{
    require 'php/view/nutzungsbedingungen.php';
}