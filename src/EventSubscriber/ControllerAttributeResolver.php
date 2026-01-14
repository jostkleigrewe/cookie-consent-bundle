<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;
use Symfony\Component\HttpFoundation\Request;

/**
 * DE: Liest Consent-Attribute auf Controllern/Actions aus.
 * EN: Resolves consent attributes from controllers/actions.
 */
final class ControllerAttributeResolver
{
    /**
     * @var array<string, array{stateless: bool, required: bool}>
     */
    private array $cache = [];

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
        if (!is_string($controller) || $controller === '') {
            return ['stateless' => false, 'required' => false];
        }

        if (isset($this->cache[$controller])) {
            return $this->cache[$controller];
        }

        if (!str_contains($controller, '::')) {
            return $this->cache[$controller] = ['stateless' => false, 'required' => false];
        }

        [$class, $method] = explode('::', $controller, 2);
        if (!class_exists($class)) {
            return $this->cache[$controller] = ['stateless' => false, 'required' => false];
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
            return $this->cache[$controller] = ['stateless' => true, 'required' => false];
        }

        return $this->cache[$controller] = ['stateless' => false, 'required' => $required];
    }
}
