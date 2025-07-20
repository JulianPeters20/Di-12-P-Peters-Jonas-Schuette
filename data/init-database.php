<?php
require_once 'pdo.php';

$db = Database::getConnection();

try {
    $db->beginTransaction();

    // Bestehende Tabellen löschen (für Neuinitialisierung)
    // Reihenfolge beachtet Foreign Key Constraints
    // WICHTIG: Tabellen werden gelöscht um eine saubere Neuinitialisierung zu gewährleisten
    // und Konflikte mit geänderten Tabellenstrukturen zu vermeiden
    $tabellen = ['RezeptKategorie', 'RezeptUtensil', 'RezeptZutat', 'Bewertung', 'GespeicherteRezepte',
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

    // GespeicherteRezepte - Tabelle für Nutzer-Favoriten
    $db->exec("
        CREATE TABLE GespeicherteRezepte (
            NutzerID INTEGER,
            RezeptID INTEGER,
            GespeichertAm TEXT NOT NULL DEFAULT CURRENT_DATE,
            PRIMARY KEY (NutzerID, RezeptID),
            FOREIGN KEY (NutzerID) REFERENCES Nutzer(NutzerID) ON DELETE CASCADE,
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE
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
    $nutzerStmt->execute(['Leon', 'admin1@example.com', password_hash('AdminLeon1', PASSWORD_DEFAULT), '2025-04-01', 1]);
    $nutzerStmt->execute(['Julian', 'admin2@example.com', password_hash('JulianAdmin2', PASSWORD_DEFAULT), '2025-04-01', 1]);
    $nutzerStmt->execute(['Dibo', 'admin3@example.com', password_hash('DiboPass3', PASSWORD_DEFAULT), '2025-04-01', 1]);

    // Beispiel-Nutzer verteilt bis zum aktuellen Datum
    $nutzerStmt->execute(['max_muster', 'max@example.com', password_hash('MaxPass123', PASSWORD_DEFAULT), '2025-04-15', 0]);
    $nutzerStmt->execute(['anna_koch', 'anna@example.com', password_hash('AnnaKocht8', PASSWORD_DEFAULT), '2025-05-10', 0]);
    $nutzerStmt->execute(['tom_baker', 'tom@example.com', password_hash('TomBakes9', PASSWORD_DEFAULT), '2025-06-05', 0]);
    $nutzerStmt->execute(['lisa_vegan', 'lisa@example.com', password_hash('LisaVeg7', PASSWORD_DEFAULT), '2025-06-25', 0]);
    $nutzerStmt->execute(['peter_grill', 'peter@example.com', password_hash('PeterGrill4', PASSWORD_DEFAULT), date('Y-m-d'), 0]);

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
        ('Schüssel'),
        ('Schneebesen'),
        ('Nudelholz'),
        ('Grillpfanne'),
        ('Wok'),
        ('Dampfgarer'),
        ('Reibe'),
        ('Zitronenpresse'),
        ('Backblech'),
        ('Auflaufform'),
        ('Mörser'),
        ('Küchenwaage'),
        ('Messbecher')
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
    ('Gesund'),
    ('Französisch'),
    ('Indisch'),
    ('Suppen'),
    ('Eintöpfe'),
    ('Fingerfood'),
    ('Comfort Food')
");
    $db->exec("
        INSERT INTO Zutat (Name) VALUES
        ('Pasta'), ('Tomaten'), ('Basilikum'), ('Olivenöl'), ('Parmesan'),
        ('Zwiebeln'), ('Knoblauch'), ('Karotten'), ('Sellerie'), ('Paprika'),
        ('Champignons'), ('Brokkoli'), ('Spinat'), ('Zucchini'), ('Aubergine'),
        ('Kartoffeln'), ('Reis'), ('Quinoa'), ('Linsen'), ('Kichererbsen'),
        ('Hähnchenbrust'), ('Rindfleisch'), ('Lachs'), ('Garnelen'), ('Eier'),
        ('Milch'), ('Sahne'), ('Butter'), ('Mehl'), ('Zucker'),
        ('Salz'), ('Pfeffer'), ('Oregano'), ('Thymian'), ('Rosmarin'),
        ('Zitronen'), ('Ingwer'), ('Chili'), ('Kokosmilch'), ('Sojasauce'), ('Kakao')
    ");

    // Beispiel-Rezepte
    $rezeptStmt = $db->prepare("
        INSERT INTO Rezept (Titel, Zubereitung, BildPfad, ErstellerID, PreisklasseID, PortionsgroesseID, Erstellungsdatum)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    // Rezept 1: Nudeln mit Pesto (Leon - 7 Bewertungen)
    $rezeptStmt->execute([
        'Nudeln mit Pesto',
        'Pasta kochen, Pesto herstellen und unterheben.',
        'images/pesto.jpg',
        1, // Leon
        2, // 5-10€
        2, // 2 Personen
        '2025-04-10'
    ]);

    // Rezept 2: Gemüsecurry (Julian - 5 Bewertungen)
    $rezeptStmt->execute([
        'Cremiges Gemüsecurry',
        'Reis kochen. Zwiebeln und Knoblauch anbraten, Gemüse hinzufügen, mit Kokosmilch ablöschen und würzen. 20 Min köcheln lassen.',
        'images/reis_mit_curry.jpg',
        2, // Julian
        2, // 5-10€
        4, // 3 Personen
        '2025-04-20'
    ]);

    // Rezept 3: Gegrillter Lachs (Dibo - 7 Bewertungen)
    $rezeptStmt->execute([
        'Gegrillter Lachs mit Gemüse',
        'Lachs würzen und grillen, Gemüse parallel zubereiten. Mit Zitrone servieren.',
        'images/lachs.jpg',
        3, // Dibo
        4, // 15-20€
        2, // 2 Personen
        '2025-05-01'
    ]);

    // Rezept 4: Quinoa Salat (max_muster - 4 Bewertungen)
    $rezeptStmt->execute([
        'Mediterraner Quinoa Salat',
        'Quinoa kochen, abkühlen lassen. Mit Zwiebeln, Olivenöl und Zitrone mischen.',
        'images/quinoa_salat.jpg',
        4, // max_muster
        2, // 5-10€
        3, // 3 Personen
        '2025-05-20'
    ]);

    // Rezept 5: Hähnchen Stir-Fry (anna_koch - 3 Bewertungen)
    $rezeptStmt->execute([
        'Asiatisches Hähnchen Stir-Fry',
        'Hähnchen in Streifen schneiden, im Wok anbraten. Gemüse hinzufügen und mit Sojasauce würzen.',
        'images/stirfry.jpg',
        5, // anna_koch
        3, // 10-15€
        4, // 4 Personen
        '2025-06-15'
    ]);

    // Rezept 6: Schokoladen Brownies (peter_grill - 0 Bewertungen, neuestes Rezept)
    $rezeptStmt->execute([
        'Saftige Schokoladen Brownies',
        'Butter schmelzen, mit Zucker und Eier verrühren. Mehl und Kakao unterrühren und backen.',
        'images/brownies.jpg',
        8, // peter_grill
        2, // 5-10€
        5, // Familie (5+)
        date('Y-m-d') // jeweils "heutiges" Datum
    ]);

    // Zutaten zu den Rezepten
    $db->exec("
        INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit) VALUES
        -- Rezept 1: Nudeln mit Pesto
        (1, 'Pasta', '200', 'g'),
        (1, 'Basilikum', '50', 'g'),
        (1, 'Olivenöl', '30', 'ml'),
        (1, 'Parmesan', '20', 'g'),

        -- Rezept 2: Gemüsecurry
        (2, 'Zwiebeln', '2', 'Stück'),
        (2, 'Knoblauch', '3', 'Zehen'),
        (2, 'Paprika', '2', 'Stück'),
        (2, 'Zucchini', '1', 'Stück'),
        (2, 'Kokosmilch', '100', 'ml'),
        (2, 'Reis', '200', 'g'),

        -- Rezept 3: Gegrillter Lachs
        (3, 'Lachs', '400', 'g'),
        (3, 'Brokkoli', '300', 'g'),
        (3, 'Karotten', '2', 'Stück'),
        (3, 'Zitronen', '1', 'Stück'),
        (3, 'Olivenöl', '20', 'ml'),

        -- Rezept 4: Quinoa Salat
        (4, 'Quinoa', '150', 'g'),
        (4, 'Tomaten', '3', 'Stück'),
        (4, 'Zwiebeln', '1', 'Stück'),
        (4, 'Olivenöl', '40', 'ml'),
        (4, 'Zitronen', '1', 'Stück'),

        -- Rezept 5: Hähnchen Stir-Fry
        (5, 'Hähnchenbrust', '300', 'g'),
        (5, 'Paprika', '2', 'Stück'),
        (5, 'Brokkoli', '200', 'g'),
        (5, 'Sojasauce', '30', 'ml'),
        (5, 'Ingwer', '10', 'g'),

        -- Rezept 6: Schokoladen Brownies
        (6, 'Butter', '200', 'g'),
        (6, 'Zucker', '150', 'g'),
        (6, 'Eier', '3', 'Stück'),
        (6, 'Mehl', '100', 'g')
    ");

    // Utensilien zu den Rezepten
    $db->exec("
        INSERT INTO RezeptUtensil (RezeptID, UtensilID) VALUES
        -- Rezept 1: Nudeln mit Pesto (Topf, Kochlöffel, Sieb, Mixer)
        (1, 1), (1, 3), (1, 4), (1, 8),

        -- Rezept 2: Gemüsecurry (Topf, Kochlöffel, Schneidebrett, Messer)
        (2, 1), (2, 3), (2, 5), (2, 6),

        -- Rezept 3: Gegrillter Lachs (Grillpfanne, Schneidebrett, Messer, Zitronenpresse)
        (3, 12), (3, 5), (3, 6), (3, 18),

        -- Rezept 4: Quinoa Salat (Topf, Sieb, Schüssel, Schneidebrett, Messer)
        (4, 1), (4, 4), (4, 9), (4, 5), (4, 6),

        -- Rezept 5: Hähnchen Stir-Fry (Wok, Kochlöffel, Schneidebrett, Messer)
        (5, 13), (5, 3), (5, 5), (5, 6),

        -- Rezept 6: Schokoladen Brownies (peter_grill - Schüssel, Handrührgerät, Backblech, Backofen)
        (6, 9), (6, 16), (6, 20), (6, 7)
    ");

    // Kategorien zu den Rezepten
    $db->exec("
        INSERT INTO RezeptKategorie (RezeptID, KategorieID) VALUES
        -- Rezept 1: Nudeln mit Pesto (Vegetarisch, Schnell, Italienisch)
        (1, 1), (1, 2), (1, 10),

        -- Rezept 2: Gemüsecurry (Vegetarisch, Vegan, Gesund, Hauptgericht)
        (2, 1), (2, 3), (2, 26), (2, 18),

        -- Rezept 3: Gegrillter Lachs (Gesund, High Protein, Grillen, Hauptgericht)
        (3, 26), (3, 23), (3, 15), (3, 18),

        -- Rezept 4: Quinoa Salat (Vegetarisch, Gesund, Salate, Meal Prep)
        (4, 1), (4, 26), (4, 14), (4, 11),

        -- Rezept 5: Hähnchen Stir-Fry (Asiatisch, High Protein, Schnell, Hauptgericht)
        (5, 9), (5, 23), (5, 2), (5, 18),

        -- Rezept 6: Schokoladen Brownies (Dessert, Kinderfreundlich, Comfort Food)
        (6, 5), (6, 12), (6, 32)
    ");

    // Beispiel-Bewertungen für die Rezepte (keine Selbstbewertungen!)
    $db->exec("
        INSERT INTO Bewertung (RezeptID, NutzerID, Punkte, Bewertungsdatum) VALUES
        -- Rezept 1: Nudeln mit Pesto (Leon) - 7 Bewertungen
        (1, 2, 5, '2025-04-12'), -- Julian bewertet Leons Pesto
        (1, 3, 4, '2025-04-15'), -- Dibo bewertet Leons Pesto
        (1, 4, 5, '2025-04-18'), -- max_muster bewertet Leons Pesto
        (1, 5, 4, '2025-05-12'), -- anna_koch bewertet Leons Pesto
        (1, 6, 5, '2025-06-08'), -- tom_baker bewertet Leons Pesto
        (1, 7, 3, '2025-06-28'), -- lisa_vegan bewertet Leons Pesto
        (1, 8, 4, '" . date('Y-m-d') . "'), -- peter_grill bewertet Leons Pesto

        -- Rezept 2: Gemüsecurry (Julian) - 5 Bewertungen
        (2, 1, 5, '2025-04-22'), -- Leon bewertet Julians Curry
        (2, 3, 4, '2025-05-05'), -- Dibo bewertet Julians Curry
        (2, 4, 5, '2025-05-25'), -- max_muster bewertet Julians Curry
        (2, 5, 5, '2025-06-10'), -- anna_koch bewertet Julians Curry
        (2, 7, 4, '2025-06-30'), -- lisa_vegan bewertet Julians Curry

        -- Rezept 3: Gegrillter Lachs (Dibo) - 7 Bewertungen (wie gewünscht)
        (3, 1, 5, '2025-05-05'), -- Leon bewertet Dibos Lachs
        (3, 2, 5, '2025-05-08'), -- Julian bewertet Dibos Lachs
        (3, 4, 5, '2025-05-22'), -- max_muster bewertet Dibos Lachs
        (3, 5, 5, '2025-06-12'), -- anna_koch bewertet Dibos Lachs
        (3, 6, 5, '2025-06-10'), -- tom_baker bewertet Dibos Lachs
        (3, 7, 5, '2025-07-01'), -- lisa_vegan bewertet Dibos Lachs
        (3, 8, 4, '" . date('Y-m-d') . "'), -- peter_grill bewertet Dibos Lachs

        -- Rezept 4: Quinoa Salat (max_muster) - 4 Bewertungen
        (4, 1, 4, '2025-05-25'), -- Leon bewertet max_musters Salat
        (4, 2, 5, '2025-06-01'), -- Julian bewertet max_musters Salat
        (4, 5, 5, '2025-06-18'), -- anna_koch bewertet max_musters Salat
        (4, 7, 4, '2025-07-02'), -- lisa_vegan bewertet max_musters Salat

        -- Rezept 5: Hähnchen Stir-Fry (anna_koch) - 3 Bewertungen
        (5, 1, 4, '2025-06-20'), -- Leon bewertet anna_kochs Stir-Fry
        (5, 2, 5, '2025-06-25'), -- Julian bewertet anna_kochs Stir-Fry
        (5, 6, 4, '2025-07-08')  -- tom_baker bewertet anna_kochs Stir-Fry

        -- Rezept 6: Schokoladen Brownies (peter_grill) - 0 Bewertungen (neuestes Rezept)
        -- Keine Bewertungen, da gerade erst heute erstellt
    ");

    // Beispiel-Daten für gespeicherte Rezepte hinzufügen
    $db->exec("
        INSERT INTO GespeicherteRezepte (NutzerID, RezeptID, GespeichertAm)
        VALUES
        -- Dibo (Admin, ID: 3) speichert einige Rezepte
        (3, 1, date('now', '-10 days')),  -- Dibo speichert Leons Pesto
        (3, 2, date('now', '-7 days')),   -- Dibo speichert Julians Pasta
        (3, 4, date('now', '-3 days')),   -- Dibo speichert max_musters Quinoa Salat
        (3, 5, date('now', '-1 day')),    -- Dibo speichert anna_kochs Hähnchen Stir-Fry

        -- Weitere Nutzer speichern auch Rezepte (für Testzwecke)
        (2, 1, date('now', '-5 days')),   -- Julian speichert Leons Pesto
        (2, 6, date('now', '-2 days')),   -- Julian speichert peter_grills Brownies
        (4, 2, date('now', '-4 days')),   -- max_muster speichert Julians Pasta
        (5, 1, date('now', '-6 days')),   -- anna_koch speichert Leons Pesto
        (5, 3, date('now', '-1 day'))     -- anna_koch speichert Dibos Lachs
    ");

    $db->commit();

    error_log("SQLite-Datenbank erfolgreich initialisiert.");
    return true;

} catch (Exception $e) {
    $db->rollBack();
    error_log("Fehler bei SQLite-Datenbankinitialisierung: " . $e->getMessage());
    throw new RuntimeException("Datenbankinitialisierung fehlgeschlagen: " . $e->getMessage());
}