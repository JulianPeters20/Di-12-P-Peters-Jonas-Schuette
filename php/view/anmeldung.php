<main>
    <h2>Anmeldung</h2>
    <form action="index.php?page=anmeldung" method="post" autocomplete="off">

        <div class="form-row">
            <label for="email">E-Mail-Adresse:</label>
            <input type="email" id="email" name="email" required autocomplete="username">
        </div>

        <div class="form-row">
            <label for="passwort">Passwort:</label>
            <input type="password" id="passwort" name="passwort" required autocomplete="current-password">
        </div>

        <div class="form-row">
            <input type="submit" value="Anmelden">
        </div>
    </form>
    <p>Noch kein Konto? <a href="index.php?page=registrierung">Jetzt registrieren</a>.</p>
</main>