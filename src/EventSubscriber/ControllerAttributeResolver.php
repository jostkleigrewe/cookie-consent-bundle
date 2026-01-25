<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;
use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerAttributeResolver - Liest Consent-Attribute von Controllern
 *
 * Analysiert Controller-Klassen und -Methoden auf #[ConsentRequired]
 *     und #[ConsentStateless] Attribute. Cached die Ergebnisse fuer Performance.
 *
 * Analyzes controller classes and methods for #[ConsentRequired]
 *     and #[ConsentStateless] attributes. Caches results for performance.
 *
 * Attribute priority:
 * - Method attribute overrides class attribute
 * - #[ConsentStateless] takes precedence over #[ConsentRequired]
 *
 * @example
 * // Class attribute applies to all methods
 * #[ConsentRequired]
 * class MyController {
 *     public function index() { }  // ConsentRequired
 *
 *     #[ConsentStateless]
 *     public function api() { }    // ConsentStateless (overrides class)
 * }
 */
final class ControllerAttributeResolver
{
    /**
     * Cache for resolved attributes (controller string => result).
     *
     * @var array<string, array{stateless: bool, required: bool}>
     */
    private array $cache = [];

    /**
     * Checks if the controller/action is marked as stateless.
     *
     * @param Request $request HTTP request with _controller attribute
     * @return bool true if stateless
     */
    public function isStateless(Request $request): bool
    {
        $attributes = $this->resolve($request);

        return $attributes['stateless'];
    }

    /**
     * Checks if the controller/action is marked as required.
     *
     * @param Request $request HTTP request with _controller attribute
     * @return bool true if required
     */
    public function isRequired(Request $request): bool
    {
        $attributes = $this->resolve($request);

        return $attributes['required'];
    }

    /**
     * Resolves attributes for the controller in the request.
     *     Uses reflection and caches the result.
     *
     * @param Request $request HTTP request
     * @return array{stateless: bool, required: bool} Resolved attributes
     */
    private function resolve(Request $request): array
    {
        // Get _controller from request attributes (set by router)
        $controller = $request->attributes->get('_controller');
        if (!is_string($controller) || $controller === '') {
            return ['stateless' => false, 'required' => false];
        }

        // Check cache
        if (isset($this->cache[$controller])) {
            return $this->cache[$controller];
        }

        // Controller format must be 'Class::method'
        if (!str_contains($controller, '::')) {
            return $this->cache[$controller] = ['stateless' => false, 'required' => false];
        }

        [$class, $method] = explode('::', $controller, 2);

        // Class must exist
        if (!class_exists($class)) {
            return $this->cache[$controller] = ['stateless' => false, 'required' => false];
        }

        // Check attributes at class level
        $refClass = new \ReflectionClass($class);
        $stateless = !empty($refClass->getAttributes(ConsentStateless::class));
        $required = !empty($refClass->getAttributes(ConsentRequired::class));

        // Check attributes at method level (overrides class)
        if ($refClass->hasMethod($method)) {
            $refMethod = $refClass->getMethod($method);
            $stateless = $stateless || !empty($refMethod->getAttributes(ConsentStateless::class));
            $required = $required || !empty($refMethod->getAttributes(ConsentRequired::class));
        }

        // Stateless takes precedence over Required
        if ($stateless) {
            return $this->cache[$controller] = ['stateless' => true, 'required' => false];
        }

        return $this->cache[$controller] = ['stateless' => false, 'required' => $required];
    }
}
