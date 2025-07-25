<li class="rezept-karte">
    <img src="<?= htmlspecialchars($rezept['BildPfad'] ?? 'images/placeholder.jpg') ?>"
         alt="<?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>">
    <div class="inhalt">
        <h3>
            <a href="index.php?page=rezept&id=<?= urlencode($rezept['RezeptID'] ?? 0) ?>">
                <?= htmlspecialchars($rezept['Titel'] ?? 'Unbekannt') ?>
            </a>
        </h3>

        <div class="meta" style="font-size: 0.9rem; color: #666; margin-bottom: 6px;">
            <?php
            // Durchschnittliche Bewertung als Sterne anzeigen
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

        <div class="meta" style="margin-bottom:6px;">
            <?php
            // Kategorien ohne "Kategorien:" Schriftzug, max. 3 Kategorien mit "..." wenn mehr
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

        <div class="meta" style="font-size: 0.9rem; color: #666;">
            <?= htmlspecialchars($rezept['Erstellungsdatum'] ?? '-') ?>

            <?php
            $autorName = $rezept['erstellerName'] ?? null;
            if ($autorName) {
                // Nur Benutzername, kein "Autor:" davor
                echo ' · ' . htmlspecialchars($autorName);
            } else {
                echo ' · Autor-ID: ' . htmlspecialchars($rezept['ErstellerID'] ?? '-');
            }
            ?>
        </div>
    </div>
</li>
