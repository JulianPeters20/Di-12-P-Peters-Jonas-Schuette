<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Preisklasse.php';

class PreisklasseDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT PreisklasseID, Preisspanne FROM Preisklasse ORDER BY Preisspanne");
        $preisklassen = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $preisklassen[] = new Preisklasse(
                (int)$row['PreisklasseID'],
                $row['Preisspanne']
            );
        }
        return $preisklassen;
    }

    public function findeNachId(int $id): ?Preisklasse {
        $stmt = $this->db->prepare("SELECT PreisklasseID, Preisspanne FROM Preisklasse WHERE PreisklasseID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Preisklasse(
            (int)$row['PreisklasseID'],
            $row['Preisspanne']
        ) : null;
    }

    public function fuegePreisklasseHinzu(string $preisspanne): bool {
        $stmt = $this->db->prepare("INSERT INTO Preisklasse (Preisspanne) VALUES (?)");
        return $stmt->execute([trim($preisspanne)]);
    }

    public function aktualisierePreisklasse(int $id, string $preisspanne): bool {
        $stmt = $this->db->prepare("UPDATE Preisklasse SET Preisspanne = ? WHERE PreisklasseID = ?");
        return $stmt->execute([trim($preisspanne), $id]);
    }

    public function loeschePreisklasse(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Preisklasse WHERE PreisklasseID = ?");
        return $stmt->execute([$id]);
    }
}