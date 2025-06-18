<?php
require_once '../php/model/RezeptDAO.php';

$query = trim($_GET['query'] ?? '');

if ($query === '') {
    echo "<p>Bitte gib einen Suchbegriff ein.</p>";
    exit;
}

$dao = new RezeptDAO();
$alleRezepte = $dao->findeAlle(); // oder gezielte Suchmethode

$gefiltert = array_filter($alleRezepte, function ($rezept) use ($query) {
    return stripos($rezept['Titel'] ?? '', $query) !== false;
});

if (empty($gefiltert)) {
    echo "<p>Keine Treffer für <strong>" . htmlspecialchars($query) . "</strong>.</p>";
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
