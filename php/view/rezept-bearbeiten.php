<?php
/** @var array $rezept */
?>
<main>
    <h2>Rezept bearbeiten</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <div class="form-container">
        <form action="index.php?page=rezept-aktualisieren&id=<?= urlencode($rezept['id']) ?>" method="post" enctype="multipart/form-data">
            <!-- TITEL -->
            <div class="form-row">
                <label for="titel">Titel:</label>
            </div>
            <div class="form-row">
                <input type="text" id="titel" name="titel" required value="<?= htmlspecialchars($_SESSION['formdata']['titel'] ?? $rezept['titel']) ?>">
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
                    foreach ($zutaten as $index => $z):
                        $disableRemove = $index === 0;
                        ?>
                        <div class="zutaten-paar" style="display: flex; gap: 8px; margin-bottom: 6px; align-items:center;">
                            <input type="text" name="zutatennamen[]" placeholder="Zutat" value="<?= htmlspecialchars($z['zutat']) ?>">
                            <input type="text" name="mengen[]" placeholder="Menge" value="<?= htmlspecialchars($z['menge']) ?>">
                            <select name="einheiten[]">
                                <option value="">Einheit</option>
                                <?php foreach ($einheitenListe as $e):
                                    $selected = $z['einheit'] === $e ? "selected" : "";
                                    echo "<option value='$e' $selected>$e</option>";
                                endforeach; ?>
                            </select>
                            <button type="button" class="remove-zutat" <?= $disableRemove ? 'disabled style="opacity:0.4;cursor:default;"' : '' ?> onclick="if(!this.disabled)this.parentElement.remove();">&#x2715;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn" style="margin-top: 8px;" onclick="neueZutat()">+ Neue Zutat</button>
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
                        <span class="dropdown-label">-- auswählen --</span>
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
                        <span class="dropdown-label">-- auswählen --</span>
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
                        <span class="dropdown-label">-- auswählen --</span>
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
                        <span class="dropdown-label">-- auswählen --</span>
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
            // Initialisiere Label mit bereits ausgewähltem Wert
            const selected = dropdown.querySelector("li.selected");
            if (selected) {
                label.textContent = selected.textContent;
            }
        });
    });
</script>