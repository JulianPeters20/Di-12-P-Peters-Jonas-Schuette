<!DOCTYPE html>
<html lang="de">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Broke & Hungry - Anmeldung</title>
</head>
<body>

  <!-- Kopfbereich -->
  <?php include_once 'header.php'; ?>

  <!-- Hauptinhalt -->
  <main>
    <h2>Anmeldung</h2>

    <form action="#" method="post">
      <p>
        <label for="email">E-Mail-Adresse:<br>
          <input type="email" id="email" name="email" required>
        </label>
      </p>

      <p>
        <label for="passwort">Passwort:<br>
          <input type="password" id="passwort" name="passwort" required>
        </label>
      </p>

      <p>
        <input type="submit" value="Anmelden">
      </p>
    </form>

    <p>Du hast noch kein Konto? <a href="registrierung.php">Jetzt registrieren</a>.</p>
  </main>

  <!-- Fußbereich -->
  <?php include_once 'footer.php'; ?>

</body>
</html>
