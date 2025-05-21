<main>
    <h2>Nutzerprofil</h2>
    <?php if (!empty($nutzer)): ?>
        <p><strong>Benutzername:</strong> <?= htmlspecialchars($nutzer['benutzername']) ?></p>
        <p><strong>E-Mail:</strong> <?= htmlspecialchars($nutzer['email']) ?></p>
        <p><strong>Registrierungsdatum:</strong> <?= htmlspecialchars($nutzer['registriert']) ?></p>
    <?php else: ?>
        <p>Nutzer nicht gefunden.</p>
    <?php endif; ?>
</main>

<?php
// Dateipfad: nutzer.php

require_once 'php/controller/NutzerController.php';

// Aufrufen der Funktion showNutzerProfil()
showNutzerProfil();