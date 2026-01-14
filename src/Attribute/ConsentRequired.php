<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Attribute;

use Attribute;

/**
 * DE: Markiert Controller/Action als consent-pflichtig (immer erforderlich).
 * EN: Marks controllers/actions as consent-required (always required).
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ConsentRequired
{
}
