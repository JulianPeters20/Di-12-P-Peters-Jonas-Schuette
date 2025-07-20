<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Datenbank-Initialisierer - Verwaltet SQLite und MySQL Datenbankeinrichtung
 *
 */
class DatabaseInitializer {

    /**
     * Initialisiert Datenbank basierend auf Konfiguration
     * Wählt automatisch zwischen SQLite und MySQL
     *
     * @return bool true bei erfolgreicher Initialisierung
     * @throws RuntimeException bei Initialisierungsfehlern
     */
    public static function initialize(): bool {
        try {
            if (USE_MYSQL) {
                return self::initializeMySQL();
            } else {
                return self::initializeSQLite();
            }
        } catch (Exception $e) {
            error_log("Datenbankinitialisierung fehlgeschlagen: " . $e->getMessage());
            throw new RuntimeException("Datenbankinitialisierung fehlgeschlagen: " . $e->getMessage());
        }
    }

    /**
     * Initialisiert SQLite-Datenbank
     * Stellt sicher, dass Verzeichnis existiert und .sqlite-Erweiterung korrekt ist
     *
     * @return bool true bei erfolgreicher Initialisierung
     * @throws RuntimeException bei Initialisierungsfehlern
     */
    private static function initializeSQLite(): bool {
        try {
            // Sicherstellen, dass Datenbankverzeichnis existiert
            $dbDir = dirname(__DIR__ . '/../data/brokeandhungry.sqlite');
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            // SQLite-Initialisierung einbinden und ausführen
            $result = include __DIR__ . '/../../data/init-database.php';

            // Prüfen, dass Datenbankdatei mit .sqlite-Erweiterung erstellt wurde
            $dbFile = __DIR__ . '/../../data/brokeandhungry.sqlite';
            if (!file_exists($dbFile)) {
                throw new RuntimeException("SQLite-Datenbankdatei wurde nicht erstellt");
            }

            error_log("SQLite-Datenbank erfolgreich initialisiert");
            return true;
        } catch (Exception $e) {
            error_log("SQLite-Initialisierungsfehler: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialisiert MySQL-Datenbank
     * Führt MySQL-spezifische Initialisierung aus
     *
     * @return bool true bei erfolgreicher Initialisierung
     * @throws RuntimeException bei Initialisierungsfehlern
     */
    private static function initializeMySQL(): bool {
        try {
            // MySQL-Initialisierung einbinden und ausführen
            $result = include __DIR__ . '/../../data/init-database-mysql.php';

            error_log("MySQL-Datenbank erfolgreich initialisiert");
            return true;
        } catch (Exception $e) {
            error_log("MySQL-Initialisierungsfehler: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prüft, ob Datenbank ordnungsgemäß initialisiert ist
     * Überprüft Existenz der wichtigsten Tabellen
     *
     * @return bool true wenn alle Haupttabellen existieren
     */
    public static function isInitialized(): bool {
        try {
            $db = Database::getConnection();

            // Prüfen, ob Haupttabellen existieren
            $tables = ['Nutzer', 'Rezept', 'Kategorie', 'Preisklasse', 'Portionsgroesse'];

            foreach ($tables as $table) {
                if (USE_MYSQL) {
                    $stmt = $db->prepare("SHOW TABLES LIKE ?");
                    $stmt->execute([$table]);
                } else {
                    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
                    $stmt->execute([$table]);
                }

                if (!$stmt->fetch()) {
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Datenbankprüfung fehlgeschlagen: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ermittelt Datenbankstatus-Informationen
     * Sammelt Informationen über Datenbanktyp, Initialisierung und Tabellen
     *
     * @return array Assoziatives Array mit Statusinformationen
     */
    public static function getStatus(): array {
        $status = [
            'type' => USE_MYSQL ? 'MySQL' : 'SQLite',
            'initialized' => false,
            'tables' => [],
            'error' => null
        ];

        try {
            $db = Database::getConnection();
            $status['initialized'] = self::isInitialized();

            // Tabellenliste ermitteln
            if (USE_MYSQL) {
                $stmt = $db->query("SHOW TABLES");
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $status['tables'][] = $row[0];
                }
            } else {
                $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $status['tables'][] = $row['name'];
                }
            }

        } catch (Exception $e) {
            $status['error'] = $e->getMessage();
        }

        return $status;
    }
}
