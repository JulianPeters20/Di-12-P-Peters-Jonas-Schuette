<main>
    <h2>Neues Rezept erstellen</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <div class="form-container">

    <form action="index.php?page=rezept-neu" method="post" enctype="multipart/form-data">
        <div class="form-row">
            <label for="titel">Titel:</label>
        </div>
        <div class="form-row">
            <input type="text" id="titel" name="titel" required
                   value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? "") ?>">
        </div>

        <div class="form-row">
            <label for="zutaten">Zutaten:</label>
        </div>

        <div class="zutaten-bereich">
            <div id="zutaten-container">
                <?php
                $zutatennamen = $_SESSION["formdata"]["zutatennamen"] ?? [""];
                $mengen = $_SESSION["formdata"]["mengen"] ?? [""];
                $einheiten = $_SESSION["formdata"]["einheiten"] ?? [""];
                $einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];
                $count = max(count($zutatennamen), count($mengen), count($einheiten));
                for ($i = 0; $i < $count; $i++):
                    $disableRemove = ($i === 0);?>
                    <div class="zutaten-paar" style="display: flex; gap: 8px; margin-bottom: 6px;">
                        <input type="text" name="zutatennamen[]" placeholder="Zutat"
                               value="<?= htmlspecialchars($zutatennamen[$i]) ?>">
                        <input type="text" name="mengen[]" placeholder="Menge"
                               value="<?= htmlspecialchars($mengen[$i]) ?>">
                        <select name="einheiten[]">
                            <option value="">Einheit</option>
                            <?php foreach ($einheitenListe as $einheit):
                                $selected = ($einheiten[$i] === $einheit) ? "selected" : "";
                                echo "<option value=\"$einheit\" $selected>$einheit</option>";
                            endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </div>

            <button type="button" class="btn" style="margin-top: 8px;" onclick="neueZutat()">+ Neue Zutat</button>
        </div>

        <div class="form-row">
            <label for="zubereitung">Zubereitung:</label>
        </div>
        <div class="form-row">
            <textarea id="zubereitung" name="zubereitung" rows="6" required><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? "") ?></textarea>
        </div>

        <div class="form-row">
            <label for="utensilien">Utensilien:</label>
            <div class="dropdown-multiselect">
                <div class="dropdown-header" onclick="toggleDropdown(this)">
                    <span class="dropdown-label">-- auswählen --</span>
                    <span class="dropdown-arrow">▾</span>
                </div>
                <div class="dropdown-list">
                    <?php foreach ($_SESSION['utensilienListe'] as $id => $utensil):
                        $checked = in_array($id, $_SESSION["formdata"]["utensilien"] ?? []) ? "checked" : "";
                        ?>
                        <label>
                            <input type="checkbox" name="utensilien[]" value="<?= htmlspecialchars($id) ?>" <?= $checked ?>>
                            <?= htmlspecialchars($utensil->Name ?? $utensil) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-row">
            <label>Kategorien:</label>
            <div class="dropdown-multiselect">
                <div class="dropdown-header" onclick="toggleDropdown(this)">
                    <span class="dropdown-label">-- auswählen --</span>
                    <span class="dropdown-arrow">▾</span>
                </div>
                <div class="dropdown-list">
                    <?php foreach ($_SESSION['kategorienListe'] as $id => $kategorie):
                        $checked = in_array($id, $_SESSION["formdata"]["kategorien"] ?? []) ? "checked" : "";
                        ?>
                        <label>
                            <input type="checkbox" name="kategorien[]" value="<?= htmlspecialchars($id) ?>" <?= $checked ?>>
                            <?= htmlspecialchars($kategorie->Bezeichnung ?? $kategorie) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-row">
            <label for="preisklasse">Preisklasse:</label>
            <div class="dropdown-multiselect single-select">
                <div class="dropdown-header" onclick="toggleDropdown(this)">
                    <span class="dropdown-label">-- auswählen --</span>
                    <span class="dropdown-arrow">▾</span>
                </div>
                <div class="dropdown-list">
                    <?php foreach ($_SESSION['preisklasseListe'] as $id => $pl):
                        $selected = ((string)($_SESSION["formdata"]["preisklasse"] ?? '') === (string)$id) ? "selected" : "";
                        ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pl->Preisspanne ?? $pl) ?></option>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="preisklasse" id="preisklasse-hidden">
            </div>
        </div>

        <div class="form-row">
            <label for="portionsgroesse">Portionsgröße:</label>
            <div class="dropdown-multiselect single-select">
                <div class="dropdown-header" onclick="toggleDropdown(this)">
                    <span class="dropdown-label">-- auswählen --</span>
                    <span class="dropdown-arrow">▾</span>
                </div>
                <div class="dropdown-list">
                    <?php foreach ($_SESSION['portionsgroesseListe'] as $id => $pg):
                        $selected = ((string)($_SESSION["formdata"]["portionsgroesse"] ?? '') === (string)$id) ? "selected" : "";
                        ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pg->Angabe ?? $pg) ?></option>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="portionsgroesse" id="portionsgroesse-hidden">
            </div>
        </div>

        <div class="form-row datei-auswahl">
            <div class="custom-file-upload">
                <button type="button" id="btn-select-file" class="btn">Datei auswählen</button>
                <span id="selected-file-name">Keine Datei ausgewählt</span>
                <input type="file" id="bild" name="bild" accept="image/*" hidden>
            </div>
        </div>

        <div id="preview-container" style="display:none; border-radius:6px; margin-top: 10px; overflow: visible;">
            <img id="img-preview" src="" alt="Bildvorschau" style="border-radius:6px; display:none; width:auto; max-width:300px; height:auto; object-fit:contain;">
        </div>

        <div class="form-row">
            <input type="submit" value="Rezept speichern" class="btn">
            <input type="reset" value="Eingaben zurücksetzen" class="btn">
        </div>
    </form>

    </div>

    <?php unset($_SESSION["formdata"]); ?>

</main>

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
        function toggleDropdown(headerElement) {
            const currentDropdown = headerElement.parentElement;

            // Schließe alle anderen Dropdowns
            document.querySelectorAll(".dropdown-multiselect.open").forEach(dropdown => {
                if (dropdown !== currentDropdown) {
                    dropdown.classList.remove("open");
                }
            });

            // Aktuelles Dropdown öffnen/schließen
            currentDropdown.classList.toggle("open");
        }

        document.addEventListener("DOMContentLoaded", () => {
            // Zähler-Label bei Multiselect aktualisieren
            document.querySelectorAll(".dropdown-multiselect").forEach(dropdown => {
                const labelSpan = dropdown.querySelector(".dropdown-label");
                const checkboxes = dropdown.querySelectorAll("input[type='checkbox']");

                const updateLabel = () => {
                    const count = Array.from(checkboxes).filter(cb => cb.checked).length;
                    labelSpan.textContent = count === 0 ? "-- auswählen --" : `${count} ausgewählt`;
                };

                checkboxes.forEach(cb => cb.addEventListener("change", updateLabel));
                updateLabel();
            });

            // Klick außerhalb schließt offene Dropdowns
            document.addEventListener("click", (event) => {
                document.querySelectorAll(".dropdown-multiselect.open").forEach(dropdown => {
                    if (!dropdown.contains(event.target)) {
                        dropdown.classList.remove("open");
                    }
                });
            });
        });
        document.addEventListener("DOMContentLoaded", () => {
            // Single-Select Dropdowns
            document.querySelectorAll(".single-select").forEach(dropdown => {
                const label = dropdown.querySelector(".dropdown-label");
                const hiddenInput = dropdown.querySelector("input[type=hidden]");
                const options = dropdown.querySelectorAll("li");

                options.forEach(option => {
                    option.addEventListener("click", () => {
                        const value = option.getAttribute("data-value");
                        const text = option.textContent;

                        hiddenInput.value = value;
                        label.textContent = text;

                        // Entferne alte Auswahl-Styles
                        options.forEach(o => o.classList.remove("selected"));
                        option.classList.add("selected");

                        dropdown.classList.remove("open");
                    });
                });
            });
        });
    </script>

    <script>
        document.querySelector("form").addEventListener("reset", function () {
            // Bildvorschau zurücksetzen
            const preview = document.getElementById("img-preview");
            const previewContainer = document.getElementById("preview-container");
            if (preview && previewContainer) {
                preview.src = "";
                preview.style.display = "none";
                previewContainer.style.display = "none";
            }

            // Dateiname zurücksetzen
            const selectedFileName = document.getElementById("selected-file-name");
            if (selectedFileName) {
                selectedFileName.textContent = "Keine ausgewählt";
            }

            // Multiselect-Zähler zurücksetzen
            document.querySelectorAll(".dropdown-multiselect").forEach(dropdown => {
                const labelSpan = dropdown.querySelector(".dropdown-label");
                if (labelSpan) labelSpan.textContent = "-- auswählen --";
            });

            // Geöffnete Dropdowns schließen
            document.querySelectorAll(".dropdown-multiselect.open").forEach(dropdown => {
                dropdown.classList.remove("open");
            });

            // Zutatenfelder zurücksetzen
            const zutatenContainer = document.getElementById("zutaten-container");
            if (zutatenContainer) {
                zutatenContainer.innerHTML = "";
                zutatenContainer.appendChild(createZutatenZeile(true));
            }
        });
    </script>