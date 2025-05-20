<?php
session_start();
require_once 'php/model/NutzerDAO.php';

$fehlermeldung = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $passwort = $_POST['passwort'] ?? '';

    $nutzer = NutzerDAO::findeNachEmail($email);

    if ($nutzer && password_verify($passwort, $nutzer->passwort)) {
        $_SESSION['eingeloggt'] = true;
        $_SESSION['benutzername'] = $nutzer->benutzername;
        $_SESSION['email'] = $nutzer->email;
        header('Location: index.php');
        exit();
    } else {
        $fehlermeldung = 'E-Mail oder Passwort ist ungültig.';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Broke & Hungry - Anmeldung</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include_once 'header.php'; ?>

<main>
    <h2>Anmeldung</h2>

    <?php if (!empty($fehlermeldung)): ?>
        <p style="color:red; margin-bottom: 20px;"><?= htmlspecialchars($fehlermeldung) ?></p>
    <?php endif; ?>

    <form action="anmeldung.php" method="post">
        <p>
            <label for="email">E-Mail-Adresse:<br>
                <input type="email" id="email" name="email" required placeholder="z. B. name@example.com">
            </label>
        </p>

        <p>
            <label for="passwort">Passwort:<br>
                <input type="password" id="passwort" name="passwort" required minlength="8" placeholder="mind. 8 Zeichen">
            </label>
        </p>

        <p>
            <input type="submit" value="Anmelden">
            <a href="registrierung.php" class="btn">Noch keinen Account?</a>
        </p>
    </form>
</main>

<?php include_once 'footer.php'; ?>

</body>
</html>
