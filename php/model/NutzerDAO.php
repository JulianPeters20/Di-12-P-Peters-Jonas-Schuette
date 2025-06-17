<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Nutzer.php';

class NutzerDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Sucht einen Nutzer anhand seiner ID.
     */
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

    /**
     * Sucht einen Nutzer anhand seiner E-Mail-Adresse.
     */
    public function findeNachEmail(string $email): ?Nutzer {
        $stmt = $this->db->prepare("SELECT * FROM Nutzer WHERE Email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("[DEBUG] Suche nach Email: " . $email);


        return $row ? new Nutzer(
            (int)$row['NutzerID'],
            $row['Benutzername'],
            $row['Email'],
            $row['PasswortHash'],
            $row['RegistrierungsDatum'],
            (bool)$row['IstAdmin']
        ) : null;
    }

    /**
     * Führt die Registrierung eines neuen Nutzers durch.
     * Rückgabe: true bei Erfolg, false bei Fehler oder vorhandener E-Mail.
     */
    public function registrieren(string $benutzername, string $email, string $passwort): bool {
        try {
            $this->db->beginTransaction();

            // Duplikat vermeiden
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

    /**
     * Führt einen Login-Versuch durch (Email + Passwort).
     * Rückgabe: Nutzerobjekt bei Erfolg, null bei Fehlschlag.
     */
    public function findeBenutzer(string $email, string $passwort): ?Nutzer {
        $nutzer = $this->findeNachEmail($email);
        if ($nutzer && password_verify($passwort, $nutzer->passwortHash)) {
            return $nutzer;
        }
        return null;
    }

    /**
     * Gibt alle Nutzer aus der Datenbank zurück.
     * @return Nutzer[] Liste aller Nutzer als Objekte.
     */
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