<?php

declare(strict_types=1);

use JostKleigrewe\CookieConsentBundle\Controller\CookieConsentController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('cookie_consent_update', '%cookie_consent.routes.consent_endpoint%')
        ->controller([CookieConsentController::class, 'update'])
        ->methods(['POST']);
};
