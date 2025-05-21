<main>
    <h2 style="margin-top: 30px; margin-bottom: 20px;">Beliebte Rezepte</h2>

    <div class="rezept-galerie">
        <?php foreach ($rezepte as $rezept): ?>
            <div class="rezept-karte">
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
            </div>
        <?php endforeach; ?>
    </div>

</main>