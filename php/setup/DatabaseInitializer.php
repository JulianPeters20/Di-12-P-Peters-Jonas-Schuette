<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Database Initializer - Handles both SQLite and MySQL database setup
 * Fixes issues with database creation and table generation
 */
class DatabaseInitializer {
    
    /**
     * Initialize database based on configuration
     */
    public static function initialize(): bool {
        try {
            if (USE_MYSQL) {
                return self::initializeMySQL();
            } else {
                return self::initializeSQLite();
            }
        } catch (Exception $e) {
            error_log("Database initialization failed: " . $e->getMessage());
            throw new RuntimeException("Datenbankinitialisierung fehlgeschlagen: " . $e->getMessage());
        }
    }

    /**
     * Initialize SQLite database
     */
    private static function initializeSQLite(): bool {
        try {
            // Ensure database directory exists
            $dbDir = dirname(__DIR__ . '/../data/brokeandhungry.sqlite');
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            // Include and run SQLite initialization
            $result = include __DIR__ . '/../../data/init-database.php';
            
            // Verify database file was created with .sqlite extension
            $dbFile = __DIR__ . '/../../data/brokeandhungry.sqlite';
            if (!file_exists($dbFile)) {
                throw new RuntimeException("SQLite database file was not created");
            }

            error_log("SQLite database initialized successfully");
            return true;
        } catch (Exception $e) {
            error_log("SQLite initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialize MySQL database
     */
    private static function initializeMySQL(): bool {
        try {
            // Include and run MySQL initialization
            $result = include __DIR__ . '/../../data/init-database-mysql.php';
            
            error_log("MySQL database initialized successfully");
            return true;
        } catch (Exception $e) {
            error_log("MySQL initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if database is properly initialized
     */
    public static function isInitialized(): bool {
        try {
            $db = Database::getConnection();
            
            // Check if main tables exist
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
            error_log("Database check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get database status information
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
            
            // Get table list
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
