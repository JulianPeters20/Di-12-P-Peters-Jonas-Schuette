<?php
require_once 'pdo.php';

$db = Database::getConnection();

try {
    $db->beginTransaction();

    // Bestehende Tabellen löschen (für Neuinitialisierung)
    // Reihenfolge beachtet Foreign Key Constraints
    // WICHTIG: Tabellen werden gelöscht um eine saubere Neuinitialisierung zu gewährleisten
    // und Konflikte mit geänderten Tabellenstrukturen zu vermeiden
    $tabellen = ['RezeptKategorie', 'RezeptUtensil', 'RezeptZutat', 'Bewertung',
        'Rezept', 'Kategorie', 'Utensil', 'Zutat', 'Preisklasse', 'Portionsgroesse', 'Nutzer',
        'RezeptNaehrwerte', 'api_cache', 'api_log'];
    foreach ($tabellen as $t) {
        $db->exec("DROP TABLE IF EXISTS $t");
    }

    // Nutzer
    $db->exec("
        CREATE TABLE Nutzer (
            NutzerID INTEGER PRIMARY KEY AUTOINCREMENT,
            Benutzername TEXT NOT NULL,
            Email TEXT NOT NULL UNIQUE,
            PasswortHash TEXT NOT NULL,
            RegistrierungsDatum TEXT NOT NULL,
            IstAdmin INTEGER NOT NULL DEFAULT 0
        )
    ");

    // Preisklasse
    $db->exec("
        CREATE TABLE Preisklasse (
            PreisklasseID INTEGER PRIMARY KEY AUTOINCREMENT,
            Preisspanne TEXT NOT NULL
        )
    ");

    // Portionsgroesse
    $db->exec("
        CREATE TABLE Portionsgroesse (
            PortionsgroesseID INTEGER PRIMARY KEY AUTOINCREMENT,
            Angabe TEXT NOT NULL
        )
    ");

    // Rezept
    $db->exec("
        CREATE TABLE Rezept (
            RezeptID INTEGER PRIMARY KEY AUTOINCREMENT,
            Titel TEXT NOT NULL,
            Zubereitung TEXT NOT NULL,
            BildPfad TEXT,
            ErstellerID INTEGER,
            PreisklasseID INTEGER,
            PortionsgroesseID INTEGER,
            Erstellungsdatum TEXT NOT NULL,
            FOREIGN KEY (ErstellerID) REFERENCES Nutzer(NutzerID) ON DELETE CASCADE,
            FOREIGN KEY (PreisklasseID) REFERENCES Preisklasse(PreisklasseID),
            FOREIGN KEY (PortionsgroesseID) REFERENCES Portionsgroesse(PortionsgroesseID)
        )
    ");

    // Bewertung
    $db->exec("
        CREATE TABLE Bewertung (
            RezeptID INTEGER,
            NutzerID INTEGER,
            Punkte INTEGER NOT NULL CHECK (Punkte BETWEEN 1 AND 5),
            Bewertungsdatum TEXT NOT NULL DEFAULT CURRENT_DATE,
            PRIMARY KEY (RezeptID, NutzerID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (NutzerID) REFERENCES Nutzer(NutzerID) ON DELETE CASCADE
        )
    ");

    // Nährwerte-Tabelle für Spoonacular API Integration
    // Speichert berechnete Nährwerte für Rezepte
    $db->exec("
        CREATE TABLE RezeptNaehrwerte (
            RezeptID INTEGER PRIMARY KEY,
            Kalorien REAL,
            Protein REAL,
            Kohlenhydrate REAL,
            Fett REAL,
            Ballaststoffe REAL,
            Zucker REAL,
            Natrium REAL,
            Berechnet_am TEXT NOT NULL,
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE
        )
    ");

    // Cache-Tabelle für API-Aufrufe (Performance-Optimierung)
    $db->exec("
        CREATE TABLE api_cache (
            cache_key TEXT PRIMARY KEY,
            naehrwerte_json TEXT NOT NULL,
            erstellt_am TEXT NOT NULL
        )
    ");

    // API-Log-Tabelle für Monitoring und Debugging
    $db->exec("
        CREATE TABLE api_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            endpoint TEXT NOT NULL,
            status TEXT NOT NULL,
            response_time REAL,
            error_message TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Zutat
    $db->exec("
        CREATE TABLE Zutat (
            ZutatID INTEGER PRIMARY KEY AUTOINCREMENT,
            Name TEXT NOT NULL
        )
    ");

    // Utensil
    $db->exec("
        CREATE TABLE Utensil (
            UtensilID INTEGER PRIMARY KEY AUTOINCREMENT,
            Name TEXT NOT NULL
        )
    ");

    // Kategorie
    $db->exec("
        CREATE TABLE Kategorie (
            KategorieID INTEGER PRIMARY KEY AUTOINCREMENT,
            Bezeichnung TEXT NOT NULL
        )
    ");

    // RezeptZutat
    $db->exec("
        CREATE TABLE RezeptZutat (
            RezeptID INTEGER,
            Zutat TEXT,
            Menge TEXT,
            Einheit TEXT,
            PRIMARY KEY (RezeptID, Zutat),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE
        )
    ");

    // RezeptUtensil
    $db->exec("
        CREATE TABLE RezeptUtensil (
            RezeptID INTEGER,
            UtensilID INTEGER,
            PRIMARY KEY (RezeptID, UtensilID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (UtensilID) REFERENCES Utensil(UtensilID)
        )
    ");

    // RezeptKategorie
    $db->exec("
        CREATE TABLE RezeptKategorie (
            RezeptID INTEGER,
            KategorieID INTEGER,
            PRIMARY KEY (RezeptID, KategorieID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (KategorieID) REFERENCES Kategorie(KategorieID)
        )
    ");

    // Beispiel-Nutzer für Entwicklung und Tests
    $nutzerStmt = $db->prepare("
        INSERT INTO Nutzer (Benutzername, Email, PasswortHash, RegistrierungsDatum, IstAdmin)
        VALUES (?, ?, ?, ?, ?)
    ");

    // Administrator-Accounts
    $nutzerStmt->execute(['admin1', 'admin1@example.com', password_hash('adminpass1', PASSWORD_DEFAULT), date('Y-m-d'), 1]);
    $nutzerStmt->execute(['admin2', 'admin2@example.com', password_hash('adminpass2', PASSWORD_DEFAULT), date('Y-m-d'), 1]);
    // Standard-Nutzer
    $nutzerStmt->execute(['max_muster', 'max@example.com', password_hash('geheim123', PASSWORD_DEFAULT), date('Y-m-d'), 0]);

    // Preisklasse Beispielwerte
    $db->exec("
        INSERT INTO Preisklasse (Preisspanne) VALUES
        ('unter 5€'), 
        ('5–10€'),
        ('10–15€'),
        ('15–20€'),
        ('über 20€')
    ");

    // Portionsgroesse Beispielwerte
    $db->exec("
        INSERT INTO Portionsgroesse (Angabe) VALUES
        ('1 Person'), 
        ('2 Personen'), 
        ('3 Personen'), 
        ('4 Personen'),
        ('Familie (5+)')
    ");

    // Utensilien Beispielwerte
    $db->exec("
        INSERT INTO Utensil (Name) VALUES
        ('Topf'), 
        ('Pfanne'),
        ('Kochlöffel'),
        ('Sieb'),
        ('Schneidebrett'),
        ('Messer'),
        ('Backofen'),
        ('Mixer'),
        ('Schüssel')
    ");

    // Kategorien & Zutaten Beispielwerte
    $db->exec("
    INSERT INTO Kategorie (Bezeichnung) VALUES
    ('Vegetarisch'),
    ('Schnell (unter 20 Min)'),
    ('Vegan'),
    ('Herzhaft'),
    ('Dessert'),
    ('Glutenfrei'),
    ('Low-Carb'),
    ('Frühstück'),
    ('Asiatisch'),
    ('Italienisch'),
    ('Meal Prep'),
    ('Kinderfreundlich'),
    ('Snacks'),
    ('Salate'),
    ('Grillen'),
    ('Laktosefrei'),
    ('Aufwendig (über 60 Min)'),
    ('Hauptgericht'),
    ('Vorspeise'),
    ('Mexikanisch'),
    ('Deutsch'),
    ('Mediterran'),
    ('High Protein'),
    ('Keto'),
    ('Paleo'),
    ('Gesund')
");
    $db->exec("INSERT INTO Zutat (Name) VALUES ('Pasta'), ('Tomaten'), ('Basilikum'), ('Olivenöl'), ('Parmesan')");

    // Beispiel-Rezept angepasst für Nudeln mit Pesto
    $rezeptStmt = $db->prepare("
        INSERT INTO Rezept (Titel, Zubereitung, BildPfad, ErstellerID, PreisklasseID, PortionsgroesseID, Erstellungsdatum)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $rezeptStmt->execute([
        'Nudeln mit Pesto',
        'Pasta kochen, Pesto herstellen und unterheben.',
        'images/pesto.jpg',
        1, // admin1
        2, // 5-10€
        2, // 2 Personen
        date('Y-m-d')
    ]);

    // Zutaten zum Rezept
    $db->exec("
        INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit) VALUES
        (1, 'Pasta', '200', 'g'),
        (1, 'Basilikum', '50', 'g'),
        (1, 'Olivenöl', '30', 'ml'),
        (1, 'Parmesan', '20', 'g')
    ");

    // Utensilien zum Rezept (Topf, Kochlöffel, Sieb: IDs 1,3,4)
    $db->exec("INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES (1, 1), (1, 3), (1, 4)");

    // Kategorien zum Rezept (Vegetarisch=1, Schnell=2)
    $db->exec("INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES (1, 1), (1, 2)");

    // Beispiel-Bewertung für Testrezept
    $db->exec("INSERT INTO Bewertung (RezeptID, NutzerID, Punkte, Bewertungsdatum) VALUES (1, 1, 5, '" . date('Y-m-d') . "')");

    $db->commit();

    error_log("SQLite-Datenbank erfolgreich initialisiert.");
    return true;

} catch (Exception $e) {
    $db->rollBack();
    error_log("Fehler bei SQLite-Datenbankinitialisierung: " . $e->getMessage());
    throw new RuntimeException("Datenbankinitialisierung fehlgeschlagen: " . $e->getMessage());
}