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
            <?php if (!empty($gespeicherteRezepte)): ?>
                <ul class="rezept-galerie">
                    <?php foreach ($gespeicherteRezepte as $rezept): ?>
                        <li class="rezept-karte" data-rezept-id="<?= (int)($rezept['RezeptID'] ?? 0) ?>" style="display: flex; flex-direction: column;">
                            <img src="<?= htmlspecialchars($rezept['BildPfad'] ?? 'images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($rezept['Titel'] ?? '-') ?>">
                            <div class="inhalt" style="flex: 1; display: flex; flex-direction: column;">
                                <h4>
                                    <a href="index.php?page=rezept&id=<?= (int)($rezept['RezeptID'] ?? 0) ?>">
                                        <?= htmlspecialchars($rezept['Titel'] ?? '-') ?>
                                    </a>
                                </h4>

                                <div class="meta" style="font-size: 0.9rem; color: #666; margin-bottom: 6px;">
                                    <?php
                                    // Durchschnittliche Bewertung als Sterne anzeigen
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

                                <div class="meta" style="margin-bottom:6px;">
                                    <?php
                                    // Kategorien anzeigen
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

                                <div class="meta" style="font-size: 0.9rem; color: #666; margin-bottom: 6px;">
                                    <?= htmlspecialchars($rezept['Erstellungsdatum'] ?? '-') ?>
                                    <?php
                                    $autorName = $rezept['erstellerName'] ?? null;
                                    if ($autorName) {
                                        echo ' · ' . htmlspecialchars($autorName);
                                    }
                                    ?>
                                </div>

                                <div class="meta" style="font-size: 0.9rem; color: #888; margin-bottom: 6px;">
                                    Gespeichert am: <?= htmlspecialchars($rezept['GespeichertAm'] ?? '-') ?>
                                </div>

                                <div class="rezept-aktion" style="margin-top: auto; padding-top: 10px;">
                                    <button type="button" class="btn gespeichert-entfernen-btn" data-id="<?= $rezept['RezeptID'] ?>">Aus Favoriten entfernen</button>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Keine gespeicherten Rezepte vorhanden.</p>
            <?php endif; ?>
        </section>

        <!-- Abmeldung und Konto löschen -->
        <div style="margin-top: 30px; display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="index.php?page=abmeldung" class="btn">Abmelden</a>
            <button type="button" class="btn btn-danger" id="konto-loeschen-btn">Konto löschen</button>
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

    <dialog id="konto-loesch-modal" class="modal-dialog">
        <div class="modal-box">
            <h3>⚠️ Konto unwiderruflich löschen</h3>
            <div style="margin: 20px 0;">
                <p><strong>Achtung:</strong> Diese Aktion kann nicht rückgängig gemacht werden!</p>
                <p>Folgende Daten werden <strong>permanent gelöscht</strong>:</p>
                <ul style="text-align: left; margin: 10px 0;">
                    <li>Dein Benutzerkonto</li>
                    <li>Alle deine Rezepte</li>
                    <li>Alle deine Bewertungen</li>
                    <li>Alle damit verbundenen Daten</li>
                </ul>
                <p>Bist du dir absolut sicher, dass du dein Konto löschen möchtest?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" id="konto-abbrechen-btn">Abbrechen</button>
                <button type="button" class="btn btn-danger" id="konto-bestaetigen-btn">Ja, Konto unwiderruflich löschen</button>
            </div>
        </div>
    </dialog>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Konto löschen Modal
    const kontoLoeschenBtn = document.getElementById('konto-loeschen-btn');
    const kontoModal = document.getElementById('konto-loesch-modal');
    const kontoAbbrechenBtn = document.getElementById('konto-abbrechen-btn');
    const kontoBestaetigenBtn = document.getElementById('konto-bestaetigen-btn');

    if (kontoLoeschenBtn && kontoModal) {
        kontoLoeschenBtn.addEventListener('click', () => {
            kontoModal.showModal();
        });

        kontoAbbrechenBtn.addEventListener('click', () => {
            kontoModal.close();
        });

        kontoBestaetigenBtn.addEventListener('click', async () => {
            kontoBestaetigenBtn.disabled = true;
            kontoBestaetigenBtn.textContent = 'Lösche...';

            try {
                const formData = new FormData();

                const response = await fetchWithCSRF('index.php?page=konto-loeschen', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    // Erfolgreich gelöscht - zur Startseite weiterleiten
                    window.location.href = 'index.php';
                } else {
                    throw new Error('Fehler beim Löschen des Kontos');
                }
            } catch (error) {
                console.error('Fehler:', error);
                alert('Fehler beim Löschen des Kontos. Bitte versuche es erneut.');
                kontoBestaetigenBtn.disabled = false;
                kontoBestaetigenBtn.textContent = 'Ja, Konto unwiderruflich löschen';
            }
        });

        // Modal schließen bei Klick auf Backdrop
        kontoModal.addEventListener('click', (e) => {
            if (e.target === kontoModal) {
                kontoModal.close();
            }
        });
    }

    // Event-Listener für "Aus Favoriten entfernen" Buttons
    document.querySelectorAll(".gespeichert-entfernen-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const rezeptId = btn.dataset.id;
            const formData = new FormData();
            formData.append("rezeptId", rezeptId);
            formData.append("aktion", "entfernen");
            formData.append("csrf_token", "<?php require_once 'php/include/csrf_protection.php'; echo generateCSRFToken(); ?>");

            try {
                const res = await fetch("api/rezept-speichern.php", {
                    method: "POST",
                    body: formData
                });

                const json = await res.json();
                if (json.success) {
                    btn.closest(".rezept-karte").remove();

                    // Prüfen ob noch Rezepte vorhanden sind
                    const gespeichertTab = document.getElementById("gespeichert");
                    const rezeptGalerie = gespeichertTab.querySelector(".rezept-galerie");
                    const verbleibendeRezepte = rezeptGalerie.querySelectorAll(".rezept-karte");

                    if (verbleibendeRezepte.length === 0) {
                        // Alle Rezepte entfernt - Nachricht anzeigen
                        rezeptGalerie.innerHTML = '<p>Keine gespeicherten Rezepte vorhanden.</p>';
                    }

                    // Toast-Nachricht anzeigen
                    showToast(json.message, "success");
                } else {
                    showToast("Fehler: " + json.message, "error");
                }
            } catch (error) {
                showToast("Netzwerkfehler beim Entfernen", "error");
            }
        });
    });

    // Toast-Funktion
    function showToast(message, type) {
        const toast = document.createElement("div");
        toast.className = `flash-toast ${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;

        if (type === "success") {
            toast.style.backgroundColor = "#28a745";
        } else if (type === "error") {
            toast.style.backgroundColor = "#dc3545";
        }

        document.body.appendChild(toast);

        // Einblenden
        setTimeout(() => toast.style.opacity = "1", 10);

        // Ausblenden und entfernen
        setTimeout(() => {
            toast.style.opacity = "0";
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }
});
</script>

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

    /* Danger Button Styling */
    .btn-danger {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
    }

    .btn-danger:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
    }

    /* Modal Styling */
    .modal-dialog {
        border: none;
        border-radius: 8px;
        padding: 0;
        max-width: 500px;
        width: 90%;
    }

    .modal-box {
        padding: 20px;
        background: white;
        border-radius: 8px;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .modal-dialog::backdrop {
        background: rgba(0, 0, 0, 0.5);
    }
</style>
