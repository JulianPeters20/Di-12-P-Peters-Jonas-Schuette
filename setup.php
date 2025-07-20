<?php
/**
 * Database Setup Page
 * Use this page to initialize the database properly
 */

require_once 'php/setup/DatabaseInitializer.php';

$message = '';
$status = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'initialize':
                DatabaseInitializer::initialize();
                $message = 'Datenbank erfolgreich initialisiert!';
                break;
            case 'status':
                // Just refresh status
                break;
        }
    } catch (Exception $e) {
        $message = 'Fehler: ' . $e->getMessage();
    }
}

// Get current status
try {
    $status = DatabaseInitializer::getStatus();
} catch (Exception $e) {
    $message = 'Fehler beim Abrufen des Status: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenbank Setup - Broke & Hungry</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .status { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        .danger { background: #dc3545; }
        .danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <h1>Datenbank Setup - Broke & Hungry</h1>
    
    <?php if ($message): ?>
        <div class="status <?= strpos($message, 'Fehler') === 0 ? 'error' : 'success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="status">
        <h2>Aktueller Status</h2>
        <?php if ($status): ?>
            <table>
                <tr><th>Datenbanktyp</th><td><?= htmlspecialchars($status['type']) ?></td></tr>
                <tr><th>Initialisiert</th><td><?= $status['initialized'] ? '✅ Ja' : '❌ Nein' ?></td></tr>
                <tr><th>Anzahl Tabellen</th><td><?= count($status['tables']) ?></td></tr>
                <?php if ($status['error']): ?>
                <tr><th>Fehler</th><td class="error"><?= htmlspecialchars($status['error']) ?></td></tr>
                <?php endif; ?>
            </table>
            
            <?php if (!empty($status['tables'])): ?>
                <h3>Vorhandene Tabellen</h3>
                <ul>
                    <?php foreach ($status['tables'] as $table): ?>
                        <li><?= htmlspecialchars($table) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php else: ?>
            <p class="error">Status konnte nicht abgerufen werden.</p>
        <?php endif; ?>
    </div>

    <div class="status">
        <h2>Aktionen</h2>
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="status">
            <button type="submit">Status aktualisieren</button>
        </form>
        
        <form method="post" style="display: inline;" onsubmit="return confirm('Möchten Sie die Datenbank wirklich (neu) initialisieren? Alle vorhandenen Daten gehen verloren!')">
            <input type="hidden" name="action" value="initialize">
            <button type="submit" class="danger">Datenbank initialisieren</button>
        </form>
    </div>

    <div class="status warning">
        <h3>⚠️ Wichtige Hinweise</h3>
        <ul>
            <li><strong>SQLite:</strong> Die Datenbankdatei wird automatisch mit .sqlite-Erweiterung erstellt</li>
            <li><strong>MySQL:</strong> Stellen Sie sicher, dass die Datenbankverbindung korrekt konfiguriert ist</li>
            <li><strong>Sicherheit:</strong> Löschen Sie diese Datei nach der Einrichtung aus Sicherheitsgründen</li>
            <li><strong>Daten:</strong> Die Initialisierung löscht alle vorhandenen Daten und erstellt Beispieldaten</li>
        </ul>
    </div>

    <div class="status">
        <h3>Konfiguration</h3>
        <p><strong>Datenbanktyp:</strong> <?= USE_MYSQL ? 'MySQL' : 'SQLite' ?></p>
        <p><small>Ändern Sie die Konfiguration in <code>php/config.php</code></small></p>
    </div>

    <p><a href="index.php">← Zurück zur Anwendung</a></p>
</body>
</html>
