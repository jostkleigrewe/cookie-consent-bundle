<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Attribute;

use Attribute;

/**
 * ConsentRequired - Markiert Controller/Action als consent-pflichtig
 *
 * PHP 8 Attribut das Controller oder Actions als consent-pflichtig markiert.
 *     Routes mit diesem Attribut zeigen immer das Consent-Modal wenn kein
 *     Consent vorliegt, unabhaengig von anderen Enforcement-Einstellungen.
 *
 * PHP 8 attribute that marks controllers or actions as consent-required.
 *     Routes with this attribute always show the consent modal if no
 *     consent exists, regardless of other enforcement settings.
 *
 * Can be used at class or method level:
 * - Class: All actions in the controller are consent-required
 * - Method: Only this action is consent-required
 *
 * @example
 * // Entire controller requires consent
 * #[ConsentRequired]
 * class NewsletterController
 * {
 *     public function subscribe(): Response { ... }
 *     public function unsubscribe(): Response { ... }
 * }
 *
 * @example
 * // Only one action requires consent
 * class ContactController
 * {
 *     #[ConsentRequired]
 *     public function trackingForm(): Response { ... }
 *
 *     public function simpleForm(): Response { ... }  // No consent needed
 * }
 *
 * @see ConsentStateless - Opposite: route is consent-free
 * @see ConsentRequirementResolver - Evaluates the attribute
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ConsentRequired
{
}
