<main>
    <h2>Neues Rezept erstellen</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <form action="index.php?page=rezept-neu" method="post" enctype="multipart/form-data">
        <div class="form-row">
            <label for="titel">Titel:<br>
                <input type="text" id="titel" name="titel" required
                       value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? "") ?>">
            </label>
        </div>

        <div class="form-row">
            <label for="zubereitung">Zubereitung:<br>
                <textarea id="zubereitung" name="zubereitung" rows="6" cols="50" required><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? "") ?></textarea>
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
            <label>Kategorien:<br>
                <?php foreach ($_SESSION['kategorienListe'] as $id => $label):
                    $checked = in_array($id, $_SESSION["formdata"]["kategorien"] ?? []) ? "checked" : "";
                    echo "<label><input type='checkbox' name='kategorien[]' value='$id' $checked> $label</label><br>";
                endforeach; ?>
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

        <div class="form-row">
            <label for="bild">Bild (Pflichtfeld):<br>
                <input type="file" id="bild" name="bild" accept="image/*" required>
            </label>
        </div>

        <div class="form-row">
            <input type="submit" value="Rezept speichern">
            <input type="reset" value="Eingaben zurücksetzen">
        </div>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>