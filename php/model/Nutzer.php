<?php
/**
 * Nutzer-Entität für das Broke & Hungry System
 * Repräsentiert einen registrierten Benutzer mit allen relevanten Daten
 */
class Nutzer {
    public int $id;
    public string $benutzername;
    public string $email;
    public string $passwortHash;  // Gehashtes Passwort (nie Klartext speichern)
    public string $registrierungsDatum;
    public bool $istAdmin;  // Administrator-Berechtigung

    /**
     * Konstruktor für Nutzer-Objekt
     *
     * @param int $id Eindeutige Nutzer-ID
     * @param string $benutzername Anzeigename des Nutzers
     * @param string $email E-Mail-Adresse (eindeutig)
     * @param string $passwortHash Gehashtes Passwort
     * @param string $registrierungsDatum Datum der Registrierung
     * @param bool $istAdmin Administrator-Status (Standard: false)
     */
    public function __construct(
        int $id,
        string $benutzername,
        string $email,
        string $passwortHash,
        string $registrierungsDatum,
        bool $istAdmin = false
    ) {
        $this->id = $id;
        $this->benutzername = $benutzername;
        $this->email = $email;
        $this->passwortHash = $passwortHash;
        $this->registrierungsDatum = $registrierungsDatum;
        $this->istAdmin = $istAdmin;
    }
}