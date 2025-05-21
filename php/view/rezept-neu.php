<main>
    <h2>Neues Rezept erstellen</h2>
    <form action="index.php?page=rezept-neu" method="post" enctype="multipart/form-data">
        <p>
            <label for="titel">Titel des Rezepts:<br>
                <input type="text" id="titel" name="titel" required>
            </label>
        </p>
        <p>
            <label for="kategorie">Kategorie:<br>
                <select id="kategorie" name="kategorie" required>
                    <option value="">-- bitte auswählen --</option>
                    <option value="vegetarisch">Vegetarisch</option>
                    <option value="vegan">Vegan</option>
                    <option value="schnell">Schnell</option>
                    <option value="guenstig">Günstig</option>
                </select>
            </label>
        </p>
        <p>
            <input type="file" name="bild" accept="image/*">
        </p>
        <p>
            <input type="submit" value="Rezept speichern">
            <input type="reset" value="Eingaben zurücksetzen">
        </p>
    </form>
</main>