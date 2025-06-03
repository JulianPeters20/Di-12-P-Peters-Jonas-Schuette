<main>
    <?php if (!empty($rezept)): ?>
        <article class="rezept-detail">
            <h2 class="rezept-titel"><?= htmlspecialchars($rezept['titel']) ?></h2>
            <div class="rezept-detail-content">
                <?php if (!empty($rezept['bild'])): ?>
                    <img class="rezept-detail-bild" src="<?= htmlspecialchars($rezept['bild']) ?>" alt="<?= htmlspecialchars($rezept['titel']) ?>">
                <?php endif; ?>
                <dl class="rezept-info">
                    <?php
                    // Kategorie(n) als Text:
                    $kats = $rezept['kategorie'] ?? '';
                    if (is_array($kats)) {
                        $katText = implode(', ', array_map('htmlspecialchars', $kats));
                    } else {
                        $katText = htmlspecialchars($kats);
                    }
                    ?>
                    <dt>Kategorie:</dt>
                    <dd><?= $katText ?></dd>

                    <?php if (!empty($rezept['portionsgroesse'])): ?>
                        <dt>Portionsgröße:</dt>
                        <dd><?= htmlspecialchars($rezept['portionsgroesse']) ?></dd>
                    <?php endif; ?>

                    <?php if (!empty($rezept['preis'])): ?>
                        <dt>Kosten für Zutaten:</dt>
                        <dd>
                            <?php
                            $preise = [
                                "lt5" => "&lt; 5 €",
                                "5 - 10" => "5 bis 10 €",
                                "10 - 15" => "10 bis 15 €",
                                "15-20" => "15 bis 20 €",
                                "gt20" => "&gt; 20 €"
                            ];
                            $pr = $rezept['preis'];
                            echo isset($preise[$pr]) ? $preise[$pr] : htmlspecialchars($pr);
                            ?>
                        </dd>
                    <?php endif; ?>

                    <?php if (!empty($rezept['datum'])): ?>
                        <dt>Datum:</dt>
                        <dd><?= htmlspecialchars($rezept['datum']) ?></dd>
                    <?php endif; ?>

                    <?php if (!empty($rezept['autor'])): ?>
                        <dt>Autor:</dt>
                        <dd><?= htmlspecialchars($rezept['autor']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>

            <section class="rezept-block">
                <h3>Zutaten</h3>
                <pre class="rezept-pre"><?= htmlspecialchars($rezept['zutaten']) ?></pre>
            </section>
            <?php if (!empty($rezept['utensilien'])): ?>
                <section class="rezept-block">
                    <h3>Küchenutensilien</h3>
                    <pre class="rezept-pre"><?= htmlspecialchars($rezept['utensilien']) ?></pre>
                </section>
            <?php endif; ?>
            <section class="rezept-block">
                <h3>Zubereitung</h3>
                <pre class="rezept-pre"><?= htmlspecialchars($rezept['zubereitung']) ?></pre>
            </section>
        </article>
    <?php else: ?>
        <div class="message-box">Rezept nicht gefunden.</div>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align:center;">
        <a href="index.php?page=rezepte" class="btn">Zurück zur Übersicht</a>
    </div>
</main>