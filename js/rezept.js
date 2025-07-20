/**
 * Rezept-spezifische JavaScript-Funktionen
 * Enthält: Bewertungssystem, Modal-Dialoge, Rezept-Löschung
 */

// Bewertungssystem (Sterne)
function initStarRating() {
    const stars = document.querySelectorAll('#star-rating .star');
    const hiddenInput = document.getElementById('punkte-input');
    
    if (stars.length === 0 || !hiddenInput) return;

    function setStars(rating) {
        stars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            if (starValue <= rating) {
                star.classList.add('selected');
                star.style.color = '#f5c518';
            } else {
                star.classList.remove('selected');
                star.style.color = '#ccc';
            }
        });
        hiddenInput.value = rating;
    }

    // Initiale Färbung beim Laden setzen
    setStars(parseInt(hiddenInput.value) || 0);

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.value);
            setStars(rating);
        });

        star.addEventListener('mouseover', () => {
            const rating = parseInt(star.dataset.value);
            stars.forEach(s => {
                const sValue = parseInt(s.dataset.value);
                s.style.color = (sValue <= rating) ? '#f5c518' : '#ccc';
            });
        });

        star.addEventListener('mouseout', () => {
            setStars(parseInt(hiddenInput.value) || 0);
        });
    });
}

// Modal-Dialog für Rezept-Löschung
function initDeleteModal() {
    const modal = document.getElementById("loesch-modal");
    const loeschText = document.getElementById("loesch-text");
    const abbrechenBtn = document.getElementById("btn-abbrechen");
    const bestaetigenBtn = document.getElementById("btn-bestaetigen");

    if (!modal || !loeschText || !abbrechenBtn || !bestaetigenBtn) return;

    let aktiveButton = null;

    // Event-Listener für alle Lösch-Buttons
    document.querySelectorAll(".rezept-loeschen-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault(); // Verhindert versehentlichen Submit
            aktiveButton = btn;
            
            // Rezept-Titel ermitteln
            const rezeptKarte = btn.closest(".rezept-karte");
            const titel = rezeptKarte?.querySelector("h4")?.innerText || 
                         rezeptKarte?.querySelector("h4 a")?.innerText || 
                         "dieses Rezept";
            
            loeschText.textContent = `Möchtest du „${titel}" wirklich löschen?`;
            modal.showModal();
        });
    });

    // Abbrechen-Button
    abbrechenBtn.addEventListener("click", () => {
        modal.close();
        aktiveButton = null;
    });

    // Bestätigen-Button
    bestaetigenBtn.addEventListener("click", async () => {
        if (!aktiveButton) return;

        const id = aktiveButton.dataset.id;
        if (!id) {
            zeigeFlash("error", "Fehler: Rezept-ID nicht gefunden.");
            return;
        }

        try {
            const formData = new FormData();
            formData.append("id", id);

            const res = await fetchWithCSRF("api/rezept-loeschen.php", {
                method: "POST",
                body: formData
            });

            const json = await res.json();
            
            if (json.success) {
                // Rezept-Karte aus DOM entfernen
                const rezeptKarte = aktiveButton.closest(".rezept-karte");
                if (rezeptKarte) {
                    rezeptKarte.remove();
                }
                zeigeFlash("success", "Rezept erfolgreich gelöscht.");
            } else {
                zeigeFlash("error", "Fehler: " + (json.message || "Unbekannter Fehler"));
            }
        } catch (error) {
            console.error("Fehler beim Löschen:", error);
            zeigeFlash("error", "Fehler beim Löschen des Rezepts.");
        }

        // Modal schließen
        modal.setAttribute("hidden", true);
        modal.style.display = "none";
        aktiveButton = null;
    });

    // Modal schließen bei Klick auf Backdrop
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.close();
            aktiveButton = null;
        }
    });

    // Modal schließen mit Escape-Taste (automatisch durch dialog-Element)
}

// Einverständniserklärung für Nährwerte
function initNutritionConsent() {
    const consentCheckbox = document.getElementById('consent-checkbox');
    const consentBtn = document.getElementById('consent-btn');
    const consentArea = document.getElementById('consent-area');
    const calculateBtn = document.getElementById('berechne-naehrwerte-btn');

    if (!consentCheckbox || !consentBtn) return;

    // Checkbox-Event: Button aktivieren/deaktivieren
    consentCheckbox.addEventListener('change', () => {
        consentBtn.disabled = !consentCheckbox.checked;
    });

    // Einwilligung speichern
    consentBtn.addEventListener('click', async () => {
        if (!consentCheckbox.checked) {
            zeigeFlash("error", "Bitte stimme der Datenübertragung zu.");
            return;
        }

        // Loading-Zustand
        consentBtn.disabled = true;
        consentBtn.textContent = "Speichere...";

        try {
            const formData = new FormData();
            formData.append('einwilligung', 'true');

            const response = await fetchWithCSRF('index.php?page=setzeNaehrwerteEinwilligung', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Einverständnisbereich ausblenden
                if (consentArea) {
                    consentArea.style.display = 'none';
                }

                // "Nährwerte berechnen" Button anzeigen
                if (calculateBtn) {
                    calculateBtn.style.display = 'inline-block';
                }

                zeigeFlash("success", "Einwilligung gespeichert. Du kannst jetzt Nährwerte berechnen.");
            } else {
                zeigeFlash("error", "Fehler beim Speichern der Einwilligung.");
            }
        } catch (error) {
            console.error("Fehler beim Speichern der Einwilligung:", error);
            zeigeFlash("error", "Fehler beim Speichern der Einwilligung.");
        } finally {
            // Loading-Zustand zurücksetzen
            consentBtn.disabled = false;
            consentBtn.textContent = "Einwilligung speichern";
        }
    });
}

// Nährwerte-Berechnung
function initNutritionCalculation() {
    const calculateBtn = document.getElementById('berechne-naehrwerte-btn');
    const loadingDiv = document.getElementById('naehrwerte-loading');
    const errorDiv = document.getElementById('naehrwerte-error');
    const displayDiv = document.getElementById('naehrwerte-display');
    const placeholderDiv = document.getElementById('naehrwerte-placeholder');

    if (!calculateBtn) return;

    calculateBtn.addEventListener('click', async () => {
        // Rezept-ID aus URL extrahieren
        const urlParams = new URLSearchParams(window.location.search);
        const rezeptId = urlParams.get('id');

        if (!rezeptId) {
            zeigeFlash("error", "Rezept-ID nicht gefunden.");
            return;
        }

        // UI-Zustand setzen
        calculateBtn.style.display = 'none';
        if (loadingDiv) loadingDiv.style.display = 'block';
        if (errorDiv) errorDiv.style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('rezeptId', rezeptId);

            const response = await fetchWithCSRF('index.php?page=berechneNaehrwerte', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Seite neu laden um die aktualisierten Nährwerte anzuzeigen
                window.location.reload();
            } else {
                if (errorDiv) {
                    errorDiv.textContent = result.error || "Fehler bei der Berechnung.";
                    errorDiv.style.display = 'block';
                }
                zeigeFlash("error", result.error || "Fehler bei der Berechnung.");
            }
        } catch (error) {
            console.error("Fehler bei Nährwerte-Berechnung:", error);
            if (errorDiv) {
                errorDiv.textContent = "Fehler bei der Nährwerte-Berechnung.";
                errorDiv.style.display = 'block';
            }
            zeigeFlash("error", "Fehler bei der Nährwerte-Berechnung.");
        } finally {
            // Loading-Zustand zurücksetzen
            if (loadingDiv) loadingDiv.style.display = 'none';
            calculateBtn.style.display = 'inline-block';
        }
    });
}

// Fallback für Bewertungen ohne JavaScript
function initRatingFallback() {
    const ratingForm = document.querySelector('.bewertung-form');
    const starRating = document.getElementById('star-rating');
    
    if (!ratingForm || !starRating) return;

    // Prüfe ob JavaScript verfügbar ist
    if (typeof window.zeigeFlash === 'undefined') {
        // Fallback: Zeige normale Radio-Buttons
        const fallbackHTML = `
            <div class="rating-fallback">
                <label><input type="radio" name="punkte" value="1"> 1 Stern</label>
                <label><input type="radio" name="punkte" value="2"> 2 Sterne</label>
                <label><input type="radio" name="punkte" value="3"> 3 Sterne</label>
                <label><input type="radio" name="punkte" value="4"> 4 Sterne</label>
                <label><input type="radio" name="punkte" value="5"> 5 Sterne</label>
            </div>
        `;
        starRating.innerHTML = fallbackHTML;
    }
}

// Initialisierung
document.addEventListener('DOMContentLoaded', function() {
    initStarRating();
    initDeleteModal();
    initNutritionConsent();
    initNutritionCalculation();
    initRatingFallback();
});

// Globale Funktionen für Rückwärtskompatibilität
window.initStarRating = initStarRating;
window.initDeleteModal = initDeleteModal;
