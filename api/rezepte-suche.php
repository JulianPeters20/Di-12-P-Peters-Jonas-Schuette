<?php
declare(strict_types=1);

// Sichere Headers setzen
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

session_start();
require_once '../php/model/RezeptDAO.php';
require_once '../php/model/BewertungDAO.php';

// Input-Validierung und Sanitization
$query = trim($_GET['query'] ?? '');

// Minimale Länge für Suchbegriff - für AJAX keine Redirects!
if ($query === '' || strlen($query) < 2) {
    echo '<p class="search-info">Bitte mindestens 2 Zeichen eingeben.</p>';
    exit;
}

// Maximale Länge begrenzen
if (strlen($query) > 100) {
    echo '<p class="search-error">Suchbegriff zu lang (maximal 100 Zeichen).</p>';
    exit;
}

$dao = new RezeptDAO();
$bewertungDAO = new BewertungDAO();
$alleRezepte = $dao->findeAlle();

// Erweiterte Suche: Rezeptname, Kategorien und Autor
$gefiltert = array_filter($alleRezepte, function ($rezept) use ($query) {
    $suchbegriff = strtolower($query);

    // Suche in Rezeptname (Titel)
    if (stripos($rezept['Titel'] ?? '', $suchbegriff) !== false) {
        return true;
    }

    // Suche in Kategorien
    $kategorien = $rezept['kategorien'] ?? [];
    if (is_array($kategorien)) {
        foreach ($kategorien as $kategorie) {
            if (stripos($kategorie, $suchbegriff) !== false) {
                return true;
            }
        }
    }

    // Suche in Autor (Benutzername)
    if (stripos($rezept['erstellerName'] ?? '', $suchbegriff) !== false) {
        return true;
    }

    return false;
});

if (empty($gefiltert)) {
    echo '<p class="search-no-results">Keine Rezepte für "' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . '" gefunden.</p>';
    exit;
}

// Bewertungen zu den gefilterten Rezepten hinzufügen
foreach ($gefiltert as &$rezept) {
    $rezeptID = $rezept['RezeptID'] ?? 0;
    $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
    $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
}
unset($rezept);

// Suchergebnisse als vollständige HTML-Liste ausgeben
?>
<ul class="rezept-galerie">
<?php foreach ($gefiltert as $rezept): ?>
    <?php include '../php/include/rezept-karte.php'; ?>
<?php endforeach; ?>
</ul>
