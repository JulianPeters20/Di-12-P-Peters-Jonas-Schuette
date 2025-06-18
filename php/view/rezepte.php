<main>
    <!-- Suchformular -->
    <form method="get" action="index.php" class="suchleiste">
        <input type="hidden" name="page" value="rezepte">
        <input type="search" name="suche" class="suchfeld"
               placeholder="Suchbegriff eingeben..."
               value="<?= htmlspecialchars($_GET["suche"] ?? "") ?>" aria-label="Suchbegriff eingeben">
        <button type="submit" class="btn suchen-btn">Suchen</button>
    </form>

    <h2 class="mb-2 mt-3">Beliebte Rezepte</h2>

    <ul class="rezept-galerie">
        <?php if (!empty($rezepte)) : ?>
            <?php foreach ($rezepte as $rezept): ?>
                <li class="rezept-karte">
                    <img src="<?= htmlspecialchars($rezept['BildPfad'] ?? 'images/platzhalter.jpg') ?>" alt="<?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>">
                    <div class="inhalt">
                        <h3>
                            <a href="index.php?page=rezept&id=<?= urlencode($rezept['RezeptID'] ?? 0) ?>">
                                <?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>
                            </a>
                        </h3>

                        <!-- Bewertung oben, vor Kategorien -->
                        <div class="meta" style="font-size: 0.9rem; color: #666; margin-bottom: 6px;">
                            <?php
                            $durchschnitt = $rezept['durchschnitt'] ?? null;
                            $anzahlBewertungen = $rezept['anzahlBewertungen'] ?? 0;

                            if ($durchschnitt !== null && $anzahlBewertungen > 0) {
                                $sterne = round($durchschnitt);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $sterne ? '★' : '☆';
                                }
                                echo ' (' . number_format($durchschnitt, 2) . ' aus ' . $anzahlBewertungen . ' Bewertung' . ($anzahlBewertungen > 1 ? 'en' : '') . ')';
                            } else {
                                echo '(Keine Bewertungen)';
                            }
                            ?>
                        </div>

                        <!-- Kategorien ohne vorangestelltes Wort, max. 3 Kategorien -->
                        <div class="meta" style="margin-bottom:6px;">
                            <?php
                            $kategorien = $rezept['kategorien'] ?? [];
                            if (is_array($kategorien) && count($kategorien) > 0) {
                                $anzeigeKategorien = array_slice($kategorien, 0, 3);
                                echo htmlspecialchars(implode(', ', $anzeigeKategorien));
                                if (count($kategorien) > 3) {
                                    echo ', ...';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>

                        <!-- Datum und Autor (nur Name, keine Beschriftung) -->
                        <div class="meta" style="font-size: 0.9rem; color: #666;">
                            <?= htmlspecialchars($rezept['Erstellungsdatum'] ?? '-') ?>
                            <?php
                            $autorName = $rezept['erstellerName'] ?? null;
                            if ($autorName) {
                                echo ' · ' . htmlspecialchars($autorName);
                            } else {
                                echo ' · Autor-ID: ' . htmlspecialchars($rezept['ErstellerID'] ?? '-');
                            }
                            ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Keine Rezepte vorhanden.</li>
        <?php endif; ?>
    </ul>

    <!-- Button für neues Rezept -->
    <a href="index.php?page=rezept-neu" class="btn neuer-rezept-btn">Neues Rezept hinzufügen</a>

</main>