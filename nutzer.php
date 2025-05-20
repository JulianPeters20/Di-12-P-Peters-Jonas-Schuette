<!DOCTYPE html>
<html lang="de">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Broke & Hungry - Rezepte</title>
</head>
<body>

  <!-- Kopfbereich -->
  <?php include_once 'header.php'; ?>

  <!-- Hauptinhalt -->
  <main>
    <h2>Mein Profil</h2>

    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
      <img src="images/Icon Nutzer ChatGPT.webp" alt="Profilbild" style="height: 80px; width: 80px; border-radius: 50%; padding: 10px;">
      <div>
        <p><strong>Benutzername:</strong> student123</p>
        <p><strong>E-Mail:</strong> student@beispiel.de</p>
      </div>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 20px;">Eigene Rezepte</h3>

    <!-- Rezept-Galerie -->
    <div class="rezept-galerie">
      <!-- Beispiel-Karten -->
      <div class="rezept-karte">
        <img src="images/pesto.jpg" alt="Nudeln mit Pesto">
        <div class="inhalt">
          <h3><a href="rezept.php">Nudeln mit Pesto</a></h3>
          <p class="meta">Vegetarisch · 21.04.2025 · julia@example.com</p>
        </div>
      </div>

      <div class="rezept-karte">
        <img src="images/reis_mit_curry.jpg" alt="Reis mit Curry">
        <div class="inhalt">
          <h3><a href="rezept.php">Reis mit Curry</a></h3>
          <p class="meta">Vegan · 20.04.2025 · max@example.com</p>
        </div>
      </div>

      <!-- Weitere Karten -->
    </div>


    <h3 style="margin-top: 30px; margin-bottom: 20px;">Gespeicherte Rezepte</h3>

    <!-- Rezept-Galerie -->
    <div class="rezept-galerie">
      <!-- Beispiel-Karten -->
      <div class="rezept-karte">
        <img src="images/pesto.jpg" alt="Nudeln mit Pesto">
        <div class="inhalt">
          <h3><a href="rezept.php">Nudeln mit Pesto</a></h3>
          <p class="meta">Vegetarisch · 21.04.2025 · julia@example.com</p>
        </div>
      </div>

      <div class="rezept-karte">
        <img src="images/reis_mit_curry.jpg" alt="Reis mit Curry">
        <div class="inhalt">
          <h3><a href="rezept.php">Reis mit Curry</a></h3>
          <p class="meta">Vegan · 20.04.2025 · max@example.com</p>
        </div>
      </div>

      <!-- Weitere Karten -->
    </div>

    <div style="margin-top: 30px;">
      <a href="abmeldung.html" class="btn">Abmelden</a>
    </div>
  </main>

  <!-- Fußbereich -->
  <?php include_once 'footer.php'; ?>

</body>
</html>
