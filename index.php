<?php
declare(strict_types=1);

// Zentrales Router/Dispatcher-File für Broke & Hungry
// Leitet "page"-Aufrufe an entsprechende Controller bzw. Views weiter
ini_set('session.cookie_lifetime', '0'); // Session gilt nur solange Browser offen ist
session_start();

/**
 * Validiert die ID aus dem GET-Parameter.
 * Liefert int oder null (bei ungültiger Eingabe).
 *
 * @param mixed $idRaw
 * @return int|null
 */
function validateId($idRaw): ?int
{
    if (isset($idRaw) && ctype_digit((string)$idRaw)) {
        return (int)$idRaw;
    }
    return null;
}

/**
 * Validiert eine E-Mail-Adresse aus GET-Parameter.
 * Liefert String oder null.
 *
 * @param mixed $emailRaw
 * @return string|null
 */
function validateEmail($emailRaw): ?string
{
    if (isset($emailRaw) && filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
        return $emailRaw;
    }
    return null;
}

$page = $_GET['page'] ?? 'home';

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Broke & Hungry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once 'php/include/header.php'; ?>

<?php
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
        $id = validateId($_GET['id'] ?? null);
        showRezeptDetails($id);
        break;

    case 'rezept-bearbeiten':
        require_once 'php/controller/RezeptController.php';
        $id = validateId($_GET['id'] ?? null);
        showRezeptBearbeitenFormular($id);
        break;

    case 'rezept-aktualisieren':
        require_once 'php/controller/RezeptController.php';
        $id = validateId($_GET['id'] ?? null);
        aktualisiereRezept($id);
        break;

    case 'rezept-loeschen':
        require_once 'php/controller/RezeptController.php';
        $id = validateId($_GET['id'] ?? null);
        loescheRezept($id);
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
        $email = validateEmail($_GET['email'] ?? null);
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