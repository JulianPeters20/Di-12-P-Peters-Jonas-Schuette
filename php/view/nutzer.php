<?php
require_once 'php/model/NutzerDAO.php';
require_once 'php/model/RezeptDAO.php';
require_once 'php/model/BewertungDAO.php';

// Robust prüfen, ob Nutzer angemeldet ist
$nutzerId = $_SESSION['nutzerId'] ?? null;
$nutzer = null;
$rezepte = [];

if ($nutzerId !== null && is_numeric($nutzerId)) {
    $nutzerDAO = new NutzerDAO();
    $nutzer = $nutzerDAO->findeNachID((int)$nutzerId);
    $rezeptDAO = new RezeptDAO();
    $bewertungDAO = new BewertungDAO();

    if ($nutzer) {
        $rezepte = $rezeptDAO->findeNachErstellerID($nutzer->NutzerID ?? $nutzer->id ?? 0);

        // Bewertungen hinzufügen
        foreach ($rezepte as &$rezept) {
            $rezeptID = $rezept['RezeptID'] ?? 0;
            $rezept['durchschnitt'] = $bewertungDAO->berechneDurchschnittRating($rezeptID);
            $rezept['anzahlBewertungen'] = $bewertungDAO->zaehleBewertungen($rezeptID);
        }
        unset($rezept);
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
                    <!-- ID unsichtbar, aber z.B. für JavaScript/Formulare verfügbar -->
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

                                <div class="meta" style="font-size: 0.9rem; color: #666; margin-bottom: 6px;">
                                    <?php
                                    $durchschnitt = $rezept['durchschnitt'] ?? null;
                                    $anzahlBewertungen = $rezept['anzahlBewertungen'] ?? 0;

                                    if ($durchschnitt !== null && $anzahlBewertungen > 0) {
                                        $sterne = round($durchschnitt);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $sterne ? '★' : '☆';
                                        }
                                        echo ' (' . number_format($durchschnitt, 2) . ' aus ' . $anzahlBewertungen . ' Bewertung' . ($anzahlBewertungen > 1 ? 'en' : '') . ')';
                                    } else {
                                        echo '(Keine Bewertungen)';
                                    }
                                    ?>
                                </div>

                                <div class="meta" style="margin-bottom: 6px;">
                                    <?php
                                    $kategorien = $rezept['kategorien'] ?? [];
                                    if (is_array($kategorien) && count($kategorien) > 0) {
                                        $anzeigeKategorien = array_slice($kategorien, 0, 3);
                                        echo htmlspecialchars(implode(', ', $anzeigeKategorien));
                                        if (count($kategorien) > 3) {
                                            echo ', ...';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>

                                <div class="meta" style="font-size: 0.9rem; color: #666; margin-top: 4px;">
                                    <?= htmlspecialchars($rezept['Erstellungsdatum'] ?? '-') ?>
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