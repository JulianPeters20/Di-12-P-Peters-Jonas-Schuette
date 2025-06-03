<main>
    <h2 class="mb-2 mt-3">Beliebte Rezepte</h2>

    <ul class="rezept-galerie">
        <?php foreach ($rezepte as $rezept): ?>
            <li class="rezept-karte">
                <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="<?= htmlspecialchars($rezept['titel']) ?>">
                <div class="inhalt">
                    <h3>
                        <a href="index.php?page=rezept&id=<?= $rezept['id'] ?>">
                            <?= htmlspecialchars($rezept['titel']) ?>
                        </a>
                    </h3>
                    <div class="meta">
                        <?php
                        if (is_array($rezept['kategorie'])) {
                            echo htmlspecialchars(implode(', ', $rezept['kategorie']));
                        } else {
                            echo htmlspecialchars($rezept['kategorie']);
                        }
                        ?> · <?= htmlspecialchars($rezept['datum']) ?> · <?= htmlspecialchars($rezept['autor']) ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>