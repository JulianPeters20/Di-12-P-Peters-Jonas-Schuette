<main>
    <h2>Rezept bearbeiten</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <form action="index.php?page=rezept-aktualisieren&id=<?= urlencode($rezept['id']) ?>" method="post" enctype="multipart/form-data">
        <div class="form-row">
            <label for="titel">
                <span>Titel:</span>
                <input type="text" id="titel" name="titel" required
                       value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? $rezept['titel']) ?>">
            </label>
        </div>

        <div class="form-row">
            <label>Zutaten:<br>
                <?php
                // Zutaten-Array vorbereiten (aus Session oder aus Daten)
                $zutaten = $_SESSION["formdata"]["zutaten"] ?? $rezept['zutaten'] ?? [];
                $einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];
                $anzahl = max(count($zutaten), 5);
                for ($i = 0; $i < $anzahl; $i++):
                    $z = $zutaten[$i]['zutat'] ?? '';
                    $m = $zutaten[$i]['menge'] ?? '';
                    $e = $zutaten[$i]['einheit'] ?? '';
                    ?>
                    <div class="zutaten-paar" style="display: flex; gap: 8px; margin-bottom: 6px;">
                        <input type="text" name="zutatennamen[]" placeholder="Zutat"
                               value="<?= htmlspecialchars($z) ?>">

                        <input type="text" name="mengen[]" placeholder="Menge"
                               value="<?= htmlspecialchars($m) ?>">

                        <select name="einheiten[]">
                            <option value="">Einheit wählen</option>
                            <?php foreach ($einheitenListe as $einheit):
                                $selected = ($e === $einheit) ? "selected" : "";
                                echo "<option value=\"$einheit\" $selected>$einheit</option>";
                            endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </label>
        </div>

        <div class="form-row">
            <label for="zubereitung">Zubereitung:<br>
                <textarea id="zubereitung" name="zubereitung" rows="6" cols="50" required><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? $rezept['zubereitung']) ?></textarea>
            </label>
        </div>

        <div class="form-row">
            <label>Utensilien:<br>
                <?php
                $formUtensilien = $_SESSION["formdata"]["utensilien"] ?? $rezept['utensilien'] ?? [];
                foreach ($_SESSION['utensilienListe'] as $id => $utensil):
                    // $utensil ist Objekt oder string? Wir nehmen bei Objekt den Namen
                    $name = is_object($utensil) ? ($utensil->Name ?? '') : (is_array($utensil) ? ($utensil['Name'] ?? '') : $utensil);
                    // Formularwerte sind IDs - wenn $formUtensilien ist Array mit IDs oder (beim Rezept) Array von Utensil-Arrays mit 'UtensilID'
                    $checked = '';
                    if (is_array($formUtensilien) && count($formUtensilien) > 0) {
                        // Normalfall: IDs in Array
                        if (in_array($id, $formUtensilien, true)) {
                            $checked = "checked";
                        } else {
                            // Falls $formUtensilien Array von Arrays (z.B. aus Rezept) mit UtensilID-Key
                            foreach ($formUtensilien as $u) {
                                if (is_array($u) && isset($u['UtensilID']) && (int)$u['UtensilID'] === (int)$id) {
                                    $checked = "checked";
                                    break;
                                }
                            }
                        }
                    }
                    ?>
                    <label>
                        <input type="checkbox" name="utensilien[]" value="<?= htmlspecialchars($id) ?>" <?= $checked ?>>
                        <?= htmlspecialchars($name) ?>
                    </label><br>
                <?php endforeach; ?>
            </label>
        </div>

        <div class="form-row">
            <label>Kategorien:<br>
                <?php
                // IDs der Kategorien, die vorausgewählt sein sollen
                $kategorieIdsAusgewählt = [];

                if (isset($_SESSION["formdata"]["kategorien"]) && is_array($_SESSION["formdata"]["kategorien"])) {
                    $kategorieIdsAusgewählt = $_SESSION["formdata"]["kategorien"];
                } elseif (isset($rezept['kategorienMitIds']) && is_array($rezept['kategorienMitIds'])) {
                    // $rezept['kategorienMitIds'] enthält Array mit ['KategorieID' => int, 'Bezeichnung' => string]
                    $kategorieIdsAusgewählt = array_map(fn($k) => (int)$k['KategorieID'], $rezept['kategorienMitIds']);
                }

                foreach ($_SESSION['kategorienListe'] as $id => $kategorie):
                    $name = is_object($kategorie) ? ($kategorie->Bezeichnung ?? '') : (is_array($kategorie) ? ($kategorie['Bezeichnung'] ?? '') : $kategorie);
                    $checked = in_array($id, $kategorieIdsAusgewählt, true) ? "checked" : "";
                    ?>
                    <label>
                        <input type="checkbox" name="kategorien[]" value="<?= htmlspecialchars($id) ?>" <?= $checked ?>>
                        <?= htmlspecialchars($name) ?>
                    </label><br>
                <?php endforeach; ?>
            </label>
        </div>

        <div class="form-row">
            <label for="portionsgroesse">Portionsgröße:<br>
                <select id="portionsgroesse" name="portionsgroesse" required>
                    <?php
                    $formPortionsgroesse = $_SESSION["formdata"]["portionsgroesse"] ?? $rezept['portionsgroesseId'] ?? null;
                    foreach ($_SESSION['portionsgroesseListe'] as $id => $pg):
                        $selected = ((string)$formPortionsgroesse === (string)$id) ? "selected" : "";
                        $label = is_object($pg) ? ($pg->Angabe ?? '') : (is_array($pg) ? ($pg['Angabe'] ?? '') : $pg);
                        ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="preisklasse">Preisklasse:<br>
                <select id="preisklasse" name="preisklasse" required>
                    <?php
                    $formPreisklasse = $_SESSION["formdata"]["preisklasse"] ?? $rezept['preisklasseId'] ?? null;
                    foreach ($_SESSION['preisklasseListe'] as $id => $pl):
                        $selected = ((string)$formPreisklasse === (string)$id) ? "selected" : "";
                        $label = is_object($pl) ? ($pl->Preisspanne ?? '') : (is_array($pl) ? ($pl['Preisspanne'] ?? '') : $pl);
                        ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="bild-upload">
            <label>Aktuelles Bild:<br>
                <?php if (!empty($rezept['bild']) && file_exists(realpath(__DIR__ . '/images/') . $rezept['bild'])): ?>
                    <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="Rezeptbild" style="border-radius:6px; width:auto; max-width: 300px; display:block; margin-bottom: 30px; object-fit:contain; height:auto;">
                <?php else: ?>
                    <p>Kein Bild vorhanden</p>
                <?php endif; ?>
            </label>

            <label for="bild">Neues Bild hochladen (optional): <br>
                <div class="form-row datei-auswahl" >
                    <div class="custom-file-upload">
                        <button type="button" id="btn-select-file" class="btn">Datei auswählen</button>
                        <span id="selected-file-name">Keine ausgewählt</span>
                        <input type="file" id="bild" name="bild" accept="image/*" hidden>
                    </div>
                </div>

                <div id="preview-container" style="display:none; border-radius:6px; margin-top: 10px; overflow: visible;">
                    <img id="img-preview" src="" alt="Bildvorschau" style="border-radius:6px; display:none; width:auto; max-width: 300px; height:auto; object-fit:contain;">
                </div>
            </label>
        </div>

        <div class="form-row">
            <input type="submit" value="Änderungen speichern" class="btn">
            <input type="reset" value="Zurücksetzen" class="btn">
        </div>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>