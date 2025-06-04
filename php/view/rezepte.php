<main>
    <h2 class="mb-2 mt-3">Beliebte Rezepte</h2>
    <ul class="rezept-galerie">
        <?php if (!empty($rezepte)) : ?>
            <?php foreach ($rezepte as $rezept): ?>
                <li class="rezept-karte">
                    <img src="<?= htmlspecialchars($rezept['BildPfad'] ?? 'images/platzhalter.jpg') ?>" alt="<?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>">
                    <div class="inhalt">
                        <h3>
                            <a href="index.php?page=rezept&id=<?= urlencode($rezept['RezeptID'] ?? 0) ?>">
                                <?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>
                            </a>
                        </h3>
                        <div class="meta">
                            <?php
                            // Kategorien sind IDs – du könntest sie noch auf Namen mappen
                            echo 'Kategorien-IDs: ' . htmlspecialchars(implode(', ', $rezept['kategorien'] ?? []));
                            echo ' · ' . htmlspecialchars($rezept['Erstellungsdatum'] ?? '-');
                            echo ' · Autor-ID: ' . htmlspecialchars($rezept['ErstellerID'] ?? '-');
                            ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Keine Rezepte vorhanden.</li>
        <?php endif; ?>
    </ul>
</main>