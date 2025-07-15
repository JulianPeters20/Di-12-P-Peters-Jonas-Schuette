<main>
    <!-- Flash-Toast anzeigen -->
    <?php if (!empty($_SESSION['flash'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const toast = document.createElement("div");
                toast.className = "flash-toast <?= $_SESSION['flash']['type'] ?>";
                toast.textContent = "<?= htmlspecialchars($_SESSION['flash']['message']) ?>";

                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 4600);
            });
        </script>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <h2>Anmeldung</h2>
    <form action="index.php?page=anmeldung" method="post" autocomplete="off">
        <?= getCSRFTokenField() ?>

        <div class="form-row">
            <label for="email">E-Mail-Adresse:</label>
            <input type="email" id="email" name="email" required autocomplete="username">
        </div>

        <div class="form-row">
            <label for="passwort">Passwort:</label>
            <input type="password" id="passwort" name="passwort" required autocomplete="current-password">
        </div>

        <div class="form-row justify-center">
            <input type="submit" value="Anmelden">
        </div>
    </form>
    <div style="margin-top:16px; text-align:center;">
        Noch kein Konto? <a href="index.php?page=registrierung" class="btn">Jetzt registrieren</a>
    </div>
</main>