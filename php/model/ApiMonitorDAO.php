<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Data Access Object für API-Monitoring
 * 
 * Sammelt und verwaltet Statistiken über API-Nutzung und Performance
 */
class ApiMonitorDAO {
    
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    

    
    /**
     * Loggt einen API-Aufruf
     */
    public function loggeApiAufruf(string $endpoint, string $status, float $responseTime = 0, ?string $errorMessage = null): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO api_log (endpoint, status, response_time, error_message, created_at)
                VALUES (?, ?, ?, ?, datetime('now'))
            ");
            $stmt->execute([$endpoint, $status, $responseTime, $errorMessage]);
        } catch (Exception $e) {
            error_log("Fehler beim Loggen des API-Aufrufs: " . $e->getMessage());
        }
    }
    
    /**
     * Holt API-Statistiken für heute
     */
    public function getHeutigeStatistiken(): array {
        $heute = date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as gesamt_aufrufe,
                COUNT(CASE WHEN status = 'success' THEN 1 END) as erfolgreiche_aufrufe,
                COUNT(CASE WHEN status = 'error' THEN 1 END) as fehlerhafte_aufrufe,
                AVG(response_time) as durchschnittliche_antwortzeit
            FROM api_log 
            WHERE date(created_at) = ?
        ");
        $stmt->execute([$heute]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'gesamt_aufrufe' => 0,
            'erfolgreiche_aufrufe' => 0,
            'fehlerhafte_aufrufe' => 0,
            'durchschnittliche_antwortzeit' => 0
        ];
    }
    
    /**
     * Holt Gesamt-API-Statistiken
     */
    public function getGesamtStatistiken(): array {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as gesamt_aufrufe,
                COUNT(CASE WHEN status = 'success' THEN 1 END) as erfolgreiche_aufrufe,
                COUNT(CASE WHEN status = 'error' THEN 1 END) as fehlerhafte_aufrufe,
                AVG(response_time) as durchschnittliche_antwortzeit
            FROM api_log
        ");
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'gesamt_aufrufe' => 0,
            'erfolgreiche_aufrufe' => 0,
            'fehlerhafte_aufrufe' => 0,
            'durchschnittliche_antwortzeit' => 0
        ];
    }
    
    /**
     * Holt Cache-Statistiken
     */
    public function getCacheStatistiken(): array {
        // Cache-Treffer aus api_cache Tabelle
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as cache_eintraege,
                COUNT(CASE WHEN erstellt_am > datetime('now', '-24 hours') THEN 1 END) as cache_heute
            FROM api_cache
        ");
        $cacheStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // API-Aufrufe vs Cache-Hits schätzen
        $heutigeStats = $this->getHeutigeStatistiken();
        $cacheHitRate = 0;
        
        if ($heutigeStats['gesamt_aufrufe'] > 0) {
            $geschaetzteCacheHits = max(0, $cacheStats['cache_heute'] - $heutigeStats['gesamt_aufrufe']);
            $cacheHitRate = round(($geschaetzteCacheHits / ($heutigeStats['gesamt_aufrufe'] + $geschaetzteCacheHits)) * 100, 1);
        }
        
        return [
            'cache_eintraege' => $cacheStats['cache_eintraege'] ?? 0,
            'cache_heute' => $cacheStats['cache_heute'] ?? 0,
            'cache_hit_rate' => $cacheHitRate
        ];
    }
    
    /**
     * Holt Rezepte mit Nährwerten
     */
    public function getRezepteMitNaehrwerten(): array {
        $stmt = $this->db->query("
            SELECT 
                r.RezeptID,
                r.Titel,
                r.Erstellungsdatum,
                n.Berechnet_am,
                n.Kalorien,
                nu.Benutzername as Ersteller
            FROM Rezept r
            JOIN RezeptNaehrwerte n ON r.RezeptID = n.RezeptID
            LEFT JOIN Nutzer nu ON r.ErstellerID = nu.NutzerID
            ORDER BY n.Berechnet_am DESC
            LIMIT 20
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Holt letzte API-Fehler
     */
    public function getLetzteApiFehler(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT 
                endpoint,
                error_message,
                created_at,
                response_time
            FROM api_log 
            WHERE status = 'error' AND error_message IS NOT NULL
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Berechnet API-Limit-Status (basierend auf Spoonacular Free Plan)
     */
    public function getApiLimitStatus(): array {
        $heute = date('Y-m-d');
        $heutigeAufrufe = $this->getHeutigeStatistiken()['gesamt_aufrufe'];
        
        // Spoonacular Free Plan: 150 Anfragen/Tag
        $tagesLimit = 150;
        $verbraucht = $heutigeAufrufe;
        $verfuegbar = max(0, $tagesLimit - $verbraucht);
        $prozentVerbraucht = round(($verbraucht / $tagesLimit) * 100, 1);
        
        return [
            'tages_limit' => $tagesLimit,
            'verbraucht' => $verbraucht,
            'verfuegbar' => $verfuegbar,
            'prozent_verbraucht' => $prozentVerbraucht,
            'status' => $prozentVerbraucht > 90 ? 'kritisch' : ($prozentVerbraucht > 70 ? 'warnung' : 'ok')
        ];
    }
    
    /**
     * Bereinigt alte Log-Einträge (älter als 30 Tage)
     */
    public function bereinigeAlteLogEintraege(): int {
        $stmt = $this->db->prepare("
            DELETE FROM api_log 
            WHERE created_at < datetime('now', '-30 days')
        ");
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    /**
     * Holt API-Nutzung der letzten 7 Tage für Chart
     */
    public function getWoechlicheNutzung(): array {
        $stmt = $this->db->query("
            SELECT 
                date(created_at) as datum,
                COUNT(*) as aufrufe,
                COUNT(CASE WHEN status = 'success' THEN 1 END) as erfolg,
                COUNT(CASE WHEN status = 'error' THEN 1 END) as fehler
            FROM api_log 
            WHERE created_at >= datetime('now', '-7 days')
            GROUP BY date(created_at)
            ORDER BY datum DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
