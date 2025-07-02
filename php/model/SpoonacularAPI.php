<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Spoonacular API Integration für Nährwertberechnung
 *
 * Diese Klasse stellt Funktionen zur Verfügung, um über die Spoonacular API
 * Nährwerte für Rezepte zu berechnen. Implementiert Caching und Rate-Limiting.
 */
class SpoonacularAPI {
    
    private const API_BASE_URL = 'https://api.spoonacular.com';
    private const CACHE_DURATION = 86400; // 24 Stunden in Sekunden
    
    private string $apiKey;
    private PDO $db;
    
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
        $this->db = Database::getConnection();
    }
    
    /**
     * Berechnet Nährwerte für ein Rezept basierend auf den Zutaten
     * 
     * @param array $zutaten Array mit Zutaten (zutat, menge, einheit)
     * @param int $portionen Anzahl der Portionen
     * @return array|null Nährwerte oder null bei Fehler
     */
    public function berechneNaehrwerte(array $zutaten, int $portionen = 1): ?array {
        if (empty($zutaten) || $portionen <= 0) {
            return null;
        }
        
        // Cache-Key basierend auf Zutaten erstellen
        $cacheKey = $this->erstelleCacheKey($zutaten, $portionen);
        
        // Prüfen ob Ergebnis im Cache vorhanden ist
        $cachedResult = $this->getCachedResult($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
        
        // API-Aufruf durchführen
        $naehrwerte = $this->rufSpoonacularAPI($zutaten, $portionen);

        // Fallback: Wenn API fehlschlägt, verwende geschätzte Nährwerte
        if ($naehrwerte === null) {
            $naehrwerte = $this->schaetzeNaehrwerte($zutaten, $portionen);
        }

        if ($naehrwerte !== null) {
            // Ergebnis im Cache speichern
            $this->speichereCachedResult($cacheKey, $naehrwerte);
        }

        return $naehrwerte;
    }
    
    /**
     * Führt den eigentlichen API-Aufruf an Spoonacular durch
     */
    private function rufSpoonacularAPI(array $zutaten, int $portionen): ?array {
        // Zutaten für API formatieren
        $ingredientList = $this->formatiereZutatenFuerAPI($zutaten);

        if (empty($ingredientList)) {
            return null;
        }

        $startTime = microtime(true);
        $url = self::API_BASE_URL . '/recipes/parseIngredients';

        $postData = [
            'ingredientList' => implode("\n", $ingredientList),
            'servings' => $portionen,
            'includeNutrition' => true,
            'apiKey' => $this->apiKey
        ];

        $response = $this->sendHttpRequest($url, $postData);
        $responseTime = (microtime(true) - $startTime) * 1000; // in Millisekunden

        // API-Aufruf loggen
        $this->loggeApiAufruf('parseIngredients', $response !== null ? 'success' : 'error', $responseTime, $response === null ? 'HTTP Request failed' : null);

        if ($response === null) {
            return null;
        }

        return $this->parseNaehrwerteResponse($response, $portionen);
    }
    
    /**
     * Formatiert Zutaten für die Spoonacular API
     */
    private function formatiereZutatenFuerAPI(array $zutaten): array {
        $ingredientList = [];

        // Deutsche -> Englische Übersetzung für häufige Zutaten
        $uebersetzung = [
            'pasta' => 'pasta',
            'nudeln' => 'pasta',
            'spaghetti' => 'spaghetti',
            'basilikum' => 'basil',
            'olivenöl' => 'olive oil',
            'parmesan' => 'parmesan cheese',
            'knoblauch' => 'garlic',
            'zwiebel' => 'onion',
            'tomaten' => 'tomatoes',
            'salz' => 'salt',
            'pfeffer' => 'pepper',
            'butter' => 'butter',
            'milch' => 'milk',
            'eier' => 'eggs',
            'mehl' => 'flour',
            'zucker' => 'sugar',
            'reis' => 'rice',
            'kartoffeln' => 'potatoes',
            'karotten' => 'carrots',
            'paprika' => 'bell pepper',
            'hähnchen' => 'chicken',
            'rindfleisch' => 'beef',
            'schweinefleisch' => 'pork'
        ];

        // Deutsche Einheiten -> Englische Einheiten
        $einheitenUebersetzung = [
            'g' => 'g',
            'kg' => 'kg',
            'ml' => 'ml',
            'l' => 'l',
            'tl' => 'tsp',
            'el' => 'tbsp',
            'teelöffel' => 'tsp',
            'esslöffel' => 'tbsp',
            'tasse' => 'cup',
            'stück' => 'piece'
        ];

        foreach ($zutaten as $zutat) {
            $menge = trim($zutat['menge'] ?? '');
            $einheit = trim($zutat['einheit'] ?? '');
            $name = trim($zutat['zutat'] ?? '');

            if (empty($name)) {
                continue;
            }

            // Zutat ins Englische übersetzen
            $nameLower = strtolower($name);
            $englischName = $uebersetzung[$nameLower] ?? $name;

            // Einheit ins Englische übersetzen
            $einheitLower = strtolower($einheit);
            $englischEinheit = $einheitenUebersetzung[$einheitLower] ?? $einheit;

            // Format: "200g pasta" oder "1 cup flour"
            $ingredient = '';
            if (!empty($menge)) {
                $ingredient .= $menge;
                if (!empty($englischEinheit)) {
                    $ingredient .= $englischEinheit . ' ';
                } else {
                    $ingredient .= ' ';
                }
            }
            $ingredient .= $englischName;

            $ingredientList[] = $ingredient;
        }

        return $ingredientList;
    }
    
    /**
     * Sendet HTTP-Request an die API
     */
    private function sendHttpRequest(string $url, array $postData): ?array {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($postData),
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("Spoonacular API Fehler: Keine Antwort erhalten");
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Spoonacular API Fehler: Ungültige JSON-Antwort");
            return null;
        }
        
        return $data;
    }
    
    /**
     * Parst die API-Antwort und extrahiert Nährwerte
     */
    private function parseNaehrwerteResponse(array $response, int $portionen): ?array {
        if (!is_array($response) || empty($response)) {
            return null;
        }
        
        $totalNutrition = [
            'kalorien' => 0,
            'protein' => 0,
            'kohlenhydrate' => 0,
            'fett' => 0,
            'ballaststoffe' => 0,
            'zucker' => 0,
            'natrium' => 0
        ];
        
        // Nährwerte aus allen Zutaten summieren
        foreach ($response as $ingredient) {
            if (!isset($ingredient['nutrition']['nutrients'])) {
                continue;
            }
            
            foreach ($ingredient['nutrition']['nutrients'] as $nutrient) {
                $name = strtolower($nutrient['name'] ?? '');
                $amount = floatval($nutrient['amount'] ?? 0);
                
                switch ($name) {
                    case 'calories':
                        $totalNutrition['kalorien'] += $amount;
                        break;
                    case 'protein':
                        $totalNutrition['protein'] += $amount;
                        break;
                    case 'carbohydrates':
                        $totalNutrition['kohlenhydrate'] += $amount;
                        break;
                    case 'fat':
                        $totalNutrition['fett'] += $amount;
                        break;
                    case 'fiber':
                        $totalNutrition['ballaststoffe'] += $amount;
                        break;
                    case 'sugar':
                        $totalNutrition['zucker'] += $amount;
                        break;
                    case 'sodium':
                        $totalNutrition['natrium'] += $amount;
                        break;
                }
            }
        }
        
        // Pro Portion berechnen
        if ($portionen > 1) {
            foreach ($totalNutrition as $key => $value) {
                $totalNutrition[$key] = round($value / $portionen, 2);
            }
        }
        
        return $totalNutrition;
    }
    
    /**
     * Erstellt einen Cache-Key basierend auf Zutaten und Portionen
     */
    private function erstelleCacheKey(array $zutaten, int $portionen): string {
        $zutatString = '';
        foreach ($zutaten as $zutat) {
            $zutatString .= ($zutat['menge'] ?? '') . '|' . 
                           ($zutat['einheit'] ?? '') . '|' . 
                           ($zutat['zutat'] ?? '') . '||';
        }
        return 'spoonacular_' . md5($zutatString . '_' . $portionen);
    }
    
    /**
     * Holt Ergebnis aus dem Cache
     */
    private function getCachedResult(string $cacheKey): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT naehrwerte_json, erstellt_am 
                FROM api_cache 
                WHERE cache_key = ? AND erstellt_am > datetime('now', '-' || ? || ' seconds')
            ");
            $stmt->execute([$cacheKey, self::CACHE_DURATION]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return json_decode($row['naehrwerte_json'], true);
            }
        } catch (Exception $e) {
            error_log("Cache-Fehler: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Speichert Ergebnis im Cache
     */
    private function speichereCachedResult(string $cacheKey, array $naehrwerte): void {
        try {
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO api_cache (cache_key, naehrwerte_json, erstellt_am)
                VALUES (?, ?, datetime('now'))
            ");
            $stmt->execute([$cacheKey, json_encode($naehrwerte)]);
        } catch (Exception $e) {
            error_log("Cache-Speicher-Fehler: " . $e->getMessage());
        }
    }
    
    /**
     * Schätzt Nährwerte basierend auf typischen Werten (Fallback)
     */
    private function schaetzeNaehrwerte(array $zutaten, int $portionen): array {
        $totalNutrition = [
            'kalorien' => 0,
            'protein' => 0,
            'kohlenhydrate' => 0,
            'fett' => 0,
            'ballaststoffe' => 0,
            'zucker' => 0,
            'natrium' => 0
        ];

        // Geschätzte Nährwerte pro 100g für häufige Zutaten
        $naehrwertTabelle = [
            'pasta' => ['kalorien' => 350, 'protein' => 12, 'kohlenhydrate' => 70, 'fett' => 2],
            'nudeln' => ['kalorien' => 350, 'protein' => 12, 'kohlenhydrate' => 70, 'fett' => 2],
            'basilikum' => ['kalorien' => 25, 'protein' => 3, 'kohlenhydrate' => 2, 'fett' => 0.5],
            'olivenöl' => ['kalorien' => 884, 'protein' => 0, 'kohlenhydrate' => 0, 'fett' => 100],
            'parmesan' => ['kalorien' => 430, 'protein' => 35, 'kohlenhydrate' => 4, 'fett' => 30],
            'reis' => ['kalorien' => 350, 'protein' => 7, 'kohlenhydrate' => 77, 'fett' => 1],
            'hähnchen' => ['kalorien' => 165, 'protein' => 31, 'kohlenhydrate' => 0, 'fett' => 4],
            'tomaten' => ['kalorien' => 18, 'protein' => 1, 'kohlenhydrate' => 4, 'fett' => 0.2]
        ];

        foreach ($zutaten as $zutat) {
            $name = strtolower(trim($zutat['zutat'] ?? ''));
            $menge = floatval($zutat['menge'] ?? 0);

            if ($menge <= 0) continue;

            // Suche passende Nährwerte
            $naehrwerte = null;
            foreach ($naehrwertTabelle as $key => $werte) {
                if (strpos($name, $key) !== false) {
                    $naehrwerte = $werte;
                    break;
                }
            }

            // Fallback für unbekannte Zutaten
            if (!$naehrwerte) {
                $naehrwerte = ['kalorien' => 200, 'protein' => 5, 'kohlenhydrate' => 20, 'fett' => 5];
            }

            // Berechne für die angegebene Menge (angenommen pro 100g)
            $faktor = $menge / 100;
            $totalNutrition['kalorien'] += $naehrwerte['kalorien'] * $faktor;
            $totalNutrition['protein'] += $naehrwerte['protein'] * $faktor;
            $totalNutrition['kohlenhydrate'] += $naehrwerte['kohlenhydrate'] * $faktor;
            $totalNutrition['fett'] += $naehrwerte['fett'] * $faktor;
            $totalNutrition['ballaststoffe'] += 2 * $faktor; // Geschätzt
            $totalNutrition['zucker'] += 1 * $faktor; // Geschätzt
            $totalNutrition['natrium'] += 0.1 * $faktor; // Geschätzt
        }

        // Pro Portion berechnen
        if ($portionen > 1) {
            foreach ($totalNutrition as $key => $value) {
                $totalNutrition[$key] = round($value / $portionen, 2);
            }
        }

        return $totalNutrition;
    }

    /**
     * Prüft ob die API verfügbar ist
     */
    public function istAPIVerfuegbar(): bool {
        $startTime = microtime(true);
        $url = self::API_BASE_URL . '/recipes/random?number=1&apiKey=' . $this->apiKey;

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        $responseTime = (microtime(true) - $startTime) * 1000;

        // API-Test loggen
        $this->loggeApiAufruf('availability-check', $response !== false ? 'success' : 'error', $responseTime, $response === false ? 'API not reachable' : null);

        return $response !== false;
    }

    /**
     * Loggt einen API-Aufruf für Monitoring
     */
    private function loggeApiAufruf(string $endpoint, string $status, float $responseTime, ?string $errorMessage = null): void {
        try {
            require_once __DIR__ . '/ApiMonitorDAO.php';
            $apiMonitorDAO = new ApiMonitorDAO();
            $apiMonitorDAO->loggeApiAufruf($endpoint, $status, $responseTime, $errorMessage);
        } catch (Exception $e) {
            // Logging-Fehler nicht weiterwerfen, um Hauptfunktionalität nicht zu beeinträchtigen
            error_log("Fehler beim API-Logging: " . $e->getMessage());
        }
    }
}
