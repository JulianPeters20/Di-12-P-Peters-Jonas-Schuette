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

    // Registrierung erzeugt neuen Nutzer (nach Bestätigung)
    // Password hashing moved to UserService (Business Logic Layer)
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
                $hashedPassword, // Already hashed in UserService
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

    public function findeBenutzer(string $email, string $passwort): ?Nutzer {
        $nutzer = $this->findeNachEmail($email);
        if ($nutzer && password_verify($passwort, $nutzer->passwortHash)) {
            return $nutzer;
        }
        return null;
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
     * Password hashing should be done in UserService (Business Logic Layer)
     */
    public function passwortAktualisieren(int $nutzerId, string $hashedPassword): bool {
        $stmt = $this->db->prepare("UPDATE Nutzer SET PasswortHash = ? WHERE NutzerID = ?");
        return $stmt->execute([$hashedPassword, $nutzerId]);
    }
}