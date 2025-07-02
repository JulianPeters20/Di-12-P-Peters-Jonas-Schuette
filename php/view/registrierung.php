<main>
    <h2>Registrierung</h2>

    <?php if (!empty($error)) : ?>
        <div class="error-msg" style="color:#b30000; background:#ffe6e6; padding:0.7em; margin-bottom:1em; border-radius:6px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?page=registrierung" method="post" autocomplete="off" novalidate>
        <?= getCSRFTokenField() ?>

        <div class="form-row">
            <label for="benutzername">Benutzername:</label>
            <div style="display: flex; align-items: center; gap: 10px; flex:1;">
                <input type="text" id="benutzername" name="benutzername" maxlength="30"
                       autocomplete="username"
                       value="<?= isset($benutzername) ? htmlspecialchars($benutzername) : (isset($_POST['benutzername']) ? htmlspecialchars($_POST['benutzername']) : '') ?>">
                <span class="hinweis">(öffentlich sichtbar, optional)</span>
            </div>
        </div>

        <div class="form-row">
            <label for="email">E-Mail-Adresse:</label>
            <input type="email" id="email" name="email" required autocomplete="email"
                   value="<?= isset($email) ? htmlspecialchars($email) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '') ?>">
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
            <input type="checkbox" id="agb" name="agb" required <?= (isset($_POST['agb']) ? 'checked' : '') ?>>
            <label for="agb">
                Ich akzeptiere die <a href="index.php?page=nutzungsbedingungen" target="_blank">Nutzungsbedingungen</a>.
            </label>
        </div>
        <div class="form-row">
            <input type="checkbox" id="datenschutz" name="datenschutz" required <?= (isset($_POST['datenschutz']) ? 'checked' : '') ?>>
            <label for="datenschutz">
                Ich habe die <a href="index.php?page=datenschutz" target="_blank">Datenschutzerklärung</a> gelesen und akzeptiere sie.
            </label>
        </div>

        <div class="form-row justify-center">
            <input type="submit" value="Registrieren" class="btn">
            <input type="reset" value="Zurücksetzen">
        </div>
    </form>
    <div style="margin-top: 16px; text-align:center;">
        Du hast bereits ein Konto?
        <form action="index.php?page=anmeldung" method="get" style="display:inline;">
            <input type="hidden" name="page" value="anmeldung">
            <button type="submit" class="btn" style="margin-left:8px;">Hier anmelden</button>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const benutzernameInput = document.getElementById('benutzername');
        const errorMsg = document.getElementById('benutzername-fehler');
        const form = benutzernameInput.closest('form');

        let timeout = null;

        benutzernameInput.addEventListener('input', () => {
            clearTimeout(timeout);
            const name = benutzernameInput.value.trim();

            if (name.length === 0) {
                errorMsg.textContent = '';
                benutzernameInput.setCustomValidity('');
                return;
            }

            timeout = setTimeout(() => {
                fetch(`index.php?page=pruefeBenutzername&benutzername=` + encodeURIComponent(name))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Netzwerk-Antwort war nicht ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.exists) {
                            errorMsg.textContent = 'Dieser Benutzername ist bereits vergeben.';
                            benutzernameInput.setCustomValidity('Dieser Benutzername ist bereits vergeben.');
                        } else {
                            errorMsg.textContent = '';
                            benutzernameInput.setCustomValidity('');
                        }
                    })
                    .catch(() => {
                        errorMsg.textContent = 'Fehler bei der Prüfung.';
                        benutzernameInput.setCustomValidity('Fehler bei der Prüfung.');
                    });
            }, 500);
        });

        form.addEventListener('submit', (e) => {
            if (!benutzernameInput.checkValidity()) {
                e.preventDefault();
                benutzernameInput.reportValidity();
            }
        });
    });
</script>