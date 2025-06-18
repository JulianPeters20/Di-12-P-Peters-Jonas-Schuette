<main>
    <?php if (!empty($rezept)): ?>
        <article class="rezept-detail">
            <header>
                <h2 class="rezept-titel"><?= htmlspecialchars($rezept['titel'] ?? 'Unbekannt') ?></h2>
            </header>

            <section class="rezept-block">
                <h3>Durchschnittliche Bewertung</h3>
                <?php if (isset($durchschnitt) && $durchschnitt !== null && isset($anzahlBewertungen) && $anzahlBewertungen > 0): ?>
                    <p>
                        <?php
                        $sterne = round($durchschnitt);
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $sterne ? '★' : '☆';
                        }
                        ?>
                        (<?= number_format($durchschnitt, 2) ?> Sterne aus <?= $anzahlBewertungen ?> Bewertung<?= $anzahlBewertungen > 1 ? 'en' : '' ?>)
                    </p>
                <?php else: ?>
                    <p>Dieses Rezept wurde noch nicht bewertet.</p>
                <?php endif; ?>
            </section>

            <?php
            $projektRoot = realpath(__DIR__ . '/images/'); // Pfad anpassen falls nötig
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

            <!-- Bewertungsformular nur, wenn angemeldet und nicht Ersteller -->
            <?php if (!empty($_SESSION['nutzerId']) && isset($istEigenerErsteller) && !$istEigenerErsteller): ?>
                <section class="rezept-block">
                    <h3>Deine Bewertung</h3>
                    <form action="index.php?page=bewerteRezept" method="post" id="bewertungs-form" style="display:inline-block;">
                        <input type="hidden" name="rezeptId" value="<?= htmlspecialchars($rezept['id']) ?>">
                        <div id="star-rating" style="font-size: 2rem; user-select: none;">
                            <?php
                            $eigenePunkte = ($nutzerBewertung && isset($nutzerBewertung->Punkte)) ? (int)$nutzerBewertung->Punkte : 0;
                            for ($i = 1; $i <= 5; $i++):
                                $class = ($i <= $eigenePunkte) ? 'selected' : '';
                                ?>
                                <span class="star <?= $class ?>" data-value="<?= $i ?>" style="cursor: pointer;">&#9733;</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="punkte" id="punkte-input" value="<?= $eigenePunkte ?>">
                        <br>
                        <button type="submit" class="btn" style="margin-top: 8px;">Bewertung speichern</button>
                    </form>
                </section>
            <?php endif; ?>

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

            <!-- Ganz unten: Anmelden Button mit Text -->
            <?php if (empty($_SESSION['nutzerId'])): ?>
                <section class="rezept-block" style="margin-top: 20px;">
                    <form action="index.php?page=anmeldung" method="get" style="display:inline;">
                        <button type="submit" class="btn">Anmelden</button>
                    </form>
                    <span>um das Rezept zu bewerten.</span>
                </section>
            <?php elseif (isset($istEigenerErsteller) && $istEigenerErsteller): ?>
                <section class="rezept-block" style="margin-top: 20px;">
                    <p>Du kannst dein eigenes Rezept nicht bewerten.</p>
                </section>
            <?php endif; ?>

        </article>
    <?php else: ?>
        <div class="message-box">Rezept nicht gefunden.</div>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align:center;">
        <a href="index.php?page=rezepte" class="btn">Zurück zur Übersicht</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stars = document.querySelectorAll('#star-rating .star');
            const hiddenInput = document.getElementById('punkte-input');

            function setStars(rating) {
                stars.forEach(star => {
                    if (parseInt(star.dataset.value) <= rating) {
                        star.classList.add('selected');
                        star.style.color = '#f5c518';
                    } else {
                        star.classList.remove('selected');
                        star.style.color = '#ccc';
                    }
                });
                hiddenInput.value = rating;
            }

            // Initiale Färbung direkt beim Laden setzen
            setStars(parseInt(hiddenInput.value) || 0);

            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const rating = parseInt(star.dataset.value);
                    setStars(rating);
                });

                star.addEventListener('mouseover', () => {
                    const rating = parseInt(star.dataset.value);
                    stars.forEach(s => {
                        s.style.color = (parseInt(s.dataset.value) <= rating) ? '#f5c518' : '#ccc';
                    });
                });

                star.addEventListener('mouseout', () => {
                    setStars(parseInt(hiddenInput.value) || 0);
                });
            });
        });
    </script>
</main>