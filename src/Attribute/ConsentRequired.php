<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Attribute;

use Attribute;

/**
 * ConsentRequired - Markiert Controller/Action als consent-pflichtig
 *
 * DE: PHP 8 Attribut das Controller oder Actions als consent-pflichtig markiert.
 *     Routes mit diesem Attribut zeigen immer das Consent-Modal wenn kein
 *     Consent vorliegt, unabhaengig von anderen Enforcement-Einstellungen.
 *
 * EN: PHP 8 attribute that marks controllers or actions as consent-required.
 *     Routes with this attribute always show the consent modal if no
 *     consent exists, regardless of other enforcement settings.
 *
 * Kann auf Klassen- oder Methoden-Ebene verwendet werden:
 * - Klasse: Alle Actions im Controller sind consent-pflichtig
 * - Methode: Nur diese Action ist consent-pflichtig
 *
 * @example
 * // DE: Gesamter Controller ist consent-pflichtig
 * // EN: Entire controller requires consent
 * #[ConsentRequired]
 * class NewsletterController
 * {
 *     public function subscribe(): Response { ... }
 *     public function unsubscribe(): Response { ... }
 * }
 *
 * @example
 * // DE: Nur eine Action ist consent-pflichtig
 * // EN: Only one action requires consent
 * class ContactController
 * {
 *     #[ConsentRequired]
 *     public function trackingForm(): Response { ... }
 *
 *     public function simpleForm(): Response { ... }  // Kein Consent nötig
 * }
 *
 * @see ConsentStateless - Das Gegenteil: Route ist consent-frei
 * @see ConsentRequirementResolver - Wertet das Attribut aus
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ConsentRequired
{
}
