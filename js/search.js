/**
 * Such-Funktionalität mit AJAX
 * Enthält: Live-Suche, Ergebnis-Anzeige, Klickbare Ergebnisse
 */

// Live-Suche-Funktionalität
function initLiveSearch() {
    const form = document.getElementById("suchformular");
    const feld = document.getElementById("suchfeld");
    const ergebnisContainer = document.getElementById("such-ergebnisse");
    const originalContent = document.getElementById("original-rezepte");

    if (!form || !feld || !ergebnisContainer) return;

    let searchTimeout = null;
    let isSearching = false;

    async function sucheAusfuehren(begriff = null) {
        if (begriff === null) {
            begriff = feld.value.trim();
        }

        // Wenn Suchfeld leer ist, ursprünglichen Inhalt wiederherstellen
        if (begriff.length === 0) {
            ergebnisContainer.innerHTML = '';
            ergebnisContainer.style.display = 'none';
            if (originalContent) {
                originalContent.style.display = 'block';
            }
            return;
        }

        // Mindestlänge prüfen
        if (begriff.length < 2) {
            ergebnisContainer.innerHTML = "<p class='search-info'>Bitte mindestens 2 Zeichen eingeben.</p>";
            ergebnisContainer.style.display = 'block';
            if (originalContent) {
                originalContent.style.display = 'none';
            }
            return;
        }

        // Verhindere mehrfache gleichzeitige Anfragen
        if (isSearching) return;
        isSearching = true;

        // Loading-Anzeige
        ergebnisContainer.innerHTML = "<p class='search-loading'>Suche läuft...</p>";
        ergebnisContainer.style.display = 'block';
        
        // Ursprünglichen Inhalt verstecken
        if (originalContent) {
            originalContent.style.display = 'none';
        }

        try {
            const response = await fetch(`api/rezepte-suche.php?query=${encodeURIComponent(begriff)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const html = await response.text();
            
            // Prüfe ob die Antwort gültig ist
            if (html.trim().length === 0) {
                ergebnisContainer.innerHTML = "<p class='search-no-results'>Keine Rezepte gefunden.</p>";
            } else {
                ergebnisContainer.innerHTML = html;

                // Klickbare Links zu den Suchergebnissen hinzufügen
                makeSearchResultsClickable();
            }

            // Originale Liste verstecken, Suchergebnisse anzeigen
            ergebnisContainer.style.display = 'block';
            if (originalContent) {
                originalContent.style.display = 'none';
            }
            
        } catch (error) {
            console.error("Suchfehler:", error);
            ergebnisContainer.innerHTML = "<p class='search-error'>Fehler beim Laden der Suchergebnisse. Bitte versuchen Sie es erneut.</p>";
        } finally {
            isSearching = false;
        }
    }

    // Suchergebnisse klickbar machen
    function makeSearchResultsClickable() {
        const resultItems = ergebnisContainer.querySelectorAll('.rezept-karte');
        
        resultItems.forEach(item => {
            // Prüfe ob bereits ein Link vorhanden ist
            const existingLink = item.querySelector('h4 a');
            if (existingLink) return;
            
            // Rezept-ID aus data-Attribut oder anderen Quellen ermitteln
            const rezeptId = item.dataset.rezeptId || 
                           item.querySelector('[data-rezept-id]')?.dataset.rezeptId ||
                           item.querySelector('.rezept-loeschen-btn')?.dataset.id;
            
            if (rezeptId) {
                // Titel klickbar machen
                const title = item.querySelector('h4');
                if (title && !title.querySelector('a')) {
                    const titleText = title.textContent;
                    title.innerHTML = `<a href="index.php?page=rezept&id=${encodeURIComponent(rezeptId)}">${titleText}</a>`;
                }
                
                // Bild klickbar machen
                const img = item.querySelector('img');
                if (img && !img.closest('a')) {
                    const link = document.createElement('a');
                    link.href = `index.php?page=rezept&id=${encodeURIComponent(rezeptId)}`;
                    img.parentNode.insertBefore(link, img);
                    link.appendChild(img);
                }
                
                // Gesamte Karte klickbar machen (als Fallback)
                if (!item.style.cursor) {
                    item.style.cursor = 'pointer';
                    item.addEventListener('click', (e) => {
                        // Nur wenn nicht auf einen Button oder Link geklickt wurde
                        if (!e.target.closest('button') && !e.target.closest('a')) {
                            window.location.href = `index.php?page=rezept&id=${encodeURIComponent(rezeptId)}`;
                        }
                    });
                }
            }
        });
    }

    // Event-Listener für Live-Suche
    feld.addEventListener('input', () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Debounce: Warte 300ms nach dem letzten Tastendruck
        searchTimeout = setTimeout(() => {
            sucheAusfuehren();
        }, 300);
    });

    // Event-Listener für Form-Submit (verhindert Reload)
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        sucheAusfuehren();
    });

    // Enter-Taste im Suchfeld
    feld.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            sucheAusfuehren();
        }
    });
}

// Fallback für Suche ohne JavaScript
function initSearchFallback() {
    const form = document.getElementById("suchformular");
    const feld = document.getElementById("suchfeld");
    
    if (!form || !feld) return;

    // Prüfe ob JavaScript-Funktionen verfügbar sind
    if (typeof window.fetch === 'undefined') {
        // Fallback: Normale Form-Submission
        form.action = "index.php";
        form.method = "GET";
        
        // Hidden input für page parameter
        if (!form.querySelector('input[name="page"]')) {
            const pageInput = document.createElement('input');
            pageInput.type = 'hidden';
            pageInput.name = 'page';
            pageInput.value = 'rezepte';
            form.appendChild(pageInput);
        }
        
        // Suchfeld-Name anpassen
        feld.name = 'suche';
        
        // Submit-Button sichtbar machen falls versteckt
        let submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (!submitBtn) {
            submitBtn = document.createElement('button');
            submitBtn.type = 'submit';
            submitBtn.textContent = 'Suchen';
            submitBtn.className = 'btn';
            form.appendChild(submitBtn);
        }
        submitBtn.style.display = 'inline-block';
    }
}

// Suchfeld-Verhalten verbessern
function enhanceSearchField() {
    const feld = document.getElementById("suchfeld");
    if (!feld) return;

    // Placeholder-Text
    if (!feld.placeholder) {
        feld.placeholder = "Rezepte durchsuchen...";
    }

    // Autocomplete deaktivieren für bessere UX
    feld.autocomplete = "off";

    // Focus-Verhalten
    feld.addEventListener('focus', () => {
        feld.select(); // Text bei Focus markieren
    });

    // Clear-Button (X) hinzufügen
    if (!feld.parentElement.querySelector('.search-clear')) {
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'search-clear';
        clearBtn.innerHTML = '×';
        clearBtn.title = 'Suche löschen';
        clearBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #999;
            display: none;
        `;
        
        // Container für relative Positionierung
        const container = feld.parentElement;
        if (container.style.position !== 'relative') {
            container.style.position = 'relative';
        }
        container.appendChild(clearBtn);
        
        // Clear-Button-Funktionalität
        clearBtn.addEventListener('click', () => {
            feld.value = '';
            feld.focus();
            clearBtn.style.display = 'none';
            
            // Trigger input event für Live-Suche
            feld.dispatchEvent(new Event('input'));
        });
        
        // Clear-Button anzeigen/verstecken
        feld.addEventListener('input', () => {
            clearBtn.style.display = feld.value.length > 0 ? 'block' : 'none';
        });
    }
}

// Initialisierung
document.addEventListener('DOMContentLoaded', function() {
    initLiveSearch();
    initSearchFallback();
    enhanceSearchField();
});

// Globale Funktionen
window.initLiveSearch = initLiveSearch;
