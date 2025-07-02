<?php
declare(strict_types=1);

// Sichere Headers setzen
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

session_start();
require_once '../php/model/RezeptDAO.php';
require_once '../php/include/form_utils.php';

// Input-Validierung und Sanitization
$query = trim($_GET['query'] ?? '');
$query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

// Minimale Länge für Suchbegriff
if ($query === '' || strlen($query) < 2) {
    flash("warning", "Bitte gib einen Suchbegriff mit mindestens 2 Zeichen ein.");
    header("Location: ../index.php?page=rezepte");
    exit;
}

// Maximale Länge begrenzen
if (strlen($query) > 100) {
    flash("warning", "Suchbegriff zu lang (maximal 100 Zeichen).");
    header("Location: ../index.php?page=rezepte");
    exit;
}

$dao = new RezeptDAO();
$alleRezepte = $dao->findeAlle(); // oder gezielte Suchmethode

$gefiltert = array_filter($alleRezepte, function ($rezept) use ($query) {
    return stripos($rezept['Titel'] ?? '', $query) !== false;
});

if (empty($gefiltert)) {
    flash("info", "Keine Treffer für \"" . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . "\" gefunden.");
    header("Location: ../index.php?page=rezepte");
    exit;
}

foreach ($gefiltert as $rezept): ?>
    <div class="rezept-karte">
        <img src="<?= htmlspecialchars($rezept['BildPfad'] ?? 'images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($rezept['Titel']) ?>">
        <div class="inhalt">
            <h4><?= htmlspecialchars($rezept['Titel']) ?></h4>
            <p><?= htmlspecialchars($rezept['Erstellungsdatum']) ?></p>
        </div>
    </div>
<?php endforeach;
