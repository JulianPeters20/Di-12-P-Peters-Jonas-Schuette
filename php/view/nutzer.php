<?php require_once 'php/model/RezeptDAO.php'; ?>
<main>
    <h2>Mein Profil</h2>

    <?php if (!empty($nutzer)): ?>
        <!-- Nutzer-Infos -->
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
            <img src="images/Icon Nutzer ChatGPT.webp" alt="Profilbild"
                 style="height: 80px; width: 80px; border-radius: 50%; padding: 10px;">
            <div>
                <p><strong>Benutzername:</strong> <?= htmlspecialchars($nutzer['benutzername']) ?></p>
                <p><strong>E-Mail:</strong> <?= htmlspecialchars($nutzer['email']) ?></p>
                <p><strong>Registrierungsdatum:</strong> <?= htmlspecialchars($nutzer['registriert']) ?></p>
            </div>
        </div>

        <!-- Eigene Rezepte -->
        <h3 style="margin-top: 30px; margin-bottom: 20px;">Eigene Rezepte</h3>
        <div class="rezept-galerie">
            <?php
            $rezepte = RezeptDAO::findeAlle();
            $eigene = array_filter($rezepte, function ($r) use ($nutzer) {
                return $r['autor'] === $nutzer['email'];
            });
            ?>
            <?php foreach ($eigene as $rezept): ?>
                <div class="rezept-karte">
                    <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="<?= htmlspecialchars($rezept['titel']) ?>">
                    <div class="inhalt">
                        <h3>
                            <a href="index.php?page=rezept&id=<?= $rezept['id'] ?>">
                                <?= htmlspecialchars($rezept['titel']) ?>
                            </a>
                        </h3>
                        <p class="meta"><?= htmlspecialchars($rezept['kategorie']) ?> · <?= htmlspecialchars($rezept['datum']) ?></p>

                        <div style="margin-top: 10px;">
                            <a href="index.php?page=rezept-bearbeiten&id=<?= $rezept['id'] ?>" class="btn">Bearbeiten</a>
                            <a href="index.php?page=rezept-loeschen&id=<?= $rezept['id'] ?>" class="btn" onclick="return confirm('Möchtest du dieses Rezept wirklich löschen?');">Löschen</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($eigene)): ?>
                <p>Keine eigenen Rezepte vorhanden.</p>
            <?php endif; ?>
        </div>

        <!-- Optional: Gespeicherte Rezepte -->
        <h3 style="margin-top: 30px; margin-bottom: 20px;">Gespeicherte Rezepte</h3>
        <div class="rezept-galerie">
            <p>(Diese Funktion ist aktuell noch nicht implementiert.)</p>
        </div>

        <!-- Abmelde-Button -->
        <div style="margin-top: 30px;">
            <a href="index.php?page=abmeldung" class="btn">Abmelden</a>
        </div>

    <?php else: ?>
        <p>Nutzer nicht gefunden.</p>
    <?php endif; ?>
</main>
