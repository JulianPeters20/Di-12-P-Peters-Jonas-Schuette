<main>
    <h2>Anmeldung</h2>
    <form action="index.php?page=anmeldung" method="post">
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
    <p>Noch kein Konto? <a href="index.php?page=registrierung">Jetzt registrieren</a>.</p>
</main>