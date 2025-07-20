<main>
    <div style="text-align: center; padding: 40px 20px;">
        <h2>Registrierung bestätigen</h2>
        
        <div style="max-width: 600px; margin: 0 auto;">
            <div style="background: #d1ecf1; color: #0c5460; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3>Fast geschafft!</h3>
                <p>Um deine Registrierung abzuschließen, bestätige bitte, dass du dich wirklich registrieren möchtest.</p>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4>Was passiert nach der Bestätigung?</h4>
                <ul style="text-align: left; display: inline-block;">
                    <li>Dein Account wird aktiviert</li>
                    <li>Du kannst dich sofort anmelden</li>
                    <li>Du erhältst Zugriff auf alle Funktionen</li>
                </ul>
            </div>

            <div style="margin: 30px 0;">
                <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" style="display: inline-block; margin-right: 20px;">
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" style="
                        background: #28a745;
                        color: white;
                        border: none;
                        padding: 15px 30px;
                        font-size: 16px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-weight: bold;
                    ">
                        ✓ Ja, Registrierung bestätigen
                    </button>
                </form>

                <a href="index.php" style="
                    background: #6c757d;
                    color: white;
                    text-decoration: none;
                    padding: 15px 30px;
                    font-size: 16px;
                    border-radius: 5px;
                    display: inline-block;
                ">
                    Abbrechen
                </a>
            </div>

            <div class="registrierung-bestaetigung-info">
                <p>Falls du dich nicht registriert hast, kannst du diese Seite einfach schließen.</p>
            </div>
        </div>
    </div>
</main>
