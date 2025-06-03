<?php if (!isset($rezepte)) $rezepte = []; ?>

<main>
    <h2>Rezeptübersicht</h2>

    <!-- Suchformular -->
    <form method="get" action="index.php" class="suchleiste">
        <input type="hidden" name="page" value="rezepte">
        <input type="search" name="suche" class="suchfeld"
               placeholder="Suchbegriff eingeben..."
               value="<?= htmlspecialchars($_GET["suche"] ?? "") ?>" aria-label="Suchbegriff eingeben">
        <button type="submit" class="btn suchen-btn">Suchen</button>
    </form>

    <!-- Keine Ergebnisse -->
    <?php if (empty($rezepte)): ?>
        <div>Keine passenden Rezepte gefunden.</div>
    <?php endif; ?>

    <!-- Rezept-Galerie -->
    <div class="rezept-galerie">
        <?php foreach ($rezepte as $rezept): ?>
            <div class="rezept-karte">
                <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="<?= htmlspecialchars($rezept['titel']) ?>">
                <div class="inhalt">
                    <h3>
                        <a href="index.php?page=rezept&id=<?= $rezept['id'] ?>">
                            <?= htmlspecialchars($rezept['titel']) ?>
                        </a>
                    </h3>
                    <div class="meta">
                        <?php
                        if (is_array($rezept['kategorie'])) {
                            echo htmlspecialchars(implode(', ', $rezept['kategorie']));
                        } else {
                            echo htmlspecialchars($rezept['kategorie']);
                        }
                        ?> · <?= htmlspecialchars($rezept['datum']) ?> · <?= htmlspecialchars($rezept['autor']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Button für neues Rezept -->
    <a href="index.php?page=rezept-neu" class="btn neuer-rezept-btn">Neues Rezept hinzufügen</a>
</main>