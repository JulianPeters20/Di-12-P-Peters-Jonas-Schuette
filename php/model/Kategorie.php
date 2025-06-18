<?php
declare(strict_types=1);

class Kategorie {
    public int $KategorieID;
    public string $Bezeichnung;

    public function __construct(int $KategorieID, string $Bezeichnung) {
        $this->KategorieID = $KategorieID;
        $this->Bezeichnung = $Bezeichnung;
    }
}