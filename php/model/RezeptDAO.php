<?php
declare(strict_types=1);

require_once __DIR__ . '/../../data/pdo.php';

/**
 * DAO-Klasse für den Zugriff auf Rezepte in der Datenbank.
 * Diese Klasse ermöglicht das Abrufen, Löschen und Suchen von Rezepten.
 * @author Julian Peters
 * @since 2025-06-03
 */
class RezeptDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Gibt alle Rezepte zurück – inkl. Kategorien, Zutaten, Utensilien.
     */
    public function findeAlle(): array {
        $sql = "
        SELECT * FROM Rezept
        ORDER BY Erstellungsdatum DESC
    ";
        $stmt = $this->db->query($sql);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rezepte as &$rezept) {
            $rezeptID = (int)$rezept['RezeptID'];

            // Kategorien
            $stmtK = $this->db->prepare("SELECT KategorieID FROM RezeptKategorie WHERE RezeptID = ?");
            $stmtK->execute([$rezeptID]);
            $rezept['kategorien'] = array_column($stmtK->fetchAll(PDO::FETCH_ASSOC), 'KategorieID');

            // Utensilien
            $stmtU = $this->db->prepare("SELECT UtensilID FROM RezeptUtensil WHERE RezeptID = ?");
            $stmtU->execute([$rezeptID]);
            $rezept['utensilien'] = array_column($stmtU->fetchAll(PDO::FETCH_ASSOC), 'UtensilID');

            // Zutaten
            $stmtZ = $this->db->prepare("SELECT Zutat, Menge, Einheit FROM RezeptZutat WHERE RezeptID = ?");
            $stmtZ->execute([$rezeptID]);
            $rezept['zutaten'] = $stmtZ->fetchAll(PDO::FETCH_ASSOC);
        }

        return $rezepte;
    }

    /**
     * @param int $nutzerId
     * Gibt alle Rezepte eines bestimmten Nutzers zurück – inkl. Kategorien, Zutaten, Utensilien.
     */
    public function findeNachErstellerID(int $nutzerId): array {
        $sql = "
        SELECT * FROM Rezept
        WHERE ErstellerID = ?
        ORDER BY Erstellungsdatum DESC
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nutzerId]);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rezepte as &$rezept) {
            $rezeptID = (int)$rezept['RezeptID'];

            // Kategorien
            $stmtK = $this->db->prepare("SELECT KategorieID FROM RezeptKategorie WHERE RezeptID = ?");
            $stmtK->execute([$rezeptID]);
            $rezept['kategorien'] = array_column($stmtK->fetchAll(PDO::FETCH_ASSOC), 'KategorieID');

            // Utensilien
            $stmtU = $this->db->prepare("SELECT UtensilID FROM RezeptUtensil WHERE RezeptID = ?");
            $stmtU->execute([$rezeptID]);
            $rezept['utensilien'] = array_column($stmtU->fetchAll(PDO::FETCH_ASSOC), 'UtensilID');

            // Zutaten
            $stmtZ = $this->db->prepare("SELECT Zutat, Menge, Einheit FROM RezeptZutat WHERE RezeptID = ?");
            $stmtZ->execute([$rezeptID]);
            $rezept['zutaten'] = $stmtZ->fetchAll(PDO::FETCH_ASSOC);
        }

        return $rezepte;
    }

    /**
     * @param int $id
     * Sucht ein Rezept anhand der ID – inkl. Zutaten, Kategorien und Utensilien.
     */
    public function findeNachId(int $id): ?array {
        // 1. Hauptrezept – gezielte Felder + Aliase
        $sql = "
        SELECT
            RezeptID AS id,
            Titel AS titel,
            Zubereitung AS zubereitung,
            BildPfad AS bild,
            Erstellungsdatum AS datum,
            ErstellerID AS erstellerId,
            PreisklasseID AS preisklasseId,
            PortionsgrößeID AS portionsgroesseId
        FROM Rezept
        WHERE RezeptID = ?
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $rezept = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rezept) {
            return null;
        }

        // 2. Kategorien (IDs)
        $stmt = $this->db->prepare("SELECT KategorieID FROM RezeptKategorie WHERE RezeptID = ?");
        $stmt->execute([$id]);
        $rezept['kategorien'] = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'KategorieID'));

        // 3. Utensilien (IDs)
        $stmt = $this->db->prepare("SELECT UtensilID FROM RezeptUtensil WHERE RezeptID = ?");
        $stmt->execute([$id]);
        $rezept['utensilien'] = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'UtensilID'));

        // 4. Zutaten (freie Texte)
        $stmt = $this->db->prepare("SELECT Zutat AS zutat, Menge AS menge, Einheit AS einheit FROM RezeptZutat WHERE RezeptID = ?");
        $stmt->execute([$id]);
        $rezept['zutaten'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rezept;
    }

    /**
     * @param int $id
     * Löscht ein Rezept samt aller Verknüpfungen.
     */
    public function loesche(int $id): bool {
        try {
            $this->db->beginTransaction();

            // 🖼 Bildpfad holen
            $stmt = $this->db->prepare("SELECT BildPfad FROM Rezept WHERE RezeptID = ?");
            $stmt->execute([$id]);
            $bildPfad = $stmt->fetchColumn();

            // Datenbankeinträge löschen
            $this->db->prepare("DELETE FROM RezeptKategorie WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM RezeptZutat WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM RezeptUtensil WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM Bewertung WHERE RezeptID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM Rezept WHERE RezeptID = ?")->execute([$id]);

            $this->db->commit();

            // Bild aus dem Dateisystem löschen (wenn vorhanden)
            if ($bildPfad && file_exists($bildPfad)) {
                unlink($bildPfad);
            }

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }


    /**
     * Sucht alle Rezepte mit bestimmter Kategorie – inkl. Zutaten & Utensilien.
     */
    public function findeAlleMitKategorie(string $kategorie): array {
        // 1. Alle passenden Rezepte (JOIN mit Kategorie-Tabelle)
        $sql = "
        SELECT r.*
        FROM Rezept r
        JOIN RezeptKategorie rk ON r.RezeptID = rk.RezeptID
        JOIN Kategorie k ON rk.KategorieID = k.KategorieID
        WHERE k.Bezeichnung = ?
        ORDER BY r.Erstellungsdatum DESC
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$kategorie]);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Für jedes Rezept: Kategorien, Utensilien, Zutaten laden
        foreach ($rezepte as &$rezept) {
            $rezeptID = (int)$rezept['RezeptID'];

            // Kategorien
            $stmtK = $this->db->prepare("SELECT KategorieID FROM RezeptKategorie WHERE RezeptID = ?");
            $stmtK->execute([$rezeptID]);
            $rezept['kategorien'] = array_column($stmtK->fetchAll(PDO::FETCH_ASSOC), 'KategorieID');

            // Utensilien
            $stmtU = $this->db->prepare("SELECT UtensilID FROM RezeptUtensil WHERE RezeptID = ?");
            $stmtU->execute([$rezeptID]);
            $rezept['utensilien'] = array_column($stmtU->fetchAll(PDO::FETCH_ASSOC), 'UtensilID');

            // Zutaten
            $stmtZ = $this->db->prepare("SELECT Zutat, Menge, Einheit FROM RezeptZutat WHERE RezeptID = ?");
            $stmtZ->execute([$rezeptID]);
            $rezept['zutaten'] = $stmtZ->fetchAll(PDO::FETCH_ASSOC);
        }

        return $rezepte;
    }

    /**
     * @param string $titel
     * @param string $zubereitung
     * @param string $bildPfad
     * @param int $erstellerID
     * @param int $preisklasseID
     * @param int $portionsgrößeID
     * @param array $kategorien      // KategorieIDs
     * @param array $zutaten         // assoziativ: [zutatID => menge]
     * @param array $utensilien       // UtensilIDs
     *
     * Fügt ein neues Rezept samt Verknüpfungen ein.
     * Gibt die ID des neuen Rezepts zurück oder false bei Fehler.
     */
    public function addRezept(
        string $titel,
        string $zubereitung,
        string $bildPfad,
        int $erstellerID,
        int $preisklasseID,
        int $portionsgrößeID,
        array $kategorien,
        array $zutaten,
        array $utensilien
    ): int|false {
        try {
            $this->db->beginTransaction();

            // 1. Hauptrezept speichern
            $stmt = $this->db->prepare("
            INSERT INTO Rezept (Titel, Zubereitung, BildPfad, ErstellerID, PreisklasseID, PortionsgrößeID, Erstellungsdatum)
            VALUES (?, ?, ?, ?, ?, ?, date('now'))
        ");
            $stmt->execute([
                trim($titel),
                trim($zubereitung),
                trim($bildPfad),
                $erstellerID,
                $preisklasseID,
                $portionsgrößeID
            ]);

            $rezeptID = (int)$this->db->lastInsertId();

            // 2. Kategorien (nur Integer-Werte zulassen)
            $stmtKategorie = $this->db->prepare("
            INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES (?, ?)
        ");
            foreach ($kategorien as $katID) {
                if (is_int($katID)) {
                    $stmtKategorie->execute([$rezeptID, $katID]);
                }
            }

            // 3. Zutaten (frei eingegeben)
            $stmtZutat = $this->db->prepare("
            INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit)
            VALUES (?, ?, ?, ?)
        ");
            foreach ($zutaten as $z) {
                $zutat = trim($z['zutat'] ?? '');
                $menge = trim($z['menge'] ?? '');
                $einheit = trim($z['einheit'] ?? '');

                if ($zutat !== '' && $menge !== '' && $einheit !== '') {
                    $stmtZutat->execute([$rezeptID, $zutat, $menge, $einheit]);
                }
            }

            // 4. Utensilien
            $stmtUtensil = $this->db->prepare("
            INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES (?, ?)
        ");
            foreach ($utensilien as $utenID) {
                if (is_int($utenID)) {
                    $stmtUtensil->execute([$rezeptID, $utenID]);
                }
            }

            $this->db->commit();
            return $rezeptID;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Optional: Logging
            // error_log("Fehler bei addRezept(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int $rezeptID
     * @param string $titel
     * @param string $zubereitung
     * @param string|null $bildPfad
     * @param array $kategorien
     * @param array $zutaten
     * @param array $utensilien
     * @return bool
     * Aktualisiert ein bestehendes Rezept.
     */
    public function aktualisiere(
        int $rezeptID,
        string $titel,
        string $zubereitung,
        ?string $bildPfad,
        array $kategorien,
        array $zutaten,     // zutatID => menge
        array $utensilien
    ): bool {
        try {
            $this->db->beginTransaction();

            $this->db->prepare("
            UPDATE Rezept SET Titel = ?, Zubereitung = ?" .
                ($bildPfad ? ", BildPfad = ?" : "") .
                " WHERE RezeptID = ?
        ")->execute(
                $bildPfad
                    ? [$titel, $zubereitung, $bildPfad, $rezeptID]
                    : [$titel, $zubereitung, $rezeptID]
            );

            $this->db->prepare("DELETE FROM RezeptKategorie WHERE RezeptID = ?")->execute([$rezeptID]);
            $this->db->prepare("DELETE FROM RezeptZutat WHERE RezeptID = ?")->execute([$rezeptID]);
            $this->db->prepare("DELETE FROM RezeptUtensil WHERE RezeptID = ?")->execute([$rezeptID]);

            foreach ($kategorien as $k) {
                $this->db->prepare("INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES (?, ?)")->execute([$rezeptID, $k]);
            }
            $stmtZutat = $this->db->prepare("
                INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($zutaten as $z) {
                if (isset($z['zutat'], $z['menge'], $z['einheit'])) {
                    $stmtZutat->execute([$rezeptID, $z['zutat'], $z['menge'], $z['einheit']]);
                }
            }
            foreach ($utensilien as $u) {
                $this->db->prepare("INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES (?, ?)")->execute([$rezeptID, $u]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

}