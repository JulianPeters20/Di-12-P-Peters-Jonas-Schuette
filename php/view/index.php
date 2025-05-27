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
                    <p class="meta">
                        <?= htmlspecialchars($rezept['kategorie']) ?> · <?= htmlspecialchars($rezept['datum']) ?> · <?= htmlspecialchars($rezept['autor']) ?>
                    </p>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>