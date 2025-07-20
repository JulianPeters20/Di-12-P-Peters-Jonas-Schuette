<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Data Access Object für gespeicherte Rezepte (Nutzer-Favoriten)
 * Verwaltet alle Datenbankoperationen für die GespeicherteRezepte-Tabelle
 */
class GespeicherteRezepteDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Prüft ob ein Rezept bereits von einem Nutzer gespeichert wurde
     * 
     * @param int $nutzerId ID des Nutzers
     * @param int $rezeptId ID des Rezepts
     * @return bool true wenn das Rezept gespeichert ist, false sonst
     */
    public function istGespeichert(int $nutzerId, int $rezeptId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM GespeicherteRezepte WHERE NutzerID = ? AND RezeptID = ?");
        $stmt->execute([$nutzerId, $rezeptId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Speichert ein Rezept für einen Nutzer
     * 
     * @param int $nutzerId ID des Nutzers
     * @param int $rezeptId ID des Rezepts
     * @return bool true bei Erfolg, false bei Fehler
     */
    public function speichereRezept(int $nutzerId, int $rezeptId): bool {
        try {
            // Prüfen ob bereits gespeichert
            if ($this->istGespeichert($nutzerId, $rezeptId)) {
                return true; // Bereits gespeichert, kein Fehler
            }

            $stmt = $this->db->prepare("INSERT INTO GespeicherteRezepte (NutzerID, RezeptID, GespeichertAm) VALUES (?, ?, datetime('now'))");
            return $stmt->execute([$nutzerId, $rezeptId]);
        } catch (Exception $e) {
            error_log("Fehler beim Speichern des Rezepts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Entfernt ein gespeichertes Rezept für einen Nutzer
     * 
     * @param int $nutzerId ID des Nutzers
     * @param int $rezeptId ID des Rezepts
     * @return bool true bei Erfolg, false bei Fehler
     */
    public function entferneRezept(int $nutzerId, int $rezeptId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM GespeicherteRezepte WHERE NutzerID = ? AND RezeptID = ?");
            return $stmt->execute([$nutzerId, $rezeptId]);
        } catch (Exception $e) {
            error_log("Fehler beim Entfernen des gespeicherten Rezepts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Findet alle gespeicherten Rezepte eines Nutzers mit vollständigen Rezept-Informationen
     * 
     * @param int $nutzerId ID des Nutzers
     * @return array Array mit gespeicherten Rezepten inklusive Rezept-Details
     */
    public function findeGespeicherteRezepte(int $nutzerId): array {
        try {
            $sql = "
                SELECT 
                    gr.GespeichertAm,
                    r.RezeptID,
                    r.Titel,
                    r.Zubereitung,
                    r.BildPfad,
                    r.ErstellerID,
                    r.PreisklasseID,
                    r.PortionsgroesseID,
                    r.Erstellungsdatum,
                    n.Benutzername AS erstellerName,
                    n.Email AS erstellerEmail,
                    pk.Preisspanne AS preisklasseName,
                    pg.Angabe AS portionsgroesseName
                FROM GespeicherteRezepte gr
                INNER JOIN Rezept r ON gr.RezeptID = r.RezeptID
                LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
                LEFT JOIN Preisklasse pk ON r.PreisklasseID = pk.PreisklasseID
                LEFT JOIN Portionsgroesse pg ON r.PortionsgroesseID = pg.PortionsgroesseID
                WHERE gr.NutzerID = ?
                ORDER BY gr.GespeichertAm DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nutzerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Fehler beim Laden der gespeicherten Rezepte: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Zählt die Anzahl der gespeicherten Rezepte eines Nutzers
     * 
     * @param int $nutzerId ID des Nutzers
     * @return int Anzahl der gespeicherten Rezepte
     */
    public function zaehleGespeicherteRezepte(int $nutzerId): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM GespeicherteRezepte WHERE NutzerID = ?");
            $stmt->execute([$nutzerId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Fehler beim Zählen der gespeicherten Rezepte: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Findet alle Nutzer die ein bestimmtes Rezept gespeichert haben
     * 
     * @param int $rezeptId ID des Rezepts
     * @return array Array mit Nutzer-IDs die das Rezept gespeichert haben
     */
    public function findeNutzerMitGespeichertemRezept(int $rezeptId): array {
        try {
            $stmt = $this->db->prepare("SELECT NutzerID FROM GespeicherteRezepte WHERE RezeptID = ?");
            $stmt->execute([$rezeptId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Fehler beim Finden der Nutzer mit gespeichertem Rezept: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Entfernt alle gespeicherten Rezepte eines Nutzers (z.B. bei Nutzer-Löschung)
     * 
     * @param int $nutzerId ID des Nutzers
     * @return bool true bei Erfolg, false bei Fehler
     */
    public function entferneAlleGespeichertenRezepte(int $nutzerId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM GespeicherteRezepte WHERE NutzerID = ?");
            return $stmt->execute([$nutzerId]);
        } catch (Exception $e) {
            error_log("Fehler beim Entfernen aller gespeicherten Rezepte: " . $e->getMessage());
            return false;
        }
    }
}
