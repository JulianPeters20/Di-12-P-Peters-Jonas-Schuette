<main>
    <h2>Neues Rezept erstellen</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <form action="index.php?page=rezept-neu" method="post" enctype="multipart/form-data">
        <div class="form-row">
            <label for="titel">
                <span>Titel:</span>
                <input type="text" id="titel" name="titel" required
                       value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? "") ?>">
            </label>
        </div>

        <div class="form-row">
            <label>Zutaten:<br>
                <?php
                $zutatennamen = $_SESSION["formdata"]["zutatennamen"] ?? array_fill(0, 5, "");
                $mengen = $_SESSION["formdata"]["mengen"] ?? array_fill(0, 5, "");
                $einheiten = $_SESSION["formdata"]["einheiten"] ?? array_fill(0, 5, "");
                $einheitenListe = ["g", "kg", "ml", "l", "Msp", "TL", "EL", "Stück"];
                for ($i = 0; $i < 5; $i++):
                    ?>
                    <div class="zutaten-paar" style="display: flex; gap: 8px; margin-bottom: 6px;">
                        <input type="text" name="zutatennamen[]" placeholder="Zutat"
                               value="<?= htmlspecialchars($zutatennamen[$i]) ?>">

                        <input type="text" name="mengen[]" placeholder="Menge"
                               value="<?= htmlspecialchars($mengen[$i]) ?>">

                        <select name="einheiten[]">
                            <option value="">Einheit wählen</option>
                            <?php foreach ($einheitenListe as $einheit):
                                $selected = ($einheiten[$i] === $einheit) ? "selected" : "";
                                echo "<option value=\"$einheit\" $selected>$einheit</option>";
                            endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </label>
        </div>

        <div class="form-row">
            <label for="zubereitung">Zubereitung:<br>
                <textarea id="zubereitung" name="zubereitung" rows="6" cols="50" required><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? "") ?></textarea>
            </label>
        </div>

        <div class="form-row">
            <label>Utensilien:<br>
                <?php foreach ($_SESSION['utensilienListe'] as $id => $label):
                    $checked = in_array($id, $_SESSION["formdata"]["utensilien"] ?? []) ? "checked" : "";
                    echo "<label><input type='checkbox' name='utensilien[]' value='$id' $checked> $label</label><br>";
                endforeach; ?>
            </label>
        </div>


        <div>
            <label for="kategorie">Kategorie:<br>
                <?php foreach ($_SESSION['kategorienListe'] as $id => $label):
                    $checked = in_array($id, $_SESSION["formdata"]["kategorien"] ?? []) ? "checked" : "";
                    echo "<label><input type='checkbox' name='kategorien[]' value='$id' $checked> $label</label><br>";
                endforeach; ?>
            </label>
        </div>

        <div class="form-row">
            <label for="portionsgroesse">Portionsgröße:<br>
                <input type="number" id="portionsgroesse" name="portionsgroesse" min="1"
                       value="<?= htmlspecialchars($_SESSION["formdata"]["portionsgroesse"] ?? "1") ?>">
            </label>

            <label for="preisklasse">Preisklasse:<br>
                <select id="preisklasse" name="preisklasse">
                    <option value="">-- auswählen --</option>
                    <option value="1">unter 5 €</option>
                    <option value="2">5–10 €</option>
                    <option value="3">10–15 €</option>
                    <option value="4">über 15 €</option>
                </select>
            </label>
        </div>

        <div class="form-row bild-upload">
            <label for="bild">
                <input type="file" id="bild" name="bild" accept="image/*" required>
            </label>
        </div>
        <div class="form-row datei-auswahl">
            <div class="custom-file-upload">
                <button type="button" id="btn-select-file" class="btn">Datei auswählen</button>
                <span id="selected-file-name">Keine ausgewählt</span>
                <input type="file" id="bild" name="bild" accept="image/*" hidden>
            </div>
        </div>

        <div id="preview-container" style="display:none; border-radius:6px; margin-top: 10px; overflow: visible;">
            <img id="img-preview" src="" alt="Bildvorschau" style="border-radius:6px; display:none; width:auto; max-width:100%; height:auto; object-fit:contain;">
        </div>

        <div class="form-row">
            <input type="submit" value="Rezept speichern" class="btn">
            <input type="reset" value="Eingaben zurücksetzen" class="btn">
        </div>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>