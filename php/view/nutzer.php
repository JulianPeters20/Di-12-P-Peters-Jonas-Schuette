<main>
    <h2>Benutzerprofil</h2>

    <?php if (!empty($nutzer)):
        $istAdmin = false;
        if (!empty($nutzer)) {
            $istAdmin = !empty($nutzer->istAdmin);
        }

        // Stelle sicher, dass $nutzer existiert
        if (!isset($nutzer)) {
            echo "<p>Fehler: Nutzerobjekt nicht verfügbar.</p>";
            exit;
        }
        $rezepte = $rezepte ?? [];
        ?>

        <!-- Tabs -->
        <nav class="tabs">
            <button class="tab-button active" data-tab="profil">Profil</button>
            <button class="tab-button" data-tab="eigene">Eigene Rezepte</button>
            <button class="tab-button" data-tab="gespeichert">Gespeicherte Rezepte</button>
        </nav>

        <!-- Profil -->
        <section class="tab-content active" id="profil">
            <div class="profilkarte">
                <div class="profilbild">
                    <img src="images/Icon Nutzer ChatGPT.webp" alt="Profilbild" >
                </div>
                <div class="profildaten">
                    <h3><?= htmlspecialchars($nutzer->benutzername ?? '-') ?></h3>
                    <p><strong>E-Mail:</strong> <?= htmlspecialchars($nutzer->email ?? '-') ?></p>
                    <p><strong>Registriert am:</strong> <?= htmlspecialchars($nutzer->registrierungsDatum ?? '-') ?></p>
                    <?php if ($istAdmin): ?>
                        <p><strong>ID:</strong> <?= htmlspecialchars($nutzer->id ?? '-') ?></p>
                        <p><strong>Rolle:</strong> Administrator</p>
                    <?php else: ?>
                        <input type="hidden" name="nutzerId" value="<?= htmlspecialchars($nutzer->id ?? '') ?>">
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Eigene Rezepte -->
        <section class="tab-content" id="eigene">
            <h3>Eigene Rezepte</h3>
            <?php if (!empty($rezepte)): ?>
                <ul class="rezept-galerie">
                    <?php foreach ($rezepte as $rezept): ?>
                        <li class="rezept-karte" data-rezept-id="<?= (int)($rezept->RezeptID ?? 0) ?>" style="display: flex; flex-direction: column;">
                            <img src="<?= htmlspecialchars($rezept->BildPfad ?? 'images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($rezept->Titel ?? '-') ?>">
                            <div class="inhalt" style="flex: 1; display: flex; flex-direction: column;">
                                <h4>
                                    <a href="index.php?page=rezept&id=<?= (int)($rezept->RezeptID ?? 0) ?>">
                                        <?= htmlspecialchars($rezept->Titel ?? '-') ?>
                                    </a>
                                </h4>

                                <div class="meta-content" style="flex: 1;">
                                    <div class="meta" style="font-size: 0.9rem; color: #666; margin-bottom: 6px;">
                                        <?php
                                        $durchschnitt = $rezept->durchschnitt ?? null;
                                        $anzahlBewertungen = $rezept->anzahlBewertungen ?? 0;

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
                                        $kategorien = $rezept->Kategorien ?? [];
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

                                    <div class="meta" style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">
                                        <?= htmlspecialchars($rezept->Erstellungsdatum ?? '-') ?>
                                    </div>
                                </div>

                                <div class="rezept-aktion" style="margin-top: auto; padding-top: 10px;">
                                    <a href="index.php?page=rezept-bearbeiten&id=<?= (int)($rezept->RezeptID ?? 0) ?>" class="btn">Bearbeiten</a>
                                    <button type="button" class="btn rezept-loeschen-btn" data-id="<?= $rezept->RezeptID ?>">Löschen</button>
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
        <section class="tab-content" id="gespeichert">
            <h3>Gespeicherte Rezepte</h3>
            <div class="rezept-galerie">
                <p>(Diese Funktion ist aktuell noch nicht implementiert.)</p>
            </div>
        </section>

        <!-- Abmeldung -->
        <div style="margin-top: 30px;">
            <a href="index.php?page=abmeldung" class="btn">Abmelden</a>
        </div>

    <?php else: ?>
        <p>Nutzer nicht gefunden.</p>
    <?php endif; ?>

    <dialog id="loesch-modal" class="modal-dialog">
        <div class="modal-box">
            <h3>Rezept löschen</h3>
            <p id="loesch-text">Möchtest du dieses Rezept wirklich löschen?</p>
            <div class="modal-actions">
                <button type="button" class="btn" id="btn-abbrechen">Abbrechen</button>
                <button type="button" class="btn" id="btn-bestaetigen">Löschen</button>
            </div>
        </div>
    </dialog>

</main>

<!-- TAB CONTENT CSS -->
<style>
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* Rezept-Karten gleichmäßig ausrichten */
    .rezept-galerie {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        list-style: none;
        padding: 0;
    }

    .rezept-karte {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%; /* Alle Karten gleich hoch */
    }

    .rezept-karte:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .rezept-karte img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .rezept-karte .inhalt {
        padding: 15px;
    }

    .rezept-karte h4 {
        margin: 0 0 10px 0;
        font-size: 1.1rem;
    }

    .rezept-karte h4 a {
        text-decoration: none;
        color: #333;
    }

    .rezept-karte h4 a:hover {
        color: #007bff;
    }

    .rezept-aktion {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .rezept-aktion .btn {
        flex: 1;
        min-width: 80px;
        text-align: center;
        padding: 8px 12px;
        font-size: 0.9rem;
    }
</style>
