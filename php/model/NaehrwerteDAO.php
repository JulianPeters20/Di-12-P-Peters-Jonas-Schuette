<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Data Access Object für Nährwerte
 * 
 * Verwaltet das Speichern und Abrufen von Nährwerten für Rezepte
 */
class NaehrwerteDAO {
    
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    /**
     * Speichert Nährwerte für ein Rezept
     * 
     * @param int $rezeptID ID des Rezepts
     * @param array $naehrwerte Nährwerte-Array
     * @return bool Erfolg der Operation
     */
    public function speichereNaehrwerte(int $rezeptID, array $naehrwerte): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO RezeptNaehrwerte 
                (RezeptID, Kalorien, Protein, Kohlenhydrate, Fett, Ballaststoffe, Zucker, Natrium, Berechnet_am)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
            ");
            
            return $stmt->execute([
                $rezeptID,
                $naehrwerte['kalorien'] ?? null,
                $naehrwerte['protein'] ?? null,
                $naehrwerte['kohlenhydrate'] ?? null,
                $naehrwerte['fett'] ?? null,
                $naehrwerte['ballaststoffe'] ?? null,
                $naehrwerte['zucker'] ?? null,
                $naehrwerte['natrium'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Fehler beim Speichern der Nährwerte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Holt Nährwerte für ein Rezept
     * 
     * @param int $rezeptID ID des Rezepts
     * @return array|null Nährwerte oder null wenn nicht vorhanden
     */
    public function holeNaehrwerte(int $rezeptID): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT Kalorien, Protein, Kohlenhydrate, Fett, Ballaststoffe, Zucker, Natrium, Berechnet_am
                FROM RezeptNaehrwerte 
                WHERE RezeptID = ?
            ");
            $stmt->execute([$rezeptID]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return null;
            }
            
            return [
                'kalorien' => $row['Kalorien'],
                'protein' => $row['Protein'],
                'kohlenhydrate' => $row['Kohlenhydrate'],
                'fett' => $row['Fett'],
                'ballaststoffe' => $row['Ballaststoffe'],
                'zucker' => $row['Zucker'],
                'natrium' => $row['Natrium'],
                'berechnet_am' => $row['Berechnet_am']
            ];
        } catch (Exception $e) {
            error_log("Fehler beim Abrufen der Nährwerte: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Prüft ob Nährwerte für ein Rezept vorhanden sind
     * 
     * @param int $rezeptID ID des Rezepts
     * @return bool True wenn Nährwerte vorhanden sind
     */
    public function hatNaehrwerte(int $rezeptID): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM RezeptNaehrwerte 
                WHERE RezeptID = ?
            ");
            $stmt->execute([$rezeptID]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($row['count'] ?? 0) > 0;
        } catch (Exception $e) {
            error_log("Fehler beim Prüfen der Nährwerte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Löscht Nährwerte für ein Rezept
     *
     * @param int $rezeptID ID des Rezepts
     * @return bool Erfolg der Operation
     */
    public function loescheNaehrwerte(int $rezeptID): bool {
        try {
            $this->db->beginTransaction();

            // Nährwerte aus Haupttabelle löschen
            $stmt = $this->db->prepare("DELETE FROM RezeptNaehrwerte WHERE RezeptID = ?");
            $erfolg = $stmt->execute([$rezeptID]);

            // Auch zugehörige Cache-Einträge löschen
            // (Cache-Keys enthalten Rezept-Zutaten, daher schwer zu identifizieren)
            // Wir löschen alte Cache-Einträge generell
            $this->bereinigeAltenCache();

            $this->db->commit();
            return $erfolg;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Fehler beim Löschen der Nährwerte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Holt alle Rezepte mit Nährwerten
     * 
     * @return array Array mit Rezept-IDs die Nährwerte haben
     */
    public function holeRezepteWithNaehrwerte(): array {
        try {
            $stmt = $this->db->query("SELECT RezeptID FROM RezeptNaehrwerte ORDER BY Berechnet_am DESC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Fehler beim Abrufen der Rezepte mit Nährwerten: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bereinigt alte Cache-Einträge (älter als 30 Tage)
     */
    public function bereinigeAltenCache(): void {
        try {
            $this->db->exec("
                DELETE FROM api_cache 
                WHERE erstellt_am < datetime('now', '-30 days')
            ");
        } catch (Exception $e) {
            error_log("Fehler beim Bereinigen des Caches: " . $e->getMessage());
        }
    }
}