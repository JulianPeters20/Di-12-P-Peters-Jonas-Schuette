<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Bewertung.php';

class BewertungDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Alle Bewertungen laden
    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT RezeptID, NutzerID, Punkte, Bewertungsdatum FROM Bewertung ORDER BY Bewertungsdatum DESC");
        $bewertungen = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bewertungen[] = new Bewertung(
                (int)$row['RezeptID'],
                (int)$row['NutzerID'],
                (int)$row['Punkte'],
                $row['Bewertungsdatum']
            );
        }
        return $bewertungen;
    }

    // Bewertung eines Nutzers für ein bestimmtes Rezept finden
    public function findeNachRezeptUndNutzer(int $rezeptID, int $nutzerID): ?Bewertung {
        $stmt = $this->db->prepare("SELECT RezeptID, NutzerID, Punkte, Bewertungsdatum FROM Bewertung WHERE RezeptID = ? AND NutzerID = ?");
        $stmt->execute([$rezeptID, $nutzerID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Bewertung(
            (int)$row['RezeptID'],
            (int)$row['NutzerID'],
            (int)$row['Punkte'],
            $row['Bewertungsdatum']
        ) : null;
    }

    // Bewertung hinzufügen (wenn noch keine Bewertung von Nutzer für Rezept existiert)
    public function fuegeBewertungHinzu(Bewertung $bewertung): bool {
        $stmt = $this->db->prepare("INSERT INTO Bewertung (RezeptID, NutzerID, Punkte, Bewertungsdatum) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $bewertung->RezeptID,
            $bewertung->NutzerID,
            $bewertung->Punkte,
            $bewertung->Bewertungsdatum
        ]);
    }

    // Bewertung aktualisieren (wenn bereits vorhanden)
    public function aktualisiereBewertung(Bewertung $bewertung): bool {
        $stmt = $this->db->prepare("UPDATE Bewertung SET Punkte = ?, Bewertungsdatum = ? WHERE RezeptID = ? AND NutzerID = ?");
        return $stmt->execute([
            $bewertung->Punkte,
            $bewertung->Bewertungsdatum,
            $bewertung->RezeptID,
            $bewertung->NutzerID
        ]);
    }

    // Bewertung löschen
    public function loescheBewertung(int $rezeptID, int $nutzerID): bool {
        $stmt = $this->db->prepare("DELETE FROM Bewertung WHERE RezeptID = ? AND NutzerID = ?");
        return $stmt->execute([$rezeptID, $nutzerID]);
    }

    // Durchschnittliche Bewertung für ein Rezept berechnen
    public function berechneDurchschnittRating(int $rezeptID): ?float {
        $stmt = $this->db->prepare("SELECT AVG(Punkte) as Durchschnitt FROM Bewertung WHERE RezeptID = ?");
        $stmt->execute([$rezeptID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['Durchschnitt'] !== null) {
            return (float) $row['Durchschnitt'];
        }
        return null;
    }

    public function zaehleBewertungen(int $rezeptID): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS anzahl FROM Bewertung WHERE RezeptID = ?");
        $stmt->execute([$rezeptID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['anzahl'] : 0;
    }

    // Flag damit der Nutzer sein eigenes Rezept nicht bewerten kann
    public function istEigenerErsteller(int $rezeptID, int $nutzerID): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM Rezept WHERE RezeptID = ? AND ErstellerID = ?");
        $stmt->execute([$rezeptID, $nutzerID]);
        return (bool) $stmt->fetchColumn();
    }
}