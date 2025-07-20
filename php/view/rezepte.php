<main>

    <!-- Suchformular -->
    <form id="suchformular" class="suchleiste" onsubmit="return false;">
        <input type="search" id="suchfeld" class="suchfeld" placeholder="Suchbegriff eingeben..." aria-label="Suchbegriff">
        <button type="submit" class="btn suchen-btn">Suchen</button>
    </form>

    <div id="such-ergebnisse" style="display: none;"></div>

    <div id="original-rezepte">
        <h2 class="mb-2 mt-3">Alle Rezepte</h2>
        <ul class="rezept-galerie">
        <?php if (!empty($rezepte)) : ?>
            <?php foreach ($rezepte as $rezept): ?>
                <?php include 'php/include/rezept-karte.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Keine Rezepte vorhanden.</li>
        <?php endif; ?>
        </ul>

        <!-- Button für neues Rezept -->
        <a href="index.php?page=rezept-neu" class="btn neuer-rezept-btn">Neues Rezept hinzufügen</a>
    </div>

</main>

