<main>
    <?php if (!isset($rezepte) || !is_array($rezepte)) $rezepte = []; ?>
    <h2 class="mb-2 mt-3">Beliebte Rezepte</h2>

    <ul class="rezept-galerie">
        <?php foreach ($rezepte as $rezept): ?>
            <li class="rezept-karte">
                <img src="<?= htmlspecialchars($rezept['BildPfad'] ?? 'images/placeholder.jpg') ?>"
                     alt="<?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>">
                <div class="inhalt">
                    <h3>
                        <a href="index.php?page=rezept&id=<?= urlencode($rezept['RezeptID'] ?? 0) ?>">
                            <?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>
                        </a>
                    </h3>
                    <div class="meta">
                        <?php
                        // Kategorien: IDs, solange keine Namen geladen werden
                        $kategorien = $rezept['kategorien'] ?? [];
                        if (is_array($kategorien) && count($kategorien) > 0) {
                            echo 'Kategorien-IDs: ' . htmlspecialchars(implode(', ', $kategorien));
                        } else {
                            echo '-';
                        }

                        echo ' · ' . htmlspecialchars($rezept['Erstellungsdatum'] ?? '-');
                        echo ' · Autor-ID: ' . htmlspecialchars($rezept['ErstellerID'] ?? '-');
                        ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>