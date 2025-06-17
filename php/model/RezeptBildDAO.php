<?php
class RezeptBildDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function fuegeBildHinzu(int $rezeptId, string $pfad): bool {
        $stmt = $this->db->prepare("INSERT INTO RezeptBild (RezeptID, Pfad, ErstelltAm) VALUES (?, ?, date('now'))");
        return $stmt->execute([$rezeptId, $pfad]);
    }

    public function findeBilderNachRezeptId(int $rezeptId): array {
        $stmt = $this->db->prepare("SELECT * FROM RezeptBild WHERE RezeptID = ?");
        $stmt->execute([$rezeptId]);
        $bilder = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bilder[] = new RezeptBild((int)$row['ID'], (int)$row['RezeptID'], $row['Pfad'], $row['ErstelltAm']);
        }
        return $bilder;
    }
}