<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Attribute;

use Attribute;

/**
 * ConsentStateless - Markiert Controller/Action als consent-frei
 *
 * DE: PHP 8 Attribut das Controller oder Actions als stateless markiert.
 *     Routes mit diesem Attribut benoetigen niemals Consent, auch wenn
 *     Session-Enforcement aktiviert ist. Ideal fuer:
 *     - API-Endpoints
 *     - Webhooks
 *     - Health-Checks
 *     - Statische Seiten
 *
 * EN: PHP 8 attribute that marks controllers or actions as stateless.
 *     Routes with this attribute never require consent, even when
 *     session enforcement is enabled. Ideal for:
 *     - API endpoints
 *     - Webhooks
 *     - Health checks
 *     - Static pages
 *
 * Hat Vorrang vor ConsentRequired wenn beide gesetzt sind.
 *
 * @example
 * // DE: Gesamter API-Controller ist stateless
 * // EN: Entire API controller is stateless
 * #[ConsentStateless]
 * class ApiController
 * {
 *     public function getData(): JsonResponse { ... }
 * }
 *
 * @example
 * // DE: Einzelne Action ist stateless
 * // EN: Single action is stateless
 * class PageController
 * {
 *     #[ConsentStateless]
 *     public function privacyPolicy(): Response { ... }  // Kein Consent noetig
 *
 *     public function contact(): Response { ... }  // Consent nach Config
 * }
 *
 * @see ConsentRequired - Das Gegenteil: Route ist consent-pflichtig
 * @see ConsentRequirementResolver - Wertet das Attribut aus
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ConsentStateless
{
}
