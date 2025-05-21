<main>
    <?php if (!empty($rezept)): ?>
        <h2><?= htmlspecialchars($rezept['titel']) ?></h2>
        <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="<?= htmlspecialchars($rezept['titel']) ?>" style="max-width:300px;">
        <p><strong>Kategorie:</strong> <?= htmlspecialchars($rezept['kategorie']) ?></p>
        <p><strong>Datum:</strong> <?= htmlspecialchars($rezept['datum']) ?></p>
        <p><strong>Autor:</strong> <?= htmlspecialchars($rezept['autor']) ?></p>
        <!-- Hier könntest du Zutaten & Zubereitung ausgeben, falls schon hinterlegt -->
    <?php else: ?>
        <p>Rezept nicht gefunden.</p>
    <?php endif; ?>
    <a href="index.php?page=rezepte">Zurück zur Übersicht</a>
</main>

<?php
// Dateipfad: rezept.php

require_once 'php/controller/RezeptController.php';

// Aufrufen der Funktion showRezept()
showRezept();