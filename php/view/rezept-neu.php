<main>
    <h2>Neues Rezept erstellen</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <form action="index.php?page=rezept-neu" method="post" enctype="multipart/form-data">

        <div class="form-row">
            <label for="titel">Titel des Rezepts:</label>
            <input type="text" id="titel" name="titel" required
                   value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? "") ?>">
        </div>

        <div class="form-row">
            <label for="zutaten">Zutaten (eine Zutat pro Zeile):</label>
            <textarea id="zutaten" name="zutaten" rows="6" cols="50" required><?= htmlspecialchars($_SESSION["formdata"]["zutaten"] ?? "") ?></textarea>
        </div>

        <div class="form-row">
            <label for="zubereitung">Zubereitung:</label>
            <textarea id="zubereitung" name="zubereitung" rows="8" cols="50" required><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? "") ?></textarea>
        </div>

        <div class="form-row">
            <label for="utensilien">Küchenutensilien (optional):</label>
            <textarea id="utensilien" name="utensilien" rows="4" cols="50"><?= htmlspecialchars($_SESSION["formdata"]["utensilien"] ?? "") ?></textarea>
        </div>

        <fieldset class="form-row">
            <legend>Kategorien:</legend>
            <?php
            $kategorien = ["vegetarisch", "vegan", "schnell", "guenstig", "klassisch"];
            $kategorieArr = $_SESSION["formdata"]["kategorie"] ?? [];
            foreach ($kategorien as $kat):
                $checked = is_array($kategorieArr) && in_array($kat, $kategorieArr) ? "checked" : "";
                ?>
                <label>
                    <input type="checkbox" name="kategorie[]" value="<?= htmlspecialchars($kat) ?>" <?= $checked ?>>
                    <?= ucfirst($kat) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <div class="form-row">
            <label for="portionsgroesse">Portionsgröße:</label>
            <input type="number" id="portionsgroesse" name="portionsgroesse" min="1"
                   value="<?= htmlspecialchars($_SESSION["formdata"]["portionsgroesse"] ?? "1") ?>" required>
        </div>

        <div class="form-row">
            <label for="preis">Kosten für Zutaten:</label>
            <select id="preis" name="preis" required>
                <option value="">-- Bitte auswählen --</option>
                <?php
                $preise = [
                    "lt5" => "&lt; 5 €",
                    "5 - 10" => "5 bis 10 €",
                    "10 - 15" => "10 bis 15 €",
                    "15-20" => "15 bis 20 €",
                    "gt20" => "&gt; 20 €"
                ];
                $auswahl = $_SESSION["formdata"]["preis"] ?? "";
                foreach ($preise as $value => $label) {
                    $selected = ($auswahl === $value) ? "selected" : "";
                    echo "<option value=\"$value\" $selected>$label</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-row">
            <label for="bild">Bild hochladen (optional):</label>
            <input type="file" id="bild" name="bild" accept="image/*">
        </div>

        <div class="form-row">
            <input type="submit" value="Rezept speichern">
            <input type="reset" value="Eingaben zurücksetzen">
        </div>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>