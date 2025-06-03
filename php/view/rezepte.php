<?php if (!isset($rezepte)) $rezepte = []; ?>

<main>
    <h2>Rezeptübersicht</h2>

    <!-- Suchformular -->
    <form method="get" action="index.php" class="suchleiste">
        <input type="hidden" name="page" value="rezepte">
        <input type="text" name="suche" class="suchfeld"
               placeholder="Suchbegriff eingeben..."
               value="<?= htmlspecialchars($_GET["suche"] ?? "") ?>">
        <input type="submit" value="Suchen" class="btn">
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
                        <a href="index.php?page=rezept&id=<?= (int)$rezept['id'] ?>">
                            <?= htmlspecialchars($rezept['titel']) ?>
                        </a>
                    </h3>
                    <div class="meta">
                        <?= htmlspecialchars(is_array($rezept['kategorie']) ? implode(', ', $rezept['kategorie']) : $rezept['kategorie']) ?>
                        · <?= htmlspecialchars($rezept['datum']) ?>
                        · <?= htmlspecialchars($rezept['autor']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Button für neues Rezept -->
    <?php if (isset($_SESSION['nutzerId'])): ?>
        <a href="index.php?page=rezept-neu" class="btn">Neues Rezept hinzufügen</a>
    <?php endif; ?>
</main>
