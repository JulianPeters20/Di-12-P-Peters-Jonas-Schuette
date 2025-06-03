<?php
// Dateipfad: controller/IndexController.php

declare(strict_types=1);

require_once 'php/model/RezeptDAO.php';

function showHome(): void
{
    $rezepte = RezeptDAO::findeNeueste();
    require 'php/view/index.php';
}

function showRezepte(): void
{
    $rezepte = RezeptDAO::findeAlle();
    require 'php/view/rezepte.php';
}

function showRezeptNeu(): void
{
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