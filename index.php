<?php
// Zentrales Router/Dispatcher-File für Broke & Hungry
// Leitet "page"-Aufrufe an entsprechende Controller bzw. Views weiter
ini_set('session.cookie_lifetime', 0); // Session gilt nur solange Browser offen ist
session_start();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <title>Broke & Hungry</title>
</head>
<body>
<?php require_once 'php/include/header.php'; ?>

<?php
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'anmeldung':
        require_once 'php/controller/NutzerController.php';
        showAnmeldeFormular();
        break;

    case 'abmeldung':
        require_once 'php/controller/NutzerController.php';
        logoutUser();
        break;

    case 'registrierung':
        require_once 'php/controller/NutzerController.php';
        showRegistrierungsFormular();
        break;

    case 'rezepte':
        require_once 'php/controller/RezeptController.php';
        showRezepte();
        break;

    case 'rezept':
        require_once 'php/controller/RezeptController.php';
        $id = $_GET['id'] ?? null;
        showRezeptDetails($id);
        break;

    case 'rezept-bearbeiten':
        require_once 'php/controller/RezeptController.php';
        $id = $_GET['id'] ?? null;
        showRezeptBearbeitenFormular($id);
        break;

    case 'rezept-aktualisieren':
        require_once 'php/controller/RezeptController.php';
        $id = $_GET['id'] ?? null;
        aktualisiereRezept($id);
        break;

    case 'rezept-loeschen':
        require_once 'php/controller/RezeptController.php';
        $id = $_GET['id'] ?? null;
        loescheRezept($id); // existiert schon!
        break;

    case 'rezept-neu':
        require_once 'php/controller/RezeptController.php';
        if (!isset($_SESSION['email'])) {
            $_SESSION["message"] = "Nur angemeldete Nutzer können neue Rezepte erstellen.";
            header("Location: index.php?page=anmeldung");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            speichereRezept();
        } else {
            showRezeptNeu();
        }
        break;

    case 'nutzerliste':
        require_once 'php/controller/NutzerController.php';
        showNutzerListe();
        break;

    case 'nutzer':
        require_once 'php/controller/NutzerController.php';
        $email = $_GET['email'] ?? null;
        showNutzerProfil($email);
        break;

    case 'impressum':
        require_once 'php/view/impressum.php';
        break;

    case 'datenschutz':
        require_once 'php/view/datenschutz.php';
        break;

    case 'nutzungsbedingungen':
        require_once 'php/view/nutzungsbedingungen.php';
        break;

    default:
        require_once 'php/controller/IndexController.php';
        showHome();
        break;
}
?>

<?php require_once 'php/include/footer.php'; ?>
</body>
</html>