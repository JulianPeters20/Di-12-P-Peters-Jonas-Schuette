<main>
    <h2>Registrierung</h2>
    <form action="index.php?page=registrierung" method="post" autocomplete="off" novalidate>

        <div class="form-row">
            <label for="benutzername">Benutzername:</label>
            <input type="text" id="benutzername" name="benutzername" required autocomplete="username">
            <div id="benutzername-fehler" style="color: red; margin-bottom: 6px; font-size: 0.9rem;"></div>
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