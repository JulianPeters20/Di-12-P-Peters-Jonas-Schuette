<?php
// Template für Nährwerte-Anzeige
// Wird per AJAX geladen

// Nährwerte aus übergebenen Daten extrahieren
if (!isset($naehrwerte) || !$naehrwerte) {
    echo '<p>Keine Nährwerte verfügbar.</p>';
    return;
}
?>

<div id="naehrwerte-display">
    <div class="naehrwerte-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 15px 0;">
        <div class="naehrwert-item">
            <strong>Kalorien:</strong><br>
            <span class="naehrwert-wert"><?= number_format($naehrwerte['kalorien'], 0) ?> kcal</span>
        </div>
        <div class="naehrwert-item">
            <strong>Protein:</strong><br>
            <span class="naehrwert-wert"><?= number_format($naehrwerte['protein'], 1) ?> g</span>
        </div>
        <div class="naehrwert-item">
            <strong>Kohlenhydrate:</strong><br>
            <span class="naehrwert-wert"><?= number_format($naehrwerte['kohlenhydrate'], 1) ?> g</span>
        </div>
        <div class="naehrwert-item">
            <strong>Fett:</strong><br>
            <span class="naehrwert-wert"><?= number_format($naehrwerte['fett'], 1) ?> g</span>
        </div>
        <div class="naehrwert-item">
            <strong>Ballaststoffe:</strong><br>
            <span class="naehrwert-wert"><?= number_format($naehrwerte['ballaststoffe'], 1) ?> g</span>
        </div>
        <div class="naehrwert-item">
            <strong>Zucker:</strong><br>
            <span class="naehrwert-wert"><?= number_format($naehrwerte['zucker'], 1) ?> g</span>
        </div>
    </div>
    <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
        Berechnet am: <?= date('d.m.Y', strtotime($naehrwerte['berechnet_am'])) ?>
    </p>
</div>
