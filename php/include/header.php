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
        echo '<div data-flash-type="' . htmlspecialchars($_SESSION['flash']['type']) . '" data-flash-message="' . htmlspecialchars($_SESSION['flash']['message']) . '" class="flash-hidden"></div>';
        // Flash-Nachricht NICHT hier löschen - wird in main.js nach Anzeige gelöscht
    }
    ?>

    <!-- JavaScript-Erkennung für Progressive Enhancement -->
    <?php
    require_once 'php/include/javascript_detection.php';
    echo getJavaScriptDetectionCode();
    echo getNoScriptDetectionCode();
    ?>

    <!-- JavaScript-Dateien -->
    <script src="js/main.js"></script>
    <script src="js/forms.js"></script>
    <script src="js/rezept.js"></script>
    <script src="js/search.js"></script>
</head>
<body class="no-js">
<header class="kopfzeile">
    <div class="logo">
        <a href="index.php">
            <img src="images/Logo ChatGPT 2.png" alt="Logo" />
        </a>
    </div>

    <!-- CSS-only Burger Menu -->
    <input type="checkbox" id="burger-toggle" class="burger-toggle" aria-label="Menü öffnen/schließen">
    <label for="burger-toggle" class="burger-btn" tabindex="0" role="button" aria-label="Menü öffnen">
        <span class="burger-line" aria-hidden="true"></span>
        <span class="burger-line" aria-hidden="true"></span>
        <span class="burger-line" aria-hidden="true"></span>
    </label>

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
                <li class="nutzer-begruessung mobile-hidden"><span>Hallo, <?= htmlspecialchars($_SESSION['benutzername'] ?? 'Nutzer') ?></span></li>
                <li class="nutzer-abmelden mobile-hidden"><a href="index.php?page=abmeldung">Abmelden</a></li>
            <?php else: ?>
                <li class="nutzer-anmelden mobile-hidden"><a href="index.php?page=anmeldung">Anmelden</a></li>
                <li class="nutzer-registrieren mobile-hidden"><a href="index.php?page=registrierung">Registrieren</a></li>
            <?php endif; ?>

            <li class="nutzer-icon-container">
                <?php if (!empty($_SESSION['nutzerId'])): ?>
                <a href="index.php?page=nutzer" title="Benutzerkonto" class="nutzer-icon-link">
                    <?php else: ?>
                    <a href="index.php?page=anmeldung" title="Benutzerkonto" class="nutzer-icon-link">
                        <?php endif; ?>
                        <img src="images/Icon Nutzer ChatGPT.webp" alt="Benutzerprofil" class="nutzer-icon">
                    </a>
            </li>
        </ul>
    </div>
</header>

