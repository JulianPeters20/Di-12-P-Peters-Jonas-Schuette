<?php
require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php';

// Robust prüfen, ob Nutzer angemeldet ist
$nutzerId = $_SESSION['nutzerId'] ?? null;
$nutzer = null;
$rezepte = [];

if ($nutzerId !== null && is_numeric($nutzerId)) {
    $nutzerDAO = new NutzerDAO();
    $nutzer = $nutzerDAO->findeNachID((int)$nutzerId);
    $rezeptDAO = new RezeptDAO();
    if ($nutzer) {
        $rezepte = $rezeptDAO->findeNachErstellerID($nutzer->NutzerID ?? $nutzer->id ?? 0);
    }
}

// Admin-Status erkennen (bitte ggf. anpassen je nach Datenstruktur)
$istAdmin = !empty($nutzer->IstAdmin) || !empty($nutzer->istAdmin);
?>

<main>
    <h2>Benutzerprofil</h2>

    <?php if (!empty($nutzer)): ?>
        <section class="nutzerprofil" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px; background-color: #f7f5f2;">
            <img src="images/Icon Nutzer ChatGPT.webp" alt="Profilbild"
                 style="height: 80px; width: 80px; border-radius: 50%; padding: 10px;">
            <dl>
                <dt>Benutzername:</dt>
                <dd><?= htmlspecialchars($nutzer->Benutzername ?? $nutzer->benutzername ?? '-') ?></dd>
                <dt>E-Mail:</dt>
                <dd><?= htmlspecialchars($nutzer->Email ?? $nutzer->email ?? '-') ?></dd>
                <dt>Registrierungsdatum:</dt>
                <dd><?= htmlspecialchars($nutzer->RegistrierungsDatum ?? $nutzer->registrierungsDatum ?? '-') ?></dd>

                <?php if ($istAdmin): ?>
                    <dt>ID:</dt>
                    <dd><?= htmlspecialchars($nutzer->NutzerID ?? $nutzer->id ?? '-') ?></dd>
                    <dt>Rolle:</dt>
                    <dd>Administrator</dd>
                <?php else: ?>
                    <!-- ID unsichtbar, aber z.B. für JavaScript oder Formulare verfügbar -->
                    <input type="hidden" name="nutzerId" value="<?= htmlspecialchars($nutzer->NutzerID ?? $nutzer->id ?? '') ?>">
                <?php endif; ?>
            </dl>
        </section>

        <section>
            <h3>Eigene Rezepte</h3>
            <?php if (!empty($rezepte)): ?>
                <ul class="rezept-galerie">
                    <?php foreach ($rezepte as $rezept): ?>
                        <li class="rezept-karte">
                            <img src="<?= htmlspecialchars($rezept['BildPfad'] ?? 'images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($rezept['Titel'] ?? '-') ?>">
                            <div class="inhalt">
                                <h4>
                                    <a href="index.php?page=rezept&id=<?= (int)($rezept['RezeptID'] ?? 0) ?>">
                                        <?= htmlspecialchars($rezept['Titel'] ?? '-') ?>
                                    </a>
                                </h4>
                                <div class="meta">
                                    Kategorien:
                                    <?= !empty($rezept['kategorien']) ? htmlspecialchars(implode(', ', $rezept['kategorien'])) : '-' ?>
                                    · <?= htmlspecialchars($rezept['Erstellungsdatum'] ?? '-') ?>
                                </div>
                                <div class="rezept-aktion" style="margin-top: 10px;">
                                    <a href="index.php?page=rezept-bearbeiten&id=<?= (int)($rezept['RezeptID'] ?? 0) ?>" class="btn">Bearbeiten</a>
                                    <a href="index.php?page=rezept-loeschen&id=<?= (int)($rezept['RezeptID'] ?? 0) ?>" class="btn" onclick="return confirm('Möchtest du dieses Rezept wirklich löschen?');">Löschen</a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Keine eigenen Rezepte vorhanden.</p>
            <?php endif; ?>
        </section>

        <section>
            <h3>Gespeicherte Rezepte</h3>
            <div class="rezept-galerie">
                <p>(Diese Funktion ist aktuell noch nicht implementiert.)</p>
            </div>
        </section>

        <div style="margin-top: 30px;">
            <a href="index.php?page=abmeldung" class="btn">Abmelden</a>
        </div>

    <?php else: ?>
        <p>Nutzer nicht gefunden.</p>
    <?php endif; ?>
</main>