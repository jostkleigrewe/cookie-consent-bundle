<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;
use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerAttributeResolver - Liest Consent-Attribute von Controllern
 *
 * DE: Analysiert Controller-Klassen und -Methoden auf #[ConsentRequired]
 *     und #[ConsentStateless] Attribute. Cached die Ergebnisse fuer Performance.
 *
 * EN: Analyzes controller classes and methods for #[ConsentRequired]
 *     and #[ConsentStateless] attributes. Caches results for performance.
 *
 * Attribut-Prioritaet / Attribute priority:
 * - Methoden-Attribut ueberschreibt Klassen-Attribut
 * - #[ConsentStateless] hat Vorrang vor #[ConsentRequired]
 *
 * @example
 * // DE: Klassen-Attribut gilt fuer alle Methoden
 * // EN: Class attribute applies to all methods
 * #[ConsentRequired]
 * class MyController {
 *     public function index() { }  // ConsentRequired
 *
 *     #[ConsentStateless]
 *     public function api() { }    // ConsentStateless (ueberschreibt Klasse)
 * }
 */
final class ControllerAttributeResolver
{
    /**
     * DE: Cache fuer aufgeloeste Attribute (Controller-String => Ergebnis).
     * EN: Cache for resolved attributes (controller string => result).
     *
     * @var array<string, array{stateless: bool, required: bool}>
     */
    private array $cache = [];

    /**
     * DE: Prueft ob der Controller/Action als stateless markiert ist.
     *
     * EN: Checks if the controller/action is marked as stateless.
     *
     * @param Request $request DE: HTTP-Request mit _controller Attribut
     *                         EN: HTTP request with _controller attribute
     * @return bool DE: true wenn stateless | EN: true if stateless
     */
    public function isStateless(Request $request): bool
    {
        $attributes = $this->resolve($request);

        return $attributes['stateless'];
    }

    /**
     * DE: Prueft ob der Controller/Action als required markiert ist.
     *
     * EN: Checks if the controller/action is marked as required.
     *
     * @param Request $request DE: HTTP-Request mit _controller Attribut
     *                         EN: HTTP request with _controller attribute
     * @return bool DE: true wenn required | EN: true if required
     */
    public function isRequired(Request $request): bool
    {
        $attributes = $this->resolve($request);

        return $attributes['required'];
    }

    /**
     * DE: Loest Attribute fuer den Controller im Request auf.
     *     Verwendet Reflection und cached das Ergebnis.
     *
     * EN: Resolves attributes for the controller in the request.
     *     Uses reflection and caches the result.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return array{stateless: bool, required: bool} DE: Aufgeloeste Attribute
     *                                                 EN: Resolved attributes
     */
    private function resolve(Request $request): array
    {
        // DE: _controller aus Request-Attributen holen (vom Router gesetzt)
        // EN: Get _controller from request attributes (set by router)
        $controller = $request->attributes->get('_controller');
        if (!is_string($controller) || $controller === '') {
            return ['stateless' => false, 'required' => false];
        }

        // DE: Cache pruefen
        // EN: Check cache
        if (isset($this->cache[$controller])) {
            return $this->cache[$controller];
        }

        // DE: Controller-Format muss 'Class::method' sein
        // EN: Controller format must be 'Class::method'
        if (!str_contains($controller, '::')) {
            return $this->cache[$controller] = ['stateless' => false, 'required' => false];
        }

        [$class, $method] = explode('::', $controller, 2);

        // DE: Klasse muss existieren
        // EN: Class must exist
        if (!class_exists($class)) {
            return $this->cache[$controller] = ['stateless' => false, 'required' => false];
        }

        // DE: Attribute auf Klassen-Ebene pruefen
        // EN: Check attributes at class level
        $refClass = new \ReflectionClass($class);
        $stateless = !empty($refClass->getAttributes(ConsentStateless::class));
        $required = !empty($refClass->getAttributes(ConsentRequired::class));

        // DE: Attribute auf Methoden-Ebene pruefen (ueberschreibt Klasse)
        // EN: Check attributes at method level (overrides class)
        if ($refClass->hasMethod($method)) {
            $refMethod = $refClass->getMethod($method);
            $stateless = $stateless || !empty($refMethod->getAttributes(ConsentStateless::class));
            $required = $required || !empty($refMethod->getAttributes(ConsentRequired::class));
        }

        // DE: Stateless hat Vorrang vor Required
        // EN: Stateless takes precedence over Required
        if ($stateless) {
            return $this->cache[$controller] = ['stateless' => true, 'required' => false];
        }

        return $this->cache[$controller] = ['stateless' => false, 'required' => $required];
    }
}
