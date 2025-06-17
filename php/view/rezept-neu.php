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
                <?php foreach ($_SESSION['utensilienListe'] as $id => $utensil):
                    $checked = in_array($id, $_SESSION["formdata"]["utensilien"] ?? []) ? "checked" : "";
                    ?>
                    <label>
                        <input type="checkbox" name="utensilien[]" value="<?= htmlspecialchars($id) ?>" <?= $checked ?>>
                        <?= htmlspecialchars($utensil->Name ?? $utensil) ?>
                    </label><br>
                <?php endforeach; ?>
            </label>
        </div>

        <div class="form-row">
            <label>Kategorien:<br>
                <?php foreach ($_SESSION['kategorienListe'] as $id => $kategorie):
                    $checked = in_array($id, $_SESSION["formdata"]["kategorien"] ?? []) ? "checked" : "";
                    ?>
                    <label>
                        <input type="checkbox" name="kategorien[]" value="<?= htmlspecialchars($id) ?>" <?= $checked ?>>
                        <?= htmlspecialchars($kategorie->Bezeichnung ?? $kategorie) ?>
                    </label><br>
                <?php endforeach; ?>
            </label>
        </div>

        <div class="form-row">
            <label for="portionsgroesse">Portionsgröße:<br>
                <select id="portionsgroesse" name="portionsgroesse" required>
                    <?php foreach ($_SESSION['portionsgroesseListe'] as $id => $pg):
                        $selected = ((string)($_SESSION["formdata"]["portionsgroesse"] ?? '') === (string)$id) ? "selected" : "";
                        ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pg->Angabe ?? $pg) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="preisklasse">Preisklasse:<br>
                <select id="preisklasse" name="preisklasse" required>
                    <?php foreach ($_SESSION['preisklasseListe'] as $id => $pl):
                        $selected = ((string)($_SESSION["formdata"]["preisklasse"] ?? '') === (string)$id) ? "selected" : "";
                        ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= htmlspecialchars($pl->Preisspanne ?? $pl) ?></option>
                    <?php endforeach; ?>
                </select>
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
            <img id="img-preview" src="" alt="Bildvorschau" style="border-radius:6px; display:none; width:auto; max-width:300px; height:auto; object-fit:contain;">
        </div>

        <div class="form-row">
            <input type="submit" value="Rezept speichern" class="btn">
            <input type="reset" value="Eingaben zurücksetzen" class="btn">
        </div>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>