# 🍳 Broke & Hungry - Rezept-Community

*Abgabe zu Aufgabenblatt 6 – Webprogrammierung (Di-12-P)*

**Projektgruppe**
- Julian Peters
- Leon Jonas

---

## 📋 Projektübersicht

**Broke & Hungry** ist eine moderne Rezept-Community-Plattform, die es Nutzern ermöglicht, Rezepte zu erstellen, zu teilen und zu bewerten. Die Anwendung legt besonderen Wert auf Datenschutz, Sicherheit und Benutzerfreundlichkeit.

### 🚀 Hauptfunktionen

- **Rezeptverwaltung**: Erstellen, bearbeiten, löschen und durchsuchen von Rezepten
- **Bewertungssystem**: 5-Sterne-Bewertungen mit Kommentaren
- **Live-Suche**: AJAX-basierte Echtzeitsuche nach Rezepten, Kategorien und Autoren
- **Nährwertberechnung**: Automatische Berechnung über Spoonacular API
- **Benutzerverwaltung**: Registrierung, Anmeldung, Profilverwaltung
- **Admin-Panel**: Umfassendes Monitoring und Verwaltungstools

---

## 🔒 Datenschutz & Rechtliches

- Rechtlich korrekte **Datenschutzerklärung**, **Impressum** und **Nutzungsbedingungen** (im Footer verlinkt)
- Registrierung mit **Opt-In-Checkboxen** für Datenschutz & Nutzungsbedingungen (sind jeweilig verlinkt)
- Registrierung ausschließlich über **E-Mail-Adresse** (Benutzername ist rein optional, nur Anzeige)
- Es werden **nur technisch notwendige Session-Cookies** verwendet (kein Tracking, kein Banner)
- **DSGVO-Rechte** (Auskunft/Löschung) werden auf Wunsch per E-Mail gewährt (keine Automatisierung)
- Keine Anzeige, ob eine E-Mail bereits vergeben ist (**kein Enumeration-Leak**)
- **Registrierungsmail** wird als klickbare HTML-Datei in `/data/mails/` simuliert (kein echter E-Mail-Versand)
- Nutzer erhält nach Absenden des Formulars einen Link zur generierten HTML-Mail („Weitere Infos finden Sie in der Datei ...“)

## 🔗 API-Integration und Nährwerte

- **Spoonacular API** für automatische Nährwertberechnung integriert
- **DSGVO-konforme Einwilligung** vor API-Nutzung (2-Klick-Lösung)
- **Intelligentes Caching-System** zur Reduzierung von API-Aufrufen und besserer Performance
- **Admin-Monitor** für API-Statistiken, Fehlerprotokollierung und Cache-Verwaltung
- **Fallback-System**: Geschätzte Nährwerte wenn API nicht verfügbar
- Nährwerte werden pro Portion berechnet und in der Datenbank gespeichert
- **API-Limit-Überwachung** mit wöchentlicher Nutzungsstatistik

## 🛡️ Sicherheitsfeatures

- **CSRF-Schutz** für alle Formulare und AJAX-Operationen
- **Rate Limiting** gegen Brute-Force-Angriffe (5 Versuche/15 Min)
- **Sichere Session-Konfiguration** (HttpOnly, Secure, SameSite)
- **Input-Validierung** und XSS-Schutz durch htmlspecialchars()
- **Sichere Datei-Uploads** mit MIME-Type- und Größenvalidierung
- **HTTP-Security-Header** (CSP, X-Frame-Options, etc.)
- **Prepared Statements** gegen SQL-Injection
- **Autorisierungsprüfungen** (Nutzer können nur eigene Rezepte bearbeiten)
- **Sichere Passwort-Hashing** mit password_hash()
- **Sicherheits-Logging** für verdächtige Aktivitäten

---

## 💻 Technische Features

### Frontend-Technologien
- **Progressive Enhancement**: Funktioniert mit und ohne JavaScript
- **AJAX-Live-Suche**: Echtzeitsuche ohne Seitenreload
- **Responsive Design**: Optimiert für Desktop und Mobile
- **Interactive UI**: Sterne-Bewertungssystem, Modal-Dialoge, Dropdown-Menüs
- **File Upload**: Drag & Drop mit Bildvorschau
- **Form Validation**: Client- und serverseitige Validierung

### Backend-Architektur
- **MVC-Pattern**: Saubere Trennung von Model, View und Controller
- **DAO-Pattern**: Datenbankzugriff über Data Access Objects
- **Service Layer**: Geschäftslogik in separaten Service-Klassen
- **Exception Handling**: Umfassendes Fehlerbehandlungssystem
- **Logging**: Detaillierte Protokollierung für Debugging und Monitoring

### JavaScript-Features
- **Modulare Struktur**: Aufgeteilt in main.js, search.js, rezept.js, forms.js
- **CSRF-Integration**: Automatische CSRF-Token-Behandlung in AJAX-Requests
- **Flash-Toast-System**: Benutzerfreundliche Benachrichtigungen
- **Fallback-Mechanismen**: Graceful Degradation bei deaktiviertem JavaScript

---

## 🎯 Verfügbare Seiten und Funktionen

### Öffentliche Bereiche
- **Startseite** (`/`) - Übersicht und Navigation
- **Rezepte** (`/?page=rezepte`) - Alle Rezepte mit Suche und Sortierung
- **Rezept-Details** (`/?page=rezept&id=X`) - Einzelansicht mit Bewertungen
- **Anmeldung** (`/?page=anmeldung`) - Benutzeranmeldung
- **Registrierung** (`/?page=registrierung`) - Neue Benutzer registrieren
- **Impressum** (`/?page=impressum`) - Rechtliche Informationen
- **Datenschutz** (`/?page=datenschutz`) - Datenschutzerklärung
- **Nutzungsbedingungen** (`/?page=nutzungsbedingungen`) - AGB

### Geschützte Bereiche (nur angemeldete Nutzer)
- **Neues Rezept** (`/?page=rezept-neu`) - Rezept erstellen
- **Rezept bearbeiten** (`/?page=rezept-bearbeiten&id=X`) - Eigene Rezepte bearbeiten
- **Benutzerprofil** (`/?page=nutzer`) - Profil anzeigen und bearbeiten
- **Konto löschen** (`/?page=konto-loeschen`) - Eigenes Konto löschen

### Admin-Bereiche (nur Administratoren)
- **Nutzerliste** (`/?page=nutzerliste`) - Alle Benutzer verwalten
- **API-Monitor** (`/?page=api-monitor`) - API-Statistiken und Cache-Verwaltung
- **Nutzer löschen** (`/?page=nutzer-loeschen&id=X`) - Benutzer entfernen

### AJAX-Endpunkte
- **Live-Suche** (`/api/rezepte-suche.php`) - Echtzeitsuche
- **Nährwerte berechnen** (`/api/naehrwerte-berechnen.php`) - API-Integration
- **Rezept speichern** (`/api/rezept-speichern.php`) - Favoriten verwalten
- **Rezept löschen** (`/api/rezept-loeschen.php`) - AJAX-Löschung

---

## 📋 Installation und Setup

- `info@brokeandhungry.de` ist eine Dummy-Adresse für unsere rechtskonforme Website
- **Registrierung mit Pop-up-System**: Nach der Registrierung wird ein Pop-up mit dem Bestätigungslink angezeigt
- **Relative URLs in Bestätigungslinks**: Die Links verwenden relative Pfade (`index.php?page=bestaetigeRegistrierung&code=...`)
- **JSON-basierte Vorregistrierung**: Registrierungsdaten werden temporär als JSON-Dateien in `/data/mails/` gespeichert
- **Automatische Verzeichniserstellung**: Das `/data/mails/` Verzeichnis wird automatisch erstellt, falls es nicht existiert
- **Sichere Passwort-Speicherung**: Passwörter werden bereits bei der Vorregistrierung gehasht
- **Keine Passwort-Reset-Funktion**: Eine echte Passwort-Zurücksetzen-Funktion ist nicht implementiert
- Der Link „Passwort zurücksetzen“ in der simulierten Registrierungs-Mail ist klickbar, dient jedoch nur als Platzhalter und leitet aktuell immer auf die Startseite zurück. Eine echte Passwort-Reset-Funktion ist im Rahmen dieses Projekts nicht implementiert.
- Nur angemeldete Nutzer können Rezepte erstellen, bearbeiten oder speichern
- Nicht angemeldete Nutzer werden bei geschützten Aktionen automatisch zur Anmeldung weitergeleitet
- **Die Datenbank muss mit `/init-database.php` oder `/init-database-mysql.php` initialisiert werden**

---

## 🛠️ Verwendete Technologien

### Backend
- **PHP 8.0+** - Serverseitige Programmierung
- **SQLite/MySQL** - Datenbankmanagement
- **PDO** - Sichere Datenbankabfragen
- **Spoonacular API** - Nährwertdaten

### Frontend
- **HTML5** - Semantische Struktur
- **CSS3** - Responsive Design mit Flexbox/Grid
- **JavaScript (ES6+)** - Interaktive Features
- **Progressive Enhancement** - Funktioniert ohne JavaScript

### Architektur & Patterns
- **MVC (Model-View-Controller)** - Saubere Code-Organisation
- **DAO (Data Access Object)** - Datenbankabstraktion
- **Service Layer** - Geschäftslogik-Kapselung
- **Repository Pattern** - Datenmanagement

### Sicherheit & Standards
- **CSRF Protection** - Cross-Site Request Forgery Schutz
- **XSS Prevention** - Cross-Site Scripting Schutz
- **SQL Injection Prevention** - Prepared Statements
- **DSGVO-Compliance** - Datenschutzkonform

---

## 📁 Projektstruktur

```
Di-12-P-Peters-Jonas/
├── api/                    # AJAX-Endpunkte
│   ├── naehrwerte-berechnen.php
│   ├── rezept-loeschen.php
│   ├── rezept-speichern.php
│   └── rezepte-suche.php
├── css/
│   └── style.css          # Hauptstylesheet
├── data/
│   ├── mails/             # Simulierte E-Mails
│   ├── brokeandhungry.sqlite
│   ├── init-database.php
│   └── pdo.php
├── images/                # Rezeptbilder und Assets
├── js/                    # JavaScript-Module
│   ├── main.js           # Hauptfunktionen
│   ├── search.js         # Live-Suche
│   ├── rezept.js         # Rezept-Features
│   └── forms.js          # Formular-Funktionen
├── php/
│   ├── config/           # Konfigurationsdateien
│   ├── controller/       # MVC-Controller
│   ├── model/            # Datenmodelle & DAOs
│   ├── view/             # View-Templates
│   ├── service/          # Service-Layer
│   └── include/          # Hilfsfunktionen
├── index.php             # Haupteinstiegspunkt
└── README.md
```

---

## 🚀 Features im Detail

### Rezeptverwaltung
- ✅ **CRUD-Operationen**: Erstellen, Lesen, Aktualisieren, Löschen
- ✅ **Bildupload**: Drag & Drop mit Vorschau
- ✅ **Kategorisierung**: Mehrfachauswahl von Kategorien
- ✅ **Zutaten & Utensilien**: Dynamische Listen
- ✅ **Portionsgrößen**: Flexible Portionsangaben

### Suchfunktionen
- ✅ **Live-Suche**: Echtzeitsuche ohne Seitenreload
- ✅ **Erweiterte Suche**: Titel, Kategorien, Autoren
- ✅ **Sortierung**: Nach Bewertung, Datum, Titel
- ✅ **Filterung**: Nach Kategorien und Bewertungen

### Bewertungssystem
- ✅ **5-Sterne-System**: Interaktive Sterne-Bewertung
- ✅ **Kommentare**: Textuelle Bewertungen
- ✅ **Durchschnittsbewertung**: Automatische Berechnung
- ✅ **Bewertungshistorie**: Alle Bewertungen einsehbar

### Nährwertberechnung
- ✅ **API-Integration**: Spoonacular API
- ✅ **Caching**: Intelligente Zwischenspeicherung
- ✅ **Fallback**: Geschätzte Werte bei API-Ausfall
- ✅ **DSGVO-Konform**: Einwilligung vor API-Nutzung

### Admin-Features
- ✅ **Nutzerverwaltung**: Benutzer anzeigen und löschen
- ✅ **API-Monitoring**: Statistiken und Fehlerprotokollierung
- ✅ **Cache-Verwaltung**: Cache leeren und bereinigen
- ✅ **System-Status**: API-Verfügbarkeit testen

---

## 🎯 Besondere Highlights

### Progressive Enhancement
Die Anwendung funktioniert vollständig ohne JavaScript und wird durch JavaScript-Features erweitert:
- **Ohne JS**: Normale Formular-Submissions, Server-seitige Suche
- **Mit JS**: AJAX-Suche, Modal-Dialoge, Live-Validierung

### Sicherheit First
- **Umfassender CSRF-Schutz** für alle Formulare und AJAX-Requests
- **Rate Limiting** gegen Brute-Force-Angriffe
- **Sichere Session-Konfiguration** mit HttpOnly, Secure, SameSite
- **Input-Sanitization** und XSS-Schutz überall

### DSGVO-Compliance
- **Minimale Datenerhebung**: Nur notwendige Daten
- **Transparente Einwilligungen**: Klare Opt-In-Mechanismen
- **Nutzerrechte**: Auskunft und Löschung auf Anfrage
- **Keine Tracking-Cookies**: Nur technisch notwendige Cookies

---

## 🔧 Entwicklung und Testing

### Lokale Entwicklung
```bash
# Repository klonen
git clone [repository-url]
cd Di-12-P-Peters-Jonas

# Datenbank initialisieren
php data/init-database.php

# Entwicklungsserver starten
php -S localhost:8000
```

### Testing
- **Manuelle Tests**: Alle Features in verschiedenen Browsern getestet
- **Sicherheitstests**: CSRF, XSS, SQL-Injection Prevention
- **Performance-Tests**: API-Caching und Datenbankoptimierung
- **Accessibility**: Keyboard-Navigation und Screen-Reader-Kompatibilität

---

## 📝 Offene und geplante Erweiterungen

### Mögliche Zukunftserweiterungen
- **E-Mail-Integration**: Echter E-Mail-Versand statt HTML-Dateien
- **Passwort-Reset**: Vollständige Passwort-Zurücksetzen-Funktion
- **Social Features**: Nutzer folgen, Rezept-Sammlungen teilen
- **Erweiterte Suche**: Volltext-Suche, Filter nach Nährwerten
- **Mobile App**: Progressive Web App (PWA) Features
- **Internationalisierung**: Mehrsprachige Unterstützung
- **Recipe Import**: Import von Rezepten aus anderen Quellen
- **Meal Planning**: Wochenplanung und Einkaufslisten

### Bekannte Limitierungen
- **E-Mail-Simulation**: Registrierungsmails werden als HTML-Dateien gespeichert
- **Passwort-Reset**: Nur Platzhalter-Link implementiert
- **API-Abhängigkeit**: Nährwerte abhängig von Spoonacular API
- **Lokale Entwicklung**: Optimiert für lokale Entwicklungsumgebung

---

## 📞 Support und Kontakt

**Projektgruppe Di-12-P**
- Julian Peters
- Leon Jonas

**Dummy-Kontakt für Demo-Zwecke:**
- E-Mail: `info@brokeandhungry.de`
- Website: Lokale Entwicklungsinstanz

---

## 📄 Lizenz und Rechtliches

Dieses Projekt wurde im Rahmen des Kurses "Webprogrammierung (Di-12-P)" entwickelt und dient ausschließlich Bildungszwecken.

**Verwendete APIs:**
- [Spoonacular API](https://spoonacular.com/food-api) - Nährwertdaten

**Rechtliche Hinweise:**
- Alle Texte in Impressum, Datenschutzerklärung und Nutzungsbedingungen sind Beispieltexte
- Die E-Mail-Adresse `info@brokeandhungry.de` ist eine Dummy-Adresse
- Keine kommerzielle Nutzung vorgesehen

---

*Letzte Aktualisierung: 2025-07-20*