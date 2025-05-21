<?php if (!isset($nutzer)) $nutzer = []; ?>
<main>
    <h2>Nutzerübersicht</h2>
    <table>
        <thead>
        <tr>
            <th>Benutzername</th>
            <th>E-Mail-Adresse</th>
            <th>Registrierungsdatum</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($nutzer as $person): ?>
            <tr>
                <td><?= htmlspecialchars($person['benutzername']) ?></td>
                <td><?= htmlspecialchars($person['email']) ?></td>
                <td><?= isset($person['registriert']) ? htmlspecialchars($person['registriert']) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php
// Dateipfad: nutzerliste.php

require_once 'php/controller/NutzerController.php';

// Aufrufen der Funktion showNutzerListe()
showNutzerListe();