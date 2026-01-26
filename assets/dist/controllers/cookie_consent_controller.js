// assets/dist/controllers/cookie_consent_controller.js

import { Controller } from '@hotwired/stimulus';

/**
 * Cookie Consent Controller
 *
 * DE: Stimulus Controller für das Cookie-Consent-Modal.
 *     Verwaltet die Anzeige des Modals, speichert Präferenzen
 *     und synchronisiert mit dem Server für DSGVO-Compliance.
 *
 * EN: Stimulus controller for the cookie consent modal.
 *     Manages modal display, stores preferences, and syncs
 *     with the server for GDPR compliance.
 *
 * @example
 * <div data-controller="cookie-consent"
 *      data-cookie-consent-endpoint-value="/cookie-consent/save"
 *      data-cookie-consent-csrf-token-value="{{ csrf_token }}"
 *      data-cookie-consent-preferences-value="{{ preferences_json }}"
 *      data-cookie-consent-required-value="{{ is_required ? 'true' : 'false' }}">
 */
export default class extends Controller {
    // ============================================================
    // DE: Targets – DOM-Elemente, auf die der Controller zugreift
    // EN: Targets – DOM elements the controller accesses
    // ============================================================
    static targets = [
        'modal',      // DE: Das Modal/Banner-Element | EN: The modal/banner element
        'checkbox',   // DE: Kategorie-Checkboxen | EN: Category checkboxes
    ];

    // ============================================================
    // DE: Values – Konfigurationswerte aus data-Attributen
    // EN: Values – Configuration values from data attributes
    // ============================================================
    static values = {
        categories: String,              // DE: JSON der verfügbaren Kategorien | EN: JSON of available categories
        preferences: String,             // DE: JSON der aktuellen Präferenzen | EN: JSON of current preferences
        endpoint: String,                // DE: Server-Endpoint für Speicherung | EN: Server endpoint for storage
        csrfToken: String,               // DE: CSRF-Token für sichere Requests | EN: CSRF token for secure requests
        required: Boolean,               // DE: Ob Banner angezeigt werden muss | EN: Whether banner must be shown
        reloadOnChange: Boolean,         // DE: Seite nach Consent-Update neu laden | EN: Reload page after consent update
        googleConsentModeEnabled: Boolean,  // DE: Google Consent Mode v2 aktivieren | EN: Enable Google Consent Mode v2
        googleConsentModeMapping: String,   // DE: JSON Mapping Bundle-Kategorien → Google Consent Types | EN: JSON mapping bundle categories → Google consent types
    };

    // ============================================================
    // LIFECYCLE METHODS
    // DE: Lebenszyklus-Methoden
    // EN: Lifecycle methods
    // ============================================================

    /**
     * DE: Wird aufgerufen, wenn der Controller mit dem DOM verbunden wird.
     *     Registriert Event-Listener und initialisiert den Zustand.
     *
     * EN: Called when the controller connects to the DOM.
     *     Registers event listeners and initializes state.
     */
    connect() {
        this._bindEventHandlers();
        this._registerEventListeners();
        this._initializeState();
    }

    /**
     * DE: Wird aufgerufen, wenn der Controller vom DOM getrennt wird.
     *     Entfernt alle Event-Listener zur Vermeidung von Memory Leaks.
     *
     * EN: Called when the controller disconnects from the DOM.
     *     Removes all event listeners to prevent memory leaks.
     */
    disconnect() {
        this._removeEventListeners();
    }

    // ============================================================
    // PUBLIC ACTIONS
    // DE: Öffentliche Aktionen (aufrufbar via data-action)
    // EN: Public actions (callable via data-action)
    // ============================================================

    /**
     * DE: Akzeptiert alle Cookie-Kategorien.
     *     Verwendung: data-action="cookie-consent#acceptAll"
     *
     * EN: Accepts all cookie categories.
     *     Usage: data-action="cookie-consent#acceptAll"
     */
    acceptAll() {
        this._submit('accept_all');
    }

    /**
     * DE: Lehnt alle optionalen Cookies ab (nur Pflicht-Cookies bleiben).
     *     Verwendung: data-action="cookie-consent#rejectOptional"
     *
     * EN: Rejects all optional cookies (only required cookies remain).
     *     Usage: data-action="cookie-consent#rejectOptional"
     */
    rejectOptional() {
        this._submit('reject_optional');
    }

    /**
     * DE: Speichert die benutzerdefinierten Präferenzen aus den Checkboxen.
     *     Verwendung: data-action="cookie-consent#save"
     *
     * EN: Saves custom preferences from checkboxes.
     *     Usage: data-action="cookie-consent#save"
     */
    save() {
        this._submit('custom', this._collectPreferences());
    }

    /**
     * DE: Zeigt das Banner/Modal an.
     *     Verwendung: data-action="cookie-consent#show"
     *
     * EN: Shows the banner/modal.
     *     Usage: data-action="cookie-consent#show"
     */
    show() {
        if (!this.hasModalTarget) {
            return;
        }

        this.modalTarget.style.display = 'flex';
        this.modalTarget.classList.add('show');
        this.modalTarget.setAttribute('aria-modal', 'true');
        this.modalTarget.setAttribute('role', 'dialog');
        document.body.classList.add('modal-open');

        // DE: Fokus auf das Modal setzen für Accessibility
        // EN: Set focus to modal for accessibility
        this.modalTarget.focus();
    }

    /**
     * DE: Versteckt das Banner/Modal.
     *     Verwendung: data-action="cookie-consent#hide"
     *
     * EN: Hides the banner/modal.
     *     Usage: data-action="cookie-consent#hide"
     */
    hide() {
        if (!this.hasModalTarget) {
            return;
        }

        this.modalTarget.classList.remove('show');
        this.modalTarget.style.display = 'none';
        this.modalTarget.removeAttribute('aria-modal');
        this.modalTarget.removeAttribute('role');
        document.body.classList.remove('modal-open');
    }

    // ============================================================
    // PRIVATE METHODS – INITIALIZATION
    // DE: Private Methoden – Initialisierung
    // EN: Private methods – Initialization
    // ============================================================

    /**
     * DE: Bindet Event-Handler an die Instanz (für korrektes `this`).
     * EN: Binds event handlers to the instance (for correct `this`).
     */
    _bindEventHandlers() {
        this._handleTurboLoad = this._handleTurboLoad.bind(this);
        this._handleOpenEvent = this._handleOpenEvent.bind(this);
        this._handleCheckboxChange = this._handleCheckboxChange.bind(this);
    }

    /**
     * DE: Registriert globale Event-Listener.
     * EN: Registers global event listeners.
     */
    _registerEventListeners() {
        // DE: Turbo-Integration: Banner nach Navigation prüfen
        // EN: Turbo integration: Check banner after navigation
        document.addEventListener('turbo:load', this._handleTurboLoad);

        // DE: Custom Event zum Öffnen des Banners (z.B. aus Footer-Link)
        // EN: Custom event to open the banner (e.g., from footer link)
        document.addEventListener('cookie-consent:open', this._handleOpenEvent);

        // DE: Checkbox-Änderungen für Vendor-UI
        // EN: Checkbox changes for vendor UI
        this.element.addEventListener('change', this._handleCheckboxChange);
    }

    /**
     * DE: Entfernt globale Event-Listener.
     * EN: Removes global event listeners.
     */
    _removeEventListeners() {
        document.removeEventListener('turbo:load', this._handleTurboLoad);
        document.removeEventListener('cookie-consent:open', this._handleOpenEvent);
        this.element.removeEventListener('change', this._handleCheckboxChange);
    }

    /**
     * DE: Initialisiert den Zustand beim Verbinden.
     * EN: Initializes state on connect.
     */
    _initializeState() {
        // DE: Bestehende Präferenzen anwenden (Scripts aktivieren/deaktivieren)
        // EN: Apply existing preferences (enable/disable scripts)
        this._applyConsent(this._parsedPreferences());

        // DE: Vendor-Checkboxen mit Kategorie-Status synchronisieren
        // EN: Sync vendor checkboxes with category status
        this._syncVendorCheckboxes();

        // DE: Banner anzeigen, falls erforderlich
        // EN: Show banner if required
        this._showIfRequired();
    }

    // ============================================================
    // PRIVATE METHODS – EVENT HANDLERS
    // DE: Private Methoden – Event-Handler
    // EN: Private methods – Event handlers
    // ============================================================

    /**
     * DE: Handler für Turbo-Navigation.
     * EN: Handler for Turbo navigation.
     */
    _handleTurboLoad() {
        this._showIfRequired();
    }

    /**
     * DE: Handler für das custom open-Event.
     * EN: Handler for the custom open event.
     */
    _handleOpenEvent() {
        this.show();
    }

    /**
     * DE: Hält Vendor-Checkboxen mit der Kategorie-Checkbox synchron.
     * EN: Keeps vendor checkboxes in sync with the category checkbox.
     *
     * @param {Event} event
     */
    _handleCheckboxChange(event) {
        const checkbox = event.target;
        if (!(checkbox instanceof HTMLInputElement)) {
            return;
        }

        if (checkbox.dataset.consentType !== 'category') {
            return;
        }

        const category = checkbox.dataset.consentCategoryName;
        if (!category) {
            return;
        }

        this._setVendorsEnabled(category, checkbox.checked);
    }

    // ============================================================
    // PRIVATE METHODS – SERVER COMMUNICATION
    // DE: Private Methoden – Server-Kommunikation
    // EN: Private methods – Server communication
    // ============================================================

    /**
     * DE: Sendet die Consent-Entscheidung an den Server.
     *     Der Server speichert in Cookie und/oder Datenbank (DSGVO-Nachweis).
     *
     * EN: Sends the consent decision to the server.
     *     The server stores in cookie and/or database (GDPR proof).
     *
     * @param {string} action - 'accept_all' | 'reject_optional' | 'custom'
     * @param {Object|null} preferences - Benutzerdefinierte Präferenzen (nur bei 'custom')
     */
    _submit(action, preferences = null) {
        const body = {
            action,
            csrf_token: this.csrfTokenValue,
        };

        if (preferences !== null) {
            body.preferences = preferences;
        }

        fetch(this.endpointValue, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                this._handleSubmitSuccess(data);
            })
            .catch((error) => {
                this._handleSubmitError(error);
            });
    }

    /**
     * DE: Verarbeitet erfolgreiche Server-Antwort.
     * EN: Handles successful server response.
     *
     * @param {Object} data - Server-Antwort mit preferences
     */
    _handleSubmitSuccess(data) {
        if (data && data.preferences) {
            // DE: Consent auf der Seite anwenden (Scripts aktivieren)
            // EN: Apply consent on the page (activate scripts)
            this._applyConsent(data.preferences);

            // DE: Event für andere Module dispatchen
            // EN: Dispatch event for other modules
            this._dispatchConsentChanged(data.preferences);
        }

        if (this.reloadOnChangeValue) {
            setTimeout(() => window.location.reload(), 150);
            return;
        }

        this.hide();
    }

    /**
     * DE: Verarbeitet Fehler bei der Server-Kommunikation.
     * EN: Handles server communication errors.
     *
     * @param {Error} error - Der aufgetretene Fehler
     */
    _handleSubmitError(error) {
        console.warn('[CookieConsent] Server sync failed:', error.message);
        // DE: Banner trotzdem schließen – Cookie wurde clientseitig nicht gesetzt,
        //     aber UX sollte nicht blockiert werden.
        // EN: Close banner anyway – cookie was not set client-side,
        //     but UX should not be blocked.
        this.hide();
    }

    // ============================================================
    // PRIVATE METHODS – CONSENT APPLICATION
    // DE: Private Methoden – Consent-Anwendung
    // EN: Private methods – Consent application
    // ============================================================

    /**
     * DE: Wendet die Consent-Präferenzen auf die Seite an.
     *     Aktiviert/deaktiviert Elemente basierend auf data-consent-category.
     *
     * EN: Applies consent preferences to the page.
     *     Enables/disables elements based on data-consent-category.
     *
     * @param {Object} preferences - Objekt mit Kategorie: { allowed, vendors }
     */
    _applyConsent(preferences) {
        const normalized = preferences || {};

        document.querySelectorAll('[data-consent-category]').forEach((element) => {
            const category = element.dataset.consentCategory;
            const categoryData = normalized[category] || {};
            const categoryAllowed = Boolean(categoryData.allowed);
            const vendor = element.dataset.consentVendor;
            const vendors = categoryData.vendors || {};
            const allowed = vendor ? categoryAllowed && Boolean(vendors[vendor]) : categoryAllowed;
            const mode = element.dataset.consentMode || 'hide';

            // DE: Spezialbehandlung für Script-Tags
            // EN: Special handling for script tags
            if (element.tagName === 'SCRIPT') {
                this._handleScriptElement(element, allowed);
                return;
            }

            // DE: Normale Elemente ein-/ausblenden oder entfernen
            // EN: Show/hide or remove normal elements
            this._handleRegularElement(element, allowed, mode);
        });

        // DE: Google Consent Mode v2 aktualisieren (falls aktiviert)
        // EN: Update Google Consent Mode v2 (if enabled)
        this._updateGoogleConsentMode(normalized);
    }

    /**
     * DE: Aktiviert/Deaktiviert Vendor-Checkboxen je Kategorie.
     * EN: Enable/disable vendor checkboxes per category.
     */
    _syncVendorCheckboxes() {
        const processedCategories = new Set();

        this.checkboxTargets.forEach((checkbox) => {
            if (checkbox.dataset.consentType !== 'vendor') {
                return;
            }

            const category = checkbox.dataset.consentCategoryName;
            if (!category) {
                return;
            }

            const categoryCheckbox = this._findCategoryCheckbox(category);
            if (!categoryCheckbox) {
                return;
            }

            const enabled = categoryCheckbox.checked;
            const required = checkbox.dataset.consentRequired === 'true';
            checkbox.disabled = required || !enabled;

            if (!processedCategories.has(category)) {
                const list = this.element.querySelector(
                    `[data-consent-vendor-list="true"][data-consent-category-name="${category}"]`
                );
                if (list instanceof HTMLElement) {
                    if (enabled) {
                        list.removeAttribute('hidden');
                    } else {
                        list.setAttribute('hidden', 'hidden');
                    }
                }
                processedCategories.add(category);
            }
        });
    }

    /**
     * DE: Setzt Vendor-Checkboxen einer Kategorie auf enabled/disabled.
     * EN: Sets vendor checkboxes of a category to enabled/disabled.
     *
     * @param {string} category
     * @param {boolean} enabled
     */
    _setVendorsEnabled(category, enabled) {
        const list = this.element.querySelector(
            `[data-consent-vendor-list="true"][data-consent-category-name="${category}"]`
        );

        this.checkboxTargets.forEach((checkbox) => {
            if (checkbox.dataset.consentType !== 'vendor') {
                return;
            }

            if (checkbox.dataset.consentCategoryName !== category) {
                return;
            }

            if (checkbox.dataset.consentRequired === 'true') {
                checkbox.disabled = true;
                checkbox.checked = true;
                return;
            }

            checkbox.disabled = !enabled;
            if (enabled) {
                checkbox.checked = checkbox.dataset.consentDefault === 'true';
            } else {
                checkbox.checked = false;
            }
        });

        if (list instanceof HTMLElement) {
            if (enabled) {
                list.removeAttribute('hidden');
            } else {
                list.setAttribute('hidden', 'hidden');
            }
        }
    }

    /**
     * DE: Findet die Kategorie-Checkbox für den Namen.
     * EN: Finds the category checkbox for the name.
     *
     * @param {string} category
     * @returns {HTMLInputElement|null}
     */
    _findCategoryCheckbox(category) {
        return this.checkboxTargets.find((checkbox) => {
            return checkbox.dataset.consentType === 'category'
                && checkbox.dataset.consentCategoryName === category;
        }) || null;
    }

    /**
     * DE: Behandelt Script-Elemente (aktiviert blockierte Scripts).
     * EN: Handles script elements (activates blocked scripts).
     *
     * @param {HTMLScriptElement} element - Das Script-Element
     * @param {boolean} allowed - Ob die Kategorie erlaubt ist
     */
    _handleScriptElement(element, allowed) {
        // DE: Nur aktivieren, wenn erlaubt UND noch nicht aktiviert (type=text/plain)
        // EN: Only activate if allowed AND not yet activated (type=text/plain)
        if (allowed && element.type === 'text/plain') {
            const script = document.createElement('script');

            // DE: Externe Script-URL übernehmen
            // EN: Copy external script URL
            if (element.dataset.consentSrc) {
                script.src = element.dataset.consentSrc;
            }

            // DE: Inline-Script-Inhalt übernehmen
            // EN: Copy inline script content
            script.text = element.textContent;

            // DE: Altes Element durch neues ersetzen (aktiviert das Script)
            // EN: Replace old element with new one (activates the script)
            element.replaceWith(script);
        }
    }

    /**
     * DE: Behandelt reguläre Elemente (nicht Scripts).
     * EN: Handles regular elements (non-scripts).
     *
     * @param {HTMLElement} element - Das Element
     * @param {boolean} allowed - Ob die Kategorie erlaubt ist
     * @param {string} mode - 'hide' oder 'remove'
     */
    _handleRegularElement(element, allowed, mode) {
        if (allowed) {
            element.hidden = false;
        } else if (mode === 'remove') {
            element.remove();
        } else {
            element.hidden = true;
        }
    }

    // ============================================================
    // PRIVATE METHODS – UTILITIES
    // DE: Private Methoden – Hilfsfunktionen
    // EN: Private methods – Utilities
    // ============================================================

    /**
     * DE: Zeigt das Banner an, falls erforderlich (kein Consent vorhanden).
     * EN: Shows the banner if required (no consent present).
     */
    _showIfRequired() {
        if (this.requiredValue) {
            this.show();
        }
    }

    /**
     * DE: Sammelt die Präferenzen aus den Checkbox-Targets.
     * EN: Collects preferences from checkbox targets.
     *
     * @returns {Object} Objekt mit Kategorie: { allowed, vendors }
     */
    _collectPreferences() {
        const preferences = {};

        this.checkboxTargets.forEach((checkbox) => {
            const type = checkbox.dataset.consentType || 'category';
            const category = checkbox.dataset.consentCategoryName || checkbox.value;

            if (!preferences[category]) {
                preferences[category] = { allowed: false, vendors: {} };
            }

            if (type === 'vendor') {
                const vendor = checkbox.dataset.consentVendor || checkbox.value;
                preferences[category].vendors[vendor] = checkbox.checked;
                return;
            }

            preferences[category].allowed = checkbox.checked;
        });

        return preferences;
    }

    /**
     * DE: Parst die Präferenzen aus dem Value-Attribut.
     * EN: Parses preferences from the value attribute.
     *
     * @returns {Object} Geparste Präferenzen oder leeres Objekt
     */
    _parsedPreferences() {
        if (!this.hasPreferencesValue || !this.preferencesValue) {
            return {};
        }

        try {
            return JSON.parse(this.preferencesValue);
        } catch (error) {
            console.warn('[CookieConsent] Failed to parse preferences:', error.message);
            return {};
        }
    }

    /**
     * DE: Dispatcht ein Custom Event bei Consent-Änderung.
     *     Andere Module können darauf reagieren.
     *
     * EN: Dispatches a custom event on consent change.
     *     Other modules can react to this.
     *
     * @param {Object} preferences - Die neuen Präferenzen
     */
    _dispatchConsentChanged(preferences) {
        document.dispatchEvent(
            new CustomEvent('cookie-consent:changed', {
                detail: { preferences },
                bubbles: true,
            })
        );
    }

    // ============================================================
    // PRIVATE METHODS – GOOGLE CONSENT MODE v2
    // DE: Private Methoden – Google Consent Mode v2 Integration
    // EN: Private methods – Google Consent Mode v2 integration
    // ============================================================

    /**
     * DE: Aktualisiert Google Consent Mode v2 basierend auf den Präferenzen.
     *     Wird nur ausgeführt wenn:
     *     - googleConsentModeEnabled = true
     *     - gtag() Funktion existiert
     *
     * EN: Updates Google Consent Mode v2 based on preferences.
     *     Only executed when:
     *     - googleConsentModeEnabled = true
     *     - gtag() function exists
     *
     * @param {Object} preferences - Die aktuellen Consent-Präferenzen
     */
    _updateGoogleConsentMode(preferences) {
        // DE: Feature deaktiviert? Abbrechen.
        // EN: Feature disabled? Abort.
        if (!this.googleConsentModeEnabledValue) {
            return;
        }

        // DE: gtag() nicht verfügbar? Warning ausgeben.
        // EN: gtag() not available? Log warning.
        if (typeof gtag !== 'function') {
            console.warn(
                '[CookieConsent] Google Consent Mode enabled but gtag() not found. ' +
                'Make sure Google Analytics/Tag Manager is loaded before the consent modal.'
            );
            return;
        }

        // DE: Mapping parsen
        // EN: Parse mapping
        const mapping = this._parseGoogleConsentModeMapping();
        if (mapping === null) {
            return;
        }

        // DE: Consent-Status für jeden Google Consent Type ermitteln
        // EN: Determine consent status for each Google consent type
        const consentUpdate = {};
        for (const [googleType, bundleCategory] of Object.entries(mapping)) {
            const categoryData = preferences[bundleCategory] || {};
            const hasConsent = Boolean(categoryData.allowed);
            consentUpdate[googleType] = hasConsent ? 'granted' : 'denied';
        }

        // DE: gtag consent update aufrufen
        // EN: Call gtag consent update
        gtag('consent', 'update', consentUpdate);

        console.debug('[CookieConsent] Google Consent Mode updated:', consentUpdate);
    }

    /**
     * DE: Parst das Google Consent Mode Mapping aus dem Value-Attribut.
     * EN: Parses Google Consent Mode mapping from value attribute.
     *
     * @returns {Object|null} Gepartes Mapping oder null bei Fehler
     */
    _parseGoogleConsentModeMapping() {
        if (!this.hasGoogleConsentModeMappingValue || !this.googleConsentModeMappingValue) {
            console.warn('[CookieConsent] Google Consent Mode enabled but no mapping configured.');
            return null;
        }

        try {
            return JSON.parse(this.googleConsentModeMappingValue);
        } catch (error) {
            console.warn('[CookieConsent] Failed to parse Google Consent Mode mapping:', error.message);
            return null;
        }
    }
}
