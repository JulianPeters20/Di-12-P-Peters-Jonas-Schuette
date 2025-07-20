<main>
    <!-- Flash-Toast anzeigen -->
    <?php if (!empty($_SESSION['flash'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const toast = document.createElement("div");
                toast.className = "flash-toast <?= $_SESSION['flash']['type'] ?>";
                toast.textContent = "<?= htmlspecialchars($_SESSION['flash']['message']) ?>";

                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 4600);
            });
        </script>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php
    // Sicherstellen, dass alle Arrays existieren
    if (!isset($beliebteste) || !is_array($beliebteste)) $beliebteste = [];
    if (!isset($bestBewertete) || !is_array($bestBewertete)) $bestBewertete = [];
    if (!isset($neuesteRezepte) || !is_array($neuesteRezepte)) $neuesteRezepte = [];
    ?>

    <!-- Beliebte Rezepte (nach Anzahl Bewertungen) -->
    <h2 class="mb-2 mt-3">Beliebte Rezepte</h2>

    <ul class="rezept-galerie">
        <?php foreach ($beliebteste as $rezept): ?>
            <?php include 'php/include/rezept-karte.php'; ?>
        <?php endforeach; ?>
        <?php if (empty($beliebteste)): ?>
            <li>Keine beliebten Rezepte vorhanden.</li>
        <?php endif; ?>
    </ul>

    <!-- Bestbewertete Rezepte (min. 3 Bewertungen) -->
    <h2 class="mb-2 mt-3">Bestbewertete Rezepte</h2>
    <ul class="rezept-galerie">
        <?php foreach ($bestBewertete as $rezept): ?>
            <?php include 'php/include/rezept-karte.php'; ?>
        <?php endforeach; ?>
        <?php if (empty($bestBewertete)): ?>
            <li>Keine bestbewerteten Rezepte vorhanden (mindestens 3 Bewertungen erforderlich).</li>
        <?php endif; ?>
    </ul>

    <!-- Neueste Rezepte -->
    <h2 class="mb-2 mt-3">Neueste Rezepte</h2>
    <ul class="rezept-galerie">
        <?php foreach ($neuesteRezepte as $rezept): ?>
            <?php include 'php/include/rezept-karte.php'; ?>
        <?php endforeach; ?>
        <?php if (empty($neuesteRezepte)): ?>
            <li>Keine neuen Rezepte vorhanden.</li>
        <?php endif; ?>
    </ul>
</main>