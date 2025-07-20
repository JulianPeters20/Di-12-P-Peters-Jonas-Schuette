<main>
    <h2>Registrierung</h2>

    <div style="display: flex; gap: 30px; align-items: flex-start;">
        <!-- Hauptformular -->
        <div style="flex: 1; max-width: 500px;">
            <form action="index.php?page=registrierung" method="post" autocomplete="off" novalidate>
        <?= getCSRFTokenField() ?>

        <div class="form-row">
            <label for="benutzername">Benutzername:</label>
            <div style="display:flex; align-items:center; gap:10px; flex:1;">
                <input type="text" id="benutzername" name="benutzername" maxlength="30"
                       autocomplete="username"
                       value="<?= isset($benutzername) ? htmlspecialchars($benutzername) : (isset($_POST['benutzername']) ? htmlspecialchars($_POST['benutzername']) : '') ?>">
                <span class="hinweis">(öffentlich sichtbar, optional)</span>
            </div>
        </div>
        <div class="form-row-benutzername-fehler">
            <span id="benutzername-fehler" class="benutzername-fehler"></span>
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
        </div>

        <!-- Passwort-Feedback-Panel -->
        <div id="password-feedback" style="flex: 0 0 320px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; font-size: 14px; height: fit-content; position: sticky; top: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #495057; font-size: 16px; font-weight: bold;">Passwort-Anforderungen:</h4>
            <ul style="margin: 0; padding: 0; list-style: none;">
                <li id="rule-length" class="password-rule">
                    <span class="rule-icon">•</span>
                    <span class="rule-text">Mindestens 8 Zeichen</span>
                </li>
                <li id="rule-number" class="password-rule">
                    <span class="rule-icon">•</span>
                    <span class="rule-text">Mindestens eine Zahl (0-9)</span>
                </li>
                <li id="rule-uppercase" class="password-rule">
                    <span class="rule-icon">•</span>
                    <span class="rule-text">Mindestens einen Großbuchstaben (A-Z)</span>
                </li>
                <li id="rule-lowercase" class="password-rule">
                    <span class="rule-icon">•</span>
                    <span class="rule-text">Mindestens einen Kleinbuchstaben (a-z)</span>
                </li>
                <li id="rule-special" class="password-rule">
                    <span class="rule-icon">•</span>
                    <span class="rule-text">Mindestens ein Sonderzeichen (!@#$%^&*()_+-=[]{}|;:,.<>?)</span>
                </li>
            </ul>
            <div id="password-strength" style="margin-top: 15px; padding: 8px 12px; border-radius: 4px; text-align: center; font-weight: bold; color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; display: none;">
                Schwach
            </div>
            <div id="no-js-info" style="margin-top: 15px; padding: 8px 12px; border-radius: 4px; text-align: center; font-size: 12px; color: #6c757d; background: #e9ecef; border: 1px solid #dee2e6;">
                💡 Mit aktiviertem JavaScript erhalten Sie Live-Feedback
            </div>
        </div>
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

    // Passwort-Validierung mit Live-Feedback
    const passwordInput = document.getElementById('passwort');
    const passwordFeedback = document.getElementById('password-feedback');

    if (passwordInput && passwordFeedback) {
        // JavaScript ist verfügbar - aktiviere Live-Feedback
        passwordFeedback.classList.add('js-enabled');

        // Zeige Passwort-Stärke und verstecke No-JS-Info
        document.getElementById('password-strength').style.display = 'block';
        document.getElementById('no-js-info').style.display = 'none';

        // Passwort-Validierungsregeln
        const rules = {
            length: {
                test: (pwd) => pwd.length >= 8,
                element: document.getElementById('rule-length')
            },
            number: {
                test: (pwd) => /[0-9]/.test(pwd),
                element: document.getElementById('rule-number')
            },
            uppercase: {
                test: (pwd) => /[A-Z]/.test(pwd),
                element: document.getElementById('rule-uppercase')
            },
            lowercase: {
                test: (pwd) => /[a-z]/.test(pwd),
                element: document.getElementById('rule-lowercase')
            },
            special: {
                test: (pwd) => /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(pwd),
                element: document.getElementById('rule-special')
            }
        };

        // Live-Validierung bei Eingabe
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let fulfilledRules = 0;

            // Prüfe jede Regel
            Object.keys(rules).forEach(ruleKey => {
                const rule = rules[ruleKey];
                const isFulfilled = rule.test(password);
                const icon = rule.element.querySelector('.rule-icon');

                if (isFulfilled) {
                    icon.textContent = '✅';
                    icon.style.color = '#28a745';
                    rule.element.style.color = '#28a745';
                    fulfilledRules++;
                } else {
                    icon.textContent = '❌';
                    icon.style.color = '#dc3545';
                    rule.element.style.color = '#6c757d';
                }
            });

            // Passwort-Stärke anzeigen
            const strengthElement = document.getElementById('password-strength');
            if (fulfilledRules === 0) {
                strengthElement.textContent = 'Schwach';
                strengthElement.style.color = '#dc3545';
            } else if (fulfilledRules <= 2) {
                strengthElement.textContent = 'Schwach';
                strengthElement.style.color = '#fd7e14';
            } else if (fulfilledRules <= 4) {
                strengthElement.textContent = 'Mittel';
                strengthElement.style.color = '#ffc107';
            } else {
                strengthElement.textContent = 'Stark';
                strengthElement.style.color = '#28a745';
            }
        });

        // Initial-Validierung
        passwordInput.dispatchEvent(new Event('input'));
    }

    // Flash-Messages initialisieren (falls View direkt geladen wird)
    <?php if (!empty($_SESSION['flash'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Flash-Message anzeigen
            if (typeof zeigeFlash === 'function') {
                zeigeFlash('<?= htmlspecialchars($_SESSION['flash']['type']) ?>', '<?= htmlspecialchars($_SESSION['flash']['message']) ?>');

                // Flash-Message aus Session löschen
                fetch('index.php?page=clearFlash', {
                    method: 'GET',
                    credentials: 'same-origin'
                }).catch(() => {
                    // Fehler ignorieren - nicht kritisch
                });
            }
        });
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    // Pop-up für Registrierungsbestätigung
    <?php if (isset($_GET['popup']) && !empty($_SESSION['show_confirmation_popup'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const popupData = <?= json_encode($_SESSION['show_confirmation_popup']) ?>;

            // Pop-up HTML erstellen
            const popup = document.createElement('div');
            popup.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
            `;

            popup.innerHTML = `
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    max-width: 500px;
                    width: 90%;
                    text-align: center;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                ">
                    <h3 style="color: #28a745; margin-bottom: 20px;">Registrierung fast abgeschlossen!</h3>
                    <p style="margin-bottom: 20px;">
                        Hallo! Um deine Registrierung für <strong>${popupData.email}</strong> abzuschließen,
                        klicke bitte auf den folgenden Button:
                    </p>
                    <p style="margin-bottom: 20px;">
                        <a href="${popupData.link}"
                           style="
                               background: #007bff;
                               color: white;
                               padding: 15px 30px;
                               text-decoration: none;
                               border-radius: 5px;
                               display: inline-block;
                               font-weight: bold;
                               font-size: 16px;
                           ">
                            Registrierung jetzt bestätigen
                        </a>
                    </p>
                    <p style="font-size: 14px; color: #666; margin-top: 20px;">
                        <strong>Wichtig:</strong> Dieser Link ist nur einmalig verwendbar.<br>
                        Bitte bestätige deine Registrierung jetzt, um deinen Account zu aktivieren.
                    </p>
                </div>
            `;

            document.body.appendChild(popup);

            // Pop-up kann nicht durch Klick außerhalb geschlossen werden
            // da der Bestätigungslink nur einmalig verwendbar ist
        });
        <?php unset($_SESSION['show_confirmation_popup']); ?>
    <?php endif; ?>
</script>

<style>
/* Passwort-Feedback-Styles */
.password-rule {
    margin: 5px 0;
    transition: color 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rule-icon {
    font-size: 12px;
    width: 16px;
    text-align: center;
}

.rule-text {
    flex: 1;
}

#password-feedback {
    transition: all 0.3s ease;
}

#password-feedback.js-enabled {
    border-color: #007bff;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

#password-strength {
    padding: 5px 10px;
    border-radius: 3px;
    text-align: center;
    font-size: 12px;
    font-weight: bold;
    transition: all 0.3s ease;
}

/* Responsive Design für kleinere Bildschirme */
@media (max-width: 768px) {
    main > div[style*="display: flex"] {
        flex-direction: column !important;
        gap: 20px !important;
    }

    #password-feedback {
        flex: 1 !important;
        max-width: none !important;
        position: static !important;
        order: -1; /* Zeige Feedback-Panel über dem Formular auf mobilen Geräten */
    }
}

/* Fallback für deaktiviertes JavaScript */
.no-js #password-feedback {
    background: #fff3cd;
    border-color: #ffeaa7;
}

.no-js #password-feedback h4 {
    color: #856404;
}

.no-js .password-rule {
    color: #856404;
}

.no-js .rule-icon {
    color: #856404;
}

.no-js #password-strength {
    display: none !important;
}

.no-js #no-js-info {
    display: none !important;
}

/* JavaScript-aktiviert Styles */
.js-enabled #password-strength {
    display: block !important;
}

.js-enabled #no-js-info {
    display: none !important;
}
</style>