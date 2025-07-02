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
     * Formatiert Zutaten für die Spoonacular API mit präziser Einheiten-Konvertierung
     */
    private function formatiereZutatenFuerAPI(array $zutaten): array {
        $ingredientList = [];

        // Erweiterte Deutsche -> Englische Übersetzung für Zutaten
        $zutatUebersetzung = [
            // Grundnahrungsmittel
            'pasta' => 'pasta',
            'nudeln' => 'pasta',
            'spaghetti' => 'spaghetti',
            'penne' => 'penne pasta',
            'fusilli' => 'fusilli pasta',
            'reis' => 'rice',
            'basmati reis' => 'basmati rice',
            'jasmin reis' => 'jasmine rice',
            'vollkornreis' => 'brown rice',
            'quinoa' => 'quinoa',
            'couscous' => 'couscous',
            'bulgur' => 'bulgur',

            // Gemüse
            'tomaten' => 'tomatoes',
            'cherry tomaten' => 'cherry tomatoes',
            'zwiebel' => 'onion',
            'zwiebeln' => 'onions',
            'rote zwiebel' => 'red onion',
            'schalotten' => 'shallots',
            'knoblauch' => 'garlic',
            'knoblauchzehe' => 'garlic clove',
            'karotten' => 'carrots',
            'möhren' => 'carrots',
            'kartoffeln' => 'potatoes',
            'süßkartoffeln' => 'sweet potatoes',
            'paprika' => 'bell pepper',
            'rote paprika' => 'red bell pepper',
            'gelbe paprika' => 'yellow bell pepper',
            'grüne paprika' => 'green bell pepper',
            'zucchini' => 'zucchini',
            'aubergine' => 'eggplant',
            'brokkoli' => 'broccoli',
            'blumenkohl' => 'cauliflower',
            'spinat' => 'spinach',
            'rucola' => 'arugula',
            'salat' => 'lettuce',
            'gurke' => 'cucumber',
            'avocado' => 'avocado',

            // Kräuter & Gewürze
            'basilikum' => 'basil',
            'petersilie' => 'parsley',
            'schnittlauch' => 'chives',
            'dill' => 'dill',
            'thymian' => 'thyme',
            'rosmarin' => 'rosemary',
            'oregano' => 'oregano',
            'majoran' => 'marjoram',
            'koriander' => 'cilantro',
            'minze' => 'mint',
            'salbei' => 'sage',
            'salz' => 'salt',
            'pfeffer' => 'black pepper',
            'schwarzer pfeffer' => 'black pepper',
            'weißer pfeffer' => 'white pepper',
            'paprikapulver' => 'paprika',
            'kreuzkümmel' => 'cumin',
            'curry' => 'curry powder',
            'zimt' => 'cinnamon',
            'muskatnuss' => 'nutmeg',

            // Proteine
            'hähnchen' => 'chicken',
            'hähnchenbrustfilet' => 'chicken breast',
            'hähnchenschenkel' => 'chicken thighs',
            'rindfleisch' => 'beef',
            'rinderhackfleisch' => 'ground beef',
            'schweinefleisch' => 'pork',
            'schweinehackfleisch' => 'ground pork',
            'lachs' => 'salmon',
            'thunfisch' => 'tuna',
            'garnelen' => 'shrimp',
            'eier' => 'eggs',
            'ei' => 'egg',

            // Milchprodukte
            'milch' => 'milk',
            'vollmilch' => 'whole milk',
            'fettarme milch' => 'low fat milk',
            'sahne' => 'heavy cream',
            'schlagsahne' => 'heavy cream',
            'saure sahne' => 'sour cream',
            'schmand' => 'sour cream',
            'crème fraîche' => 'crème fraîche',
            'butter' => 'butter',
            'margarine' => 'margarine',
            'käse' => 'cheese',
            'parmesan' => 'parmesan cheese',
            'mozzarella' => 'mozzarella cheese',
            'gouda' => 'gouda cheese',
            'cheddar' => 'cheddar cheese',
            'feta' => 'feta cheese',
            'ricotta' => 'ricotta cheese',
            'quark' => 'quark',
            'joghurt' => 'yogurt',
            'griechischer joghurt' => 'greek yogurt',

            // Öle & Fette
            'olivenöl' => 'olive oil',
            'sonnenblumenöl' => 'sunflower oil',
            'rapsöl' => 'canola oil',
            'kokosöl' => 'coconut oil',
            'sesamöl' => 'sesame oil',

            // Backzutaten
            'mehl' => 'all-purpose flour',
            'weizenmehl' => 'wheat flour',
            'vollkornmehl' => 'whole wheat flour',
            'dinkelmehl' => 'spelt flour',
            'roggenmehl' => 'rye flour',
            'zucker' => 'sugar',
            'brauner zucker' => 'brown sugar',
            'puderzucker' => 'powdered sugar',
            'honig' => 'honey',
            'ahornsirup' => 'maple syrup',
            'backpulver' => 'baking powder',
            'natron' => 'baking soda',
            'hefe' => 'yeast',
            'vanille' => 'vanilla',
            'vanilleextrakt' => 'vanilla extract'
        ];

        foreach ($zutaten as $zutat) {
            $menge = trim($zutat['menge'] ?? '');
            $einheit = trim($zutat['einheit'] ?? '');
            $name = trim($zutat['zutat'] ?? '');

            if (empty($name)) {
                continue;
            }

            // Zutat ins Englische übersetzen (case-insensitive)
            $nameLower = strtolower($name);
            $englischName = $zutatUebersetzung[$nameLower] ?? $name;

            // Formatierte Zutat erstellen
            $formattedIngredient = $this->formatIngredientWithQuantity($menge, $einheit, $englischName);

            if (!empty($formattedIngredient)) {
                $ingredientList[] = $formattedIngredient;
            }
        }

        return $ingredientList;
    }

    /**
     * Formatiert eine einzelne Zutat mit Menge und Einheit für die Spoonacular API
     */
    private function formatIngredientWithQuantity(string $menge, string $einheit, string $ingredientName): string {
        // Deutsche Einheiten -> Englische Einheiten mit Konvertierung
        $einheitenKonvertierung = [
            // Gewicht
            'g' => ['unit' => 'g', 'factor' => 1],
            'gramm' => ['unit' => 'g', 'factor' => 1],
            'kg' => ['unit' => 'g', 'factor' => 1000], // Konvertiere kg zu g für bessere API-Kompatibilität
            'kilogramm' => ['unit' => 'g', 'factor' => 1000],

            // Volumen
            'ml' => ['unit' => 'ml', 'factor' => 1],
            'milliliter' => ['unit' => 'ml', 'factor' => 1],
            'l' => ['unit' => 'ml', 'factor' => 1000], // Konvertiere l zu ml
            'liter' => ['unit' => 'ml', 'factor' => 1000],

            // Löffel
            'tl' => ['unit' => 'tsp', 'factor' => 1],
            'teelöffel' => ['unit' => 'tsp', 'factor' => 1],
            'el' => ['unit' => 'tbsp', 'factor' => 1],
            'esslöffel' => ['unit' => 'tbsp', 'factor' => 1],
            'msp' => ['unit' => 'pinch', 'factor' => 1],
            'messerspitze' => ['unit' => 'pinch', 'factor' => 1],
            'prise' => ['unit' => 'pinch', 'factor' => 1],

            // Tassen & Portionen
            'tasse' => ['unit' => 'cup', 'factor' => 1],
            'becher' => ['unit' => 'cup', 'factor' => 1],

            // Stück
            'stück' => ['unit' => '', 'factor' => 1], // Keine Einheit für Stückzahlen
            'stk' => ['unit' => '', 'factor' => 1],
            'st' => ['unit' => '', 'factor' => 1],

            // Spezielle Einheiten
            'zehe' => ['unit' => 'clove', 'factor' => 1], // für Knoblauch
            'zehen' => ['unit' => 'cloves', 'factor' => 1],
            'scheibe' => ['unit' => 'slice', 'factor' => 1],
            'scheiben' => ['unit' => 'slices', 'factor' => 1],
            'bund' => ['unit' => 'bunch', 'factor' => 1],
            'dose' => ['unit' => 'can', 'factor' => 1],
            'dosen' => ['unit' => 'cans', 'factor' => 1],
            'packung' => ['unit' => 'package', 'factor' => 1],
            'pkg' => ['unit' => 'package', 'factor' => 1]
        ];

        // Wenn keine Menge angegeben ist
        if (empty($menge)) {
            return $this->handleUnitlessIngredient($ingredientName);
        }

        // Menge als Zahl extrahieren (kann Brüche oder Dezimalzahlen enthalten)
        $numericAmount = $this->parseAmount($menge);

        if ($numericAmount <= 0) {
            return $this->handleUnitlessIngredient($ingredientName);
        }

        // Einheit konvertieren
        $einheitLower = strtolower(trim($einheit));
        $unitData = $einheitenKonvertierung[$einheitLower] ?? ['unit' => $einheit, 'factor' => 1];

        // Menge mit Konvertierungsfaktor anpassen
        $convertedAmount = $numericAmount * $unitData['factor'];
        $convertedUnit = $unitData['unit'];

        // Formatierung für Spoonacular API
        if (empty($convertedUnit)) {
            // Für Stückzahlen: "2 eggs", "1 onion"
            $formattedAmount = $this->formatAmount($convertedAmount);
            return trim("$formattedAmount $ingredientName");
        } else {
            // Für Mengen mit Einheiten: "200g pasta", "2 tbsp olive oil"
            $formattedAmount = $this->formatAmount($convertedAmount);
            return trim("$formattedAmount$convertedUnit $ingredientName");
        }
    }

    /**
     * Behandelt Zutaten ohne spezifische Mengenangabe
     */
    private function handleUnitlessIngredient(string $ingredientName): string {
        // Für Zutaten ohne Mengenangabe eine Standard-Portion annehmen
        $defaultPortions = [
            'onion' => '1 medium onion',
            'onions' => '1 medium onion',
            'garlic' => '2 cloves garlic',
            'garlic clove' => '1 clove garlic',
            'egg' => '1 large egg',
            'eggs' => '2 large eggs',
            'tomato' => '1 medium tomato',
            'tomatoes' => '2 medium tomatoes',
            'bell pepper' => '1 medium bell pepper',
            'carrot' => '1 medium carrot',
            'carrots' => '2 medium carrots',
            'potato' => '1 medium potato',
            'potatoes' => '2 medium potatoes',
            'avocado' => '1 medium avocado',
            'lemon' => '1 medium lemon',
            'lime' => '1 medium lime',
            'apple' => '1 medium apple',
            'banana' => '1 medium banana'
        ];

        $ingredientLower = strtolower($ingredientName);
        return $defaultPortions[$ingredientLower] ?? "100g $ingredientName";
    }

    /**
     * Parst Mengenangaben (unterstützt Brüche und Dezimalzahlen)
     */
    private function parseAmount(string $amount): float {
        $amount = trim($amount);

        // Brüche handhaben (z.B. "1/2", "3/4")
        if (preg_match('/^(\d+)\/(\d+)$/', $amount, $matches)) {
            return floatval($matches[1]) / floatval($matches[2]);
        }

        // Gemischte Zahlen handhaben (z.B. "1 1/2")
        if (preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', $amount, $matches)) {
            $whole = floatval($matches[1]);
            $fraction = floatval($matches[2]) / floatval($matches[3]);
            return $whole + $fraction;
        }

        // Komma durch Punkt ersetzen für deutsche Dezimalzahlen
        $amount = str_replace(',', '.', $amount);

        // Nur Zahlen extrahieren
        if (preg_match('/(\d+(?:\.\d+)?)/', $amount, $matches)) {
            return floatval($matches[1]);
        }

        return 0.0;
    }

    /**
     * Formatiert Mengenangaben für die API
     */
    private function formatAmount(float $amount): string {
        // Ganze Zahlen ohne Dezimalstellen anzeigen
        if ($amount == floor($amount)) {
            return strval(intval($amount));
        }

        // Dezimalzahlen mit maximal 2 Nachkommastellen
        return number_format($amount, 2, '.', '');
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
     * Schätzt Nährwerte basierend auf typischen Werten (Fallback) mit verbesserter Mengenberechnung
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

        // Erweiterte Nährwerttabelle pro 100g/100ml
        $naehrwertTabelle = [
            // Grundnahrungsmittel
            'pasta' => ['kalorien' => 350, 'protein' => 12, 'kohlenhydrate' => 70, 'fett' => 2, 'ballaststoffe' => 3, 'zucker' => 2, 'natrium' => 0.01],
            'nudeln' => ['kalorien' => 350, 'protein' => 12, 'kohlenhydrate' => 70, 'fett' => 2, 'ballaststoffe' => 3, 'zucker' => 2, 'natrium' => 0.01],
            'reis' => ['kalorien' => 350, 'protein' => 7, 'kohlenhydrate' => 77, 'fett' => 1, 'ballaststoffe' => 1, 'zucker' => 0.5, 'natrium' => 0.005],
            'quinoa' => ['kalorien' => 368, 'protein' => 14, 'kohlenhydrate' => 64, 'fett' => 6, 'ballaststoffe' => 7, 'zucker' => 4, 'natrium' => 0.005],

            // Gemüse
            'tomaten' => ['kalorien' => 18, 'protein' => 1, 'kohlenhydrate' => 4, 'fett' => 0.2, 'ballaststoffe' => 1.2, 'zucker' => 2.6, 'natrium' => 0.005],
            'zwiebel' => ['kalorien' => 40, 'protein' => 1.1, 'kohlenhydrate' => 9, 'fett' => 0.1, 'ballaststoffe' => 1.7, 'zucker' => 4.2, 'natrium' => 0.004],
            'knoblauch' => ['kalorien' => 149, 'protein' => 6.4, 'kohlenhydrate' => 33, 'fett' => 0.5, 'ballaststoffe' => 2.1, 'zucker' => 1, 'natrium' => 0.017],
            'karotten' => ['kalorien' => 41, 'protein' => 0.9, 'kohlenhydrate' => 10, 'fett' => 0.2, 'ballaststoffe' => 2.8, 'zucker' => 4.7, 'natrium' => 0.069],
            'kartoffeln' => ['kalorien' => 77, 'protein' => 2, 'kohlenhydrate' => 17, 'fett' => 0.1, 'ballaststoffe' => 2.2, 'zucker' => 0.8, 'natrium' => 0.006],
            'paprika' => ['kalorien' => 31, 'protein' => 1, 'kohlenhydrate' => 7, 'fett' => 0.3, 'ballaststoffe' => 2.5, 'zucker' => 4.2, 'natrium' => 0.004],
            'spinat' => ['kalorien' => 23, 'protein' => 2.9, 'kohlenhydrate' => 3.6, 'fett' => 0.4, 'ballaststoffe' => 2.2, 'zucker' => 0.4, 'natrium' => 0.079],

            // Kräuter (pro 100g, aber meist in kleinen Mengen verwendet)
            'basilikum' => ['kalorien' => 22, 'protein' => 3.2, 'kohlenhydrate' => 2.6, 'fett' => 0.6, 'ballaststoffe' => 1.6, 'zucker' => 0.3, 'natrium' => 0.004],
            'petersilie' => ['kalorien' => 36, 'protein' => 3, 'kohlenhydrate' => 6, 'fett' => 0.8, 'ballaststoffe' => 3.3, 'zucker' => 0.9, 'natrium' => 0.056],

            // Proteine
            'hähnchen' => ['kalorien' => 165, 'protein' => 31, 'kohlenhydrate' => 0, 'fett' => 3.6, 'ballaststoffe' => 0, 'zucker' => 0, 'natrium' => 0.074],
            'rindfleisch' => ['kalorien' => 250, 'protein' => 26, 'kohlenhydrate' => 0, 'fett' => 15, 'ballaststoffe' => 0, 'zucker' => 0, 'natrium' => 0.055],
            'lachs' => ['kalorien' => 208, 'protein' => 20, 'kohlenhydrate' => 0, 'fett' => 13, 'ballaststoffe' => 0, 'zucker' => 0, 'natrium' => 0.059],
            'eier' => ['kalorien' => 155, 'protein' => 13, 'kohlenhydrate' => 1.1, 'fett' => 11, 'ballaststoffe' => 0, 'zucker' => 1.1, 'natrium' => 0.124],

            // Milchprodukte
            'milch' => ['kalorien' => 42, 'protein' => 3.4, 'kohlenhydrate' => 5, 'fett' => 1, 'ballaststoffe' => 0, 'zucker' => 5, 'natrium' => 0.044],
            'butter' => ['kalorien' => 717, 'protein' => 0.9, 'kohlenhydrate' => 0.1, 'fett' => 81, 'ballaststoffe' => 0, 'zucker' => 0.1, 'natrium' => 0.011],
            'parmesan' => ['kalorien' => 431, 'protein' => 35, 'kohlenhydrate' => 4, 'fett' => 29, 'ballaststoffe' => 0, 'zucker' => 0.9, 'natrium' => 1.529],
            'mozzarella' => ['kalorien' => 300, 'protein' => 22, 'kohlenhydrate' => 2.2, 'fett' => 22, 'ballaststoffe' => 0, 'zucker' => 1, 'natrium' => 0.627],

            // Öle & Fette
            'olivenöl' => ['kalorien' => 884, 'protein' => 0, 'kohlenhydrate' => 0, 'fett' => 100, 'ballaststoffe' => 0, 'zucker' => 0, 'natrium' => 0.002],
            'sonnenblumenöl' => ['kalorien' => 884, 'protein' => 0, 'kohlenhydrate' => 0, 'fett' => 100, 'ballaststoffe' => 0, 'zucker' => 0, 'natrium' => 0],

            // Backzutaten
            'mehl' => ['kalorien' => 364, 'protein' => 10, 'kohlenhydrate' => 76, 'fett' => 1, 'ballaststoffe' => 2.7, 'zucker' => 0.3, 'natrium' => 0.002],
            'zucker' => ['kalorien' => 387, 'protein' => 0, 'kohlenhydrate' => 100, 'fett' => 0, 'ballaststoffe' => 0, 'zucker' => 100, 'natrium' => 0],
            'honig' => ['kalorien' => 304, 'protein' => 0.3, 'kohlenhydrate' => 82, 'fett' => 0, 'ballaststoffe' => 0.2, 'zucker' => 82, 'natrium' => 0.004]
        ];

        foreach ($zutaten as $zutat) {
            $name = strtolower(trim($zutat['zutat'] ?? ''));
            $menge = $this->parseAmount($zutat['menge'] ?? '');
            $einheit = strtolower(trim($zutat['einheit'] ?? ''));

            if ($menge <= 0) {
                // Für Zutaten ohne Mengenangabe Standardwerte verwenden
                $menge = $this->getDefaultAmount($name);
                $einheit = 'g'; // Standardeinheit
            }

            // Menge in Gramm konvertieren
            $mengeInGramm = $this->convertToGrams($menge, $einheit, $name);

            if ($mengeInGramm <= 0) continue;

            // Suche passende Nährwerte
            $naehrwerte = null;
            foreach ($naehrwertTabelle as $key => $werte) {
                if (strpos($name, $key) !== false || $key === $name) {
                    $naehrwerte = $werte;
                    break;
                }
            }

            // Fallback für unbekannte Zutaten
            if (!$naehrwerte) {
                $naehrwerte = ['kalorien' => 200, 'protein' => 5, 'kohlenhydrate' => 20, 'fett' => 5, 'ballaststoffe' => 2, 'zucker' => 1, 'natrium' => 0.1];
            }

            // Berechne für die angegebene Menge (Nährwerttabelle ist pro 100g)
            $faktor = $mengeInGramm / 100;

            foreach ($naehrwerte as $naehrstoff => $wert) {
                if (isset($totalNutrition[$naehrstoff])) {
                    $totalNutrition[$naehrstoff] += $wert * $faktor;
                }
            }
        }

        // Pro Portion berechnen
        if ($portionen > 1) {
            foreach ($totalNutrition as $key => $value) {
                $totalNutrition[$key] = round($value / $portionen, 2);
            }
        } else {
            // Werte runden
            foreach ($totalNutrition as $key => $value) {
                $totalNutrition[$key] = round($value, 2);
            }
        }

        return $totalNutrition;
    }

    /**
     * Konvertiert verschiedene Einheiten zu Gramm für die Nährwertberechnung
     */
    private function convertToGrams(float $amount, string $unit, string $ingredientName): float {
        $unit = strtolower($unit);

        switch ($unit) {
            case 'g':
            case 'gramm':
                return $amount;

            case 'kg':
            case 'kilogramm':
                return $amount * 1000;

            case 'ml':
            case 'milliliter':
                // Für Flüssigkeiten: 1ml ≈ 1g (Näherung)
                return $amount;

            case 'l':
            case 'liter':
                return $amount * 1000;

            case 'tl':
            case 'teelöffel':
                // 1 TL ≈ 5g (abhängig von der Zutat)
                return $amount * $this->getTeaspoonWeight($ingredientName);

            case 'el':
            case 'esslöffel':
                // 1 EL ≈ 15g (abhängig von der Zutat)
                return $amount * $this->getTablespoonWeight($ingredientName);

            case 'tasse':
            case 'becher':
                // 1 Tasse ≈ 240ml ≈ 240g für Flüssigkeiten
                return $amount * $this->getCupWeight($ingredientName);

            case 'stück':
            case 'stk':
            case '':
                // Für Stückzahlen das durchschnittliche Gewicht verwenden
                return $amount * $this->getPieceWeight($ingredientName);

            default:
                // Unbekannte Einheit: als Gramm behandeln
                return $amount;
        }
    }

    /**
     * Gibt das durchschnittliche Gewicht eines Teelöffels für verschiedene Zutaten zurück
     */
    private function getTeaspoonWeight(string $ingredient): float {
        $weights = [
            'salz' => 6,
            'zucker' => 4,
            'mehl' => 3,
            'olivenöl' => 5,
            'honig' => 7,
            'zimt' => 2,
            'paprikapulver' => 2
        ];

        foreach ($weights as $key => $weight) {
            if (strpos($ingredient, $key) !== false) {
                return $weight;
            }
        }

        return 5; // Standard: 5g
    }

    /**
     * Gibt das durchschnittliche Gewicht eines Esslöffels für verschiedene Zutaten zurück
     */
    private function getTablespoonWeight(string $ingredient): float {
        return $this->getTeaspoonWeight($ingredient) * 3; // 1 EL = 3 TL
    }

    /**
     * Gibt das durchschnittliche Gewicht einer Tasse für verschiedene Zutaten zurück
     */
    private function getCupWeight(string $ingredient): float {
        $weights = [
            'mehl' => 120,
            'zucker' => 200,
            'reis' => 185,
            'milch' => 240,
            'wasser' => 240,
            'olivenöl' => 220
        ];

        foreach ($weights as $key => $weight) {
            if (strpos($ingredient, $key) !== false) {
                return $weight;
            }
        }

        return 240; // Standard: 240g
    }

    /**
     * Gibt das durchschnittliche Gewicht pro Stück für verschiedene Zutaten zurück
     */
    private function getPieceWeight(string $ingredient): float {
        $weights = [
            'ei' => 60,
            'eier' => 60,
            'zwiebel' => 150,
            'knoblauchzehe' => 3,
            'knoblauch' => 3,
            'tomate' => 150,
            'kartoffel' => 200,
            'karotte' => 100,
            'paprika' => 150,
            'avocado' => 200,
            'zitrone' => 100,
            'apfel' => 180,
            'banane' => 120
        ];

        foreach ($weights as $key => $weight) {
            if (strpos($ingredient, $key) !== false) {
                return $weight;
            }
        }

        return 100; // Standard: 100g
    }

    /**
     * Gibt Standardmengen für Zutaten ohne Mengenangabe zurück
     */
    private function getDefaultAmount(string $ingredient): float {
        $defaults = [
            'salz' => 5,      // 1 TL
            'pfeffer' => 2,   // 1/2 TL
            'olivenöl' => 15, // 1 EL
            'butter' => 20,   // 1 EL
            'knoblauch' => 6, // 2 Zehen
            'zwiebel' => 150, // 1 mittelgroße
            'ei' => 60,       // 1 Ei
            'basilikum' => 10 // 1 EL frisch
        ];

        foreach ($defaults as $key => $amount) {
            if (strpos($ingredient, $key) !== false) {
                return $amount;
            }
        }

        return 100; // Standard: 100g
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
