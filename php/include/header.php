<header class="kopfzeile">
    <div class="logo">
        <a href="index.php">
            <img src="images/Logo ChatGPT 2.png" alt="Logo" />
        </a>
    </div>

    <nav class="haupt-nav">
        <ul>
            <li><a href="index.php?page=rezepte">Rezepte</a></li>

            <?php if (!empty($_SESSION['istAdmin'])): ?>
                <li><a href="index.php?page=nutzerliste">Nutzerliste</a></li>
            <?php endif; ?>

            <?php if (!empty($_SESSION['email'])): ?>
                <li><a href="index.php?page=rezept-neu">Neues Rezept</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="nutzer-nav">
        <ul>
            <?php if (!empty($_SESSION['email']) && !empty($_SESSION['eingeloggt'])): ?>
                <li><span>Hallo, <?= htmlspecialchars($_SESSION['benutzername']) ?></span></li>
                <li><a href="index.php?page=abmeldung">Abmelden</a></li>
            <?php else: ?>
                <li><a href="index.php?page=anmeldung">Anmelden</a></li>
                <li><a href="index.php?page=registrierung">Registrieren</a></li>
            <?php endif; ?>

            <li>
                <?php if (!empty($_SESSION['email'])): ?>
                <a href="index.php?page=nutzer&email=<?= urlencode($_SESSION['email']) ?>" title="Benutzerkonto">
                    <?php else: ?>
                    <a href="index.php?page=anmeldung" title="Benutzerkonto">
                        <?php endif; ?>
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