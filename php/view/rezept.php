<main>
    <?php if (!empty($rezept)): ?>
        <article>
            <header>
                <h2><?= htmlspecialchars($rezept['titel']) ?></h2>
            </header>
            <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="<?= htmlspecialchars($rezept['titel']) ?>" style="max-width:300px;">
            <?php
            $kats = $rezept['kategorie'];
            if (is_array($kats)) {
                $katText = implode(', ', array_map('htmlspecialchars', $kats));
            } else {
                $katText = htmlspecialchars($kats);
            }
            ?>
            <dl>
                <dt>Kategorie:</dt>
                <dd><?= $katText ?></dd>
                <dt>Datum:</dt>
                <dd><?= htmlspecialchars($rezept['datum']) ?></dd>
                <dt>Autor:</dt>
                <dd><?= htmlspecialchars($rezept['autor']) ?></dd>
            </dl>
            <section>
                <h3>Zutaten</h3>
                <pre><?= htmlspecialchars($rezept['zutaten']) ?></pre>
            </section>
            <section>
                <h3>Zubereitung</h3>
                <pre><?= htmlspecialchars($rezept['zubereitung']) ?></pre>
            </section>
        </article>
    <?php else: ?>
        <div>Rezept nicht gefunden.</div>
    <?php endif; ?>
    <a href="index.php?page=rezepte">Zurück zur Übersicht</a>
</main>