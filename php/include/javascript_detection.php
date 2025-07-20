<?php
/**
 * JavaScript-Erkennungssystem für Progressive Enhancement
 */

/**
 * Überprüft, ob JavaScript im Browser verfügbar ist
 * @return bool True wenn JavaScript verfügbar ist
 */
function isJavaScriptEnabled(): bool {
    // Prüfe Cookie-basierte JavaScript-Erkennung
    return isset($_COOKIE['js_enabled']) && $_COOKIE['js_enabled'] === '1';
}

/**
 * Setzt JavaScript-Verfügbarkeit in Session
 * @param bool $enabled JavaScript verfügbar
 */
function setJavaScriptStatus(bool $enabled): void {
    $_SESSION['js_enabled'] = $enabled;
}

/**
 * Holt JavaScript-Status aus Session
 * @return bool JavaScript verfügbar
 */
function getJavaScriptStatus(): bool {
    return isset($_SESSION['js_enabled']) && $_SESSION['js_enabled'] === true;
}

/**
 * Erstellt JavaScript-Code zur Erkennung und Cookie-Setzung
 * @return string JavaScript-Code
 */
function getJavaScriptDetectionCode(): string {
    return "
    <script>
        // JavaScript-Erkennung: Setze Cookie
        document.cookie = 'js_enabled=1; path=/; SameSite=Lax';
        
        // Globale JavaScript-Verfügbarkeits-Variable
        window.jsEnabled = true;
        
        // Session-Status aktualisieren via AJAX
        fetch('index.php?page=setJSStatus&enabled=1', {
            method: 'GET',
            credentials: 'same-origin'
        }).catch(() => {
            // Fehler ignorieren - nicht kritisch
        });
    </script>
    ";
}

/**
 * Erstellt Noscript-Fallback für JavaScript-Erkennung
 * @return string Noscript-HTML
 */
function getNoScriptDetectionCode(): string {
    return "
    <noscript>
        <script>
            // Fallback: JavaScript nicht verfügbar
            document.cookie = 'js_enabled=0; path=/; SameSite=Lax';
        </script>
        <style>
            .js-only { display: none !important; }
            .no-js-only { display: block !important; }
        </style>
    </noscript>
    ";
}
?>
