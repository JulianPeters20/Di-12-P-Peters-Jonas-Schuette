/**
 * Hauptfunktionen für die Broke & Hungry Webanwendung
 * Enthält: Flash-Toasts, Burger-Menu, allgemeine Hilfsfunktionen
 */

// Flash-Toast-Funktionalität
function zeigeFlash(typ, nachricht) {
    // Alte Flash-Toasts entfernen
    document.querySelectorAll(".flash-toast").forEach(e => e.remove());

    // Neue Flash-Toast erstellen
    const box = document.createElement("div");
    box.className = "flash-toast " + typ;
    box.textContent = nachricht;

    // Längere Anzeigedauer für Nährwerte-Nachrichten
    const isNutritionMessage = nachricht.includes("Nährwerte");
    const displayDuration = isNutritionMessage ? 6000 : 4600; // 6s für Nährwerte, 4.6s für andere

    // Custom Animation für längere Anzeige
    if (isNutritionMessage) {
        box.style.animation = "fadein 0.3s forwards, fadeout 0.4s forwards 5.5s";
    }

    document.body.appendChild(box);

    // Automatisch nach Animation entfernen
    setTimeout(() => {
        if (box.parentNode) {
            box.parentNode.removeChild(box);
        }
    }, displayDuration);
}

// Burger-Menu-Funktionalität
function initBurgerMenu() {
    const burgerToggle = document.querySelector('.burger-toggle');
    const burgerBtn = document.querySelector('.burger-btn');
    const hauptNav = document.querySelector('.haupt-nav');

    if (burgerToggle && burgerBtn) {
        // Aria-Label dynamisch aktualisieren
        burgerToggle.addEventListener('change', () => {
            const isOpen = burgerToggle.checked;
            burgerBtn.setAttribute('aria-label', isOpen ? 'Menü schließen' : 'Menü öffnen');
        });

        // Klick außerhalb des Menüs schließt es
        document.addEventListener('click', (e) => {
            if (burgerToggle.checked) {
                // Prüfen ob der Klick außerhalb des Menüs und Burger-Buttons war
                if (!hauptNav.contains(e.target) && !burgerBtn.contains(e.target) && e.target !== burgerToggle) {
                    burgerToggle.checked = false;
                    burgerBtn.setAttribute('aria-label', 'Menü öffnen');
                }
            }
        });

        // ESC-Taste schließt das Menü
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && burgerToggle.checked) {
                burgerToggle.checked = false;
                burgerBtn.setAttribute('aria-label', 'Menü öffnen');
                burgerBtn.focus(); // Fokus zurück zum Button
            }
        });
    }
}

// Tab-System-Funktionalität
function initTabSystem() {
    const buttons = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');
    
    if (buttons.length === 0) return;

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            // Alle Buttons deaktivieren
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Alle Contents verstecken
            contents.forEach(c => {
                if (c.id === target) {
                    c.classList.add('active');
                } else {
                    c.classList.remove('active');
                }
            });
        });
    });
}

// Automatische Flash-Toast-Anzeige beim Laden
function initFlashMessages() {
    // Prüfe auf PHP-Flash-Nachrichten im DOM
    const flashData = document.querySelector('[data-flash-type]');
    if (flashData) {
        const type = flashData.dataset.flashType || 'info';
        const message = flashData.dataset.flashMessage || '';
        if (message) {
            zeigeFlash(type, message);
            // Flash-Nachricht aus der Session löschen via AJAX
            clearFlashFromSession();
        }
        flashData.remove();
    }
}

// Flash-Nachricht aus der PHP-Session löschen
function clearFlashFromSession() {
    // Einfacher GET-Request um die Session zu bereinigen
    fetch('index.php?page=clearFlash', {
        method: 'GET',
        credentials: 'same-origin'
    }).catch(() => {
        // Fehler ignorieren - nicht kritisch
    });
}

// Hilfsfunktion: CSRF-Token abrufen
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
           document.querySelector('input[name="csrf_token"]')?.value || '';
}

// Hilfsfunktion: Fetch mit CSRF-Token
async function fetchWithCSRF(url, options = {}) {
    const csrfToken = getCSRFToken();
    
    if (options.method && options.method.toLowerCase() !== 'get' && csrfToken) {
        if (options.body instanceof FormData) {
            options.body.append('csrf_token', csrfToken);
        } else if (options.body && typeof options.body === 'string') {
            const params = new URLSearchParams(options.body);
            params.append('csrf_token', csrfToken);
            options.body = params.toString();
        }
    }
    
    return fetch(url, options);
}

// JavaScript-Erkennung: Body-Klasse aktualisieren
document.documentElement.classList.remove('no-js');
document.documentElement.classList.add('js');
document.body.classList.remove('no-js');
document.body.classList.add('js');

// Initialisierung beim DOM-Load
document.addEventListener('DOMContentLoaded', function() {
    initBurgerMenu();
    initTabSystem();
    initFlashMessages();
});

// Globale Funktionen für Rückwärtskompatibilität
window.zeigeFlash = zeigeFlash;
window.getCSRFToken = getCSRFToken;
window.fetchWithCSRF = fetchWithCSRF;
