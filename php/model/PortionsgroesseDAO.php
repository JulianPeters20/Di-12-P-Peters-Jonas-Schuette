<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Portionsgroesse.php';

class PortionsgroesseDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT PortionsgroesseID, Angabe FROM Portionsgroesse ORDER BY Angabe");
        $portionsgroessen = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $portionsgroessen[] = new Portionsgroesse(
                (int)$row['PortionsgroesseID'],
                $row['Angabe']
            );
        }
        return $portionsgroessen;
    }

    public function findeNachId(int $id): ?Portionsgroesse {
        $stmt = $this->db->prepare("SELECT PortionsgroesseID, Angabe FROM Portionsgroesse WHERE PortionsgroesseID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Portionsgroesse(
            (int)$row['PortionsgroesseID'],
            $row['Angabe']
        ) : null;
    }

    public function fuegePortionsgroesseHinzu(string $angabe): bool {
        $stmt = $this->db->prepare("INSERT INTO Portionsgroesse (Angabe) VALUES (?)");
        return $stmt->execute([trim($angabe)]);
    }

    public function aktualisierePortionsgroesse(int $id, string $angabe): bool {
        $stmt = $this->db->prepare("UPDATE Portionsgroesse SET Angabe = ? WHERE PortionsgroesseID = ?");
        return $stmt->execute([trim($angabe), $id]);
    }

    public function loeschePortionsgroesse(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Portionsgroesse WHERE PortionsgroesseID = ?");
        return $stmt->execute([$id]);
    }
}