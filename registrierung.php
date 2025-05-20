<!DOCTYPE html>
<html lang="de">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Broke & Hungry - Registrierung</title>
</head>
<body>

  <!-- Kopfbereich -->
  <?php include_once 'header.php'; ?>

  <!-- Hauptinhalt -->
  <main>
    <h2>Registrierung</h2>

    <form action="index.php" method="post">
      <p>
        <label for="benutzername">Benutzername:<br>
          <input type="text" id="benutzername" name="benutzername" required placeholder="z.B. student123">
        </label>
      </p>

      <p>
        <label for="email">E-Mail-Adresse:<br>
          <input type="email" id="email" name="email" required placeholder="max@musterman.de">
        </label>
      </p>

      <p>
        <label for="passwort">Passwort:<br>
          <input type="password" id="passwort" name="passwort" required minlength="8" placeholder="mindestens 8 Zeichen">
        </label>
      </p>

      <p>
        <label for="passwort-wdh">Passwort wiederholen:<br>
          <input type="password" id="passwort-wdh" name="passwort-wdh" required minlength="8" placeholder="Passwort wiederholen">
        </label>
      </p>

      <p>
        <input type="submit" value="Registrieren">
        <input type="reset" value="Zurücksetzen">
      </p>
    </form>

    <p>Du hast bereits ein Konto? <a href="anmeldung.php">Hier anmelden</a>.</p>
  </main>

  <!-- Fußbereich -->
  <?php include_once 'footer.php'; ?>

</body>
</html>
