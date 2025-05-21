<header class="kopfzeile">
    <div class="logo">
        <a href="index.php">
            <img src="images/Logo ChatGPT 2.png" alt="Logo" />
        </a>
    </div>

    <nav class="haupt-nav">
        <ul>
            <li><a href="index.php?page=rezepte">Rezepte</a></li>
            <li><a href="index.php?page=nutzerliste">Nutzerliste</a></li>
            <?php if (isset($_SESSION['email'])): ?>
                <li><a href="index.php?page=rezept-neu">Neues Rezept</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="nutzer-nav">
        <ul>
            <?php if (isset($_SESSION['email'])): ?>
                <li><span>Hallo, <?= htmlspecialchars($_SESSION['benutzername']) ?></span></li>
                <li><a href="index.php?page=abmeldung">Abmelden</a></li>
            <?php else: ?>
                <li><a href="index.php?page=anmeldung">Anmelden</a></li>
                <li><a href="index.php?page=registrierung">Registrieren</a></li>
            <?php endif; ?>

            <li>
                <a href="index.php?page=<?= isset($_SESSION['email']) ? 'nutzer&email=' . urlencode($_SESSION['email']) : 'anmeldung' ?>"
                   title="Benutzerkonto">
                    <img src="images/Icon Nutzer ChatGPT.webp" alt="Benutzerprofil" class="nutzer-icon">
                </a>
            </li>
        </ul>
    </div>

</header>

<!-- 💬 Session Message anzeigen -->
<?php if (isset($_SESSION["message"])): ?>
    <div class="message-box">
        <?= htmlspecialchars($_SESSION["message"]) ?>
    </div>
    <?php unset($_SESSION["message"]); ?>
<?php endif; ?>
