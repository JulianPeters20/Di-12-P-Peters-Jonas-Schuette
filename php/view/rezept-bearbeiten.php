<main>
    <h2>Rezept bearbeiten</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <form action="index.php?page=rezept-aktualisieren&id=<?= urlencode($rezept['id']) ?>" method="post" enctype="multipart/form-data">
        <div>
            <label for="titel">
                Titel:
                <input type="text" id="titel" name="titel" required
                       value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? $rezept['titel']) ?>">
            </label>
        </div>

        <div class="form-row">
            <label>Zutaten:<br>
                <?php
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

        <div>
            <label for="utensilien">Küchenutensilien (optional):<br>
                <textarea id="utensilien" name="utensilien" rows="4"><?= htmlspecialchars($_SESSION["formdata"]["utensilien"] ?? $rezept['utensilien']) ?></textarea>
            </label>
        </div>

        <div class="form-row">
            <label>Kategorien:<br>
                <?php foreach ($_SESSION['kategorienListe'] as $id => $label):
                    $checked = in_array($id, $_SESSION["formdata"]["kategorien"] ?? $rezept['kategorien'] ?? []) ? "checked" : "";
                    echo "<label><input type='checkbox' name='kategorien[]' value='$id' $checked> $label</label><br>";
                endforeach; ?>
            </label>
        </div>

        <div class="form-row">
            <label>Utensilien:<br>
                <?php foreach ($_SESSION['utensilienListe'] as $id => $label):
                    $checked = in_array($id, $_SESSION["formdata"]["utensilien"] ?? $rezept['utensilien'] ?? []) ? "checked" : "";
                    echo "<label><input type='checkbox' name='utensilien[]' value='$id' $checked> $label</label><br>";
                endforeach; ?>
            </label>
        </div>

        <div>
            <label for="portionsgroesse">Portionsgröße:<br>
                <input type="number" id="portionsgroesse" name="portionsgroesse" min="1"
                       value="<?= htmlspecialchars($_SESSION["formdata"]["portionsgroesse"] ?? $rezept['portionsgroesse']) ?>" required>
            </label>
        </div>

        <div>
            <label for="preis">Kosten für Zutaten:<br>
                <input type="number" id="preis" name="preis" min="1"
                       value="<?= htmlspecialchars($_SESSION["formdata"]["preis"] ?? $rezept['preis']) ?>" required>
            </label>
        </div>

        <div class="form-row">
            <label for="bild">Aktuelles Bild:<br>
                <img src="<?= htmlspecialchars($rezept['bild']) ?>" alt="Rezeptbild" style="max-width:200px; display:block; margin-bottom: 8px;">
            </label>

            <div class="form-row bild-upload">
                <label for="bild">Bild ändern:</label>
            </div>

            <label for="bild">Neues Bild hochladen (optional):<br>
                <input type="hidden" name="bestehendesBild" value="<?= htmlspecialchars($rezept['bild']) ?>">
            </label>
            <div class="custom-file-upload">
                <button type="button" id="btn-select-file" class="btn">Datei auswählen</button>
                <span id="selected-file-name">Keine ausgewählt</span>
                <input type="file" id="bild" name="bild" accept="image/*" hidden>
            </div>
        </div>

        <div id="preview-container" style="display:none; border-radius:6px; margin-top: 10px; overflow: visible;">
            <img id="img-preview" src="" alt="Bildvorschau" style="border-radius:6px; display:none; width:auto; max-width:100%; height:auto; object-fit:contain;">
        </div>

        <div class="form-row justify-center">
            <input type="submit" value="Änderungen speichern" class="btn">
            <input type="reset" value="Zurücksetzen" class="btn">
        </div>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>