<main>
    <?php if (!isset($rezepte) || !is_array($rezepte)) $rezepte = []; ?>
    <h2 class="mb-2 mt-3">Beliebte Rezepte</h2>

    <ul class="rezept-galerie">
        <?php foreach ($rezepte as $rezept): ?>
            <li class="rezept-karte">
                <img src="<?= htmlspecialchars($rezept['bild'] ?? 'images/platzhalter.jpg') ?>" alt="<?= htmlspecialchars($rezept['titel'] ?? 'Unbekannt') ?>">
                <div class="inhalt">
                    <h3>
                        <a href="index.php?page=rezept&id=<?= urlencode($rezept['id'] ?? 0) ?>">
                            <?= htmlspecialchars($rezept['titel'] ?? 'Unbekannt') ?>
                        </a>
                    </h3>
                    <div class="meta">
                        <?php
                        $kategorie = $rezept['kategorie'] ?? '-';
                        if (is_array($kategorie)) {
                            echo htmlspecialchars(implode(', ', $kategorie));
                        } else {
                            echo htmlspecialchars($kategorie);
                        }

                        echo ' · ' . htmlspecialchars($rezept['datum'] ?? '-') . ' · ' . htmlspecialchars($rezept['autor'] ?? '-');
                        ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>