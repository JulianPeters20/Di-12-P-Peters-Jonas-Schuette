<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Zutat.php';

class ZutatDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT ZutatID, Name FROM Zutat ORDER BY Name");
        $zutaten = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $zutaten[] = new Zutat(
                (int)$row['ZutatID'],
                $row['Name']
            );
        }
        return $zutaten;
    }

    public function findeNachId(int $id): ?Zutat {
        $stmt = $this->db->prepare("SELECT ZutatID, Name FROM Zutat WHERE ZutatID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Zutat(
            (int)$row['ZutatID'],
            $row['Name']
        ) : null;
    }

    public function fuegeZutatHinzu(string $name): bool {
        $stmt = $this->db->prepare("INSERT INTO Zutat (Name) VALUES (?)");
        return $stmt->execute([trim($name)]);
    }

    public function aktualisiereZutat(int $id, string $name): bool {
        $stmt = $this->db->prepare("UPDATE Zutat SET Name = ? WHERE ZutatID = ?");
        return $stmt->execute([trim($name), $id]);
    }

    public function loescheZutat(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Zutat WHERE ZutatID = ?");
        return $stmt->execute([$id]);
    }
}