<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Nutzer.php';

class NutzerDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findeNachID(int $id): ?Nutzer {
        $stmt = $this->db->prepare("SELECT * FROM Nutzer WHERE NutzerID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Nutzer(
            (int)$row['NutzerID'],
            $row['Benutzername'] ?? "",
            $row['Email'],
            $row['PasswortHash'],
            $row['RegistrierungsDatum'],
            (bool)($row['IstAdmin'] ?? false)
        ) : null;
    }

    public function findeNachEmail(string $email): ?Nutzer {
        $stmt = $this->db->prepare("SELECT * FROM Nutzer WHERE Email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Nutzer(
            (int)$row['NutzerID'],
            $row['Benutzername'] ?? "",
            $row['Email'],
            $row['PasswortHash'],
            $row['RegistrierungsDatum'],
            (bool)($row['IstAdmin'] ?? false)
        ) : null;
    }

    /**
     * Registriert einen neuen Nutzer in der Datenbank
     * Passwort-Hashing wurde in UserService (Business Logic Layer) verlagert
     *
     * @param string $benutzername Gewünschter Benutzername
     * @param string $email E-Mail-Adresse (muss eindeutig sein)
     * @param string $hashedPassword Bereits gehashtes Passwort
     * @return bool true bei erfolgreicher Registrierung
     */
    public function registrieren(string $benutzername, string $email, string $hashedPassword): bool {
        try {
            $this->db->beginTransaction();

            // Finaler Check: existiert Email bereits?
            if ($this->findeNachEmail($email) !== null) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("
                INSERT INTO Nutzer (Benutzername, Email, PasswortHash, RegistrierungsDatum)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $benutzername,
                $email,
                $hashedPassword, // Bereits in UserService gehasht
                date('Y-m-d')
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function existiertBenutzername(string $name): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM Nutzer WHERE Benutzername = ?");
        $stmt->execute([$name]);
        return (bool)$stmt->fetchColumn();
    }

    public function findeAlle(): array {
        $stmt = $this->db->query("SELECT * FROM Nutzer ORDER BY RegistrierungsDatum DESC");
        $nutzer = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nutzer[] = new Nutzer(
                (int)$row['NutzerID'],
                $row['Benutzername'] ?? "",
                $row['Email'],
                $row['PasswortHash'],
                $row['RegistrierungsDatum'],
                (bool)($row['IstAdmin'] ?? false)
            );
        }

        return $nutzer;
    }

    public function loesche(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Nutzer WHERE NutzerID = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Aktualisiert das Passwort eines Nutzers
     * Passwort-Hashing sollte in UserService (Business Logic Layer) erfolgen
     *
     * @param int $nutzerId ID des Nutzers
     * @param string $hashedPassword Bereits gehashtes neues Passwort
     * @return bool true bei erfolgreicher Aktualisierung
     */
    public function passwortAktualisieren(int $nutzerId, string $hashedPassword): bool {
        $stmt = $this->db->prepare("UPDATE Nutzer SET PasswortHash = ? WHERE NutzerID = ?");
        return $stmt->execute([$hashedPassword, $nutzerId]);
    }

    /**
     * Findet alle Nutzer die Rezepte erstellt haben
     * @return array Array mit Benutzernamen der Ersteller
     */
    public function findeAlleErsteller(): array {
        $stmt = $this->db->query("
            SELECT DISTINCT n.Benutzername
            FROM Nutzer n
            INNER JOIN Rezept r ON n.NutzerID = r.ErstellerID
            ORDER BY n.Benutzername
        ");

        $ersteller = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ersteller[] = $row['Benutzername'];
        }
        return $ersteller;
    }
}