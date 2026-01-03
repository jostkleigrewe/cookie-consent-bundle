<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class ConsentRequired
{
}
