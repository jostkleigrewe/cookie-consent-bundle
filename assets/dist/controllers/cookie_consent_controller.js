// assets/dist/controllers/cookie_consent_controller.js

import { Controller } from '@hotwired/stimulus';

/**
 * Cookie Consent Controller
 *
 * DE: Stimulus Controller für das Cookie-Consent-Banner.
 *     Verwaltet die Anzeige des Banners, speichert Präferenzen
 *     und synchronisiert mit dem Server für DSGVO-Compliance.
 *
 * EN: Stimulus controller for the cookie consent banner.
 *     Manages banner display, stores preferences, and syncs
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
        categories: String,   // DE: JSON der verfügbaren Kategorien | EN: JSON of available categories
        preferences: String,  // DE: JSON der aktuellen Präferenzen | EN: JSON of current preferences
        endpoint: String,     // DE: Server-Endpoint für Speicherung | EN: Server endpoint for storage
        csrfToken: String,    // DE: CSRF-Token für sichere Requests | EN: CSRF token for secure requests
        required: Boolean,    // DE: Ob Banner angezeigt werden muss | EN: Whether banner must be shown
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
    }

    /**
     * DE: Entfernt globale Event-Listener.
     * EN: Removes global event listeners.
     */
    _removeEventListeners() {
        document.removeEventListener('turbo:load', this._handleTurboLoad);
        document.removeEventListener('cookie-consent:open', this._handleOpenEvent);
    }

    /**
     * DE: Initialisiert den Zustand beim Verbinden.
     * EN: Initializes state on connect.
     */
    _initializeState() {
        // DE: Bestehende Präferenzen anwenden (Scripts aktivieren/deaktivieren)
        // EN: Apply existing preferences (enable/disable scripts)
        this._applyConsent(this._parsedPreferences());

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
     * @param {Object} preferences - Objekt mit Kategorie: boolean Paaren
     */
    _applyConsent(preferences) {
        const normalized = preferences || {};

        document.querySelectorAll('[data-consent-category]').forEach((element) => {
            const category = element.dataset.consentCategory;
            const allowed = Boolean(normalized[category]);
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
     * @returns {Object} Objekt mit Kategorie: boolean Paaren
     */
    _collectPreferences() {
        const preferences = {};

        this.checkboxTargets.forEach((checkbox) => {
            preferences[checkbox.value] = checkbox.checked;
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
}
