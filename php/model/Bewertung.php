<?php
class Bewertung {
    public int $RezeptID;
    public int $NutzerID;
    public int $Punkte;
    public string $Bewertungsdatum;

    public function __construct(int $RezeptID, int $NutzerID, int $Punkte, string $Bewertungsdatum) {
        $this->RezeptID = $RezeptID;
        $this->NutzerID = $NutzerID;
        $this->Punkte = $Punkte;
        $this->Bewertungsdatum = $Bewertungsdatum;
    }
}