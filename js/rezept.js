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
            modal.removeAttribute("hidden");
            modal.style.display = "flex"; // Sicherstellen, dass Modal sichtbar ist
        });
    });

    // Abbrechen-Button
    abbrechenBtn.addEventListener("click", () => {
        modal.setAttribute("hidden", true);
        modal.style.display = "none";
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

    // Modal schließen bei Klick auf Overlay
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.setAttribute("hidden", true);
            modal.style.display = "none";
            aktiveButton = null;
        }
    });

    // Modal schließen mit Escape-Taste
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal.hasAttribute("hidden")) {
            modal.setAttribute("hidden", true);
            modal.style.display = "none";
            aktiveButton = null;
        }
    });
}

// Nährwerte-Berechnung
function initNutritionCalculation() {
    const calculateBtn = document.getElementById('naehrwerte-berechnen');
    const nutritionSection = document.querySelector('.naehrwerte-section');
    
    if (!calculateBtn) return;

    calculateBtn.addEventListener('click', async () => {
        const rezeptId = calculateBtn.dataset.rezeptId;
        if (!rezeptId) {
            zeigeFlash("error", "Rezept-ID nicht gefunden.");
            return;
        }

        // Loading-Zustand
        calculateBtn.disabled = true;
        calculateBtn.textContent = "Berechne...";

        try {
            const formData = new FormData();
            formData.append('rezept_id', rezeptId);

            const response = await fetchWithCSRF('api/naehrwerte-berechnen.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Nährwerte-Sektion aktualisieren
                if (nutritionSection && result.html) {
                    nutritionSection.innerHTML = result.html;
                }
                zeigeFlash("success", "Nährwerte erfolgreich berechnet.");
            } else {
                zeigeFlash("error", result.message || "Fehler bei der Berechnung.");
            }
        } catch (error) {
            console.error("Fehler bei Nährwerte-Berechnung:", error);
            zeigeFlash("error", "Fehler bei der Nährwerte-Berechnung.");
        } finally {
            // Loading-Zustand zurücksetzen
            calculateBtn.disabled = false;
            calculateBtn.textContent = "Nährwerte berechnen";
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
    initNutritionCalculation();
    initRatingFallback();
});

// Globale Funktionen für Rückwärtskompatibilität
window.initStarRating = initStarRating;
window.initDeleteModal = initDeleteModal;
