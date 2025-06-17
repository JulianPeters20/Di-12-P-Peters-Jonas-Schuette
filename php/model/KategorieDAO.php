<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Kategorie.php';

class KategorieDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT KategorieID, Bezeichnung FROM Kategorie ORDER BY Bezeichnung");
        $kategorien = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $kategorien[] = new Kategorie(
                (int)$row['KategorieID'],
                $row['Bezeichnung']
            );
        }
        return $kategorien;
    }

    public function findeNachId(int $id): ?Kategorie {
        $stmt = $this->db->prepare("SELECT KategorieID, Bezeichnung FROM Kategorie WHERE KategorieID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Kategorie(
            (int)$row['KategorieID'],
            $row['Bezeichnung']
        ) : null;
    }

    public function fuegeKategorieHinzu(string $bezeichnung): bool {
        $stmt = $this->db->prepare("INSERT INTO Kategorie (Bezeichnung) VALUES (?)");
        return $stmt->execute([trim($bezeichnung)]);
    }

    public function aktualisiereKategorie(int $id, string $bezeichnung): bool {
        $stmt = $this->db->prepare("UPDATE Kategorie SET Bezeichnung = ? WHERE KategorieID = ?");
        return $stmt->execute([trim($bezeichnung), $id]);
    }

    public function loescheKategorie(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Kategorie WHERE KategorieID = ?");
        return $stmt->execute([$id]);
    }
}