<?php
class RezeptBild {
    public int $id;
    public int $rezeptId;
    public string $pfad;      // Dateipfad oder URL
    public string $erstelltAm;

    public function __construct(int $id, int $rezeptId, string $pfad, string $erstelltAm) {
        $this->id = $id;
        $this->rezeptId = $rezeptId;
        $this->pfad = $pfad;
        $this->erstelltAm = $erstelltAm;
    }
}