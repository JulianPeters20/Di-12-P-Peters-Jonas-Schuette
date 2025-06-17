<?php
declare(strict_types=1);

class Rezept {
    public int $RezeptID;
    public string $Titel;
    public string $Zubereitung;
    public ?string $BildPfad;
    public int $ErstellerID;
    public ?string $ErstellerName;
    public ?string $ErstellerEmail;
    public int $PreisklasseID;
    public int $PortionsgroesseID;
    public string $Erstellungsdatum;

    public array $Kategorien = [];
    public array $Utensilien = [];
    public array $Zutaten = [];

    public function __construct(
        int $RezeptID,
        string $Titel,
        string $Zubereitung,
        ?string $BildPfad,
        int $ErstellerID,
        ?string $ErstellerName,
        ?string $ErstellerEmail,
        int $PreisklasseID,
        int $PortionsgroesseID,
        string $Erstellungsdatum,
        array $Kategorien = [],
        array $Utensilien = [],
        array $Zutaten = []
    ) {
        $this->RezeptID = $RezeptID;
        $this->Titel = $Titel;
        $this->Zubereitung = $Zubereitung;
        $this->BildPfad = $BildPfad;
        $this->ErstellerID = $ErstellerID;
        $this->ErstellerName = $ErstellerName;
        $this->ErstellerEmail = $ErstellerEmail;
        $this->PreisklasseID = $PreisklasseID;
        $this->PortionsgroesseID = $PortionsgroesseID;
        $this->Erstellungsdatum = $Erstellungsdatum;

        $this->Kategorien = $Kategorien;
        $this->Utensilien = $Utensilien;
        $this->Zutaten = $Zutaten;
    }
}