<?php
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
        showRezeptListe();
        break;
    case 'rezept':
        require_once 'php/controller/RezeptController.php';
        $id = $_GET['id'] ?? null;
        showRezeptDetails($id);
        break;
    case 'rezept-neu':
        require_once 'php/controller/RezeptController.php';
        showNeuesRezeptFormular();
        break;
    case 'nutzerliste':
        require_once 'php/controller/NutzerController.php';
        showNutzerListe();
        break;
    case 'nutzer':
        require_once 'php/controller/NutzerController.php';
        $id = $_GET['id'] ?? null;
        showNutzerProfil($id);
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