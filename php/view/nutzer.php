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
                        <li class="rezept-karte">
                            <img src="<?= htmlspecialchars($rezept->BildPfad ?? 'images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($rezept->Titel ?? '-') ?>">
                            <div class="inhalt">
                                <h4>
                                    <a href="index.php?page=rezept&id=<?= (int)($rezept->RezeptID ?? 0) ?>">
                                        <?= htmlspecialchars($rezept->Titel ?? '-') ?>
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
                                <div class="meta">
                                    Kategorien:
                                    <?= !empty($rezept->Kategorien) ? implode(', ', $rezept->Kategorien) : '-' ?>
                                    · <?= htmlspecialchars($rezept->Erstellungsdatum ?? '-') ?>
                                </div>

                                <div class="rezept-aktion" style="margin-top: 10px;">
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

    <div id="loesch-modal" class="modal-overlay" hidden>
        <div class="modal-box">
            <h3>Rezept löschen</h3>
            <p id="loesch-text">Möchtest du dieses Rezept wirklich löschen?</p>
            <div class="modal-actions">
                <button type="button" class="btn" id="btn-abbrechen">Abbrechen</button>
                <button type="button" class="btn" id="btn-bestaetigen">Löschen</button>
            </div>
        </div>
    </div>

</main>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const buttons = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.tab-content');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tab;

                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                contents.forEach(c => {
                    c.classList.toggle('active', c.id === target);
                });
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById("loesch-modal");
        const loeschText = document.getElementById("loesch-text");
        const abbrechenBtn = document.getElementById("btn-abbrechen");
        const bestaetigenBtn = document.getElementById("btn-bestaetigen");
        let aktiveButton = null;

        document.querySelectorAll(".rezept-loeschen-btn").forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault(); // wichtig: verhindert versehentlichen submit
                aktiveButton = btn;
                const titel = btn.closest(".rezept-karte")?.querySelector("h4")?.innerText || "dieses Rezept";
                loeschText.textContent = `Möchtest du „${titel}“ wirklich löschen?`;
                modal.removeAttribute("hidden");
            });
        });

        abbrechenBtn.addEventListener("click", () => {
            console.log("Abbrechen gedrückt!");
            aktiveButton = null;
            modal.setAttribute("hidden", true);
        });

        bestaetigenBtn.addEventListener("click", async () => {
            if (!aktiveButton) return;

            const id = aktiveButton.dataset.id;
            const formData = new FormData();
            formData.append("id", id);

            const res = await fetch("api/rezept-loeschen.php", {
                method: "POST",
                body: formData
            });

            const json = await res.json();
            if (json.success) {
                aktiveButton.closest(".rezept-karte").remove();
                zeigeFlash("success", "Rezept erfolgreich gelöscht.");
            } else {
                zeigeFlash("error", "Fehler: " + json.message);
            }

            aktiveButton = null;
            modal.setAttribute("hidden", true);
        });
    });
</script>

<script>
    function zeigeFlash(typ, nachricht) {
        // Alte entfernen
        document.querySelectorAll(".flash-toast").forEach(e => e.remove());

        // Neue erstellen
        const box = document.createElement("div");
        box.className = "flash-toast " + typ;
        box.textContent = nachricht;

        document.body.appendChild(box);

        // Automatisch nach Animation entfernen
        setTimeout(() => {
            box.remove();
        }, 4600); // etwas mehr als fadeout-delay
    }
</script>
