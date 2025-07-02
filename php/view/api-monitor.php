<main>
    <h2>API-Monitor</h2>
    <div>Hinweis: Diese Seite ist nur für Administratoren vorgesehen.</div>

    <!-- Flash-Nachrichten anzeigen -->
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="flash-message" style="margin: 20px 0; padding: 15px; border-radius: 5px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- API-Status und Schnellaktionen -->
    <div class="api-status-bar" style="display: flex; gap: 15px; margin: 20px 0; flex-wrap: wrap;">
        <div class="status-item" style="background: <?= $apiKonfiguriert ? '#d4edda' : '#f8d7da' ?>; padding: 10px 15px; border-radius: 5px; border: 1px solid <?= $apiKonfiguriert ? '#c3e6cb' : '#f5c6cb' ?>;">
            <strong>API-Status:</strong> <?= $apiKonfiguriert ? '✅ Konfiguriert' : '❌ Nicht konfiguriert' ?>
        </div>
        
        <div class="status-item" style="background: <?= $apiLimitStatus['status'] === 'ok' ? '#d4edda' : ($apiLimitStatus['status'] === 'warnung' ? '#fff3cd' : '#f8d7da') ?>; padding: 10px 15px; border-radius: 5px;">
            <strong>Tages-Limit:</strong> <?= $apiLimitStatus['verbraucht'] ?>/<?= $apiLimitStatus['tages_limit'] ?> (<?= $apiLimitStatus['prozent_verbraucht'] ?>%)
        </div>
        
        <div class="actions" style="margin-left: auto;">
            <button onclick="testeAPI()" class="btn" style="margin-right: 10px;">API testen</button>
            <a href="index.php?page=api-cache-leeren" class="btn" onclick="return confirm('Cache wirklich leeren?')">Cache leeren</a>
        </div>
    </div>

    <!-- Statistik-Karten -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
        
        <!-- Heutige Statistiken -->
        <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
            <h3 style="margin-top: 0; color: #495057;">📊 Heute</h3>
            <div class="stat-item">
                <strong>Gesamt-Aufrufe:</strong> <?= number_format($heutigeStats['gesamt_aufrufe']) ?>
            </div>
            <div class="stat-item">
                <strong>Erfolgreich:</strong> <span style="color: #28a745;"><?= number_format($heutigeStats['erfolgreiche_aufrufe']) ?></span>
            </div>
            <div class="stat-item">
                <strong>Fehler:</strong> <span style="color: #dc3545;"><?= number_format($heutigeStats['fehlerhafte_aufrufe']) ?></span>
            </div>
            <div class="stat-item">
                <strong>Ø Antwortzeit:</strong> <?= number_format($heutigeStats['durchschnittliche_antwortzeit'] ?? 0, 2) ?>ms
            </div>
        </div>

        <!-- Gesamt-Statistiken -->
        <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
            <h3 style="margin-top: 0; color: #495057;">📈 Gesamt</h3>
            <div class="stat-item">
                <strong>Gesamt-Aufrufe:</strong> <?= number_format($gesamtStats['gesamt_aufrufe']) ?>
            </div>
            <div class="stat-item">
                <strong>Erfolgreich:</strong> <span style="color: #28a745;"><?= number_format($gesamtStats['erfolgreiche_aufrufe']) ?></span>
            </div>
            <div class="stat-item">
                <strong>Fehler:</strong> <span style="color: #dc3545;"><?= number_format($gesamtStats['fehlerhafte_aufrufe']) ?></span>
            </div>
            <div class="stat-item">
                <strong>Ø Antwortzeit:</strong> <?= number_format($gesamtStats['durchschnittliche_antwortzeit'] ?? 0, 2) ?>ms
            </div>
        </div>

        <!-- Cache-Statistiken -->
        <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
            <h3 style="margin-top: 0; color: #495057;">💾 Cache</h3>
            <div class="stat-item">
                <strong>Cache-Einträge:</strong> <?= number_format($cacheStats['cache_eintraege']) ?>
            </div>
            <div class="stat-item">
                <strong>Heute erstellt:</strong> <?= number_format($cacheStats['cache_heute']) ?>
            </div>
            <div class="stat-item">
                <strong>Hit-Rate:</strong> <span style="color: #28a745;"><?= $cacheStats['cache_hit_rate'] ?>%</span>
            </div>
            <div class="stat-item">
                <strong>Nährwerte-Rezepte:</strong> <?= number_format($gesamtRezepteMitNaehrwerten) ?>
            </div>
        </div>

    </div>

    <!-- Wöchliche Nutzung -->
    <?php if (!empty($woechlicheNutzung)): ?>
    <div class="weekly-usage" style="margin: 30px 0;">
        <h3>📅 Nutzung der letzten 7 Tage</h3>
        <div class="usage-chart" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #e9ecef;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">Datum</th>
                        <th style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">Aufrufe</th>
                        <th style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">Erfolg</th>
                        <th style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">Fehler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($woechlicheNutzung as $tag): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #dee2e6;"><?= date('d.m.Y', strtotime($tag['datum'])) ?></td>
                        <td style="padding: 8px; text-align: center; border: 1px solid #dee2e6;"><?= $tag['aufrufe'] ?></td>
                        <td style="padding: 8px; text-align: center; border: 1px solid #dee2e6; color: #28a745;"><?= $tag['erfolg'] ?></td>
                        <td style="padding: 8px; text-align: center; border: 1px solid #dee2e6; color: #dc3545;"><?= $tag['fehler'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Rezepte mit Nährwerten -->
    <div class="nutrition-recipes" style="margin: 30px 0;">
        <h3>🍽️ Rezepte mit berechneten Nährwerten (letzte 20)</h3>
        <?php if (!empty($rezepteMitNaehrwerten)): ?>
        <div class="recipe-list" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #e9ecef;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">Rezept</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">Ersteller</th>
                        <th style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">Kalorien</th>
                        <th style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">Berechnet am</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rezepteMitNaehrwerten as $rezept): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #dee2e6;">
                            <a href="index.php?page=rezept&id=<?= $rezept['RezeptID'] ?>" style="color: #007bff; text-decoration: none;">
                                <?= htmlspecialchars($rezept['Titel']) ?>
                            </a>
                        </td>
                        <td style="padding: 8px; border: 1px solid #dee2e6;"><?= htmlspecialchars($rezept['Ersteller'] ?? 'Unbekannt') ?></td>
                        <td style="padding: 8px; text-align: center; border: 1px solid #dee2e6;"><?= number_format($rezept['Kalorien'], 0) ?> kcal</td>
                        <td style="padding: 8px; text-align: center; border: 1px solid #dee2e6;"><?= date('d.m.Y H:i', strtotime($rezept['Berechnet_am'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p style="color: #6c757d; font-style: italic;">Noch keine Rezepte mit berechneten Nährwerten vorhanden.</p>
        <?php endif; ?>
    </div>

    <!-- Letzte API-Fehler -->
    <?php if (!empty($letzteApiFehler)): ?>
    <div class="api-errors" style="margin: 30px 0;">
        <h3>⚠️ Letzte API-Fehler</h3>
        <div class="error-list" style="background: #fff3cd; padding: 20px; border-radius: 8px; border: 1px solid #ffeaa7;">
            <?php foreach ($letzteApiFehler as $fehler): ?>
            <div class="error-item" style="margin-bottom: 10px; padding: 10px; background: #fff; border-radius: 4px; border-left: 4px solid #dc3545;">
                <div><strong>Endpoint:</strong> <?= htmlspecialchars($fehler['endpoint']) ?></div>
                <div><strong>Fehler:</strong> <?= htmlspecialchars($fehler['error_message']) ?></div>
                <div><strong>Zeit:</strong> <?= date('d.m.Y H:i:s', strtotime($fehler['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Admin-Aktionen -->
    <div class="admin-actions" style="margin: 30px 0; padding: 20px; background: #e9ecef; border-radius: 8px;">
        <h3>🔧 Admin-Aktionen</h3>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="index.php?page=api-logs-bereinigen" class="btn" onclick="return confirm('Alte API-Logs wirklich bereinigen?')">
                Logs bereinigen (>30 Tage)
            </a>
            <button onclick="window.location.reload()" class="btn">
                Seite aktualisieren
            </button>
        </div>
    </div>

</main>

<script>
function testeAPI() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Teste...';
    button.disabled = true;
    
    fetch('index.php?page=api-test', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        const message = data.success 
            ? `✅ ${data.message} (${data.response_time}ms)`
            : `❌ ${data.error}`;
        
        alert(message);
        
        button.textContent = originalText;
        button.disabled = false;
    })
    .catch(error => {
        alert('❌ Fehler beim API-Test: ' + error.message);
        button.textContent = originalText;
        button.disabled = false;
    });
}
</script>
