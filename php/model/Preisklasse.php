<?php
declare(strict_types=1);

class Preisklasse {
    public int $PreisklasseID;
    public string $Preisspanne;

    public function __construct(int $PreisklasseID, string $Preisspanne) {
        $this->PreisklasseID = $PreisklasseID;
        $this->Preisspanne = $Preisspanne;
    }
}