<main>

    <!-- Suchformular mit integriertem Sortier-Widget -->
    <div class="suchleiste-container">
        <form id="suchformular" class="suchleiste" onsubmit="return false;">
            <input type="search" id="suchfeld" class="suchfeld" placeholder="Suche nach Rezeptitel, Kategorie oder Autor..." aria-label="Suchbegriff">
            <button type="submit" class="btn suchen-btn">Suchen</button>
        </form>

        <!-- Sortier-Widget neben der Suchleiste -->
        <div class="sortier-widget-neben-suche">
            <form id="sortier-form" method="get" action="index.php">
                <input type="hidden" name="page" value="rezepte">

                <label for="sort-select" class="sortier-label">Sortieren nach:</label>
                <select id="sort-select" name="sort" class="sortier-select">
                    <option value="datum" <?= ($currentSort ?? 'datum') === 'datum' ? 'selected' : '' ?>>
                        📅 Neueste zuerst
                    </option>
                    <option value="bewertung" <?= ($currentSort ?? '') === 'bewertung' ? 'selected' : '' ?>>
                        ⭐ Beste Bewertung
                    </option>
                    <option value="beliebtheit" <?= ($currentSort ?? '') === 'beliebtheit' ? 'selected' : '' ?>>
                        👥 Beliebteste
                    </option>
                </select>
            </form>
        </div>
    </div>

    <div id="such-ergebnisse" style="display: none;"></div>

    <div id="original-rezepte">
        <!-- Nur noch der Titel -->
        <h2 class="rezepte-titel">Alle Rezepte</h2>

        <ul class="rezept-galerie">
        <?php if (!empty($rezepte)) : ?>
            <?php foreach ($rezepte as $rezept): ?>
                <?php include 'php/include/rezept-karte.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Keine Rezepte vorhanden.</li>
        <?php endif; ?>
        </ul>

        <!-- Button für neues Rezept -->
        <a href="index.php?page=rezept-neu" class="btn neuer-rezept-btn">Neues Rezept hinzufügen</a>
    </div>

</main>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortierForm = document.getElementById('sortier-form');
    const sortSelect = document.getElementById('sort-select');
    const sortierWidget = document.querySelector('.sortier-widget-neben-suche');
    const rezeptGalerie = document.querySelector('.rezept-galerie');

    if (!sortierForm || !sortSelect) {
        console.log('Sortier-Elemente nicht gefunden - Fallback auf Standard-Formular');
        return;
    }

    // Progressive Enhancement: AJAX-Sortierung aktivieren
    let isAjaxEnabled = true;

    // Event Listener für Sortier-Änderungen
    function handleSortChange() {
        if (isAjaxEnabled) {
            performAjaxSort();
        } else {
            // Fallback: Standard-Formular-Submit
            sortierForm.submit();
        }
    }

    // AJAX-Sortierung durchführen
    function performAjaxSort() {
        const formData = new FormData(sortierForm);
        const params = new URLSearchParams(formData);

        // Loading-State anzeigen
        sortierWidget.classList.add('loading');

        // AJAX-Request
        fetch('index.php?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Extrahiere nur die Rezept-Galerie aus der Antwort
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGalerie = doc.querySelector('.rezept-galerie');

            if (newGalerie) {
                // Ersetze Galerie-Inhalt
                rezeptGalerie.innerHTML = newGalerie.innerHTML;

                // URL aktualisieren ohne Seitenneuladung
                const newUrl = window.location.pathname + '?' + params.toString();
                history.pushState({}, '', newUrl);

                // Smooth scroll zur Galerie
                rezeptGalerie.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                throw new Error('Galerie nicht in Antwort gefunden');
            }
        })
        .catch(error => {
            console.error('AJAX-Sortierung fehlgeschlagen:', error);
            // Fallback: Standard-Formular-Submit
            isAjaxEnabled = false;
            sortierForm.submit();
        })
        .finally(() => {
            // Loading-State entfernen
            sortierWidget.classList.remove('loading');
        });
    }

    // Event Listener für Sortier-Select
    sortSelect.addEventListener('change', handleSortChange);

    // Verhindere Standard-Submit wenn AJAX aktiviert ist
    sortierForm.addEventListener('submit', function(e) {
        if (isAjaxEnabled) {
            e.preventDefault();
            handleSortChange();
        }
    });

    // Browser-Zurück-Button unterstützen
    window.addEventListener('popstate', function(e) {
        // Seite neu laden bei Zurück-Navigation
        window.location.reload();
    });

    console.log('Sortier-Widget neben Suchleiste initialisiert - AJAX-Modus aktiviert');
});
</script>

