<main>
    <?php if (!empty($rezept)): ?>


        <article class="rezept-detail">
            <header>
                <h2 class="rezept-titel"><?= htmlspecialchars($rezept['titel'] ?? 'Unbekannt') ?></h2>

                <!-- Speichern Button für angemeldete Nutzer (nicht Ersteller) -->
                <?php if (!empty($_SESSION['nutzerId']) && isset($istEigenerErsteller) && !$istEigenerErsteller): ?>
                    <?php
                    // Prüfen ob Rezept bereits gespeichert ist
                    require_once 'php/model/GespeicherteRezepteDAO.php';
                    $gespeicherteRezepteDAO = new GespeicherteRezepteDAO();
                    $istGespeichert = $gespeicherteRezepteDAO->istGespeichert((int)$_SESSION['nutzerId'], (int)$rezept['id']);
                    ?>
                    <div class="rezept-speichern-oben" style="margin: 15px 0;">
                        <button type="button"
                                class="btn <?= $istGespeichert ? 'btn-secondary' : 'btn-primary' ?>"
                                id="speichern-btn"
                                data-rezept-id="<?= $rezept['id'] ?>"
                                data-ist-gespeichert="<?= $istGespeichert ? 'true' : 'false' ?>">
                            <?= $istGespeichert ? '❤️ Gespeichert' : '🤍 Speichern' ?>
                        </button>
                    </div>
                <?php endif; ?>
            </header>

            <section class="rezept-block">
                <h3>Durchschnittliche Bewertung</h3>
                <?php if (isset($durchschnitt) && $durchschnitt !== null && isset($anzahlBewertungen) && $anzahlBewertungen > 0): ?>
                    <p>
                        <?php
                        $sterne = round($durchschnitt);
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $sterne ? '★' : '☆';
                        }
                        ?>
                        (<?= number_format($durchschnitt, 2) ?> Sterne aus <?= $anzahlBewertungen ?> Bewertung<?= $anzahlBewertungen > 1 ? 'en' : '' ?>)
                    </p>
                <?php else: ?>
                    <p>Dieses Rezept wurde noch nicht bewertet.</p>
                <?php endif; ?>
            </section>

            <?php
            $projektRoot = realpath(__DIR__ . '/images/'); // Pfad anpassen falls nötig
            $bildDatei = $projektRoot . ($rezept['bild'] ?? '');

            if (!empty($rezept['bild']) && file_exists($bildDatei)) {
                $bildUrl = $rezept['bild'];
            } else {
                echo "<p>Datei existiert NICHT am erwarteten Pfad: " . htmlspecialchars($bildDatei) . "</p>";
                $bildUrl = '/images/placeholder.jpg';
            }
            ?>

            <img src="<?= htmlspecialchars($bildUrl) ?>"
                 alt="<?= htmlspecialchars($rezept['titel'] ?? 'Rezeptbild') ?>"
                 style="max-width:300px;">

            <section class="rezept-block">
                <h3>Kategorien</h3>
                <?php if (!empty($rezept['kategorien'])): ?>
                    <ul>
                        <?php foreach ($rezept['kategorien'] as $kat): ?>
                            <li><?= htmlspecialchars($kat) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Keine Kategorien</p>
                <?php endif; ?>
            </section>

            <section class="rezept-block">
                <h3>Datum</h3>
                <p><?= htmlspecialchars($rezept['datum'] ?? '-') ?></p>
            </section>

            <section class="rezept-block">
                <h3>Autor</h3>
                <p><?= htmlspecialchars($rezept['erstellerName'] ?? '-') ?> (<?= htmlspecialchars($rezept['erstellerEmail'] ?? '-') ?>)</p>
            </section>

            <section class="rezept-block">
                <h3>Preisklasse</h3>
                <p><?= htmlspecialchars($rezept['preisklasseName'] ?? '-') ?></p>
            </section>

            <section class="rezept-block">
                <h3>Portionsgröße</h3>
                <p><?= htmlspecialchars($rezept['portionsgroesseName'] ?? '-') ?></p>
            </section>

            <section class="rezept-block">
                <h3>Zutaten</h3>
                <?php if (!empty($rezept['zutaten']) && is_array($rezept['zutaten'])): ?>
                    <ul>
                        <?php foreach ($rezept['zutaten'] as $z): ?>
                            <li>
                                <?= htmlspecialchars($z['menge'] ?? '') ?>
                                <?= htmlspecialchars($z['einheit'] ?? '') ?>
                                <?= htmlspecialchars($z['zutat'] ?? '') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Keine Zutaten angegeben.</p>
                <?php endif; ?>
            </section>

            <?php if (!empty($rezept['utensilien'])): ?>
                <section class="rezept-block">
                    <h3>Küchenutensilien</h3>
                    <ul>
                        <?php foreach ($rezept['utensilien'] as $utensil): ?>
                            <li><?= htmlspecialchars($utensil['Name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <section class="rezept-block">
                <h3>Zubereitung</h3>
                <pre class="rezept-pre"><?= htmlspecialchars($rezept['zubereitung'] ?? 'Keine Angabe.') ?></pre>
            </section>

            <!-- Nährwerte-Bereich -->
            <section class="rezept-block" id="naehrwerte-section">
                <h3>Nährwerte pro Portion</h3>

                <!-- Einwilligungsbereich (wird nur angezeigt wenn noch keine Einwilligung) -->
                <div id="consent-area" class="<?= !empty($_SESSION['naehrwerte_einwilligung']) ? 'flash-hidden' : '' ?>">
                    <div class="consent-info">
                        <h4>Datenschutzhinweis</h4>
                        <p>Zur Berechnung der Nährwerte werden die Zutaten dieses Rezepts an den externen Dienst <strong>Spoonacular</strong> übertragen. Es werden keine personenbezogenen Daten übermittelt.</p>
                        <p>Weitere Informationen findest du in unserer <a href="index.php?page=datenschutz" target="_blank">Datenschutzerklärung</a>.</p>

                        <label style="display: block; margin: 10px 0;">
                            <input type="checkbox" id="consent-checkbox">
                            Ich stimme der Übertragung der Rezeptdaten zur Nährwertberechnung zu.
                        </label>

                        <button type="button" id="consent-btn" class="btn" disabled>Einwilligung speichern</button>
                    </div>
                </div>

                <!-- Nährwerte-Anzeige -->
                <div id="naehrwerte-content">
                    <?php
                    // Prüfen ob bereits Nährwerte vorhanden sind
                    $vorhandeneNaehrwerte = null;
                    try {
                        require_once 'php/model/NaehrwerteDAO.php';
                        $naehrwerteDAO = new NaehrwerteDAO();
                        $vorhandeneNaehrwerte = $naehrwerteDAO->holeNaehrwerte($rezept['id']);
                    } catch (Exception $e) {
                        // Fehler beim Laden der Nährwerte - ignorieren und ohne Nährwerte fortfahren
                        error_log("Fehler beim Laden der Nährwerte: " . $e->getMessage());
                    }
                    ?>

                    <?php if ($vorhandeneNaehrwerte): ?>
                        <div id="naehrwerte-display">
                            <div class="naehrwerte-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 15px 0;">
                                <div class="naehrwert-item">
                                    <strong>Kalorien:</strong><br>
                                    <span class="naehrwert-wert"><?= number_format($vorhandeneNaehrwerte['kalorien'], 0) ?> kcal</span>
                                </div>
                                <div class="naehrwert-item">
                                    <strong>Protein:</strong><br>
                                    <span class="naehrwert-wert"><?= number_format($vorhandeneNaehrwerte['protein'], 1) ?> g</span>
                                </div>
                                <div class="naehrwert-item">
                                    <strong>Kohlenhydrate:</strong><br>
                                    <span class="naehrwert-wert"><?= number_format($vorhandeneNaehrwerte['kohlenhydrate'], 1) ?> g</span>
                                </div>
                                <div class="naehrwert-item">
                                    <strong>Fett:</strong><br>
                                    <span class="naehrwert-wert"><?= number_format($vorhandeneNaehrwerte['fett'], 1) ?> g</span>
                                </div>
                                <div class="naehrwert-item">
                                    <strong>Ballaststoffe:</strong><br>
                                    <span class="naehrwert-wert"><?= number_format($vorhandeneNaehrwerte['ballaststoffe'], 1) ?> g</span>
                                </div>
                                <div class="naehrwert-item">
                                    <strong>Zucker:</strong><br>
                                    <span class="naehrwert-wert"><?= number_format($vorhandeneNaehrwerte['zucker'], 1) ?> g</span>
                                </div>
                            </div>
                            <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                                Berechnet am: <?= date('d.m.Y', strtotime($vorhandeneNaehrwerte['berechnet_am'])) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div id="naehrwerte-placeholder">
                            <?php
                            // Prüfen ob die Nachricht vom Rezept-Update kommt (nicht vom Bearbeiten-Link)
                            if (isset($_SESSION['naehrwerte_zurueckgesetzt']) && $_SESSION['naehrwerte_zurueckgesetzt'] === true):
                                // Flag zurücksetzen, damit Nachricht nur einmal angezeigt wird
                                unset($_SESSION['naehrwerte_zurueckgesetzt']);
                            ?>
                                <div class="info-box" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                    <strong>ℹ️ Hinweis:</strong> Das Rezept wurde bearbeitet. Die Nährwerte müssen neu berechnet werden.
                                </div>
                            <?php endif; ?>

                            <p>Für dieses Rezept wurden noch keine Nährwerte berechnet.</p>
                            <button type="button" id="berechne-naehrwerte-btn" class="btn"
                                    style="<?= empty($_SESSION['naehrwerte_einwilligung']) ? 'display: none;' : '' ?>">
                                Nährwerte berechnen
                            </button>
                        </div>
                        <div id="naehrwerte-display" style="display: none;"></div>
                    <?php endif; ?>

                    <div id="naehrwerte-loading" style="display: none;">
                        <p>Nährwerte werden berechnet... <span class="loading-spinner">⏳</span></p>
                    </div>

                    <div id="naehrwerte-error" style="display: none; color: #d32f2f; margin-top: 10px;"></div>
                </div>
            </section>

            <!-- Bewertungsformular nur, wenn angemeldet und nicht Ersteller -->
            <?php if (!empty($_SESSION['nutzerId']) && isset($istEigenerErsteller) && !$istEigenerErsteller): ?>
                <section class="rezept-block">
                    <h3>Deine Bewertung</h3>
                    <form action="index.php?page=bewerteRezept" method="post" id="bewertungs-form" style="display:inline-block;">
                        <input type="hidden" name="rezeptId" value="<?= htmlspecialchars($rezept['id']) ?>">
                        <div id="star-rating" style="font-size: 2rem; user-select: none;">
                            <?php
                            $eigenePunkte = ($nutzerBewertung && isset($nutzerBewertung->Punkte)) ? (int)$nutzerBewertung->Punkte : 0;
                            for ($i = 1; $i <= 5; $i++):
                                $class = ($i <= $eigenePunkte) ? 'selected' : '';
                                ?>
                                <span class="star <?= $class ?>" data-value="<?= $i ?>" style="cursor: pointer;">&#9733;</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="punkte" id="punkte-input" value="<?= $eigenePunkte ?>">
                        <br>
                        <button type="submit" class="btn" style="margin-top: 8px;">Bewertung speichern</button>
                    </form>
                </section>
            <?php endif; ?>

            <?php
            $darfBearbeiten = false;
            if (isset($_SESSION['nutzerId'])) {
                $darfBearbeiten = (
                    (int)$_SESSION['nutzerId'] === (int)($rezept['erstellerId'] ?? 0)
                    || !empty($_SESSION['istAdmin'])
                );
            }
            ?>



            <?php if ($darfBearbeiten): ?>
                <div class="rezept-aktion" style="margin-top: 16px;">
                    <a href="index.php?page=rezept-bearbeiten&id=<?= urlencode($rezept['id']) ?>" class="btn">Bearbeiten</a>
                    <a href="index.php?page=rezept-loeschen&id=<?= urlencode($rezept['id']) ?>"
                       class="btn"
                       onclick="return confirm('Möchtest du dieses Rezept wirklich löschen?');">
                        Löschen
                    </a>
                </div>
            <?php endif; ?>

            <!-- Ganz unten: Anmelden Button mit Text -->
            <?php if (empty($_SESSION['nutzerId'])): ?>
                <section class="rezept-block" style="margin-top: 20px;">
                    <form action="index.php" method="get" style="display:inline;">
                        <input type="hidden" name="page" value="anmeldung">
                        <input type="hidden" name="return" value="<?= 'rezept&id=' . urlencode($rezept['id']) ?>">
                        <button type="submit" class="btn">Anmelden</button>
                    </form>
                    <span>um das Rezept zu bewerten.</span>
                </section>
            <?php elseif (isset($istEigenerErsteller) && $istEigenerErsteller): ?>
                <section class="rezept-block" style="margin-top: 20px;">
                    <p>Du kannst dein eigenes Rezept nicht bewerten.</p>
                </section>
            <?php endif; ?>

        </article>
    <?php else: ?>
        <div class="message-box">Rezept nicht gefunden.</div>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align:center;">
        <a href="index.php?page=rezepte" class="btn">Zurück zur Übersicht</a>
    </div>

    <dialog id="loesch-modal" class="modal-dialog">
        <div class="modal-box">
            <h3>Rezept löschen</h3>
            <p id="loesch-text">Möchtest du dieses Rezept wirklich löschen?</p>
            <div class="modal-actions">
                <button class="btn" id="btn-abbrechen">Abbrechen</button>
                <button class="btn" id="btn-bestaetigen">Löschen</button>
            </div>
        </div>
    </dialog>

</main>



    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("loesch-modal");
            const loeschText = document.getElementById("loesch-text");
            const abbrechenBtn = document.getElementById("btn-abbrechen");
            const bestaetigenBtn = document.getElementById("btn-bestaetigen");

            let aktiveButton = null;

            document.querySelectorAll(".rezept-loeschen-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    aktiveButton = btn;
                    const titel = btn.closest(".rezept-karte").querySelector("h4")?.innerText || "dieses Rezept";
                    loeschText.textContent = `Möchtest du „${titel}“ wirklich löschen?`;
                    modal.showModal();
                });
            });

            abbrechenBtn.addEventListener("click", () => {
                modal.close();
                aktiveButton = null;
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
                } else {
                    alert("Fehler: " + json.message);
                }

                modal.close();
                aktiveButton = null;
            });

            // Speichern-Button Funktionalität
            const speichernBtn = document.getElementById("speichern-btn");
            if (speichernBtn) {
                speichernBtn.addEventListener("click", async () => {
                    const rezeptId = speichernBtn.dataset.rezeptId;
                    const istGespeichert = speichernBtn.dataset.istGespeichert === 'true';
                    const aktion = istGespeichert ? 'entfernen' : 'speichern';

                    // Button während der Anfrage deaktivieren
                    speichernBtn.disabled = true;
                    const originalText = speichernBtn.textContent;
                    speichernBtn.textContent = istGespeichert ? 'Entferne...' : 'Speichere...';

                    const formData = new FormData();
                    formData.append("rezeptId", rezeptId);
                    formData.append("aktion", aktion);
                    formData.append("csrf_token", "<?php require_once 'php/include/csrf_protection.php'; echo generateCSRFToken(); ?>");

                    try {
                        const res = await fetch("api/rezept-speichern.php", {
                            method: "POST",
                            body: formData
                        });

                        const json = await res.json();
                        if (json.success) {
                            // Button-Status aktualisieren
                            const neuerStatus = json.istGespeichert;
                            speichernBtn.dataset.istGespeichert = neuerStatus ? 'true' : 'false';
                            speichernBtn.textContent = neuerStatus ? '❤️ Gespeichert' : '🤍 Speichern';
                            speichernBtn.className = neuerStatus ? 'btn btn-secondary' : 'btn btn-primary';

                            // Toast-Nachricht anzeigen
                            showToast(json.message, "success");
                        } else {
                            showToast("Fehler: " + json.message, "error");
                            speichernBtn.textContent = originalText;
                        }
                    } catch (error) {
                        showToast("Netzwerkfehler beim Speichern", "error");
                        speichernBtn.textContent = originalText;
                    } finally {
                        speichernBtn.disabled = false;
                    }
                });
            }

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

