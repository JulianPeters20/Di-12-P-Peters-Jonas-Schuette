<?php
require_once 'pdo-mysql.php';

$db = Database::getConnection();

try {
    $db->beginTransaction();

    // Tabellen löschen (Reihenfolge wegen FK)
    $tables = [
        'Bewertung', 'RezeptKategorie', 'RezeptUtensil', 'RezeptZutat', 'RezeptNaehrwerte',
        'Rezept', 'Kategorie', 'Utensil', 'Zutat', 'Preisklasse', 'Portionsgröße', 'Nutzer',
        'api_cache', 'api_log'
    ];
    foreach ($tables as $t) {
        $db->exec("DROP TABLE IF EXISTS `$t`");
    }

    // Nutzer
    $db->exec("
        CREATE TABLE Nutzer (
            NutzerID INT AUTO_INCREMENT PRIMARY KEY,
            Benutzername VARCHAR(100) NOT NULL,
            Email VARCHAR(255) NOT NULL UNIQUE,
            PasswortHash VARCHAR(255) NOT NULL,
            RegistrierungsDatum DATE NOT NULL,
            IstAdmin BOOLEAN NOT NULL DEFAULT FALSE
        )
    ");

    // Preisklasse
    $db->exec("
        CREATE TABLE Preisklasse (
            PreisklasseID INT AUTO_INCREMENT PRIMARY KEY,
            Preisspanne VARCHAR(50) NOT NULL
        )
    ");

    // Portionsgröße
    $db->exec("
        CREATE TABLE Portionsgröße (
            PortionsgrößeID INT AUTO_INCREMENT PRIMARY KEY,
            Angabe VARCHAR(50) NOT NULL
        )
    ");

    // Rezept
    $db->exec("
        CREATE TABLE Rezept (
            RezeptID INT AUTO_INCREMENT PRIMARY KEY,
            Titel VARCHAR(255) NOT NULL,
            Zubereitung TEXT NOT NULL,
            BildPfad VARCHAR(255),
            ErstellerID INT,
            PreisklasseID INT,
            PortionsgrößeID INT,
            Erstellungsdatum DATE NOT NULL,
            FOREIGN KEY (ErstellerID) REFERENCES Nutzer(NutzerID) ON DELETE SET NULL,
            FOREIGN KEY (PreisklasseID) REFERENCES Preisklasse(PreisklasseID),
            FOREIGN KEY (PortionsgrößeID) REFERENCES Portionsgröße(PortionsgrößeID)
        )
    ");

    // Bewertung
    $db->exec("
        CREATE TABLE Bewertung (
            RezeptID INT,
            NutzerID INT,
            Punkte INT NOT NULL,
            Bewertungsdatum DATE NOT NULL,
            PRIMARY KEY (RezeptID, NutzerID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (NutzerID) REFERENCES Nutzer(NutzerID) ON DELETE CASCADE
        )
    ");

    // Nährwerte-Tabelle für Spoonacular API Integration
    $db->exec("
        CREATE TABLE RezeptNaehrwerte (
            RezeptID INT PRIMARY KEY,
            Kalorien DECIMAL(8,2),
            Protein DECIMAL(8,2),
            Kohlenhydrate DECIMAL(8,2),
            Fett DECIMAL(8,2),
            Ballaststoffe DECIMAL(8,2),
            Zucker DECIMAL(8,2),
            Natrium DECIMAL(8,2),
            Berechnet_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE
        )
    ");

    // Cache-Tabelle für API-Aufrufe
    $db->exec("
        CREATE TABLE api_cache (
            cache_key VARCHAR(255) PRIMARY KEY,
            naehrwerte_json TEXT NOT NULL,
            erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // API-Log-Tabelle für Monitoring
    $db->exec("
        CREATE TABLE api_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            endpoint VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            response_time DECIMAL(10,2),
            error_message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Zutat
    $db->exec("
        CREATE TABLE Zutat (
            ZutatID INT AUTO_INCREMENT PRIMARY KEY,
            Name VARCHAR(100) NOT NULL
        )
    ");

    // Utensil
    $db->exec("
        CREATE TABLE Utensil (
            UtensilID INT AUTO_INCREMENT PRIMARY KEY,
            Name VARCHAR(100) NOT NULL
        )
    ");

    // Kategorie
    $db->exec("
        CREATE TABLE Kategorie (
            KategorieID INT AUTO_INCREMENT PRIMARY KEY,
            Bezeichnung VARCHAR(100) NOT NULL
        )
    ");

    // RezeptZutat
    $db->exec("
        CREATE TABLE RezeptZutat (
            RezeptID INT,
            Zutat VARCHAR(100),
            Menge VARCHAR(50),
            Einheit VARCHAR(20),
            PRIMARY KEY (RezeptID, Zutat),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE
        )
    ");

    // RezeptUtensil
    $db->exec("
        CREATE TABLE RezeptUtensil (
            RezeptID INT,
            UtensilID INT,
            PRIMARY KEY (RezeptID, UtensilID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (UtensilID) REFERENCES Utensil(UtensilID)
        )
    ");

    // RezeptKategorie
    $db->exec("
        CREATE TABLE RezeptKategorie (
            RezeptID INT,
            KategorieID INT,
            PRIMARY KEY (RezeptID, KategorieID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (KategorieID) REFERENCES Kategorie(KategorieID)
        )
    ");

    // Testnutzer
    $stmt = $db->prepare("INSERT INTO Nutzer (Benutzername, Email, PasswortHash, RegistrierungsDatum, IstAdmin)
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), date('Y-m-d'), true]);

    // Beispieldaten
    $db->exec("INSERT INTO Preisklasse (Preisspanne) VALUES ('unter 5€'), ('5–10€')");
    $db->exec("INSERT INTO Portionsgröße (Angabe) VALUES ('1 Person'), ('2 Personen')");
    $db->exec("INSERT INTO Utensil (Name) VALUES ('Topf'), ('Pfanne')");
    $db->exec("INSERT INTO Kategorie (Bezeichnung) VALUES ('Vegetarisch'), ('Schnell')");

    $db->exec("
        INSERT INTO Rezept (Titel, Zubereitung, BildPfad, ErstellerID, PreisklasseID, PortionsgrößeID, Erstellungsdatum)
        VALUES ('Testrezept', 'Kochen und essen.', '/images/test.jpg', 1, 1, 1, CURDATE())
    ");

    $db->exec("INSERT INTO RezeptZutat VALUES (1, 'Pasta', '200', 'g')");
    $db->exec("INSERT INTO RezeptKategorie VALUES (1, 1)");
    $db->exec("INSERT INTO RezeptUtensil VALUES (1, 1)");
    $db->exec("INSERT INTO Bewertung VALUES (1, 1, 5, CURDATE())");

    $db->commit();
    echo "MySQL-Datenbank erfolgreich initialisiert.";
} catch (Exception $e) {
    $db->rollBack();
    die("Fehler bei Initialisierung: " . $e->getMessage());
}

