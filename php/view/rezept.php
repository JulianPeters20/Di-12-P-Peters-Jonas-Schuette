<main>
    <?php if (!empty($rezept)): ?>
        <article class="rezept-detail">
            <header>
                <h2 class="rezept-titel"><?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?></h2>
            </header>
            <div class="rezept-detail-content">
            <?php
            $bildPfad = !empty($rezept['BildPfad']) && file_exists($rezept['BildPfad'])
                ? $rezept['BildPfad']
                : 'images/placeholder.jpg'; // Standardbild verwenden, wenn kein Bild vorhanden ist
            ?>
            <img src="<?= htmlspecialchars($bildPfad) ?>"
                 alt="<?= htmlspecialchars($rezept['Titel'] ?? 'Rezeptbild') ?>"
                 style="max-width:300px;">
            <?php
            $kats = $rezept['kategorien'] ?? [];
            if (is_array($kats) && count($kats) > 0) {
                $katText = implode(', ', array_map('htmlspecialchars', $kats));
            } else {
                $katText = '-';
            }
            ?>
            <dl>
                <dt>Kategorien-IDs:</dt>
                <dd><?= $katText ?></dd>
                <dt>Datum:</dt>
                <dd><?= htmlspecialchars($rezept['Erstellungsdatum'] ?? '-') ?></dd>
                <dt>Autor-ID:</dt>
                <dd><?= htmlspecialchars($rezept['ErstellerID'] ?? '-') ?></dd>
            </dl>
            </div>

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

            <section>
            <?php if (!empty($rezept['utensilien'])): ?>
                <section class="rezept-block">
                    <h3>Küchenutensilien</h3>
                    <pre class="rezept-pre"><?= htmlspecialchars($rezept['utensilien']) ?></pre>
                </section>
            <?php endif; ?>
            <section class="rezept-block">
                <h3>Zubereitung</h3>
                <pre  class="rezept-pre"><?= htmlspecialchars($rezept['Zubereitung'] ?? 'Keine Angabe.') ?></pre>
            </section>

            <?php
            $darfBearbeiten = false;
            if (isset($_SESSION['nutzerId'])) {
                $darfBearbeiten = (
                    (int)$_SESSION['nutzerId'] === (int)($rezept['ErstellerID'] ?? 0)
                    || !empty($_SESSION['istAdmin'])
                );
            }
            ?>

            <?php if ($darfBearbeiten): ?>
                <div class="rezept-aktion" style="margin-top: 16px;">
                    <a href="index.php?page=rezept-bearbeiten&id=<?= urlencode($rezept['RezeptID']) ?>" class="btn">Bearbeiten</a>
                    <a href="index.php?page=rezept-loeschen&id=<?= urlencode($rezept['RezeptID']) ?>"
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