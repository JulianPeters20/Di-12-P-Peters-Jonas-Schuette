<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Rezept.php';

class RezeptDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeAlle(): array {
        $sql = "
            SELECT 
                r.*, 
                n.Benutzername AS erstellerName, 
                n.Email AS erstellerEmail,
                pk.Preisspanne AS preisklasseName,
                pg.Angabe AS portionsgroesseName
            FROM Rezept r
            LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
            LEFT JOIN Preisklasse pk ON r.PreisklasseID = pk.PreisklasseID
            LEFT JOIN Portionsgroesse pg ON r.PortionsgroesseID = pg.PortionsgroesseID
            ORDER BY r.Erstellungsdatum DESC
        ";
        $stmt = $this->db->query($sql);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rezepte as &$rezept) {
            $rezeptID = (int)$rezept['RezeptID'];

            // Kategorien mit Namen laden
            $stmtK = $this->db->prepare("
                SELECT k.Bezeichnung
                FROM RezeptKategorie rk
                JOIN Kategorie k ON rk.KategorieID = k.KategorieID
                WHERE rk.RezeptID = ?
            ");
            $stmtK->execute([$rezeptID]);
            $kategorien = $stmtK->fetchAll(PDO::FETCH_ASSOC);
            $rezept['kategorien'] = array_column($kategorien, 'Bezeichnung');

            // Utensilien mit Namen laden
            $stmtU = $this->db->prepare("
                SELECT u.UtensilID, u.Name
                FROM RezeptUtensil ru
                JOIN Utensil u ON ru.UtensilID = u.UtensilID
                WHERE ru.RezeptID = ?
            ");
            $stmtU->execute([$rezeptID]);
            $rezept['utensilien'] = $stmtU->fetchAll(PDO::FETCH_ASSOC);

            // Zutaten laden
            $stmtZ = $this->db->prepare("
                SELECT Zutat, Menge, Einheit 
                FROM RezeptZutat 
                WHERE RezeptID = ?
            ");
            $stmtZ->execute([$rezeptID]);
            $rezept['zutaten'] = $stmtZ->fetchAll(PDO::FETCH_ASSOC);
        }

        return $rezepte;
    }

    public function findeNachErstellerID(int $nutzerId): array {
        $sql = "
        SELECT 
            r.*, 
            n.Benutzername AS erstellerName, 
            n.Email AS erstellerEmail
        FROM Rezept r
        LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
        WHERE r.ErstellerID = ?
        ORDER BY r.Erstellungsdatum DESC
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nutzerId]);

        $rezepte = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rezeptID = (int)$row['RezeptID'];

            // Kategorien
            $stmtK = $this->db->prepare("
            SELECT k.Bezeichnung
            FROM RezeptKategorie rk
            JOIN Kategorie k ON rk.KategorieID = k.KategorieID
            WHERE rk.RezeptID = ?
        ");
            $stmtK->execute([$rezeptID]);
            $kategorien = array_column($stmtK->fetchAll(PDO::FETCH_ASSOC), 'Bezeichnung');

            // Utensilien
            $stmtU = $this->db->prepare("
            SELECT u.UtensilID, u.Name
            FROM RezeptUtensil ru
            JOIN Utensil u ON ru.UtensilID = u.UtensilID
            WHERE ru.RezeptID = ?
        ");
            $stmtU->execute([$rezeptID]);
            $utensilien = $stmtU->fetchAll(PDO::FETCH_ASSOC);

            // Zutaten
            $stmtZ = $this->db->prepare("
            SELECT Zutat, Menge, Einheit 
            FROM RezeptZutat 
            WHERE RezeptID = ?
        ");
            $stmtZ->execute([$rezeptID]);
            $zutaten = $stmtZ->fetchAll(PDO::FETCH_ASSOC);

            // Rezept-Objekt erzeugen
            $rezepte[] = new Rezept(
                $rezeptID,
                $row['Titel'],
                $row['Zubereitung'],
                $row['BildPfad'] ?? null,
                (int)$row['ErstellerID'],
                $row['erstellerName'] ?? null,
                $row['erstellerEmail'] ?? null,
                (int)$row['PreisklasseID'],
                (int)$row['PortionsgroesseID'],
                $row['Erstellungsdatum'],
                $kategorien,
                $utensilien,
                $zutaten
            );
        }

        return $rezepte;
    }

    public function findeNachId(int $id): ?array {
        $sql = "
            SELECT 
                r.RezeptID AS id,
                r.Titel AS titel,
                r.Zubereitung AS zubereitung,
                r.BildPfad AS bild,
                r.Erstellungsdatum AS datum,
                r.ErstellerID AS erstellerId,
                n.Benutzername AS erstellerName,
                n.Email AS erstellerEmail,
                r.PreisklasseID AS preisklasseId,
                r.PortionsgroesseID AS portionsgroesseId,
                pk.Preisspanne AS preisklasseName,
                pg.Angabe AS portionsgroesseName
            FROM Rezept r
            LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
            LEFT JOIN Preisklasse pk ON r.PreisklasseID = pk.PreisklasseID
            LEFT JOIN Portionsgroesse pg ON r.PortionsgroesseID = pg.PortionsgroesseID
            WHERE r.RezeptID = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $rezept = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$rezept) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT k.Bezeichnung
            FROM RezeptKategorie rk
            JOIN Kategorie k ON rk.KategorieID = k.KategorieID
         WHERE rk.RezeptID = ?
        ");
        $stmt->execute([$id]);
        $kategorien = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rezept['kategorien'] = array_column($kategorien, 'Bezeichnung');

        $stmt = $this->db->prepare("
            SELECT u.UtensilID, u.Name
            FROM RezeptUtensil ru
            JOIN Utensil u ON ru.UtensilID = u.UtensilID
            WHERE ru.RezeptID = ?
        ");
        $stmt->execute([$id]);
        $rezept['utensilien'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT Zutat AS zutat, Menge AS menge, Einheit AS einheit
            FROM RezeptZutat
            WHERE RezeptID = ?
        ");
        $stmt->execute([$id]);
        $rezept['zutaten'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rezept;
    }

    public function loesche(int $id): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT BildPfad FROM Rezept WHERE RezeptID = ?");
            $stmt->execute([$id]);
            $bildPfad = $stmt->fetchColumn();

            $this->db->prepare("DELETE FROM RezeptKategorie WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM RezeptZutat WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM RezeptUtensil WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM Bewertung WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM Rezept WHERE RezeptID = ?")->execute([$id]);

            $this->db->commit();

            if ($bildPfad && file_exists($bildPfad)) {
                unlink($bildPfad);
            }

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function findeKategorienMitIdsNachRezeptId(int $rezeptId): array {
        $stmt = $this->db->prepare("
            SELECT k.KategorieID, k.Bezeichnung
            FROM RezeptKategorie rk
            JOIN Kategorie k ON rk.KategorieID = k.KategorieID
            WHERE rk.RezeptID = ?
        ");
        $stmt->execute([$rezeptId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function findeAlleMitKategorie(string $kategorie): array {
        $sql = "
            SELECT r.*, n.Benutzername AS erstellerName, n.Email AS erstellerEmail
            FROM Rezept r
            JOIN RezeptKategorie rk ON r.RezeptID = rk.RezeptID
            JOIN Kategorie k ON rk.KategorieID = k.KategorieID
            LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
            WHERE k.Bezeichnung = ?
            ORDER BY r.Erstellungsdatum DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$kategorie]);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rezepte as &$rezept) {
            $rezeptID = (int)$rezept['RezeptID'];

            $stmtK = $this->db->prepare("
                SELECT k.Bezeichnung
                FROM RezeptKategorie rk
                JOIN Kategorie k ON rk.KategorieID = k.KategorieID
                WHERE rk.RezeptID = ?
            ");
            $stmtK->execute([$rezeptID]);
            $rezept['kategorien'] = array_column($stmtK->fetchAll(PDO::FETCH_ASSOC), 'Bezeichnung');

            $stmtU = $this->db->prepare("
                SELECT u.UtensilID, u.Name
                FROM RezeptUtensil ru
                JOIN Utensil u ON ru.UtensilID = u.UtensilID
                WHERE ru.RezeptID = ?
            ");
            $stmtU->execute([$rezeptID]);
            $rezept['utensilien'] = $stmtU->fetchAll(PDO::FETCH_ASSOC);

            $stmtZ = $this->db->prepare("SELECT Zutat, Menge, Einheit FROM RezeptZutat WHERE RezeptID = ?");
            $stmtZ->execute([$rezeptID]);
            $rezept['zutaten'] = $stmtZ->fetchAll(PDO::FETCH_ASSOC);
        }

        return $rezepte;
    }

    public function addRezept(
        string $titel,
        string $zubereitung,
        string $bildPfad,
        int $erstellerID,
        int $preisklasseID,
        int $portionsgroesseID,
        array $kategorien,
        array $zutaten,
        array $utensilien
    ): int|false {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO Rezept (Titel, Zubereitung, BildPfad, ErstellerID, PreisklasseID, PortionsgroesseID, Erstellungsdatum)
                VALUES (?, ?, ?, ?, ?, ?, date('now'))
            ");
            $stmt->execute([
                trim($titel),
                trim($zubereitung),
                trim($bildPfad),
                $erstellerID,
                $preisklasseID,
                $portionsgroesseID
            ]);

            $rezeptID = (int)$this->db->lastInsertId();

            $stmtKategorie = $this->db->prepare("INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES (?, ?)");
            foreach ($kategorien as $katID) {
                if (is_int($katID)) {
                    $stmtKategorie->execute([$rezeptID, $katID]);
                }
            }

            $stmtZutat = $this->db->prepare("INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit) VALUES (?, ?, ?, ?)");
            foreach ($zutaten as $z) {
                $zutat = trim($z['zutat'] ?? '');
                $menge = trim($z['menge'] ?? '');
                $einheit = trim($z['einheit'] ?? '');

                if ($zutat !== '' && $menge !== '' && $einheit !== '') {
                    $stmtZutat->execute([$rezeptID, $zutat, $menge, $einheit]);
                }
            }

            $stmtUtensil = $this->db->prepare("INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES (?, ?)");
            foreach ($utensilien as $utenID) {
                if (is_int($utenID)) {
                    $stmtUtensil->execute([$rezeptID, $utenID]);
                }
            }

            $this->db->commit();
            return $rezeptID;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function aktualisiere(
        int $rezeptID,
        string $titel,
        string $zubereitung,
        ?string $bildPfad,
        int $preisklasseID,
        int $portionsgroesseID,
        array $kategorien,
        array $zutaten,
        array $utensilien
    ): bool {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE Rezept SET Titel = ?, Zubereitung = ?, PreisklasseID = ?, PortionsgroesseID = ?";
            $params = [$titel, $zubereitung, $preisklasseID, $portionsgroesseID];

            if ($bildPfad) {
                $sql .= ", BildPfad = ?";
                $params[] = $bildPfad;
            }

            $sql .= " WHERE RezeptID = ?";
            $params[] = $rezeptID;

            $this->db->prepare($sql)->execute($params);

            $this->db->prepare("DELETE FROM RezeptKategorie WHERE RezeptID = ?")->execute([$rezeptID]);
            $this->db->prepare("DELETE FROM RezeptZutat WHERE RezeptID = ?")->execute([$rezeptID]);
            $this->db->prepare("DELETE FROM RezeptUtensil WHERE RezeptID = ?")->execute([$rezeptID]);

            $stmtKategorie = $this->db->prepare("INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES (?, ?)");
            foreach ($kategorien as $k) {
                $stmtKategorie->execute([$rezeptID, $k]);
            }

            $stmtZutat = $this->db->prepare("INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit) VALUES (?, ?, ?, ?)");
            foreach ($zutaten as $z) {
                if (isset($z['zutat'], $z['menge'], $z['einheit'])) {
                    $stmtZutat->execute([$rezeptID, $z['zutat'], $z['menge'], $z['einheit']]);
                }
            }

            $stmtUtensil = $this->db->prepare("INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES (?, ?)");
            foreach ($utensilien as $u) {
                $stmtUtensil->execute([$rezeptID, $u]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}