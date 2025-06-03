<main>
    <h2>Registrierung</h2>
    <form action="index.php?page=registrierung" method="post" autocomplete="off">

        <div class="form-row">
            <label for="benutzername">Benutzername:</label>
            <input type="text" id="benutzername" name="benutzername" required autocomplete="username">
        </div>

        <div class="form-row">
            <label for="email">E-Mail-Adresse:</label>
            <input type="email" id="email" name="email" required autocomplete="email">
        </div>

        <div class="form-row">
            <label for="passwort">Passwort:</label>
            <input type="password" id="passwort" name="passwort" required minlength="8" autocomplete="new-password">
        </div>

        <div class="form-row">
            <label for="passwort-wdh">Passwort wiederholen:</label>
            <input type="password" id="passwort-wdh" name="passwort-wdh" required minlength="8" autocomplete="new-password">
        </div>

        <div class="form-row">
            <input type="submit" value="Registrieren">
            <input type="reset" value="Zurücksetzen">
        </div>
    </form>
    <div>Du hast bereits ein Konto? <a href="index.php?page=anmeldung">Hier anmelden</a>.</div>
</main>