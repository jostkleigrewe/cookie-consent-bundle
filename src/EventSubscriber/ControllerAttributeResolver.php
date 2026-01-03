<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\EventSubscriber;

use JostKleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use JostKleigrewe\CookieConsentBundle\Attribute\ConsentStateless;
use Symfony\Component\HttpFoundation\Request;

final class ControllerAttributeResolver
{
    public function isStateless(Request $request): bool
    {
        $attributes = $this->resolve($request);

        return $attributes['stateless'];
    }

    public function isRequired(Request $request): bool
    {
        $attributes = $this->resolve($request);

        return $attributes['required'];
    }

    /**
     * @return array{stateless: bool, required: bool}
     */
    private function resolve(Request $request): array
    {
        $controller = $request->attributes->get('_controller');
        if (!is_string($controller) || !str_contains($controller, '::')) {
            return ['stateless' => false, 'required' => false];
        }

        [$class, $method] = explode('::', $controller, 2);
        if (!class_exists($class)) {
            return ['stateless' => false, 'required' => false];
        }

        $refClass = new \ReflectionClass($class);
        $stateless = !empty($refClass->getAttributes(ConsentStateless::class));
        $required = !empty($refClass->getAttributes(ConsentRequired::class));

        if ($refClass->hasMethod($method)) {
            $refMethod = $refClass->getMethod($method);
            $stateless = $stateless || !empty($refMethod->getAttributes(ConsentStateless::class));
            $required = $required || !empty($refMethod->getAttributes(ConsentRequired::class));
        }

        if ($stateless) {
            return ['stateless' => true, 'required' => false];
        }

        return ['stateless' => false, 'required' => $required];
    }
}
