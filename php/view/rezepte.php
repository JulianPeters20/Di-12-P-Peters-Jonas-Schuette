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
        <p>Keine passenden Rezepte gefunden.</p>
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
                    <p class="meta">
                        <?= htmlspecialchars($rezept['kategorie']) ?> · <?= htmlspecialchars($rezept['datum']) ?> · <?= htmlspecialchars($rezept['autor']) ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Button für neues Rezept -->
    <a href="index.php?page=rezept-neu" class="btn"><button>Neues Rezept hinzufügen</button></a>
</main>
