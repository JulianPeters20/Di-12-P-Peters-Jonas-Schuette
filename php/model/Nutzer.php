<?php
class Nutzer {
    public int $id;
    public string $benutzername;
    public string $email;
    public string $passwortHash;
    public string $registrierungsDatum;
    public bool $istAdmin;

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