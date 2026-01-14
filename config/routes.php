<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // DE: Fester Default-Pfad; kann in der Host-App durch eine Route mit gleichem Namen überschrieben werden.
    // EN: Fixed default path; can be overridden in the host app by defining a route with the same name.
    $routes->add('cookie_consent_update', '/_cookie-consent')
        ->controller('Jostkleigrewe\\CookieConsentBundle\\Controller\\CookieConsentController::update')
        ->methods(['POST']);
};
