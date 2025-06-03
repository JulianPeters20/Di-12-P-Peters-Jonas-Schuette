<main>
    <?php if (!empty($rezept)): ?>
        <article>
            <header>
                <h2><?= htmlspecialchars($rezept['titel']) ?></h2>
            </header>
            <?php
            $bildPfad = !empty($rezept['bild']) && file_exists($rezept['bild'])
                ? $rezept['bild']
                : 'images/placeholder.jpg'; // Standardbild verwenden, wenn kein Bild vorhanden ist
            ?>
            <img src="<?= htmlspecialchars($bildPfad) ?>"
                 alt="<?= htmlspecialchars($rezept['titel']) ?>"
                 style="max-width:300px;">
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
                <?php if (!empty($rezept['zutaten']) && is_array($rezept['zutaten'])): ?>
                    <ul>
                        <?php foreach ($rezept['zutaten'] as $z): ?>
                            <li><?= htmlspecialchars($z['menge']) ?> <?= htmlspecialchars($z['einheit']) ?> <?= htmlspecialchars($z['zutat']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Keine Zutaten angegeben.</p>
                <?php endif; ?>
            </section>

            <section>
                <h3>Zubereitung</h3>
                <pre><?= htmlspecialchars($rezept['zubereitung']) ?></pre>
            </section>

            <?php
            $darfBearbeiten = false;
            if (isset($_SESSION['nutzerId'])) {
                $darfBearbeiten = ((int)$_SESSION['nutzerId'] === (int)($rezept['erstellerId'] ?? 0)) || !empty($_SESSION['istAdmin']);
            }
            ?>

            <?php if ($darfBearbeiten): ?>
                <div class="rezept-aktion" style="margin-top: 16px;">
                    <a href="index.php?page=rezept-bearbeiten&id=<?= urlencode($rezept['id']) ?>" class="btn">Bearbeiten</a>
                    <a href="index.php?page=rezept-loeschen&id=<?= urlencode($rezept['id']) ?>"
                       class="btn"
                       onclick="return confirm('Möchtest du dieses Rezept wirklich löschen?');">
                        Löschen
                    </a>
                </div>
            <?php endif; ?>
        </article>
    <?php else: ?>
        <div>Rezept nicht gefunden.</div>
    <?php endif; ?>

    <a href="index.php?page=rezepte">Zurück zur Übersicht</a>
</main>