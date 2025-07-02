<?php
declare(strict_types=1);

require_once 'php/model/ApiMonitorDAO.php';
require_once 'php/model/NaehrwerteDAO.php';

/**
 * Admin-Controller für API-Monitoring
 * 
 * Basiert auf dem gleichen Pattern wie NutzerController::showNutzerListe()
 */

/**
 * API-Monitor anzeigen (nur Admin)
 */
function showApiMonitor(): void {
    // Zugriffskontrolle - gleich wie bei showNutzerListe()
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        $_SESSION["message"] = "Nur Administratoren dürfen den API-Monitor sehen.";
        header("Location: index.php");
        exit;
    }
    
    try {
        $apiMonitorDAO = new ApiMonitorDAO();
        
        // Alle Statistiken sammeln
        $heutigeStats = $apiMonitorDAO->getHeutigeStatistiken();
        $gesamtStats = $apiMonitorDAO->getGesamtStatistiken();
        $cacheStats = $apiMonitorDAO->getCacheStatistiken();
        $rezepteMitNaehrwerten = $apiMonitorDAO->getRezepteMitNaehrwerten();
        $letzteApiFehler = $apiMonitorDAO->getLetzteApiFehler(5);
        $apiLimitStatus = $apiMonitorDAO->getApiLimitStatus();
        $woechlicheNutzung = $apiMonitorDAO->getWoechlicheNutzung();
        
        // Zusätzliche Nährwerte-Statistiken
        $naehrwerteDAO = new NaehrwerteDAO();
        $gesamtRezepteMitNaehrwerten = count($naehrwerteDAO->holeRezepteWithNaehrwerte());
        
        // API-Konfiguration prüfen
        require_once 'php/config/api-config.php';
        $apiKonfiguriert = isApiConfigured();
        
        // View laden - gleiche Struktur wie nutzerliste.php
        require_once 'php/view/api-monitor.php';
        
    } catch (Exception $e) {
        error_log("Fehler im API-Monitor: " . $e->getMessage());
        $_SESSION["message"] = "Fehler beim Laden des API-Monitors.";
        header("Location: index.php");
        exit;
    }
}

/**
 * API-Cache leeren (nur Admin)
 */
function leereApiCache(): void {
    // Zugriffskontrolle
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        $_SESSION["message"] = "Nur Administratoren dürfen den Cache leeren.";
        header("Location: index.php");
        exit;
    }
    
    try {
        $naehrwerteDAO = new NaehrwerteDAO();
        $naehrwerteDAO->bereinigeAltenCache();
        
        // Zusätzlich: Alle Cache-Einträge löschen
        $db = Database::getConnection();
        $stmt = $db->exec("DELETE FROM api_cache");
        
        $_SESSION["message"] = "API-Cache erfolgreich geleert.";
        
    } catch (Exception $e) {
        error_log("Fehler beim Leeren des API-Caches: " . $e->getMessage());
        $_SESSION["message"] = "Fehler beim Leeren des API-Caches.";
    }
    
    header("Location: index.php?page=api-monitor");
    exit;
}

/**
 * API-Logs bereinigen (nur Admin)
 */
function bereinigeApiLogs(): void {
    // Zugriffskontrolle
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        $_SESSION["message"] = "Nur Administratoren dürfen Logs bereinigen.";
        header("Location: index.php");
        exit;
    }
    
    try {
        $apiMonitorDAO = new ApiMonitorDAO();
        $geloeschteEintraege = $apiMonitorDAO->bereinigeAlteLogEintraege();
        
        $_SESSION["message"] = "API-Logs bereinigt. $geloeschteEintraege alte Einträge entfernt.";
        
    } catch (Exception $e) {
        error_log("Fehler beim Bereinigen der API-Logs: " . $e->getMessage());
        $_SESSION["message"] = "Fehler beim Bereinigen der API-Logs.";
    }
    
    header("Location: index.php?page=api-monitor");
    exit;
}

/**
 * API-Test durchführen (nur Admin)
 */
function testeApi(): void {
    // Zugriffskontrolle
    if (empty($_SESSION['istAdmin']) || !$_SESSION['istAdmin']) {
        echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        exit;
    }
    
    // JSON-Header setzen
    header('Content-Type: application/json');
    
    try {
        require_once 'php/model/SpoonacularAPI.php';
        require_once 'php/config/api-config.php';
        
        if (!isApiConfigured()) {
            echo json_encode(['success' => false, 'error' => 'API nicht konfiguriert']);
            exit;
        }
        
        $startTime = microtime(true);
        $api = new SpoonacularAPI(getSpoonacularApiKey());
        $verfuegbar = $api->istAPIVerfuegbar();
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Test-Ergebnis loggen
        $apiMonitorDAO = new ApiMonitorDAO();
        $apiMonitorDAO->loggeApiAufruf(
            'test-endpoint', 
            $verfuegbar ? 'success' : 'error', 
            $responseTime,
            $verfuegbar ? null : 'API nicht erreichbar'
        );
        
        echo json_encode([
            'success' => $verfuegbar,
            'response_time' => $responseTime,
            'message' => $verfuegbar ? 'API ist erreichbar' : 'API ist nicht erreichbar'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}
