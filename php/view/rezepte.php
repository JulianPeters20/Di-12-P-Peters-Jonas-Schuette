<main>

    <!-- Suchformular -->
    <form id="suchformular" class="suchleiste" onsubmit="return false;">
        <input type="search" id="suchfeld" class="suchfeld" placeholder="Suchbegriff eingeben..." aria-label="Suchbegriff">
        <button type="submit" class="btn suchen-btn">Suchen</button>
    </form>

    <div id="such-ergebnisse" class="rezept-galerie"></div>

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
                        <div class="meta">
                            <?php
                            $kategorien = $rezept['kategorien'] ?? [];
                            if (is_array($kategorien) && count($kategorien) > 0) {
                                echo 'Kategorien: ' . htmlspecialchars(implode(', ', $kategorien));
                            } else {
                                echo 'Kategorien: -';
                            }
                            echo ' · ' . htmlspecialchars($rezept['Erstellungsdatum'] ?? '-');

                            $autorName = $rezept['erstellerName'] ?? null;
                            if ($autorName) {
                                echo ' · Autor: ' . htmlspecialchars($autorName);
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

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("suchformular");
        const feld = document.getElementById("suchfeld");
        const ergebnisContainer = document.getElementById("such-ergebnisse");

        async function sucheAusfuehren() {
            const begriff = feld.value.trim();
            if (begriff.length < 2) {
                ergebnisContainer.innerHTML = "<p>Bitte mindestens 2 Zeichen eingeben.</p>";
                return;
            }

            try {
                const res = await fetch(`api/rezepte-suche.php?query=${encodeURIComponent(begriff)}`);
                const html = await res.text();
                ergebnisContainer.innerHTML = html;
            } catch (err) {
                ergebnisContainer.innerHTML = "<p>Fehler beim Laden.</p>";
            }
        }

        form.addEventListener("submit", sucheAusfuehren);
        feld.addEventListener("input", () => {
            if (feld.value.length > 2) sucheAusfuehren();
        });
    });
</script>