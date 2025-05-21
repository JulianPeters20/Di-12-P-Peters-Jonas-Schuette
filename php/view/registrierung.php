<main>
    <h2>Registrierung</h2>
    <form action="index.php?page=register" method="post">
        <p>
            <label for="benutzername">Benutzername:<br>
                <input type="text" id="benutzername" name="benutzername" required>
            </label>
        </p>
        <p>
            <label for="email">E-Mail-Adresse:<br>
                <input type="email" id="email" name="email" required>
            </label>
        </p>
        <p>
            <label for="passwort">Passwort:<br>
                <input type="password" id="passwort" name="passwort" required minlength="8">
            </label>
        </p>
        <p>
            <label for="passwort-wdh">Passwort wiederholen:<br>
                <input type="password" id="passwort-wdh" name="passwort-wdh" required minlength="8">
            </label>
        </p>
        <p>
            <input type="submit" value="Registrieren">
            <input type="reset" value="Zurücksetzen">
        </p>
    </form>
    <p>Du hast bereits ein Konto? <a href="index.php?page=login">Hier anmelden</a>.</p>
</main>