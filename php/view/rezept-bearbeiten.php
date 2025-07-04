<?php
/** @var array $rezept */
?>
<main>
    <h2>Rezept bearbeiten</h2>

    <div class="form-container">
        <form action="index.php?page=rezept-aktualisieren&id=<?= urlencode($rezept['id']) ?>" method="post" enctype="multipart/form-data">
            <?= getCSRFTokenField() ?>
            <!-- TITEL -->
            <div class="form-row">
                <label for="titel">Titel:</label>
                <input type="text" id="titel" name="titel" maxlength="50" value="<?= htmlspecialchars($_SESSION['formdata']['titel'] ?? $rezept['titel'] ?? '') ?>" required>
            </div>

            <!-- ZUTATEN -->
            <div class="form-row">
                <label for="zutaten">Zutaten:</label>
            </div>
            <div class="zutaten-bereich">
                <div id="zutaten-container">
                    <?php
                    $zutaten = $_SESSION['formdata']['zutaten'] ?? $rezept['zutaten'] ?? [];
                    if (empty($zutaten)) $zutaten[] = ['zutat' => '', 'menge' => '', 'einheit' => ''];
                    $einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];
                    foreach ($zutaten as $zutat):
                        ?>
                        <div class="zutaten-paar" style="display: flex; gap: 8px; margin-bottom: 6px; align-items:center;">
                            <input type="text" name="zutatennamen[]" placeholder="Zutat" value="<?= htmlspecialchars($zutat['zutat']) ?>" required>
                            <input type="text" name="mengen[]" placeholder="Menge" value="<?= htmlspecialchars($zutat['menge']) ?>" required>
                            <select name="einheiten[]">
                                <option value="">Einheit</option>
                                <?php foreach ($einheitenListe as $einheit): ?>
                                    <option value="<?= $einheit ?>" <?= $zutat['einheit'] === $einheit ? "selected" : "" ?>><?= $einheit ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="button" class="btn" onclick="neueZutat()">+ Neue Zutat</button>
                    <button type="button" class="btn" onclick="letztesZutatEntfernen()">− Zutat entfernen</button>
                </div>
            </div>

            <!-- ZUBEREITUNG -->
            <div class="form-row">
                <label for="zubereitung">Zubereitung:</label>
            </div>
            <div class="form-row">
                <textarea id="zubereitung" name="zubereitung" rows="6" required><?= htmlspecialchars($_SESSION['formdata']['zubereitung'] ?? $rezept['zubereitung']) ?></textarea>
            </div>

            <!-- UTENSILIEN + KATEGORIEN (Dropdown mit Mehrfachauswahl) -->
            <?php
            $formUtensilien = $_SESSION['formdata']['utensilien'] ?? array_column($rezept['utensilien'], 'UtensilID');
            $formKategorien = $_SESSION['formdata']['kategorien'] ?? array_column($rezept['kategorienMitIds'], 'KategorieID');
            ?>

            <div class="form-row">
                <label for="utensilien">Utensilien:</label>
                <div class="dropdown-multiselect">
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
            </div>

            <div class="form-row">
                <label for="kategorien">Kategorien:</label>
                <div class="dropdown-multiselect">
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
            </div>

            <!-- PREISKLASSE UND PORTIONSGRÖße -->
            <?php
            $formPortionsgroesse = $_SESSION['formdata']['portionsgroesse'] ?? $rezept['portionsgroesseId'];
            $formPreisklasse = $_SESSION['formdata']['preisklasse'] ?? $rezept['preisklasseId'];
            ?>

            <div class="form-row">
                <label for="preisklasse">Preisklasse:</label>
                <div class="dropdown-multiselect single-select">
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
                        <span class="dropdown-arrow">&#x25BE;</span>
                    </div>
                    <ul class="dropdown-list">
                        <?php foreach ($_SESSION['preisklasseListe'] as $id => $pl):
                            echo "<li data-value='$id'" . ((string)$id === (string)$formPreisklasse ? " class='selected'" : "") . ">" . htmlspecialchars($pl->Preisspanne ?? $pl) . "</li>";
                        endforeach; ?>
                    </ul>
                    <input type="hidden" name="preisklasse" id="preisklasse-hidden" value="<?= htmlspecialchars($formPreisklasse) ?>">
                </div>
            </div>

            <div class="form-row">
                <label for="portionsgroesse">Portionsgröße:</label>
                <div class="dropdown-multiselect single-select">
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
                        <span class="dropdown-arrow">&#x25BE;</span>
                    </div>
                    <ul class="dropdown-list">
                        <?php foreach ($_SESSION['portionsgroesseListe'] as $id => $pg):
                            echo "<li data-value='$id'" . ((string)$id === (string)$formPortionsgroesse ? " class='selected'" : "") . ">" . htmlspecialchars($pg->Angabe ?? $pg) . "</li>";
                        endforeach; ?>
                    </ul>
                    <input type="hidden" name="portionsgroesse" id="portionsgroesse-hidden" value="<?= htmlspecialchars($formPortionsgroesse) ?>">
                </div>
            </div>

            <!-- BILD -->
            <div class="bild-upload">
                <div class="form-row">
                    <label><strong>Aktuelles Bild:</strong></label>
                </div>

                <div class="form-row">
                    <?php if (!empty($rezept['bild'])): ?>
                        <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="Rezeptbild"
                             style="border-radius:6px; max-width:300px; object-fit:contain;">
                    <?php else: ?>
                        <p>Kein Bild vorhanden</p>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <label><strong>Neues Bild hochladen (optional):</strong></label>
                </div>

                <div class="form-row datei-auswahl">
                    <div class="custom-file-upload">
                        <button type="button" id="btn-select-file" class="btn">Datei auswählen</button>
                        <span id="selected-file-name">Keine Datei ausgewählt</span>
                        <input type="file" id="bild" name="bild" accept="image/*" hidden>
                    </div>
                </div>

                <div class="form-row">
                    <div id="preview-container" style="display:none; border-radius:6px;">
                        <img id="img-preview" src="" alt="Bildvorschau"
                             style="border-radius:6px; display:none; width:auto; max-width:300px; height:auto; object-fit:contain;">
                    </div>
                </div>
            </div>

            <div class="form-row">
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

        const mengeInput = document.createElement("input");
        mengeInput.type = "text";
        mengeInput.name = "mengen[]";
        mengeInput.placeholder = "Menge";

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
        zutatInput.required = true;

        const mengeInput = document.createElement("input");
        mengeInput.type = "text";
        mengeInput.name = "mengen[]";
        mengeInput.placeholder = "Menge";
        mengeInput.required = true;

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
        // Single-Select Dropdown-Handler
        document.querySelectorAll('.dropdown-multiselect.single-select .dropdown-list li').forEach(li => {
            li.addEventListener('click', function() {
                const dropdown = this.closest('.dropdown-multiselect');
                const header = dropdown.querySelector('.dropdown-header');
                const label = header.querySelector('.dropdown-label');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');

                // Alle anderen li-Elemente deselektieren
                dropdown.querySelectorAll('.dropdown-list li').forEach(item => {
                    item.classList.remove('selected');
                });

                // Aktuelles Element selektieren
                this.classList.add('selected');

                // Label und Hidden Input aktualisieren
                label.textContent = this.textContent;
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
