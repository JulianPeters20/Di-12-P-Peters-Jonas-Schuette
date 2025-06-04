# README – Broke & Hungry

**Abgabe zu Aufgabenblatt 4 – Webprogrammierung (Di-12-P)**

## Projektgruppe
- Julian Peters
- Leon Jonas

---

## Aktuell umgesetzte Funktionen (Stand: Aufgabenblatt 4)

- Einbindung von SQLite3 und MySQL
- Wechsel zwischen SQLite3 und MySQL mit Hilfe der config
- Überarbeitung der Dummy-Daten zur Einbindung in die Datenbank

- Unser Problem, dass einige Daten nicht richitg eingebunden werden

---

## Geplante und in Bearbeitung befindliche Erweiterungen

- Einführung einer Administratorrolle mit exklusivem Zugriff auf die Nutzerliste (boolean exisitiert bereits)
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
