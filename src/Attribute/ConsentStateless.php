<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Attribute;

use Attribute;

/**
 * ConsentStateless - Markiert Controller/Action als consent-frei
 *
 * PHP 8 Attribut das Controller oder Actions als stateless markiert.
 *     Routes mit diesem Attribut benoetigen niemals Consent, auch wenn
 *     Session-Enforcement aktiviert ist. Ideal fuer:
 *     - API-Endpoints
 *     - Webhooks
 *     - Health-Checks
 *     - Statische Seiten
 *
 * PHP 8 attribute that marks controllers or actions as stateless.
 *     Routes with this attribute never require consent, even when
 *     session enforcement is enabled. Ideal for:
 *     - API endpoints
 *     - Webhooks
 *     - Health checks
 *     - Static pages
 *
 * Takes precedence over ConsentRequired when both are set.
 *
 * @example
 * // Entire API controller is stateless
 * #[ConsentStateless]
 * class ApiController
 * {
 *     public function getData(): JsonResponse { ... }
 * }
 *
 * @example
 * // Single action is stateless
 * class PageController
 * {
 *     #[ConsentStateless]
 *     public function privacyPolicy(): Response { ... }  // No consent needed
 *
 *     public function contact(): Response { ... }  // Consent per config
 * }
 *
 * @see ConsentRequired - Opposite: route is consent-required
 * @see ConsentRequirementResolver - Evaluates the attribute
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ConsentStateless
{
}
