<?php
declare(strict_types=1);
ob_start();

session_start();

require_once 'php/include/form_utils.php';

$page = htmlspecialchars($_GET['page'] ?? 'home');

// Geschützte Seiten zentral absichern
$geschuetzteSeiten = [
    'rezept-neu', 'rezept-bearbeiten', 'rezept-loeschen',
    'rezept-aktualisieren', 'nutzer', 'nutzerliste', 'nutzer-loeschen'
];

if (in_array($page, $geschuetzteSeiten, true) && empty($_SESSION['email']) && $page !== 'anmeldung') {
    $_SESSION["message"] = "Bitte melde dich zuerst an.";
    header("Location: index.php?page=anmeldung");
    exit;
}
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

        case 'bestaetigeRegistrierung':
            require_once 'php/controller/NutzerController.php';
            if (isset($_GET['code'])) {
                bestaetigeRegistrierung($_GET['code']);
            } else {
                echo '<main><div>Ungültiger Link.</div></main>';
            }
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
            showNutzerProfil();
            break;

        case 'nutzer-loeschen':
            require_once 'php/controller/NutzerController.php';

            if (!istAdmin()) {
                $_SESSION["message"] = "Nur Administratoren dürfen Nutzer löschen.";
                header("Location: index.php?page=nutzerliste");
                exit;
            }

            $id = validateId($_GET['id'] ?? null);
            if ($id === null) {
                $_SESSION["message"] = "Ungültige Nutzer-ID.";
                header("Location: index.php?page=nutzerliste");
                exit;
            }

            if (isset($_SESSION['nutzerId']) && (int)$_SESSION['nutzerId'] === $id) {
                $_SESSION["message"] = "Du kannst deinen eigenen Account nicht löschen.";
                header("Location: index.php?page=nutzerliste");
                exit;
            }

            loescheNutzer($id);
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

    <!-- Dein Bild-Upload-Script bleibt erhalten -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const realFileInput = document.getElementById('bild');
            const btnSelectFile = document.getElementById('btn-select-file');
            const fileNameSpan = document.getElementById('selected-file-name');
            const previewContainer = document.getElementById('preview-container');
            const imgPreview = document.getElementById('img-preview');

            if (btnSelectFile && realFileInput && fileNameSpan && previewContainer && imgPreview) {
                btnSelectFile.addEventListener('click', function() {
                    realFileInput.click();
                });

                realFileInput.addEventListener('change', function() {
                    if (realFileInput.files.length > 0) {
                        const file = realFileInput.files[0];
                        fileNameSpan.textContent = file.name;

                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                imgPreview.src = e.target.result;
                                previewContainer.style.display = 'block';
                                imgPreview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            previewContainer.style.display = 'none';
                            imgPreview.style.display = 'none';
                            imgPreview.src = '';
                        }
                    } else {
                        fileNameSpan.textContent = 'Keine ausgewählt';
                        previewContainer.style.display = 'none';
                        imgPreview.style.display = 'none';
                        imgPreview.src = '';
                    }
                });
            }
        });
    </script>

    </body>
    </html>
<?php ob_end_flush(); ?>