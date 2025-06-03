<main>
    <h2>Rezept bearbeiten</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <?php
    // Fallbacks für ältere Rezepte ohne alle Felder
    $rezept['zutaten'] ??= '';
    $rezept['zubereitung'] ??= '';
    $rezept['utensilien'] ??= '';
    $rezept['kategorie'] ??= '';
    $rezept['portionsgroesse'] ??= 1;
    $rezept['preis'] ??= '';
    $rezept['titel'] ??= '';
    ?>

    <form action="index.php?page=rezept-aktualisieren&id=<?= urlencode($rezept['id']) ?>" method="post" enctype="multipart/form-data">
        <div>
            <label for="titel">
                Titel des Rezepts:
                <input type="text" id="titel" name="titel" required
                       value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? $rezept['titel']) ?>">
            </label>
        </div>

        <div>
            <label for="zutaten">Zutaten (eine Zutat pro Zeile):<br>
                <textarea id="zutaten" name="zutaten" rows="6" required><?= htmlspecialchars($_SESSION["formdata"]["zutaten"] ?? $rezept['zutaten']) ?></textarea>
            </label>
        </div>

        <div>
            <label for="zubereitung">Zubereitung:<br>
                <textarea id="zubereitung" name="zubereitung" rows="8" required><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? $rezept['zubereitung']) ?></textarea>
            </label>
        </div>

        <div>
            <label for="utensilien">Küchenutensilien (optional):<br>
                <textarea id="utensilien" name="utensilien" rows="4"><?= htmlspecialchars($_SESSION["formdata"]["utensilien"] ?? $rezept['utensilien']) ?></textarea>
            </label>
        </div>

        <div>
            <label for="kategorie">Kategorie:<br>
                <select id="kategorie" name="kategorie" required>
                    <option value="">-- Bitte auswählen --</option>
                    <?php
                    $kategorien = ["vegetarisch", "vegan", "schnell", "guenstig", "klassisch"];
                    $auswahl = $_SESSION["formdata"]["kategorie"] ?? $rezept['kategorie'];
                    foreach ($kategorien as $kat) {
                        $selected = ($auswahl === $kat) ? "selected" : "";
                        echo "<option value=\"$kat\" $selected>" . ucfirst($kat) . "</option>";
                    }
                    ?>
                </select>
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
                <select id="preis" name="preis" required>
                    <option value="">-- Bitte auswählen --</option>
                    <?php
                    $preisoptionen = [
                        "lt5" => "&lt; 5 €",
                        "5 - 10" => "5 bis 10 €",
                        "10 - 15" => "10 bis 15 €",
                        "15-20" => "15 bis 20 €",
                        "gt20" => "&gt; 20 €"
                    ];
                    $preiswert = $_SESSION["formdata"]["preis"] ?? $rezept['preis'];
                    foreach ($preisoptionen as $value => $label) {
                        $selected = ($preiswert === $value) ? "selected" : "";
                        echo "<option value=\"$value\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </label>
        </div>

        <div class="form-row bild-upload">
            <label for="bild">Bild ändern:</label>
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

        <div class="form-row justify-center">
            <input type="submit" value="Änderungen speichern" class="btn">
            <input type="reset" value="Eingaben zurücksetzen" class="btn">
        </div>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>