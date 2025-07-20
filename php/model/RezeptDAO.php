<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Rezept.php';

/**
 * Data Access Object für Rezepte
 * Verwaltet alle Datenbankoperationen für Rezepte mit optimierten Abfragen
 */
class RezeptDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Findet alle Rezepte mit optimierten Batch-Abfragen
     * Verwendet JOINs für bessere Performance und vermeidet N+1-Probleme
     *
     * Diese Methode lädt alle Rezepte mit ihren verknüpften Daten in einem einzigen
     * Datenbankaufruf, anstatt für jedes Rezept separate Abfragen zu machen.
     *
     * @return array Array mit allen Rezepten inklusive Kategorien, Utensilien und Zutaten
     */
    public function findeAlle(): array {
        return $this->findeAlleMitSortierung('datum', 'desc');
    }

    /**
     * Lädt alle Rezepte mit Sortierung
     * @param string $sortBy Sortierkriterium: 'bewertung', 'beliebtheit', 'datum'
     * @param string $sortOrder Sortierreihenfolge: 'asc', 'desc'
     * @return array Array mit allen Rezepten inklusive Kategorien, Utensilien und Zutaten
     */
    public function findeAlleMitSortierung(string $sortBy, string $sortOrder): array {
        // Sortierung bestimmen
        $orderClause = $this->buildOrderClause($sortBy, $sortOrder);

        $sql = "
            SELECT
                r.RezeptID,
                r.Titel,
                r.Zubereitung,
                r.BildPfad,
                r.ErstellerID,
                r.PreisklasseID,
                r.PortionsgroesseID,
                r.Erstellungsdatum,
                n.Benutzername AS erstellerName,
                n.Email AS erstellerEmail,
                pk.Preisspanne AS preisklasseName,
                pg.Angabe AS portionsgroesseName,
                COALESCE(AVG(b.Punkte), 0) AS durchschnittsBewertung,
                COUNT(b.RezeptID) AS anzahlBewertungen
            FROM Rezept r
            LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
            LEFT JOIN Preisklasse pk ON r.PreisklasseID = pk.PreisklasseID
            LEFT JOIN Portionsgroesse pg ON r.PortionsgroesseID = pg.PortionsgroesseID
            LEFT JOIN Bewertung b ON r.RezeptID = b.RezeptID
            GROUP BY r.RezeptID, r.Titel, r.Zubereitung, r.BildPfad, r.ErstellerID,
                     r.PreisklasseID, r.PortionsgroesseID, r.Erstellungsdatum,
                     n.Benutzername, n.Email, pk.Preisspanne, pg.Angabe
            $orderClause
        ";

        $stmt = $this->db->query($sql);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rezepte)) {
            return [];
        }

        // Alle Rezept-IDs für Batch-Abfragen sammeln
        // Dies ermöglicht es, alle verknüpften Daten in wenigen Abfragen zu laden
        $rezeptIds = array_column($rezepte, 'RezeptID');
        $placeholders = str_repeat('?,', count($rezeptIds) - 1) . '?';

        // Kategorien in einem Batch laden (statt einzelne Abfragen pro Rezept)
        $kategorienMap = [];
        $stmtK = $this->db->prepare("
            SELECT rk.RezeptID, k.Bezeichnung
            FROM RezeptKategorie rk
            JOIN Kategorie k ON rk.KategorieID = k.KategorieID
            WHERE rk.RezeptID IN ($placeholders)
        ");
        $stmtK->execute($rezeptIds);
        while ($row = $stmtK->fetch(PDO::FETCH_ASSOC)) {
            $kategorienMap[$row['RezeptID']][] = $row['Bezeichnung'];
        }

        // Utensilien in einem Batch laden
        $utensilienMap = [];
        $stmtU = $this->db->prepare("
            SELECT ru.RezeptID, u.UtensilID, u.Name
            FROM RezeptUtensil ru
            JOIN Utensil u ON ru.UtensilID = u.UtensilID
            WHERE ru.RezeptID IN ($placeholders)
        ");
        $stmtU->execute($rezeptIds);
        while ($row = $stmtU->fetch(PDO::FETCH_ASSOC)) {
            $utensilienMap[$row['RezeptID']][] = ['UtensilID' => (int)$row['UtensilID'], 'Name' => $row['Name']];
        }

        // Zutaten in einem Batch laden
        $zutatenMap = [];
        $stmtZ = $this->db->prepare("
            SELECT RezeptID, Zutat, Menge, Einheit
            FROM RezeptZutat
            WHERE RezeptID IN ($placeholders)
        ");
        $stmtZ->execute($rezeptIds);
        while ($row = $stmtZ->fetch(PDO::FETCH_ASSOC)) {
            $zutatenMap[$row['RezeptID']][] = [
                'Zutat' => $row['Zutat'],
                'Menge' => $row['Menge'],
                'Einheit' => $row['Einheit']
            ];
        }

        // Daten zusammenführen
        foreach ($rezepte as &$rezept) {
            $rezeptID = (int)$rezept['RezeptID'];
            $rezept['kategorien'] = $kategorienMap[$rezeptID] ?? [];
            $rezept['utensilien'] = $utensilienMap[$rezeptID] ?? [];
            $rezept['zutaten'] = $zutatenMap[$rezeptID] ?? [];
        }

        return $rezepte;
    }

    /**
     * Erstellt die ORDER BY Klausel basierend auf Sortierkriterium und -reihenfolge
     * @param string $sortBy Sortierkriterium
     * @param string $sortOrder Sortierreihenfolge
     * @return string ORDER BY Klausel
     */
    private function buildOrderClause(string $sortBy, string $sortOrder): string {
        $direction = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        switch ($sortBy) {
            case 'bewertung':
                return "ORDER BY durchschnittsBewertung $direction, anzahlBewertungen DESC, r.Erstellungsdatum DESC";
            case 'beliebtheit':
                return "ORDER BY anzahlBewertungen $direction, durchschnittsBewertung DESC, r.Erstellungsdatum DESC";
            case 'datum':
            default:
                return "ORDER BY r.Erstellungsdatum $direction, r.RezeptID $direction";
        }
    }

    /**
     * Findet die neuesten Rezepte limitiert auf eine bestimmte Anzahl
     * @param int $limit Anzahl der Rezepte die zurückgegeben werden sollen
     * @return array Array mit den neuesten Rezepten
     */
    public function findeNeuesteLimitiert(int $limit = 10): array {
        $sql = "
            SELECT
                r.RezeptID,
                r.Titel,
                r.Zubereitung,
                r.BildPfad,
                r.ErstellerID,
                r.PreisklasseID,
                r.PortionsgroesseID,
                r.Erstellungsdatum,
                n.Benutzername AS erstellerName,
                n.Email AS erstellerEmail,
                pk.Preisspanne AS preisklasseName,
                pg.Angabe AS portionsgroesseName
            FROM Rezept r
            LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
            LEFT JOIN Preisklasse pk ON r.PreisklasseID = pk.PreisklasseID
            LEFT JOIN Portionsgroesse pg ON r.PortionsgroesseID = pg.PortionsgroesseID
            ORDER BY r.Erstellungsdatum DESC, r.RezeptID DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rezepte)) {
            return [];
        }

        return $this->enricheRezepteWithDetails($rezepte);
    }

    /**
     * Findet die beliebtesten Rezepte sortiert nach Anzahl der Bewertungen
     * @param int $limit Anzahl der Rezepte die zurückgegeben werden sollen
     * @return array Array mit Rezepten sortiert nach Bewertungsanzahl (absteigend)
     */
    public function findeBeliebteste(int $limit = 10): array {
        $sql = "
            SELECT
                r.RezeptID,
                r.Titel,
                r.Zubereitung,
                r.BildPfad,
                r.ErstellerID,
                r.PreisklasseID,
                r.PortionsgroesseID,
                r.Erstellungsdatum,
                n.Benutzername AS erstellerName,
                n.Email AS erstellerEmail,
                pk.Preisspanne AS preisklasseName,
                pg.Angabe AS portionsgroesseName,
                COUNT(b.RezeptID) as anzahlBewertungen
            FROM Rezept r
            LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
            LEFT JOIN Preisklasse pk ON r.PreisklasseID = pk.PreisklasseID
            LEFT JOIN Portionsgroesse pg ON r.PortionsgroesseID = pg.PortionsgroesseID
            LEFT JOIN Bewertung b ON r.RezeptID = b.RezeptID
            GROUP BY r.RezeptID, r.Titel, r.Zubereitung, r.BildPfad, r.ErstellerID,
                     r.PreisklasseID, r.PortionsgroesseID, r.Erstellungsdatum,
                     n.Benutzername, n.Email, pk.Preisspanne, pg.Angabe
            ORDER BY anzahlBewertungen DESC, r.Erstellungsdatum DESC, r.RezeptID DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rezepte)) {
            return [];
        }

        return $this->enricheRezepteWithDetails($rezepte);
    }

    /**
     * Findet die bestbewerteten Rezepte (mindestens 3 Bewertungen)
     * @param int $limit Anzahl der Rezepte die zurückgegeben werden sollen
     * @return array Array mit Rezepten sortiert nach Durchschnittsbewertung (absteigend)
     */
    public function findeBestBewertete(int $limit = 10): array {
        $sql = "
            SELECT
                r.RezeptID,
                r.Titel,
                r.Zubereitung,
                r.BildPfad,
                r.ErstellerID,
                r.PreisklasseID,
                r.PortionsgroesseID,
                r.Erstellungsdatum,
                n.Benutzername AS erstellerName,
                n.Email AS erstellerEmail,
                pk.Preisspanne AS preisklasseName,
                pg.Angabe AS portionsgroesseName,
                COUNT(b.RezeptID) as anzahlBewertungen,
                AVG(b.Punkte) as durchschnittsBewertung
            FROM Rezept r
            LEFT JOIN Nutzer n ON r.ErstellerID = n.NutzerID
            LEFT JOIN Preisklasse pk ON r.PreisklasseID = pk.PreisklasseID
            LEFT JOIN Portionsgroesse pg ON r.PortionsgroesseID = pg.PortionsgroesseID
            LEFT JOIN Bewertung b ON r.RezeptID = b.RezeptID
            GROUP BY r.RezeptID, r.Titel, r.Zubereitung, r.BildPfad, r.ErstellerID,
                     r.PreisklasseID, r.PortionsgroesseID, r.Erstellungsdatum,
                     n.Benutzername, n.Email, pk.Preisspanne, pg.Angabe
            HAVING COUNT(b.RezeptID) >= 3
            ORDER BY durchschnittsBewertung DESC, anzahlBewertungen DESC, r.RezeptID DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $rezepte = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rezepte)) {
            return [];
        }

        return $this->enricheRezepteWithDetails($rezepte);
    }

    /**
     * Hilfsmethode um Rezepte mit Kategorien, Utensilien und Zutaten anzureichern
     * @param array $rezepte Array mit Basis-Rezeptdaten
     * @return array Array mit angereicherten Rezeptdaten
     */
    private function enricheRezepteWithDetails(array $rezepte): array {
        if (empty($rezepte)) {
            return [];
        }

        // Alle Rezept-IDs für Batch-Abfragen sammeln
        $rezeptIds = array_column($rezepte, 'RezeptID');
        $placeholders = str_repeat('?,', count($rezeptIds) - 1) . '?';

        // Kategorien in einem Batch laden
        $kategorienMap = [];
        $stmtK = $this->db->prepare("
            SELECT rk.RezeptID, k.Bezeichnung
            FROM RezeptKategorie rk
            JOIN Kategorie k ON rk.KategorieID = k.KategorieID
            WHERE rk.RezeptID IN ($placeholders)
        ");
        $stmtK->execute($rezeptIds);
        while ($row = $stmtK->fetch(PDO::FETCH_ASSOC)) {
            $kategorienMap[$row['RezeptID']][] = $row['Bezeichnung'];
        }

        // Utensilien in einem Batch laden
        $utensilienMap = [];
        $stmtU = $this->db->prepare("
            SELECT ru.RezeptID, u.UtensilID, u.Name
            FROM RezeptUtensil ru
            JOIN Utensil u ON ru.UtensilID = u.UtensilID
            WHERE ru.RezeptID IN ($placeholders)
        ");
        $stmtU->execute($rezeptIds);
        while ($row = $stmtU->fetch(PDO::FETCH_ASSOC)) {
            $utensilienMap[$row['RezeptID']][] = [
                'UtensilID' => $row['UtensilID'],
                'Name' => $row['Name']
            ];
        }

        // Zutaten in einem Batch laden
        $zutatenMap = [];
        $stmtZ = $this->db->prepare("
            SELECT RezeptID, Zutat, Menge, Einheit
            FROM RezeptZutat
            WHERE RezeptID IN ($placeholders)
        ");
        $stmtZ->execute($rezeptIds);
        while ($row = $stmtZ->fetch(PDO::FETCH_ASSOC)) {
            $zutatenMap[$row['RezeptID']][] = [
                'Zutat' => $row['Zutat'],
                'Menge' => $row['Menge'],
                'Einheit' => $row['Einheit']
            ];
        }

        // Daten zusammenführen
        foreach ($rezepte as &$rezept) {
            $rezeptID = (int)$rezept['RezeptID'];
            $rezept['kategorien'] = $kategorienMap[$rezeptID] ?? [];
            $rezept['utensilien'] = $utensilienMap[$rezeptID] ?? [];
            $rezept['zutaten'] = $zutatenMap[$rezeptID] ?? [];
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
        ORDER BY r.Erstellungsdatum DESC, r.RezeptID DESC
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

    /**
     * Löscht ein Rezept mit verbesserter Fehlerbehandlung
     * Verwendet CASCADE DELETE für automatisches Löschen verknüpfter Datensätze
     *
     * @param int $id Die ID des zu löschenden Rezepts
     * @return bool true bei erfolgreichem Löschen
     * @throws InvalidArgumentException wenn Rezept nicht existiert
     * @throws RuntimeException bei Datenbankfehlern
     */
    public function loesche(int $id): bool {
        try {
            $this->db->beginTransaction();

            // Prüfen ob Rezept existiert und Bildpfad ermitteln
            $stmt = $this->db->prepare("SELECT BildPfad FROM Rezept WHERE RezeptID = ?");
            $stmt->execute([$id]);
            $bildPfad = $stmt->fetchColumn();

            if ($bildPfad === false) {
                $this->db->rollBack();
                throw new InvalidArgumentException("Rezept mit ID $id nicht gefunden");
            }

            // Mit CASCADE DELETE werden verknüpfte Datensätze automatisch gelöscht
            $stmt = $this->db->prepare("DELETE FROM Rezept WHERE RezeptID = ?");
            $result = $stmt->execute([$id]);

            if (!$result) {
                throw new RuntimeException("Fehler beim Löschen des Rezepts");
            }

            $this->db->commit();

            // Bilddatei löschen falls vorhanden
            if ($bildPfad && file_exists($bildPfad)) {
                if (!unlink($bildPfad)) {
                    error_log("Warnung: Bilddatei $bildPfad konnte nicht gelöscht werden");
                }
            }

            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Datenbankfehler beim Löschen von Rezept $id: " . $e->getMessage());
            throw new RuntimeException("Datenbankfehler beim Löschen des Rezepts");
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Fehler beim Löschen von Rezept $id: " . $e->getMessage());
            throw $e;
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
            ORDER BY r.Erstellungsdatum DESC, r.RezeptID DESC
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

    /**
     * Fügt ein neues Rezept hinzu mit konsistenter Rückgabe
     * Verwendet Transaktionen für Datenintegrität
     *
     * @param string $titel Titel des Rezepts
     * @param string $zubereitung Zubereitungsanleitung
     * @param string $bildPfad Pfad zum Rezeptbild
     * @param int $erstellerID ID des Erstellers
     * @param int $preisklasseID ID der Preisklasse
     * @param int $portionsgroesseID ID der Portionsgröße
     * @param array $kategorien Array mit Kategorie-IDs
     * @param array $zutaten Array mit Zutaten-Daten
     * @param array $utensilien Array mit Utensil-IDs
     * @return int ID des erstellten Rezepts
     * @throws RuntimeException bei Datenbankfehlern
     */
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
    ): int {
        try {
            $this->db->beginTransaction();

            // Aktuelles Datum verwenden - kompatibel mit SQLite und MySQL
            $currentDate = date('Y-m-d');
            $stmt = $this->db->prepare("
                INSERT INTO Rezept (Titel, Zubereitung, BildPfad, ErstellerID, PreisklasseID, PortionsgroesseID, Erstellungsdatum)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                trim($titel),
                trim($zubereitung),
                trim($bildPfad),
                $erstellerID,
                $preisklasseID,
                $portionsgroesseID,
                $currentDate
            ]);

            $rezeptID = (int)$this->db->lastInsertId();
            if ($rezeptID === 0) {
                throw new RuntimeException("Fehler beim Erstellen des Rezepts - keine ID erhalten");
            }

            // Kategorien hinzufügen
            if (!empty($kategorien)) {
                $stmtKategorie = $this->db->prepare("INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES (?, ?)");
                foreach ($kategorien as $katID) {
                    if (is_numeric($katID)) {
                        $stmtKategorie->execute([$rezeptID, (int)$katID]);
                    }
                }
            }

            // Zutaten hinzufügen
            if (!empty($zutaten)) {
                $stmtZutat = $this->db->prepare("INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit) VALUES (?, ?, ?, ?)");
                foreach ($zutaten as $z) {
                    $zutat = trim($z['zutat'] ?? '');
                    $menge = trim($z['menge'] ?? '');
                    $einheit = trim($z['einheit'] ?? '');

                    if ($zutat !== '' && $menge !== '' && $einheit !== '') {
                        $stmtZutat->execute([$rezeptID, $zutat, $menge, $einheit]);
                    }
                }
            }

            // Utensilien hinzufügen
            if (!empty($utensilien)) {
                $stmtUtensil = $this->db->prepare("INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES (?, ?)");
                foreach ($utensilien as $utenID) {
                    if (is_numeric($utenID)) {
                        $stmtUtensil->execute([$rezeptID, (int)$utenID]);
                    }
                }
            }

            $this->db->commit();
            return $rezeptID;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Datenbankfehler beim Erstellen des Rezepts: " . $e->getMessage());
            throw new RuntimeException("Datenbankfehler beim Erstellen des Rezepts");
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Fehler beim Erstellen des Rezepts: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aktualisiert ein Rezept mit optimierter Methode
     * Löscht Nährwerte bei Änderungen und verwendet effiziente DELETE/INSERT-Operationen
     *
     * @param int $rezeptID ID des zu aktualisierenden Rezepts
     * @param string $titel Neuer Titel
     * @param string $zubereitung Neue Zubereitungsanleitung
     * @param string|null $bildPfad Neuer Bildpfad (optional)
     * @param int $preisklasseID Neue Preisklasse-ID
     * @param int $portionsgroesseID Neue Portionsgröße-ID
     * @param array $kategorien Array mit Kategorie-IDs
     * @param array $zutaten Array mit Zutaten-Daten
     * @param array $utensilien Array mit Utensil-IDs
     * @return bool true bei erfolgreichem Update
     * @throws RuntimeException bei Datenbankfehlern
     */
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

            // Hauptrezept-Daten aktualisieren
            $sql = "UPDATE Rezept SET Titel = ?, Zubereitung = ?, PreisklasseID = ?, PortionsgroesseID = ?";
            $params = [$titel, $zubereitung, $preisklasseID, $portionsgroesseID];

            if ($bildPfad) {
                $sql .= ", BildPfad = ?";
                $params[] = $bildPfad;
            }

            $sql .= " WHERE RezeptID = ?";
            $params[] = $rezeptID;

            $this->db->prepare($sql)->execute($params);

            // Nährwerte löschen da sich Zutaten geändert haben könnten
            // (Benutzer muss Nährwerte neu berechnen lassen)
            $this->db->prepare("DELETE FROM RezeptNaehrwerte WHERE RezeptID = ?")->execute([$rezeptID]);

            // Kategorien aktualisieren (DELETE + INSERT für Einfachheit)
            $this->db->prepare("DELETE FROM RezeptKategorie WHERE RezeptID = ?")->execute([$rezeptID]);
            if (!empty($kategorien)) {
                $stmtKategorie = $this->db->prepare("INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES (?, ?)");
                foreach ($kategorien as $k) {
                    if (is_numeric($k)) {
                        $stmtKategorie->execute([$rezeptID, (int)$k]);
                    }
                }
            }

            // Zutaten aktualisieren (DELETE + INSERT für Einfachheit)
            $this->db->prepare("DELETE FROM RezeptZutat WHERE RezeptID = ?")->execute([$rezeptID]);
            if (!empty($zutaten)) {
                $stmtZutat = $this->db->prepare("INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit) VALUES (?, ?, ?, ?)");
                foreach ($zutaten as $z) {
                    if (isset($z['zutat'], $z['menge'], $z['einheit']) &&
                        trim($z['zutat']) !== '' && trim($z['menge']) !== '' && trim($z['einheit']) !== '') {
                        $stmtZutat->execute([$rezeptID, trim($z['zutat']), trim($z['menge']), trim($z['einheit'])]);
                    }
                }
            }

            // Utensilien aktualisieren (DELETE + INSERT für Einfachheit)
            $this->db->prepare("DELETE FROM RezeptUtensil WHERE RezeptID = ?")->execute([$rezeptID]);
            if (!empty($utensilien)) {
                $stmtUtensil = $this->db->prepare("INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES (?, ?)");
                foreach ($utensilien as $u) {
                    if (is_numeric($u)) {
                        $stmtUtensil->execute([$rezeptID, (int)$u]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Datenbankfehler beim Aktualisieren von Rezept $rezeptID: " . $e->getMessage());
            throw new RuntimeException("Datenbankfehler beim Aktualisieren des Rezepts");
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Fehler beim Aktualisieren von Rezept $rezeptID: " . $e->getMessage());
            throw $e;
        }
    }
}