<?php
// Dateipfad: controller/IndexController.php

declare(strict_types=1);

require_once 'php/model/RezeptDAO.php';

function showHome(): void
{
    $dao = new RezeptDAO();
    $alle = $dao->findeAlle();
    $rezepte = array_slice($alle, 0, 3); // neueste 3
    require 'php/view/index.php';
}

function showRezepte(): void
{
    $dao = new RezeptDAO();
    $rezepte = $dao->findeAlle();
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