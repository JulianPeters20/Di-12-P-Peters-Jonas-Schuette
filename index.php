<?php
declare(strict_types=1);
ob_start();

// Sicherheitskonfiguration laden
require_once 'php/config/security.php';

// Sichere Session-Konfiguration
configureSecureSession();
session_start();

// Session-Regeneration bei Login-Status-Änderung
if (isset($_SESSION['regenerate_session']) && $_SESSION['regenerate_session']) {
    session_regenerate_id(true);
    unset($_SESSION['regenerate_session']);
}

// Sicherheits-Header setzen
setSecurityHeaders();

require_once 'php/include/form_utils.php';
require_once 'php/include/csrf_protection.php';

$page = htmlspecialchars($_GET['page'] ?? 'home');

// Geschützte Seiten zentral absichern
$geschuetzteSeiten = [
    'rezept-neu', 'rezept-bearbeiten', 'rezept-loeschen',
    'rezept-aktualisieren', 'nutzer', 'nutzerliste', 'nutzer-loeschen'
];

if (in_array($page, $geschuetzteSeiten, true) && empty($_SESSION['email']) && $page !== 'anmeldung') {
    flash("warning", "Bitte melde dich zuerst an.");
    header("Location: index.php?page=anmeldung");
    exit;
}

// Header einbinden (enthält vollständige HTML-Struktur)
require_once 'php/include/header.php';

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

    case 'bestaetigeRegistrierung':
        require_once 'php/controller/NutzerController.php';
        bestaetigeRegistrierung($_GET['code'] ?? '');
        break;

    case 'pruefeBenutzername':
        require_once 'php/controller/NutzerController.php';
        pruefeBenutzername();
        exit;

    case 'rezepte':
        require_once 'php/controller/RezeptController.php';
        showRezepte();
        break;

    case 'rezept':
        require_once 'php/controller/RezeptController.php';
        $id = validateId($_GET['id'] ?? null);
        showRezeptDetails($id);
        break;

    case 'bewerteRezept':
        require_once 'php/controller/RezeptController.php';
        bewerteRezept();
        break;

    case 'berechneNaehrwerte':
        require_once 'php/controller/RezeptController.php';
        berechneNaehrwerte();
        break;

    case 'setzeNaehrwerteEinwilligung':
        require_once 'php/controller/RezeptController.php';
        setzeNaehrwerteEinwilligung();
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
            flash("warning", "Nur angemeldete Nutzer können neue Rezepte erstellen.");
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

    case 'api-monitor':
        require_once 'php/controller/AdminController.php';
        showApiMonitor();
        break;

    case 'api-cache-leeren':
        require_once 'php/controller/AdminController.php';
        leereApiCache();
        break;

    case 'api-logs-bereinigen':
        require_once 'php/controller/AdminController.php';
        bereinigeApiLogs();
        break;

    case 'api-test':
        require_once 'php/controller/AdminController.php';
        testeApi();
        break;

    case 'nutzer':
        require_once 'php/controller/NutzerController.php';
        showNutzerProfil(); // ohne Parameter
        break;

    case 'nutzer-loeschen':
        require_once 'php/controller/NutzerController.php';

        if (!istAdmin()) {
            flash("error", "Nur Administratoren dürfen Nutzer löschen.");
            header("Location: index.php?page=nutzerliste");
            exit;
        }

        $id = validateId($_GET['id'] ?? null);
        if ($id === null) {
            flash("error", "Ungültige Nutzer-ID.");
            header("Location: index.php?page=nutzerliste");
            exit;
        }

        // Schutz: Admin darf sich selbst nicht löschen
        if (isset($_SESSION['nutzerId']) && (int)$_SESSION['nutzerId'] === $id) {
            flash("warning", "Du kannst deinen eigenen Account nicht löschen.");
            header("Location: index.php?page=nutzerliste");
            exit;
        }

        // Zusätzlicher Schutz: Prüfen ob der zu löschende Nutzer ein Admin ist
        require_once 'php/model/NutzerDAO.php';
        $nutzerDAO = new NutzerDAO();
        $zuLoeschenderNutzer = $nutzerDAO->findeNachID($id);
        if ($zuLoeschenderNutzer && $zuLoeschenderNutzer->istAdmin) {
            flash("warning", "Administratoren können nicht gelöscht werden.");
            header("Location: index.php?page=nutzerliste");
            exit;
        }

        loescheNutzer($id);
        break;

    case 'konto-loeschen':
        require_once 'php/controller/NutzerController.php';
        loescheEigenesKonto();
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

    case 'clearFlash':
        // Flash-Nachricht aus Session löschen (AJAX-Endpoint)
        if (!empty($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }
        http_response_code(204); // No Content
        exit;

    case 'setJSStatus':
        // JavaScript-Status setzen
        require_once 'php/include/javascript_detection.php';
        $enabled = isset($_GET['enabled']) && $_GET['enabled'] === '1';
        setJavaScriptStatus($enabled);
        http_response_code(204); // No Content
        exit;

    default:
        require_once 'php/controller/IndexController.php';
        showHome();
        break;
}
?>

<?php require_once 'php/include/footer.php'; ?>

</body>
</html>
<?php ob_end_flush(); ?>