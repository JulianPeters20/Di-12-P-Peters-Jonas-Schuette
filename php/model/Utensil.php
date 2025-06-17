<?php
declare(strict_types=1);

class Utensil {
    public int $UtensilID;
    public string $Name;

    public function __construct(int $UtensilID, string $Name) {
        $this->UtensilID = $UtensilID;
        $this->Name = $Name;
    }
}