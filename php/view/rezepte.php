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