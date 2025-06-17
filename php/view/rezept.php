<main>
    <?php if (!empty($rezept)): ?>
        <article class="rezept-detail">
            <header>
                <h2 class="rezept-titel"><?= htmlspecialchars($rezept['titel'] ?? 'Unbekannt') ?></h2>
            </header>

            <?php
            $projektRoot = realpath(__DIR__ . '/images/'); // Pfad zum Projektstamm, ggf. anpassen
            $bildDatei = $projektRoot . ($rezept['bild'] ?? '');

            if (!empty($rezept['bild']) && file_exists($bildDatei)) {
                $bildUrl = $rezept['bild'];
            } else {
                echo "<p>Datei existiert NICHT am erwarteten Pfad: " . htmlspecialchars($bildDatei) . "</p>";
                $bildUrl = '/images/placeholder.jpg';
            }
            ?>

            <img src="<?= htmlspecialchars($bildUrl) ?>"
                 alt="<?= htmlspecialchars($rezept['titel'] ?? 'Rezeptbild') ?>"
                 style="max-width:300px;">

            <section class="rezept-block">
                <h3>Kategorien</h3>
                <?php if (!empty($rezept['kategorien'])): ?>
                    <ul>
                        <?php foreach ($rezept['kategorien'] as $kat): ?>
                            <li><?= htmlspecialchars($kat) ?></li>
                        <?php endforeach; ?>
                    </ul>

                <?php else: ?>
                    <p>Keine Kategorien</p>
                <?php endif; ?>
            </section>

            <section class="rezept-block">
                <h3>Datum</h3>
                <p><?= htmlspecialchars($rezept['datum'] ?? '-') ?></p>
            </section>

            <section class="rezept-block">
                <h3>Autor</h3>
                <p><?= htmlspecialchars($rezept['erstellerName'] ?? '-') ?> (<?= htmlspecialchars($rezept['erstellerEmail'] ?? '-') ?>)</p>
            </section>

            <section class="rezept-block">
                <h3>Preisklasse</h3>
                <p><?= htmlspecialchars($rezept['preisklasseName'] ?? '-') ?></p>
            </section>

            <section class="rezept-block">
                <h3>Portionsgröße</h3>
                <p><?= htmlspecialchars($rezept['portionsgroesseName'] ?? '-') ?></p>
            </section>

            <section class="rezept-block">
                <h3>Zutaten</h3>
                <?php if (!empty($rezept['zutaten']) && is_array($rezept['zutaten'])): ?>
                    <ul>
                        <?php foreach ($rezept['zutaten'] as $z): ?>
                            <li>
                                <?= htmlspecialchars($z['menge'] ?? '') ?>
                                <?= htmlspecialchars($z['einheit'] ?? '') ?>
                                <?= htmlspecialchars($z['zutat'] ?? '') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Keine Zutaten angegeben.</p>
                <?php endif; ?>
            </section>

            <?php if (!empty($rezept['utensilien'])): ?>
                <section class="rezept-block">
                    <h3>Küchenutensilien</h3>
                    <ul>
                        <?php foreach ($rezept['utensilien'] as $utensil): ?>
                            <li><?= htmlspecialchars($utensil['Name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <section class="rezept-block">
                <h3>Zubereitung</h3>
                <pre class="rezept-pre"><?= htmlspecialchars($rezept['zubereitung'] ?? 'Keine Angabe.') ?></pre>
            </section>

            <?php
            $darfBearbeiten = false;
            if (isset($_SESSION['nutzerId'])) {
                $darfBearbeiten = (
                    (int)$_SESSION['nutzerId'] === (int)($rezept['erstellerId'] ?? 0)
                    || !empty($_SESSION['istAdmin'])
                );
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
        <div class="message-box">Rezept nicht gefunden.</div>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align:center;">
        <a href="index.php?page=rezepte" class="btn">Zurück zur Übersicht</a>
    </div>
</main>