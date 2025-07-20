<?php
/** @var array $rezept */
?>
<main>
    <h2>Rezept bearbeiten</h2>

    <?php if (isset($_SESSION["flash"])): ?>
        <div class="flash-message <?= $_SESSION['flash']['type'] ?>"><?= htmlspecialchars($_SESSION["flash"]["message"]) ?></div>
        <?php unset($_SESSION["flash"]); ?>
    <?php endif; ?>

    <div class="form-container">
        <form action="index.php?page=rezept-aktualisieren&id=<?= urlencode($rezept['id']) ?>" method="post" enctype="multipart/form-data">
            <?= getCSRFTokenField() ?>
            <div class="form-group">
                <label for="titel">Titel:</label>
                <input
                        type="text"
                        id="titel"
                        name="titel"
                        maxlength="50"
                        required
                        value="<?= htmlspecialchars($_SESSION['formdata']['titel'] ?? $rezept['titel'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="zutaten">Zutaten:</label>
            </div>
            <div class="zutaten-bereich">
                <div id="zutaten-container">
                    <?php
                    $zutaten = $_SESSION['formdata']['zutaten'] ?? $rezept['zutaten'] ?? [];
                    if (empty($zutaten)) $zutaten[] = ['zutat' => '', 'menge' => '', 'einheit' => ''];
                    $einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];
                    $maxZutatenFallback = 15; // Für No-JS Fallback
                    $aktuelleZutatenAnzahl = count($zutaten);

                    foreach ($zutaten as $index => $zutat):
                        ?>
                        <div class="zutaten-paar" style="display: flex; gap: 8px; margin-bottom: 6px; align-items: center;">
                            <input type="text" name="zutatennamen[]" placeholder="Zutat" maxlength="20" value="<?= htmlspecialchars($zutat['zutat']) ?>" <?= $index === 0 ? 'required' : '' ?>>
                            <input type="number" name="mengen[]" placeholder="Menge" step="0.1" min="0" max="999999" value="<?= htmlspecialchars($zutat['menge']) ?>">
                            <select name="einheiten[]">
                                <option value="">Einheit</option>
                                <?php foreach ($einheitenListe as $einheit): ?>
                                    <option value="<?= $einheit ?>" <?= $zutat['einheit'] === $einheit ? "selected" : "" ?>><?= $einheit ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>

                    <!-- Zusätzliche Felder für No-JavaScript Fallback -->
                    <noscript>
                        <?php for ($i = $aktuelleZutatenAnzahl; $i < $maxZutatenFallback; $i++): ?>
                            <div class="zutaten-paar" style="display: flex; gap: 8px; margin-bottom: 6px; align-items: center;">
                                <input type="text" name="zutatennamen[]" placeholder="Zutat (optional)" maxlength="20" value="">
                                <input type="number" name="mengen[]" placeholder="Menge" step="0.1" min="0" max="999999" value="">
                                <select name="einheiten[]">
                                    <option value="">Einheit</option>
                                    <?php foreach ($einheitenListe as $einheit): ?>
                                        <option value="<?= $einheit ?>"><?= $einheit ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endfor; ?>
                        <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
                            💡 <strong>Hinweis:</strong> Mit aktiviertem JavaScript können Sie dynamisch Zutaten hinzufügen/entfernen.
                        </p>
                    </noscript>
                </div>

                <!-- JavaScript-Enhanced Buttons -->
                <div class="js-only flex" style="gap: 10px; margin-top: 8px;">
                    <button type="button" class="btn" onclick="neueZutat()">+ Neue Zutat</button>
                    <button type="button" class="btn" onclick="letztesZutatEntfernen()">− Zutat entfernen</button>
                </div>
            </div>

            <div class="form-group">
                <label for="zubereitung">Zubereitung:</label>
                <textarea
                        id="zubereitung"
                        name="zubereitung"
                        rows="6"
                        required
                        placeholder="Beschreiben Sie hier Schritt für Schritt die Zubereitung..."
                ><?= htmlspecialchars($_SESSION['formdata']['zubereitung'] ?? $rezept['zubereitung']) ?></textarea>
            </div>

            <?php
            $formUtensilien = $_SESSION['formdata']['utensilien'] ?? array_column($rezept['utensilien'], 'UtensilID');
            $formKategorien = $_SESSION['formdata']['kategorien'] ?? array_column($rezept['kategorienMitIds'], 'KategorieID');
            ?>

            <div class="form-group">
                <label for="utensilien">Utensilien:</label>

                <!-- JavaScript-Enhanced Dropdown -->
                <div class="dropdown-multiselect js-only">
                    <div class="dropdown-header" onclick="toggleDropdown(this)">
                        <span class="dropdown-label">
                            <?php
                            $selectedUtensilien = array_filter($_SESSION['utensilienListe'], function($id) use ($formUtensilien) {
                                return in_array($id, $formUtensilien);
                            }, ARRAY_FILTER_USE_KEY);

                            if (empty($selectedUtensilien)) {
                                echo '-- auswählen --';
                            } elseif (count($selectedUtensilien) === 1) {
                                $utensil = reset($selectedUtensilien);
                                echo htmlspecialchars($utensil->Name ?? $utensil);
                            } else {
                                echo count($selectedUtensilien) . ' ausgewählt';
                            }
                            ?>
                        </span>
                        <span class="dropdown-arrow">&#x25BE;</span>
                    </div>
                    <div class="dropdown-list">
                        <?php foreach ($_SESSION['utensilienListe'] as $id => $utensil):
                            $checked = in_array($id, $formUtensilien) ? "checked" : ""; ?>
                            <label><input type="checkbox" name="utensilien[]" value="<?= $id ?>" <?= $checked ?>> <?= htmlspecialchars($utensil->Name ?? $utensil) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Fallback für ohne JavaScript -->
                <noscript>
                    <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                        <?php foreach ($_SESSION['utensilienListe'] as $id => $utensil):
                            $checked = in_array($id, $formUtensilien) ? "checked" : ""; ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" name="utensilien[]" value="<?= $id ?>" <?= $checked ?>>
                                <?= htmlspecialchars($utensil->Name ?? $utensil) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </noscript>
            </div>

            <div class="form-group">
                <label>Kategorien:</label>

                <!-- JavaScript-Enhanced Dropdown -->
                <div class="dropdown-multiselect js-only">
                    <div class="dropdown-header" onclick="toggleDropdown(this)">
                        <span class="dropdown-label">
                            <?php
                            $selectedKategorien = array_filter($_SESSION['kategorienListe'], function($id) use ($formKategorien) {
                                return in_array($id, $formKategorien);
                            }, ARRAY_FILTER_USE_KEY);

                            if (empty($selectedKategorien)) {
                                echo '-- auswählen --';
                            } elseif (count($selectedKategorien) === 1) {
                                $kategorie = reset($selectedKategorien);
                                echo htmlspecialchars($kategorie->Bezeichnung ?? $kategorie);
                            } else {
                                echo count($selectedKategorien) . ' ausgewählt';
                            }
                            ?>
                        </span>
                        <span class="dropdown-arrow">&#x25BE;</span>
                    </div>
                    <div class="dropdown-list">
                        <?php foreach ($_SESSION['kategorienListe'] as $id => $kategorie):
                            $checked = in_array($id, $formKategorien) ? "checked" : ""; ?>
                            <label><input type="checkbox" name="kategorien[]" value="<?= $id ?>" <?= $checked ?>> <?= htmlspecialchars($kategorie->Bezeichnung ?? $kategorie) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Fallback für ohne JavaScript -->
                <noscript>
                    <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                        <?php foreach ($_SESSION['kategorienListe'] as $id => $kategorie):
                            $checked = in_array($id, $formKategorien) ? "checked" : ""; ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" name="kategorien[]" value="<?= $id ?>" <?= $checked ?>>
                                <?= htmlspecialchars($kategorie->Bezeichnung ?? $kategorie) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </noscript>
            </div>

            <?php
            $formPortionsgroesse = $_SESSION['formdata']['portionsgroesse'] ?? $rezept['portionsgroesseId'];
            $formPreisklasse = $_SESSION['formdata']['preisklasse'] ?? $rezept['preisklasseId'];
            ?>

            <div class="form-group">
                <label for="preisklasse">Preisklasse:</label>

                <!-- JavaScript-Enhanced Dropdown -->
                <div class="dropdown-multiselect single-select js-only">
                    <div class="dropdown-header" onclick="toggleDropdown(this)">
                        <span class="dropdown-label">
                            <?php
                            if (!empty($formPreisklasse) && isset($_SESSION['preisklasseListe'][$formPreisklasse])) {
                                $preisklasse = $_SESSION['preisklasseListe'][$formPreisklasse];
                                echo htmlspecialchars($preisklasse->Preisspanne ?? $preisklasse);
                            } else {
                                echo '-- auswählen --';
                            }
                            ?>
                        </span>
                        <span class="dropdown-arrow">▾</span>
                    </div>
                    <input type="hidden" name="preisklasse" id="preisklasse-hidden" value="<?= htmlspecialchars($formPreisklasse) ?>">
                    <div class="dropdown-list">
                        <?php foreach ($_SESSION['preisklasseListe'] as $id => $pl): ?>
                            <div data-value="<?= htmlspecialchars($id) ?>" class="dropdown-option <?= ((string)$id === (string)$formPreisklasse) ? 'selected' : '' ?>">
                                <?= htmlspecialchars($pl->Preisspanne ?? $pl) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Fallback für ohne JavaScript -->
                <noscript>
                    <select name="preisklasse" required>
                        <option value="">-- Bitte wählen --</option>
                        <?php foreach ($_SESSION['preisklasseListe'] as $id => $pl):
                            $selected = ((string)$id === (string)$formPreisklasse) ? "selected" : "";
                            ?>
                            <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pl->Preisspanne ?? $pl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </noscript>
            </div>

            <div class="form-group">
                <label for="portionsgroesse">Portionsgröße:</label>

                <!-- JavaScript-Enhanced Dropdown -->
                <div class="dropdown-multiselect single-select js-only">
                    <div class="dropdown-header" onclick="toggleDropdown(this)">
                        <span class="dropdown-label">
                            <?php
                            if (!empty($formPortionsgroesse) && isset($_SESSION['portionsgroesseListe'][$formPortionsgroesse])) {
                                $portionsgroesse = $_SESSION['portionsgroesseListe'][$formPortionsgroesse];
                                echo htmlspecialchars($portionsgroesse->Angabe ?? $portionsgroesse);
                            } else {
                                echo '-- auswählen --';
                            }
                            ?>
                        </span>
                        <span class="dropdown-arrow">▾</span>
                    </div>
                    <input type="hidden" name="portionsgroesse" id="portionsgroesse-hidden" value="<?= htmlspecialchars($formPortionsgroesse) ?>">
                    <div class="dropdown-list">
                        <?php foreach ($_SESSION['portionsgroesseListe'] as $id => $pg): ?>
                            <div data-value="<?= htmlspecialchars($id) ?>" class="dropdown-option <?= ((string)$id === (string)$formPortionsgroesse) ? 'selected' : '' ?>">
                                <?= htmlspecialchars($pg->Angabe ?? $pg) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Fallback für ohne JavaScript -->
                <noscript>
                    <select name="portionsgroesse" required>
                        <option value="">-- Bitte wählen --</option>
                        <?php foreach ($_SESSION['portionsgroesseListe'] as $id => $pg):
                            $selected = ((string)$id === (string)$formPortionsgroesse) ? "selected" : "";
                            ?>
                            <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pg->Angabe ?? $pg) ?></option>
                        <?php endforeach; ?>
                    </select>
                </noscript>
            </div>

            <div class="form-group">
                <label><strong>Aktuelles Bild:</strong></label>
                <?php if (!empty($rezept['bild'])): ?>
                    <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="Rezeptbild"
                         style="border-radius:6px; max-width:300px; object-fit:contain; margin-top: 10px;">
                <?php else: ?>
                    <p style="color: #666; margin-top: 5px;">Kein Bild vorhanden</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="bild">Neues Bild hochladen (optional):</label>

                <!-- JavaScript-Enhanced File Upload -->
                <div class="custom-file-upload js-only">
                    <button type="button" id="btn-select-file" class="btn">Datei auswählen</button>
                    <span id="selected-file-name">Keine Datei ausgewählt</span>
                    <input type="file" id="bild" name="bild" accept="image/*" hidden>
                </div>

                <!-- Fallback für ohne JavaScript -->
                <noscript>
                    <input type="file" name="bild" accept="image/*" class="form-control">
                    <p style="color: #666; font-size: 0.9em; margin-top: 5px;">
                        💡 <strong>Hinweis:</strong> Mit aktiviertem JavaScript erhalten Sie eine Bildvorschau.
                    </p>
                </noscript>
            </div>

            <!-- JavaScript-Enhanced Image Preview -->
            <div id="preview-container" class="image-preview-container js-only" style="display: none;">
                <img id="img-preview" src="" alt="Bildvorschau" class="image-preview">
            </div>

            <div class="form-group">
                <input type="submit" value="Änderungen speichern" class="btn">
                <button type="button" class="btn" onclick="window.history.back()">Abbrechen</button>
            </div>
        </form>
    </div>

    <?php unset($_SESSION['formdata']); ?>
</main>

<?php if (!empty($_SESSION['flash'])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            zeigeFlash("<?= $_SESSION['flash']['type'] ?>", "<?= htmlspecialchars($_SESSION['flash']['message']) ?>");
        });
    </script>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>


<script>
    function createZutatenZeile(disabled = false) {
        const einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];

        const div = document.createElement("div");
        div.className = "zutaten-paar";
        div.style.cssText = "display:flex; gap:8px; margin-bottom:6px; align-items:center;";

        const zutatInput = document.createElement("input");
        zutatInput.type = "text";
        zutatInput.name = "zutatennamen[]";
        zutatInput.placeholder = "Zutat";
        zutatInput.maxLength = 20;

        const mengeInput = document.createElement("input");
        mengeInput.type = "number";
        mengeInput.name = "mengen[]";
        mengeInput.placeholder = "Menge";
        mengeInput.step = "0.1";
        mengeInput.min = "0";
        mengeInput.max = "999999";

        const einheitSelect = document.createElement("select");
        einheitSelect.name = "einheiten[]";
        einheitSelect.innerHTML = `<option value="">Einheit</option>` +
            einheitenListe.map(e => `<option value="${e}">${e}</option>`).join('');

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "remove-zutat";
        removeBtn.innerHTML = "✕";
        removeBtn.title = disabled ? "" : "Diese Zeile entfernen";
        removeBtn.disabled = disabled;
        removeBtn.style.cssText = `
            background: #eee;
            border: 1px solid #ccc;
            padding: 4px 8px;
            cursor: ${disabled ? "default" : "pointer"};
            border-radius: 4px;
            opacity: ${disabled ? "0.4" : "1"};
        `;

        if (!disabled) {
            removeBtn.addEventListener("click", () => div.remove());
        }

        // Alle Elemente anhängen
        div.appendChild(zutatInput);
        div.appendChild(mengeInput);
        div.appendChild(einheitSelect);
        div.appendChild(removeBtn);

        return div;
    }

    function neueZutat() {
        const container = document.getElementById("zutaten-container");
        container.appendChild(createZutatenZeile(false)); // neue Zeile ist entfernbar
    }
</script>

<script>
    function createZutatenZeile() {
        const einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];
        const div = document.createElement("div");
        div.className = "zutaten-paar";
        div.style.cssText = "display:flex; gap:8px; margin-bottom:6px; align-items:center;";

        const zutatInput = document.createElement("input");
        zutatInput.type = "text";
        zutatInput.name = "zutatennamen[]";
        zutatInput.placeholder = "Zutat";
        zutatInput.maxLength = 20;
        zutatInput.required = true;

        const mengeInput = document.createElement("input");
        mengeInput.type = "number";
        mengeInput.name = "mengen[]";
        mengeInput.placeholder = "Menge";
        mengeInput.step = "0.1";
        mengeInput.min = "0";
        mengeInput.max = "999999";

        const einheitSelect = document.createElement("select");
        einheitSelect.name = "einheiten[]";
        einheitSelect.innerHTML = `<option value="">Einheit</option>` +
            einheitenListe.map(e => `<option value="${e}">${e}</option>`).join('');

        div.appendChild(zutatInput);
        div.appendChild(mengeInput);
        div.appendChild(einheitSelect);

        return div;
    }

    function neueZutat() {
        const container = document.getElementById("zutaten-container");
        container.appendChild(createZutatenZeile());
    }

    function letztesZutatEntfernen() {
        const container = document.getElementById("zutaten-container");
        const zutaten = container.getElementsByClassName("zutaten-paar");
        if (zutaten.length > 1) {
            container.removeChild(zutaten[zutaten.length - 1]);
        }
    }
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

    // Dropdown-Funktionalität
    function toggleDropdown(header) {
        const dropdown = header.parentElement;
        const isOpen = dropdown.classList.contains('open');

        // Alle anderen Dropdowns schließen
        document.querySelectorAll('.dropdown-multiselect.open').forEach(d => {
            if (d !== dropdown) {
                d.classList.remove('open');
            }
        });

        // Aktuelles Dropdown umschalten
        dropdown.classList.toggle('open', !isOpen);
    }

    // Event-Listener für Single-Select Dropdowns (Preisklasse, Portionsgröße)
    document.addEventListener('DOMContentLoaded', function() {
        // Single-Select Dropdown-Handler für DIV-basierte Optionen
        document.querySelectorAll('.dropdown-multiselect.single-select .dropdown-list .dropdown-option').forEach(option => {
            option.addEventListener('click', function() {
                const dropdown = this.closest('.dropdown-multiselect');
                const header = dropdown.querySelector('.dropdown-header');
                const label = header.querySelector('.dropdown-label');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');

                // Alle anderen Optionen deselektieren
                dropdown.querySelectorAll('.dropdown-list .dropdown-option').forEach(item => {
                    item.classList.remove('selected');
                });

                // Aktuelles Element selektieren
                this.classList.add('selected');

                // Label und Hidden Input aktualisieren
                label.textContent = this.textContent.trim();
                hiddenInput.value = this.getAttribute('data-value');

                // Dropdown schließen
                dropdown.classList.remove('open');
            });
        });

        // Multi-Select Dropdown-Handler (Kategorien, Utensilien)
        document.querySelectorAll('.dropdown-multiselect:not(.single-select)').forEach(dropdown => {
            const header = dropdown.querySelector('.dropdown-header');
            const label = header.querySelector('.dropdown-label');
            const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');

            // Label beim Laden aktualisieren
            updateMultiSelectLabel(dropdown);

            // Checkbox-Änderungen überwachen
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateMultiSelectLabel(dropdown);
                });
            });
        });

        // Single-Select Labels beim Laden aktualisieren
        document.querySelectorAll('.dropdown-multiselect.single-select').forEach(dropdown => {
            const selectedLi = dropdown.querySelector('.dropdown-list li.selected');
            if (selectedLi) {
                const label = dropdown.querySelector('.dropdown-label');
                label.textContent = selectedLi.textContent;
            }
        });

        // Außerhalb klicken schließt Dropdowns
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-multiselect')) {
                document.querySelectorAll('.dropdown-multiselect.open').forEach(d => {
                    d.classList.remove('open');
                });
            }
        });
    });

    function updateMultiSelectLabel(dropdown) {
        const label = dropdown.querySelector('.dropdown-label');
        const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]:checked');

        if (checkboxes.length === 0) {
            label.textContent = '-- auswählen --';
        } else if (checkboxes.length === 1) {
            label.textContent = checkboxes[0].parentElement.textContent.trim();
        } else {
            label.textContent = `${checkboxes.length} ausgewählt`;
        }
    }
</script>
