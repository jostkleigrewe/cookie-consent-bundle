// assets/dist/controllers/cookie_consent_settings_button_controller.js

import { Controller } from '@hotwired/stimulus';

/**
 * Cookie Consent Settings Button Controller
 *
 * DE: Stimulus Controller für den Cookie-Einstellungen-Button.
 *     Ermöglicht das erneute Öffnen des Cookie-Consent-Modals,
 *     z.B. aus dem Footer oder einer Datenschutzseite heraus.
 *     Dispatcht ein Custom Event, auf das der Haupt-Controller reagiert.
 *
 * EN: Stimulus controller for the cookie settings button.
 *     Allows reopening the cookie consent modal,
 *     e.g., from the footer or a privacy policy page.
 *     Dispatches a custom event that the main controller listens to.
 *
 * @example
 * // DE: Verwendung in Twig/HTML
 * // EN: Usage in Twig/HTML
 * <button data-controller="cookie-consent-settings-button"
 *         data-action="cookie-consent-settings-button#open">
 *   Cookie-Einstellungen / Cookie Settings
 * </button>
 *
 * @example
 * // DE: Als Twig-Komponente
 * // EN: As Twig component
 * <twig:CookieSettingsButton label="Cookie-Einstellungen" />
 *
 * @see cookie_consent_controller.js - DE: Haupt-Controller, der auf das Event reagiert
 *                                     EN: Main controller that listens to the event
 */
export default class extends Controller {
    // ============================================================
    // PUBLIC ACTIONS
    // DE: Öffentliche Aktionen (aufrufbar via data-action)
    // EN: Public actions (callable via data-action)
    // ============================================================

    /**
     * DE: Öffnet das Cookie-Consent-Modal.
     *     Dispatcht das Event 'cookie-consent:open', auf das der
     *     CookieConsentController hört und das Modal anzeigt.
     *
     * EN: Opens the cookie consent modal.
     *     Dispatches the 'cookie-consent:open' event, which the
     *     CookieConsentController listens to and displays the modal.
     *
     * @example
     * // DE: Button mit Klick-Aktion
     * // EN: Button with click action
     * <button data-action="cookie-consent-settings-button#open">
     *   Einstellungen ändern / Change settings
     * </button>
     *
     * @example
     * // DE: Link im Footer
     * // EN: Link in footer
     * <a href="#" data-controller="cookie-consent-settings-button"
     *             data-action="click->cookie-consent-settings-button#open">
     *   Cookie-Präferenzen / Cookie Preferences
     * </a>
     *
     * @fires cookie-consent:open
     *        DE: Custom Event ohne payload, das das Modal öffnet
     *        EN: Custom event without payload that opens the modal
     */
    open() {
        document.dispatchEvent(new CustomEvent('cookie-consent:open'));
    }
}
