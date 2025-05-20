<?php
session_start();
include_once 'header.php';

if (!isset($_SESSION['eingeloggt']) || $_SESSION['eingeloggt'] !== true) {
    header('Location: anmeldung.php');
    exit();
}
?>

<main>
    <h2>Mein Profil</h2>

    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
        <img src="images/Icon Nutzer ChatGPT.webp" alt="Profilbild" style="height: 80px; width: 80px; border-radius: 50%; padding: 10px;">
        <div>
            <p><strong>Benutzername:</strong> <?= htmlspecialchars($_SESSION['benutzername']) ?></p>
            <p><strong>E-Mail:</strong> <?= htmlspecialchars($_SESSION['email']) ?></p>
        </div>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 20px;">Eigene Rezepte</h3>

    <div class="rezept-galerie">
        <!-- TODO: Rezepte aus der Datenbank einbinden -->
        <div class="rezept-karte">
            <img src="images/pesto.jpg" alt="Nudeln mit Pesto">
            <div class="inhalt">
                <h3><a href="rezept.php">Nudeln mit Pesto</a></h3>
                <p class="meta">Vegetarisch · 21.04.2025 · <?= htmlspecialchars($_SESSION['benutzername']) ?></p>
            </div>
        </div>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 20px;">Gespeicherte Rezepte</h3>

    <div class="rezept-galerie">
        <div class="rezept-karte">
            <img src="images/reis_mit_curry.jpg" alt="Reis mit Curry">
            <div class="inhalt">
                <h3><a href="rezept.php">Reis mit Curry</a></h3>
                <p class="meta">Vegan · 20.04.2025 · max@example.com</p>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px;">
        <a href="abmeldung.php" class="btn">Abmelden</a>
    </div>
</main>

<?php include_once 'footer.php'; ?>
