/**
 * Formular-spezifische JavaScript-Funktionen
 * Enthält: Dropdown-Multiselect, Datei-Upload, Zutaten-Management
 */

// Dropdown-Multiselect-Funktionalität
function toggleDropdown(header) {
    const dropdown = header.parentElement;
    const isOpen = dropdown.classList.contains('open');
    
    // Alle anderen Dropdowns schließen
    document.querySelectorAll('.dropdown-multiselect.open').forEach(d => {
        if (d !== dropdown) d.classList.remove('open');
    });
    
    // Aktuelles Dropdown togglen
    dropdown.classList.toggle('open', !isOpen);
    
    // Event-Listener für Klick außerhalb
    if (!isOpen) {
        setTimeout(() => {
            document.addEventListener('click', closeDropdownOnOutsideClick);
        }, 0);
    }
}

function closeDropdownOnOutsideClick(e) {
    if (!e.target.closest('.dropdown-multiselect')) {
        document.querySelectorAll('.dropdown-multiselect.open').forEach(d => {
            d.classList.remove('open');
        });
        document.removeEventListener('click', closeDropdownOnOutsideClick);
    }
}

// Dropdown-Label aktualisieren
function updateDropdownLabel(dropdown) {
    const header = dropdown.querySelector('.dropdown-header');
    const label = header.querySelector('.dropdown-label');
    const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    
    if (checkboxes.length === 0) {
        label.textContent = '-- auswählen --';
    } else if (checkboxes.length === 1) {
        label.textContent = checkboxes[0].parentElement.textContent.trim();
    } else {
        label.textContent = `${checkboxes.length} ausgewählt`;
    }
}

// Single-Select Dropdown
function initSingleSelectDropdowns() {
    document.querySelectorAll('.dropdown-multiselect.single-select').forEach(dropdown => {
        const header = dropdown.querySelector('.dropdown-header');
        const hiddenSelect = dropdown.querySelector('select');
        const label = header.querySelector('.dropdown-label');
        
        if (!hiddenSelect) return;
        
        // Optionen aus Select erstellen
        const list = dropdown.querySelector('.dropdown-list');
        if (!list) return;
        
        list.innerHTML = '';
        
        Array.from(hiddenSelect.options).forEach(option => {
            const div = document.createElement('div');
            div.className = 'dropdown-option';
            div.textContent = option.textContent;
            div.setAttribute('data-value', option.value);
            
            if (option.selected) {
                div.classList.add('selected');
                label.textContent = option.textContent;
            }
            
            div.addEventListener('click', () => {
                // Alle anderen deselektieren
                list.querySelectorAll('.dropdown-option').forEach(o => o.classList.remove('selected'));
                div.classList.add('selected');
                
                // Hidden select aktualisieren
                hiddenSelect.value = option.value;
                label.textContent = option.textContent;
                
                // Dropdown schließen
                dropdown.classList.remove('open');
            });
            
            list.appendChild(div);
        });
    });
}

// Datei-Upload-Funktionalität
function initFileUpload() {
    const realFileInput = document.getElementById('bild');
    const btnSelectFile = document.getElementById('btn-select-file');
    const fileNameSpan = document.getElementById('selected-file-name');
    const previewContainer = document.getElementById('preview-container');
    const imgPreview = document.getElementById('img-preview');

    if (!realFileInput || !btnSelectFile) return;

    btnSelectFile.addEventListener('click', () => {
        realFileInput.click();
    });

    realFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        
        if (file) {
            if (fileNameSpan) {
                fileNameSpan.textContent = file.name;
            }
            
            // Bildvorschau
            if (file.type.startsWith('image/') && imgPreview && previewContainer) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        } else {
            if (fileNameSpan) {
                fileNameSpan.textContent = 'Keine Datei ausgewählt';
            }
            if (previewContainer) {
                previewContainer.style.display = 'none';
            }
        }
    });
}

// Zutaten-Management
let einheitenListe = [
    'g', 'kg', 'ml', 'l', 'TL', 'EL', 'Stück', 'Prise', 'Tasse', 'Bund', 'Zehe', 'Scheibe'
];

function createZutatenZeile(entfernbar = true) {
    const div = document.createElement("div");
    div.className = "zutaten-paar";

    // Zutat-Input
    const zutatInput = document.createElement("input");
    zutatInput.type = "text";
    zutatInput.name = "zutaten[]";
    zutatInput.placeholder = "Zutat";
    zutatInput.required = true;

    // Menge-Input
    const mengeInput = document.createElement("input");
    mengeInput.type = "number";
    mengeInput.name = "mengen[]";
    mengeInput.placeholder = "Menge";
    mengeInput.step = "0.1";
    mengeInput.min = "0";

    // Einheit-Select
    const einheitSelect = document.createElement("select");
    einheitSelect.name = "einheiten[]";
    einheitSelect.innerHTML = `<option value="">Einheit</option>` +
        einheitenListe.map(e => `<option value="${e}">${e}</option>`).join('');

    // Entfernen-Button (nur wenn entfernbar)
    if (entfernbar) {
        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.textContent = "×";
        removeBtn.className = "btn-remove-zutat";
        removeBtn.onclick = () => div.remove();
        div.appendChild(removeBtn);
    }

    // Alle Elemente anhängen
    div.appendChild(zutatInput);
    div.appendChild(mengeInput);
    div.appendChild(einheitSelect);

    return div;
}

function neueZutat() {
    const container = document.getElementById("zutaten-container");
    if (container) {
        container.appendChild(createZutatenZeile(true));
    }
}

function letztesZutatEntfernen() {
    const container = document.getElementById("zutaten-container");
    if (container) {
        const zutaten = container.getElementsByClassName("zutaten-paar");
        if (zutaten.length > 1) {
            container.removeChild(zutaten[zutaten.length - 1]);
        }
    }
}

// Benutzername-Prüfung (für Registrierung)
function initUsernameCheck() {
    const benutzernameInput = document.getElementById('benutzername');
    const errorMsg = document.getElementById('benutzername-fehler');
    const form = benutzernameInput?.closest('form');
    
    if (!benutzernameInput || !errorMsg || !form) return;

    let timeout = null;

    benutzernameInput.addEventListener('input', () => {
        const name = benutzernameInput.value.trim();
        
        if (timeout) clearTimeout(timeout);
        
        if (name.length < 3) {
            errorMsg.textContent = '';
            benutzernameInput.setCustomValidity('');
            return;
        }

        timeout = setTimeout(() => {
            fetch(`index.php?page=pruefeBenutzername&benutzername=` + encodeURIComponent(name))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Netzwerk-Antwort war nicht ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.exists) {
                        errorMsg.textContent = 'Dieser Benutzername ist bereits vergeben.';
                        benutzernameInput.setCustomValidity('Dieser Benutzername ist bereits vergeben.');
                    } else {
                        errorMsg.textContent = '';
                        benutzernameInput.setCustomValidity('');
                    }
                })
                .catch(() => {
                    errorMsg.textContent = 'Fehler bei der Prüfung.';
                    benutzernameInput.setCustomValidity('Fehler bei der Prüfung.');
                });
        }, 500);
    });

    form.addEventListener('submit', (e) => {
        if (!benutzernameInput.checkValidity()) {
            e.preventDefault();
            benutzernameInput.reportValidity();
        }
    });
}

// Initialisierung
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown-Event-Listener
    document.querySelectorAll('.dropdown-multiselect').forEach(dropdown => {
        const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                updateDropdownLabel(dropdown);
            });
        });
        
        // Initial label setzen
        updateDropdownLabel(dropdown);
    });
    
    initSingleSelectDropdowns();
    initFileUpload();
    initUsernameCheck();
});

// Globale Funktionen für Rückwärtskompatibilität
window.toggleDropdown = toggleDropdown;
window.neueZutat = neueZutat;
window.letztesZutatEntfernen = letztesZutatEntfernen;
