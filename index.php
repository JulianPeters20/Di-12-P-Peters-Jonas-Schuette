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
                            previewContainer.style.display = 'block';  // Container anzeigen
                            imgPreview.style.display = 'block';         // Bild anzeigen
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.style.display = 'none'; // Container ausblenden
                        imgPreview.style.display = 'none';        // Bild ausblenden
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

        // Nährwerte-Funktionalität
        const consentCheckbox = document.getElementById('consent-checkbox');
        const consentBtn = document.getElementById('consent-btn');
        const berechneBtn = document.getElementById('berechne-naehrwerte-btn');
        const consentArea = document.getElementById('consent-area');
        const naehrwertePlaceholder = document.getElementById('naehrwerte-placeholder');
        const naehrwerteDisplay = document.getElementById('naehrwerte-display');
        const naehrwerteLoading = document.getElementById('naehrwerte-loading');
        const naehrwerteError = document.getElementById('naehrwerte-error');

        // Einwilligungs-Checkbox Handler
        if (consentCheckbox && consentBtn) {
            consentCheckbox.addEventListener('change', function() {
                consentBtn.disabled = !this.checked;
            });

            consentBtn.addEventListener('click', function() {
                const formData = new FormData();
                formData.append('einwilligung', consentCheckbox.checked);

                fetch('index.php?page=setzeNaehrwerteEinwilligung', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.einwilligung) {
                        consentArea.style.display = 'none';
                        if (berechneBtn) {
                            berechneBtn.style.display = 'inline-block';
                        }
                    }
                })
                .catch(error => {
                    console.error('Fehler beim Speichern der Einwilligung:', error);
                });
            });
        }

        // Nährwerte berechnen Handler
        if (berechneBtn) {
            berechneBtn.addEventListener('click', function() {
                const rezeptId = new URLSearchParams(window.location.search).get('id');

                if (!rezeptId) {
                    showNaehrwerteError('Rezept-ID nicht gefunden');
                    return;
                }

                // Loading anzeigen
                naehrwerteLoading.style.display = 'block';
                naehrwerteError.style.display = 'none';
                berechneBtn.disabled = true;

                const formData = new FormData();
                formData.append('rezeptId', rezeptId);

                fetch('index.php?page=berechneNaehrwerte', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    naehrwerteLoading.style.display = 'none';
                    berechneBtn.disabled = false;

                    if (data.success) {
                        showNaehrwerte(data.naehrwerte, data.cached);
                        if (naehrwertePlaceholder) {
                            naehrwertePlaceholder.style.display = 'none';
                        }
                    } else {
                        if (data.consent_required) {
                            consentArea.style.display = 'block';
                            berechneBtn.style.display = 'none';
                        } else {
                            showNaehrwerteError(data.error || 'Unbekannter Fehler');
                        }
                    }
                })
                .catch(error => {
                    naehrwerteLoading.style.display = 'none';
                    berechneBtn.disabled = false;
                    showNaehrwerteError('Netzwerkfehler: ' + error.message);
                });
            });
        }

        function showNaehrwerte(naehrwerte, cached = false) {
            if (!naehrwerteDisplay) return;

            const html = `
                <div class="naehrwerte-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 15px 0;">
                    <div class="naehrwert-item">
                        <strong>Kalorien:</strong><br>
                        <span class="naehrwert-wert">${Math.round(naehrwerte.kalorien || 0)} kcal</span>
                    </div>
                    <div class="naehrwert-item">
                        <strong>Protein:</strong><br>
                        <span class="naehrwert-wert">${(naehrwerte.protein || 0).toFixed(1)} g</span>
                    </div>
                    <div class="naehrwert-item">
                        <strong>Kohlenhydrate:</strong><br>
                        <span class="naehrwert-wert">${(naehrwerte.kohlenhydrate || 0).toFixed(1)} g</span>
                    </div>
                    <div class="naehrwert-item">
                        <strong>Fett:</strong><br>
                        <span class="naehrwert-wert">${(naehrwerte.fett || 0).toFixed(1)} g</span>
                    </div>
                    <div class="naehrwert-item">
                        <strong>Ballaststoffe:</strong><br>
                        <span class="naehrwert-wert">${(naehrwerte.ballaststoffe || 0).toFixed(1)} g</span>
                    </div>
                    <div class="naehrwert-item">
                        <strong>Zucker:</strong><br>
                        <span class="naehrwert-wert">${(naehrwerte.zucker || 0).toFixed(1)} g</span>
                    </div>
                </div>
                <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                    ${cached ? 'Aus Cache geladen' : 'Gerade berechnet'} • ${new Date().toLocaleDateString('de-DE')}
                </p>
            `;

            naehrwerteDisplay.innerHTML = html;
            naehrwerteDisplay.style.display = 'block';
        }

        function showNaehrwerteError(message) {
            if (naehrwerteError) {
                naehrwerteError.textContent = message;
                naehrwerteError.style.display = 'block';
            }
        }
    });
</script>

</body>
</html>
<?php ob_end_flush(); ?>