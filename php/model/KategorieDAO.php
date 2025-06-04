<?php
require_once __DIR__ . '/../config.php';

class KategorieDAO {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT KategorieID, Bezeichnung FROM Kategorie ORDER BY Bezeichnung");
        $erg = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $erg[(int)$row['KategorieID']] = $row['Bezeichnung'];
        }
        return $erg;
    }
}
