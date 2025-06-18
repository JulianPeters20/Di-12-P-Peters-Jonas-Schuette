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
            $row['Benutzername'],
            $row['Email'],
            $row['PasswortHash'],
            $row['RegistrierungsDatum'],
            $row['IstAdmin'] == 1
        ) : null;
    }

    public function findeNachEmail(string $email): ?Nutzer {
        $stmt = $this->db->prepare("SELECT * FROM Nutzer WHERE Email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Nutzer(
            (int)$row['NutzerID'],
            $row['Benutzername'],
            $row['Email'],
            $row['PasswortHash'],
            $row['RegistrierungsDatum'],
            (bool)$row['IstAdmin']
        ) : null;
    }

    public function registrieren(string $benutzername, string $email, string $passwort): bool {
        try {
            $this->db->beginTransaction();

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
                password_hash($passwort, PASSWORD_DEFAULT),
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
        error_log("Prüfe Benutzername in DB: " . $name);
        $stmt = $this->db->prepare("SELECT 1 FROM Nutzer WHERE Benutzername = ?");
        $stmt->execute([$name]);
        $exists = (bool)$stmt->fetchColumn();
        error_log("Benutzername existiert: " . ($exists ? 'Ja' : 'Nein'));
        return $exists;
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
                $row['Benutzername'],
                $row['Email'],
                $row['PasswortHash'],
                $row['RegistrierungsDatum'],
                (bool)$row['IstAdmin']
            );
        }

        return $nutzer;
    }
}