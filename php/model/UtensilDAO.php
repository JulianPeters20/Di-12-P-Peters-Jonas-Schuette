<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Utensil.php';

class UtensilDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT UtensilID, Name FROM Utensil ORDER BY Name");
        $utensilien = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $utensilien[] = new Utensil(
                (int)$row['UtensilID'],
                $row['Name']
            );
        }
        return $utensilien;
    }

    public function findeNachId(int $id): ?Utensil {
        $stmt = $this->db->prepare("SELECT UtensilID, Name FROM Utensil WHERE UtensilID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Utensil(
            (int)$row['UtensilID'],
            $row['Name']
        ) : null;
    }

    public function fuegeUtensilHinzu(string $name): bool {
        $stmt = $this->db->prepare("INSERT INTO Utensil (Name) VALUES (?)");
        return $stmt->execute([trim($name)]);
    }

    public function aktualisiereUtensil(int $id, string $name): bool {
        $stmt = $this->db->prepare("UPDATE Utensil SET Name = ? WHERE UtensilID = ?");
        return $stmt->execute([trim($name), $id]);
    }

    public function loescheUtensil(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Utensil WHERE UtensilID = ?");
        return $stmt->execute([$id]);
    }
}