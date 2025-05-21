<?php
require_once 'NutzerDAO.php';
include_once 'header.php';

$nutzerListe = NutzerDAO::getAlleBenutzer();
?>

<main>
    <h2>Nutzerliste</h2>

    <table class="rezept-tabelle">
        <thead>
        <tr>
            <th>Benutzername</th>
            <th>E-Mail</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($nutzerListe as $nutzer): ?>
            <tr>
                <td><?= htmlspecialchars($nutzer['benutzername']) ?></td>
                <td><?= htmlspecialchars($nutzer['email']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include_once 'footer.php'; ?>
