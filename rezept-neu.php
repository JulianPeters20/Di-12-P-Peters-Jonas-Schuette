<!DOCTYPE html>
<html lang="de">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Broke & Hungry - Neues Rezept</title>
</head>
<body>

  <!-- Kopfbereich -->
  <?php include_once 'header.php'; ?>

  <!-- Hauptinhalt -->
  <main>
    <h2>Neues Rezept erstellen</h2>

    <form action="#" method="post" enctype="multipart/form-data">
      <p>
        <label for="titel">Titel des Rezepts:<br>
          <input type="text" id="titel" name="titel" required placeholder="z.B. Pasta mit Tomatensauce">
        </label>
      </p>

      <p>
        <label for="zutaten">Zutaten (eine Zutat pro Zeile):<br>
          <textarea id="zutaten" name="zutaten" rows="6" cols="50" required placeholder="z.B.&#10;- 200 g Spaghetti&#10;- 2 EL Olivenöl&#10;- 1 Knoblauchzehe"></textarea>
        </label>
      </p>

      <p>
        <label for="zubereitung">Zubereitung:<br>
          <textarea id="zubereitung" name="zubereitung" rows="8" cols="50" required placeholder="z.B.&#10;1. Wasser mit Salz zum Kochen bringen.&#10;2. Spaghetti darin al dente kochen.&#10;3. In der Zwischenzeit Knoblauch in Öl anbraten ..."></textarea>
        </label>
      </p>

      <p>
        <label for="utensilien">Küchenutensilien (optional):<br>
          <textarea id="utensilien" name="utensilien" rows="4" cols="50" placeholder="z.B. großer Topf, Pfanne, Holzlöffel"></textarea>
        </label>
      </p>

      <p class="form-row">
        <label for="kategorie">Kategorie:<br>
          <select id="kategorie" name="kategorie" required>
            <option value="">-- Bitte auswählen --</option>
            <option value="vegetarisch">Vegetarisch</option>
            <option value="vegan">Vegan</option>
            <option value="schnell">Schnell</option>
            <option value="guenstig">Günstig</option>
            <option value="klassisch">Klassisch</option>
          </select>
        </label>
        <label for="portionsgroesse">Portionsgröße:<br>
          <input type="number" id="portionsgroesse" name="portionsgroesse" min="1" value="1" required>
        </label>
        <label for="preis">Kosten für Zutaten:<br>
          <select id="preis" name="preis" required>
            <option value="">-- Bitte auswählen --</option>
            <option value="lt5">&lt; 5 €</option>
            <option value="5 - 10">5 bis 10 €</option>
            <option value="10 - 15">10 bis 15 €</option>
            <option value="15-20">15 bis 20 €</option>
            <option value="gt20">&gt; 20 €</option>
          </select>
          <input type="number" min="5" style="display:none; margin-top:4px;" id="portionsgroesse_input" name="portionsgroesse_input" placeholder="Ab 5">
        </label>
      </p>
      

      <p>
        <input type="submit" value="Rezept speichern">
        <input type="reset" value="Eingaben zurücksetzen">
      </p>
    </form>
  </main>

  <!-- Fußbereich -->
  <?php include_once 'footer.php'; ?>

</body>
</html>
