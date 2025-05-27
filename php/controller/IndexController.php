<?php
declare(strict_types=1);

/**
 * Startseite mit den neuesten Rezepten anzeigen
 */
function showHome(): void {
    require_once 'php/model/RezeptDAO.php';
    $rezepte = RezeptDAO::findeNeueste();
    require 'php/view/index.php';
}

/**
 * (Rezeptübersicht – gibt es auch in RezeptController, hier nur der Vollständigkeit halber)
 */
function showRezepte(): void {
    $rezepte = RezeptDAO::findeAlle();
    require 'php/view/rezepte.php';
}

/**
 * Anzeige neues Rezeptformular
 */
function showRezeptNeu(): void {
    require 'php/view/rezept-neu.php';
}

/** Weitere Funktions-Stubs */
function showImpressum(): void { require 'php/view/impressum.php'; }
function showDatenschutz(): void { require 'php/view/datenschutz.php'; }
function showNutzungsbedingungen(): void { require 'php/view/nutzungsbedingungen.php'; }