<main>
    <h2>Neues Rezept erstellen</h2>

    <?php if (isset($_SESSION["flash"])): ?>
        <div class="flash-message <?= $_SESSION['flash']['type'] ?>"><?= htmlspecialchars($_SESSION["flash"]["message"]) ?></div>
        <?php unset($_SESSION["flash"]); ?>
    <?php endif; ?>

    <div class="form-container">

        <form action="index.php?page=rezept-neu" method="post" enctype="multipart/form-data">
            <?= getCSRFTokenField() ?>
            <div class="form-group">
                <label for="titel">Titel:</label>
                <input
                        type="text"
                        id="titel"
                        name="titel"
                        maxlength="50"
                        required
                        value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? "") ?>"
                >
            </div>

            <div class="form-group">
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
                    for ($i = 0; $i < $count; $i++): ?>
                        <div class="zutaten-paar">
                            <input type="text" name="zutatennamen[]" placeholder="Zutat" value="<?= htmlspecialchars($zutatennamen[$i]) ?>">
                            <input type="text" name="mengen[]" placeholder="Menge" value="<?= htmlspecialchars($mengen[$i]) ?>">
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

                <div class="zutaten-buttons">
                    <button type="button" class="btn" onclick="neueZutat()">+ Neue Zutat</button>
                    <button type="button" class="btn" id="btn-remove-zutat" onclick="entferneZutat()" disabled>- Zutat entfernen</button>
                </div>
            </div>

            <div class="form-group">
                <label for="zubereitung">Zubereitung:</label>
                <textarea
                        id="zubereitung"
                        name="zubereitung"
                        rows="6"
                        maxlength="2000"
                        required
                        placeholder="Beschreibe hier die Zubereitung deines Rezepts..."
                ><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? "") ?></textarea>
            </div>

            <div class="form-group">
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

            <div class="form-group">
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

            <div class="form-group">
                <label for="preisklasse">Preisklasse:</label>
                <div class="dropdown-multiselect single-select">
                    <div class="dropdown-header" onclick="toggleDropdown(this)">
                        <span class="dropdown-label">-- auswählen --</span>
                        <span class="dropdown-arrow">▾</span>
                    </div>
                    <select name="preisklasse" id="preisklasse-select" class="hidden-select">
                        <?php foreach ($_SESSION['preisklasseListe'] as $id => $pl):
                            $selected = ((string)($_SESSION["formdata"]["preisklasse"] ?? '') === (string)$id) ? "selected" : "";
                            ?>
                            <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pl->Preisspanne ?? $pl) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="preisklasse" id="preisklasse-hidden" value="<?= htmlspecialchars($_SESSION["formdata"]["preisklasse"] ?? '') ?>">
                    <div class="dropdown-list">
                        <?php foreach ($_SESSION['preisklasseListe'] as $id => $pl): ?>
                            <div data-value="<?= htmlspecialchars($id) ?>" class="dropdown-option">
                                <?= htmlspecialchars($pl->Preisspanne ?? $pl) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="portionsgroesse">Portionsgröße:</label>
                <div class="dropdown-multiselect single-select">
                    <div class="dropdown-header" onclick="toggleDropdown(this)">
                        <span class="dropdown-label">-- auswählen --</span>
                        <span class="dropdown-arrow">▾</span>
                    </div>
                    <select name="portionsgroesse" id="portionsgroesse-select" class="hidden-select">
                        <?php foreach ($_SESSION['portionsgroesseListe'] as $id => $pg):
                            $selected = ((string)($_SESSION["formdata"]["portionsgroesse"] ?? '') === (string)$id) ? "selected" : "";
                            ?>
                            <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pg->Angabe ?? $pg) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="portionsgroesse" id="portionsgroesse-hidden" value="<?= htmlspecialchars($_SESSION["formdata"]["portionsgroesse"] ?? '') ?>">
                    <div class="dropdown-list">
                        <?php foreach ($_SESSION['portionsgroesseListe'] as $id => $pg): ?>
                            <div data-value="<?= htmlspecialchars($id) ?>" class="dropdown-option">
                                <?= htmlspecialchars($pg->Angabe ?? $pg) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="bild">Bild:</label>
                <div class="custom-file-upload">
                    <button type="button" id="btn-select-file" class="btn">Datei auswählen</button>
                    <span id="selected-file-name">Keine Datei ausgewählt</span>
                    <input type="file" id="bild" name="bild" accept="image/*" hidden>
                </div>
            </div>

            <div id="preview-container" class="image-preview-container">
                <img id="img-preview" src="" alt="Bildvorschau" class="image-preview">
            </div>

            <div class="form-group">
                <input type="submit" value="Rezept speichern" class="btn">
                <input type="reset" value="Eingaben zurücksetzen" class="btn">
            </div>
        </form>

    </div>

    <?php unset($_SESSION["formdata"]); ?>

</main>

    <script>
        const maxZutaten = 20;

        function createZutatenZeile(disabled = false) {
            const einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];

            const container = document.getElementById("zutaten-container");
            if (container.children.length >= maxZutaten) {
                alert("Es können maximal " + maxZutaten + " Zutaten hinzugefügt werden.");
                return null;
            }

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

            div.appendChild(zutatInput);
            div.appendChild(mengeInput);
            div.appendChild(einheitSelect);

            return div;
        }

        function updateRemoveButtonState() {
            const container = document.getElementById("zutaten-container");
            const removeBtn = document.getElementById("btn-remove-zutat");
            if (!container || !removeBtn) return;

            removeBtn.disabled = container.children.length < 2;
        }

        function neueZutat() {
            const container = document.getElementById("zutaten-container");
            const neueZeile = createZutatenZeile(false);
            if (neueZeile) {
                container.appendChild(neueZeile);
                updateRemoveButtonState();
            }
        }

        function entferneZutat() {
            const container = document.getElementById("zutaten-container");
            if (container.children.length > 1) {
                container.removeChild(container.lastElementChild);
                updateRemoveButtonState();
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            updateRemoveButtonState();
        });
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
            // Single-Select Dropdowns (Preisklasse, Portionsgröße)
            document.querySelectorAll(".single-select").forEach(dropdown => {
                const label = dropdown.querySelector(".dropdown-label");
                const hiddenInput = dropdown.querySelector("input[type=hidden]");
                const options = dropdown.querySelectorAll(".dropdown-list > div.dropdown-option");

                const initialValue = hiddenInput.value;
                if (initialValue) {
                    const selectedOption = Array.from(options).find(o => o.getAttribute("data-value") === initialValue);
                    if (selectedOption) {
                        label.textContent = selectedOption.textContent;
                        selectedOption.classList.add("selected");
                    }
                }

                options.forEach(option => {
                    option.addEventListener("click", () => {
                        const value = option.getAttribute("data-value");
                        const text = option.textContent;

                        hiddenInput.value = value;
                        label.textContent = text;

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
                selectedFileName.textContent = "Keine Datei ausgewählt";
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

            // Zutatenfelder zurücksetzen: eine Zeile, Entfernen-Button deaktiviert
            const zutatenContainer = document.getElementById("zutaten-container");
            if (zutatenContainer) {
                zutatenContainer.innerHTML = "";
                zutatenContainer.appendChild(createZutatenZeile(true));
                updateRemoveButtonState();
            }
        });
    </script>