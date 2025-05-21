<?php if (!isset($rezepte)) $rezepte = []; ?>
<main>
    <h2>Rezeptübersicht</h2>
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
    <a href="index.php?page=rezept-neu" class="btn"><button>Neues Rezept hinzufügen</button></a>
</main>

<?php
// Dateipfad: rezepte.php

require_once 'php/controller/RezeptController.php';

// Aufrufen der Funktion showRezepte()
showRezepte();