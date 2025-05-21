<main>
    <h2>Rezept bearbeiten</h2>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="message-box"><?= htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif; ?>

    <?php
    // Fallbacks für ältere Rezepte ohne alle Felder
    $rezept['zutaten'] = $rezept['zutaten'] ?? '';
    $rezept['zubereitung'] = $rezept['zubereitung'] ?? '';
    $rezept['utensilien'] = $rezept['utensilien'] ?? '';
    $rezept['kategorie'] = $rezept['kategorie'] ?? '';
    $rezept['portionsgroesse'] = $rezept['portionsgroesse'] ?? 1;
    $rezept['preis'] = $rezept['preis'] ?? '';
    ?>

    <form action="index.php?page=rezept-aktualisieren&id=<?= urlencode($rezept['id']) ?>" method="post" enctype="multipart/form-data">
        <p>
            <label for="titel">Titel des Rezepts:<br>
                <input type="text" id="titel" name="titel" required
                       value="<?= htmlspecialchars($_SESSION["formdata"]["titel"] ?? $rezept['titel']) ?>">
            </label>
        </p>

        <p>
            <label for="zutaten">Zutaten (eine Zutat pro Zeile):<br>
                <textarea id="zutaten" name="zutaten" rows="6" cols="50" required><?= htmlspecialchars($_SESSION["formdata"]["zutaten"] ?? $rezept['zutaten']) ?></textarea>
            </label>
        </p>

        <p>
            <label for="zubereitung">Zubereitung:<br>
                <textarea id="zubereitung" name="zubereitung" rows="8" cols="50" required><?= htmlspecialchars($_SESSION["formdata"]["zubereitung"] ?? $rezept['zubereitung']) ?></textarea>
            </label>
        </p>

        <p>
            <label for="utensilien">Küchenutensilien (optional):<br>
                <textarea id="utensilien" name="utensilien" rows="4" cols="50"><?= htmlspecialchars($_SESSION["formdata"]["utensilien"] ?? $rezept['utensilien']) ?></textarea>
            </label>
        </p>

        <p class="form-row">
            <label for="kategorie">Kategorie:<br>
                <select id="kategorie" name="kategorie" required>
                    <option value="">-- bitte auswählen --</option>
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

            <label for="portionsgroesse">Portionsgröße:<br>
                <input type="number" id="portionsgroesse" name="portionsgroesse" min="1"
                       value="<?= htmlspecialchars($_SESSION["formdata"]["portionsgroesse"] ?? $rezept['portionsgroesse']) ?>" required>
            </label>

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
        </p>

        <p>
            <label for="bild">Bild ändern (optional):<br>
                <input type="file" id="bild" name="bild" accept="image/*">
            </label>
        </p>

        <p>
            <input type="submit" value="Änderungen speichern">
            <input type="reset" value="Eingaben zurücksetzen">
        </p>
    </form>

    <?php unset($_SESSION["formdata"]); ?>
</main>