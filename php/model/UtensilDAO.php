<?php
require_once __DIR__ . '/../data/pdo.php';

class UtensilDAO {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT UtensilID, Name FROM Utensil ORDER BY Name");
        $erg = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $erg[(int)$row['UtensilID']] = $row['Name'];
        }
        return $erg;
    }
}