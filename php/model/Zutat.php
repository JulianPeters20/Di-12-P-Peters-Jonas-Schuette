<?php
declare(strict_types=1);

class Zutat {
    public int $ZutatID;
    public string $Name;

    public function __construct(int $ZutatID, string $Name) {
        $this->ZutatID = $ZutatID;
        $this->Name = $Name;
    }
}