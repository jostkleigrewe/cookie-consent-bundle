<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Attribute;

use Attribute;

/**
 * DE: Markiert Controller/Action als consent-frei (stateless).
 * EN: Marks controllers/actions as consent-free (stateless).
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ConsentStateless
{
}
