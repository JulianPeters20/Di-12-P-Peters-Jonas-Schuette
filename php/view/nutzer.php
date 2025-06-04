<?php
require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php';

session_start();

// Prüfen, ob Nutzer eingeloggt ist
if (!isset($_SESSION['nutzerId'])) {
    header("Location: index.php?page=anmeldung");
    exit;
}

$nutzerDAO = new NutzerDAO();
$nutzer = $nutzerDAO->findeNachID((int)$_SESSION['nutzerId']);
$rezeptDAO = new RezeptDAO();
?>

<main>
    <h2>Mein Profil</h2>

    <?php if ($nutzer): ?>
        <!-- Nutzer-Infos -->
        <section class="nutzerprofil" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
            <img src="images/Icon Nutzer ChatGPT.webp" alt="Profilbild"
                 style="height: 80px; width: 80px; border-radius: 50%; padding: 10px;">
            <dl>
                <dt>Benutzername:</dt>
                <dd><?= htmlspecialchars($nutzer->benutzername) ?></dd>
                <dt>E-Mail:</dt>
                <dd><?= htmlspecialchars($nutzer->email) ?></dd>
                <dt>Registrierungsdatum:</dt>
                <dd><?= htmlspecialchars($nutzer->registrierungsDatum) ?></dd>
                <?php if ($nutzer->istAdmin): ?>
                    <dt>Rolle:</dt>
                    <dd>Administrator</dd>
                <?php endif; ?>
            </dl>
        </section>

        <!-- Eigene Rezepte -->
        <section>
            <h3>Eigene Rezepte</h3>
            <?php
            $rezepte = $rezeptDAO->findeNachErstellerID($nutzer->id);
            ?>
            <?php if (!empty($rezepte)): ?>
                <ul class="rezept-galerie">
                    <?php foreach ($rezepte as $rezept): ?>
                        <li class="rezept-karte">
                            <img src="<?= htmlspecialchars($rezept['BildPfad']) ?>" alt="<?= htmlspecialchars($rezept['Titel']) ?>">
                            <div class="inhalt">
                                <h4>
                                    <a href="index.php?page=rezept&id=<?= $rezept['RezeptID'] ?>">
                                        <?= htmlspecialchars($rezept['Titel']) ?>
                                    </a>
                                </h4>
                                <div class="meta">
                                    <?= htmlspecialchars($rezept['Kategorie'] ?? '') ?> · <?= htmlspecialchars($rezept['Erstellungsdatum']) ?>
                                </div>
                                <div class="rezept-aktion" style="margin-top: 10px;">
                                    <a href="index.php?page=rezept-bearbeiten&id=<?= $rezept['RezeptID'] ?>" class="btn">Bearbeiten</a>
                                    <a href="index.php?page=rezept-loeschen&id=<?= $rezept['RezeptID'] ?>" class="btn" onclick="return confirm('Möchtest du dieses Rezept wirklich löschen?');">Löschen</a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Keine eigenen Rezepte vorhanden.</p>
            <?php endif; ?>
        </section>

        <!-- Gespeicherte Rezepte -->
        <section>
            <h3>Gespeicherte Rezepte</h3>
            <div class="rezept-galerie">
                <p>(Diese Funktion ist aktuell noch nicht implementiert.)</p>
            </div>
        </section>

        <!-- Abmelde-Button -->
        <div style="margin-top: 30px;">
            <a href="index.php?page=abmeldung" class="btn">Abmelden</a>
        </div>

    <?php else: ?>
        <p>Nutzer nicht gefunden.</p>
    <?php endif; ?>
</main>