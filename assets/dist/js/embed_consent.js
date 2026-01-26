// assets/dist/js/embed_consent.js

/**
 * Cookie Consent Embed Handler
 *
 * DE: Selbstausführendes Modul für consent-gesteuerte Embeds.
 *     Rendert externe Inhalte (YouTube, Vimeo, Google Maps, etc.)
 *     erst nach Zustimmung des Nutzers zur entsprechenden Kategorie.
 *     Funktioniert unabhängig von Stimulus – ideal für serverseitig
 *     gerenderte Platzhalter-Elemente.
 *
 * EN: Self-executing module for consent-controlled embeds.
 *     Renders external content (YouTube, Vimeo, Google Maps, etc.)
 *     only after user consent to the corresponding category.
 *     Works independently of Stimulus – ideal for server-side
 *     rendered placeholder elements.
 *
 * @example
 * // DE: YouTube-Embed mit Platzhalter (serverseitig gerendert)
 * // EN: YouTube embed with placeholder (server-side rendered)
 * <div data-cookie-consent-embed
 *      data-type="iframe"
 *      data-src="https://www.youtube-nocookie.com/embed/VIDEO_ID"
 *      data-title="Video Title"
 *      data-category="marketing"
 *      data-aspect-ratio="16 / 9"
 *      data-preferences='{"marketing":{"allowed":false,"vendors":{}}}'>
 *   <p>Bitte akzeptieren Sie Marketing-Cookies / Please accept marketing cookies</p>
 * </div>
 *
 * @example
 * // DE: Google Maps mit HTML-Embed
 * // EN: Google Maps with HTML embed
 * <div data-cookie-consent-embed
 *      data-type="html"
 *      data-html="<iframe src='...'></iframe>"
 *      data-category="marketing">
 *   <p>Karte wird nach Zustimmung geladen / Map loads after consent</p>
 * </div>
 *
 * @listens cookie-consent:changed
 *          DE: Event vom CookieConsentController bei Consent-Änderung
 *          EN: Event from CookieConsentController on consent change
 */
(function () {
    'use strict';

    // ============================================================
    // UTILITY FUNCTIONS
    // DE: Hilfsfunktionen
    // EN: Utility functions
    // ============================================================

    /**
     * DE: Parst JSON-Präferenzen aus einem String.
     *     Gibt null zurück bei leerem Input oder Parse-Fehler.
     *
     * EN: Parses JSON preferences from a string.
     *     Returns null on empty input or parse error.
     *
     * @param {string|null|undefined} raw - DE: Roher JSON-String | EN: Raw JSON string
     * @returns {Object|null} DE: Geparste Präferenzen oder null | EN: Parsed preferences or null
     *
     * @example
     * parsePreferences('{"marketing": true}')  // → { marketing: true }
     * parsePreferences('')                      // → null
     * parsePreferences('invalid')               // → null (+ console.warn)
     */
    function parsePreferences(raw) {
        if (!raw) {
            return null;
        }

        try {
            return JSON.parse(raw);
        } catch (error) {
            console.warn('[CookieConsent] Failed to parse preferences:', error.message);
            return null;
        }
    }

    // ============================================================
    // EMBED RENDERING
    // DE: Embed-Rendering
    // EN: Embed rendering
    // ============================================================

    /**
     * DE: Rendert ein einzelnes Embed-Element basierend auf seinem Typ.
     *     Unterstützte Typen:
     *     - 'iframe': Erstellt ein iframe-Element (Standard)
     *     - 'html': Fügt beliebiges HTML ein
     *     - 'script': Lädt ein externes Script
     *
     * EN: Renders a single embed element based on its type.
     *     Supported types:
     *     - 'iframe': Creates an iframe element (default)
     *     - 'html': Inserts arbitrary HTML
     *     - 'script': Loads an external script
     *
     * @param {HTMLElement} el - DE: Das Platzhalter-Element mit data-Attributen
     *                           EN: The placeholder element with data attributes
     *
     * @example
     * // DE: Element wird durch iframe ersetzt
     * // EN: Element gets replaced by iframe
     * // <div data-type="iframe" data-src="https://...">
     * renderEmbed(element);
     * // → <iframe src="https://..." ...></iframe>
     */
    function renderEmbed(el) {
        // DE: Bereits gerendert? Abbrechen um Duplikate zu vermeiden.
        // EN: Already rendered? Abort to avoid duplicates.
        if (el.dataset.rendered === 'true') {
            return;
        }

        // DE: Konfiguration aus data-Attributen lesen
        // EN: Read configuration from data attributes
        const type = el.dataset.type || 'iframe';
        const src = el.dataset.src || '';
        const title = el.dataset.title || 'Embedded content';

        // --------------------------------------------------------
        // DE: Typ 'iframe' – Erstellt responsives iframe
        // EN: Type 'iframe' – Creates responsive iframe
        // --------------------------------------------------------
        if (type === 'iframe') {
            const iframe = document.createElement('iframe');

            // DE: Grundlegende Attribute setzen
            // EN: Set basic attributes
            iframe.src = src;
            iframe.title = title;
            iframe.loading = 'lazy';        // DE: Lazy Loading für Performance | EN: Lazy loading for performance
            iframe.allowFullscreen = true;

            // DE: Responsive Styles
            // EN: Responsive styles
            iframe.style.width = '100%';
            iframe.style.aspectRatio = el.dataset.aspectRatio || '16 / 9';
            iframe.style.border = '0';

            // DE: Optionale Permissions (z.B. für YouTube: "accelerometer; autoplay; clipboard-write")
            // EN: Optional permissions (e.g., for YouTube: "accelerometer; autoplay; clipboard-write")
            if (el.dataset.allow) {
                iframe.allow = el.dataset.allow;
            }

            // DE: Element als gerendert markieren und ersetzen
            // EN: Mark element as rendered and replace
            el.dataset.rendered = 'true';
            el.replaceWith(iframe);
            return;
        }

        // --------------------------------------------------------
        // DE: Typ 'html' – Fügt beliebiges HTML ein
        //     ACHTUNG: Nur für vertrauenswürdige Inhalte verwenden!
        // EN: Type 'html' – Inserts arbitrary HTML
        //     WARNING: Only use for trusted content!
        // --------------------------------------------------------
        if (type === 'html') {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = el.dataset.html || '';

            el.dataset.rendered = 'true';
            el.replaceWith(wrapper);
            return;
        }

        // --------------------------------------------------------
        // DE: Typ 'script' – Lädt externes Script
        //     Nützlich für Widgets wie Twitter, Instagram, etc.
        // EN: Type 'script' – Loads external script
        //     Useful for widgets like Twitter, Instagram, etc.
        // --------------------------------------------------------
        if (type === 'script') {
            const script = document.createElement('script');
            script.src = src;
            script.async = true;            // DE: Async für Performance | EN: Async for performance

            el.dataset.rendered = 'true';
            el.replaceWith(script);
        }
    }

    // ============================================================
    // CONSENT APPLICATION
    // DE: Consent-Anwendung
    // EN: Consent application
    // ============================================================

    /**
     * DE: Wendet Consent-Präferenzen auf alle Embed-Platzhalter an.
     *     Durchläuft alle Elemente mit [data-cookie-consent-embed]
     *     und rendert sie, falls die zugehörige Kategorie erlaubt ist.
     *
     * EN: Applies consent preferences to all embed placeholders.
     *     Iterates through all elements with [data-cookie-consent-embed]
     *     and renders them if the corresponding category is allowed.
     *
     * @param {Object|null} preferences - DE: Objekt mit Kategorie: { allowed, vendors }
     *                                    EN: Object with category: { allowed, vendors }
     *
     * @example
     * applyConsent({ marketing: true, analytics: false });
     * // DE: Rendert alle Embeds mit data-category="marketing"
     * // EN: Renders all embeds with data-category="marketing"
     */
    function applyConsent(preferences) {
        if (!preferences) {
            return;
        }

        document.querySelectorAll('[data-cookie-consent-embed]').forEach((el) => {
            // DE: Standard-Kategorie ist 'marketing' (häufigster Fall)
            // EN: Default category is 'marketing' (most common case)
            const category = el.dataset.category || 'marketing';
            const vendor = el.dataset.vendor || null;
            const categoryData = preferences[category] || {};
            const categoryAllowed = Boolean(categoryData.allowed);
            const vendors = categoryData.vendors || {};
            const allowed = vendor ? categoryAllowed && Boolean(vendors[vendor]) : categoryAllowed;

            // DE: Nur rendern wenn Kategorie/Vendor erlaubt
            // EN: Only render if category/vendor is allowed
            if (allowed) {
                renderEmbed(el);
            }
        });
    }

    // ============================================================
    // EVENT LISTENERS & INITIALIZATION
    // DE: Event-Listener & Initialisierung
    // EN: Event listeners & Initialization
    // ============================================================

    /**
     * DE: Listener für Consent-Änderungen.
     *     Wird ausgelöst wenn der Nutzer seine Präferenzen ändert.
     *
     * EN: Listener for consent changes.
     *     Triggered when the user changes their preferences.
     *
     * @listens cookie-consent:changed
     */
    document.addEventListener('cookie-consent:changed', (event) => {
        const preferences = (event.detail || {}).preferences || {};
        applyConsent(preferences);
    });

    /**
     * DE: Initiale Verarbeitung beim Seitenaufruf.
     *     Prüft alle Embed-Platzhalter auf eingebettete Präferenzen
     *     und rendert bereits erlaubte Embeds sofort.
     *
     * EN: Initial processing on page load.
     *     Checks all embed placeholders for embedded preferences
     *     and immediately renders already-allowed embeds.
     */
    document.querySelectorAll('[data-cookie-consent-embed]').forEach((el) => {
        // DE: Präferenzen können im Element selbst eingebettet sein
        //     (serverseitig aus dem Cookie gelesen)
        // EN: Preferences can be embedded in the element itself
        //     (read from cookie server-side)
        const preferences = parsePreferences(el.dataset.preferences);
        if (preferences) {
            applyConsent(preferences);
        }
    });
})();
