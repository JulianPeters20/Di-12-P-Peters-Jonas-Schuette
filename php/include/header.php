<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broke & Hungry</title>
    <link rel="stylesheet" href="css/style.css">

    <?php
    // Optional: Session sicherstellen, wenn nicht schon gestartet
    if (session_status() === PHP_SESSION_NONE) session_start();

    // CSRF-Token für JavaScript verfügbar machen
    if (function_exists('generateCSRFToken')) {
        $csrfToken = generateCSRFToken();
        echo '<meta name="csrf-token" content="' . htmlspecialchars($csrfToken) . '">';
    }

    // Flash-Nachrichten für JavaScript verfügbar machen
    if (!empty($_SESSION['flash'])) {
        echo '<div data-flash-message data-flash-type="' . htmlspecialchars($_SESSION['flash']['type']) . '" data-flash-message="' . htmlspecialchars($_SESSION['flash']['message']) . '" style="display:none;"></div>';
        unset($_SESSION['flash']);
    }
    ?>

    <!-- JavaScript-Dateien -->
    <script src="js/main.js"></script>
    <script src="js/forms.js"></script>
    <script src="js/rezept.js"></script>
    <script src="js/search.js"></script>
</head>
<body>
<header class="kopfzeile">
    <div class="logo">
        <a href="index.php">
            <img src="images/Logo ChatGPT 2.png" alt="Logo" />
        </a>
    </div>

    <button class="burger-btn" aria-label="Menü öffnen" aria-expanded="false" aria-controls="haupt-navigation nutzer-navigation">
        <span class="burger-line"></span>
        <span class="burger-line"></span>
        <span class="burger-line"></span>
    </button>

    <nav class="haupt-nav" id="haupt-navigation">
        <ul>
            <li><a href="index.php?page=rezepte">Rezepte</a></li>

            <?php if (!empty($_SESSION['istAdmin'])): ?>
                <li><a href="index.php?page=nutzerliste">Nutzerliste</a></li>
                <li><a href="index.php?page=api-monitor">API-Monitor</a></li>
            <?php endif; ?>

            <?php if (!empty($_SESSION['email'])): ?>
                <li><a href="index.php?page=rezept-neu">Neues Rezept</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="nutzer-nav" id="nutzer-navigation">
        <ul>
            <?php if (!empty($_SESSION['nutzerId'])): ?>
                <li><span>Hallo, <?= htmlspecialchars($_SESSION['benutzername'] ?? 'Nutzer') ?></span></li>
                <li><a href="index.php?page=abmeldung">Abmelden</a></li>
            <?php else: ?>
                <li><a href="index.php?page=anmeldung">Anmelden</a></li>
                <li><a href="index.php?page=registrierung">Registrieren</a></li>
            <?php endif; ?>

            <li>
                <?php if (!empty($_SESSION['nutzerId'])): ?>
                <a href="index.php?page=nutzer" title="Benutzerkonto">
                    <?php else: ?>
                    <a href="index.php?page=anmeldung" title="Benutzerkonto">
                        <?php endif; ?>
                        <img src="images/Icon Nutzer ChatGPT.webp" alt="Benutzerprofil" class="nutzer-icon">
                    </a>
            </li>
        </ul>
    </div>
</header>

