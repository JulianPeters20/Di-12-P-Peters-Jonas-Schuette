<?php
declare(strict_types=1);

class Portionsgroesse {
    public int $PortionsgroesseID;
    public string $Angabe;

    public function __construct(int $PortionsgroesseID, string $Angabe) {
        $this->PortionsgroesseID = $PortionsgroesseID;
        $this->Angabe = $Angabe;
    }
}