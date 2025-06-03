# README – Broke & Hungry

**Abgabe zu Aufgabenblatt 3 – Webprogrammierung (Di-12-P)**

## Projektgruppe
- Julian Peters
- Leon Jonas

---

## Aktuell umgesetzte Funktionen (Stand: Aufgabenblatt 3)

- Rezeptübersicht mit Suchfunktion (nach Titel, Kategorie und Autor)
- Detailseite für jedes Rezept
- Erstellung neuer Rezepte durch angemeldete Nutzer
- Nutzerregistrierung und -anmeldung
- Nutzerprofil mit Übersicht eigener Rezepte
- Bearbeiten und Löschen eigener Rezepte
- Vollständig integrierte Dummy-Daten (inkl. Zutaten, Zubereitung, etc.)
- Zugriffsschutz: Rezept erstellen und bearbeiten nur mit Login
- Session-basierte Fehlermeldungen und Statusmeldungen

---

## Geplante und in Bearbeitung befindliche Erweiterungen

- Einführung einer Administratorrolle mit exklusivem Zugriff auf die Nutzerliste
- Mehrfachauswahl von Kategorien beim Erstellen eines Rezepts (z. B. vegan und günstig gleichzeitig)
- Bewertungssystem: Nutzer können Rezepte mit bis zu 5 Sternen bewerten
- Erweiterung der Startseite um einen Bereich für die bestbewerteten Rezepte
- Nutzer können Rezepte speichern und später erneut abrufen
- Erweiterung der Rezeptdetails um Portionsgröße und Preisstufe
- Perspektivisch: Persistente Datenspeicherung über JSON-Dateien oder Datenbankanbindung

---

## Hinweise zur Nutzung

- Nur angemeldete Nutzer können neue Rezepte erstellen, bearbeiten oder speichern
- Nicht angemeldete Nutzer werden bei geschützten Aktionen automatisch zur Anmeldung weitergeleitet
- Die aktuelle Datenhaltung basiert auf statischen Dummy-Daten im Speicher
- Sessions werden beim Schließen des Browsers beendet (kein dauerhafter Login)
