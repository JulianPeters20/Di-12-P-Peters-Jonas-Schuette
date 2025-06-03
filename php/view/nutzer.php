<?php require_once 'php/model/RezeptDAO.php'; ?>
<main>
    <h2>Mein Profil</h2>

    <?php if (!empty($nutzer)): ?>
        <!-- Nutzer-Infos -->
        <section class="nutzerprofil" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
            <img src="images/Icon Nutzer ChatGPT.webp" alt="Profilbild"
                 style="height: 80px; width: 80px; border-radius: 50%; padding: 10px;">
            <dl>
                <dt>Benutzername:</dt>
                <dd><?= htmlspecialchars($nutzer['benutzername']) ?></dd>
                <dt>E-Mail:</dt>
                <dd><?= htmlspecialchars($nutzer['email']) ?></dd>
                <dt>Registrierungsdatum:</dt>
                <dd><?= htmlspecialchars($nutzer['registriert']) ?></dd>
            </dl>
        </section>

        <!-- Eigene Rezepte -->
        <section>
            <h3>Eigene Rezepte</h3>
            <?php
            $rezepte = RezeptDAO::findeAlle();
            $eigene = array_filter($rezepte, function ($r) use ($nutzer) {
                return $r['autor'] === $nutzer['email'];
            });
            ?>
            <?php if (!empty($eigene)): ?>
                <ul class="rezept-galerie">
                    <?php foreach ($eigene as $rezept): ?>
                        <li class="rezept-karte">
                            <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="<?= htmlspecialchars($rezept['titel']) ?>">
                            <div class="inhalt">
                                <h4>
                                    <a href="index.php?page=rezept&id=<?= $rezept['id'] ?>">
                                        <?= htmlspecialchars($rezept['titel']) ?>
                                    </a>
                                </h4>
                                <div class="meta">
                                    <?php
                                    if (is_array($rezept['kategorie'])) {
                                        echo htmlspecialchars(implode(', ', $rezept['kategorie']));
                                    } else {
                                        echo htmlspecialchars($rezept['kategorie']);
                                    }
                                    ?> · <?= htmlspecialchars($rezept['datum']) ?>
                                </div>
                                <div class="rezept-aktion" style="margin-top: 10px;">
                                    <a href="index.php?page=rezept-bearbeiten&id=<?= $rezept['id'] ?>" class="btn">Bearbeiten</a>
                                    <a href="index.php?page=rezept-loeschen&id=<?= $rezept['id'] ?>" class="btn" onclick="return confirm('Möchtest du dieses Rezept wirklich löschen?');">Löschen</a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Keine eigenen Rezepte vorhanden.</p>
            <?php endif; ?>
        </section>

        <!-- Optional: Gespeicherte Rezepte -->
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