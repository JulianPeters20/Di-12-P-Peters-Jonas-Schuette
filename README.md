# README – Broke & Hungry

*Abgabe zu Aufgabenblatt 6 – Webprogrammierung (Di-12-P)*

**Projektgruppe**
- Julian Peters
- Leon Jonas

---

## Wichtige Funktionen und Datenschutz

- Rechtlich korrekte **Datenschutzerklärung**, **Impressum** und **Nutzungsbedingungen** (im Footer verlinkt)
- Registrierung mit **Opt-In-Checkboxen** für Datenschutz & Nutzungsbedingungen (sind jeweilig verlinkt)
- Registrierung ausschließlich über **E-Mail-Adresse** (Benutzername ist rein optional, nur Anzeige)
- Es werden **nur technisch notwendige Session-Cookies** verwendet (kein Tracking, kein Banner)
- **DSGVO-Rechte** (Auskunft/Löschung) werden auf Wunsch per E-Mail gewährt (keine Automatisierung)
- Keine Anzeige, ob eine E-Mail bereits vergeben ist (**kein Enumeration-Leak**)
- **Registrierungsmail** wird als klickbare HTML-Datei in `/data/mails/` simuliert (kein echter E-Mail-Versand)
- Nutzer erhält nach Absenden des Formulars einen Link zur generierten HTML-Mail („Weitere Infos finden Sie in der Datei ...“)

## API-Integration und Nährwerte

- **Spoonacular API** für automatische Nährwertberechnung integriert
- **DSGVO-konforme Einwilligung** vor API-Nutzung (2-Klick-Lösung)
- **Caching-System** zur Reduzierung von API-Aufrufen und besserer Performance
- **Admin-Monitor** für API-Statistiken, Fehlerprotokollierung und Cache-Verwaltung
- Nährwerte werden pro Portion berechnet und in der Datenbank gespeichert

## Sicherheitsfeatures

- **CSRF-Schutz** für alle Formulare und AJAX-Operationen
- **Rate Limiting** gegen Brute-Force-Angriffe (5 Versuche/15 Min)
- **Sichere Session-Konfiguration** (HttpOnly, Secure, SameSite)
- **Input-Validierung** und XSS-Schutz durch htmlspecialchars()
- **Sichere Datei-Uploads** mit MIME-Type- und Größenvalidierung
- **HTTP-Security-Header** (CSP, X-Frame-Options, etc.)
- **Prepared Statements** gegen SQL-Injection
- **Autorisierungsprüfungen** (Nutzer können nur eigene Rezepte bearbeiten)

---

## Hinweise zur Nutzung und Test

- `info@brokeandhungry.de` ist eine Dummy-Adresse für unsere rechtskonforme Website
- **Die Links in simulierten Mails sind absolute URLs, angepasst auf unseren lokalen Serverpfad:**  
  `http://localhost/Di-12-P-Peters-Jonas-Schuette/`
- **Hinweis für Prüfer:innen:**  
  Sollte Ihr Projekt einen anderen lokalen Installations-Pfad besitzen, passen Sie bitte im Browser die URL entsprechend an:
    - Beispiel: Kopieren Sie den Link aus der HTML-Datei, ersetzen den vorderen Teil durch Ihren eigenen und fügen Sie ihn in die Browser-Adresszeile ein.
- Das Verzeichnis `/data/mails/` muss existieren und Schreibrechte für PHP besitzen
- Der Link „Passwort zurücksetzen“ in der simulierten Registrierungs-Mail ist klickbar, dient jedoch nur als Platzhalter und leitet aktuell immer auf die Startseite zurück. Eine echte Passwort-Reset-Funktion ist im Rahmen dieses Projekts nicht implementiert.
- Nur angemeldete Nutzer können Rezepte erstellen, bearbeiten oder speichern
- Nicht angemeldete Nutzer werden bei geschützten Aktionen automatisch zur Anmeldung weitergeleitet
- **Die Datenbank muss mit `/init-database.php` oder `/init-database-mysql.php` initialisiert werden**

---

## Offene und geplante Erweiterungen

- Weitere Dummy-Rezepte hinzufügen
- Sortier-/Filterfunktionen für beliebte/bewertete Rezepte (benötigt mehr Beispieldaten)
- Responsiveness verbessern -> Burgermenü für mobile/kleinere Ansichten

