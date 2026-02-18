<?php

namespace Merlin\Mvc;

use LogicException;
use RuntimeException;
use InvalidArgumentException;

class Router
{
    protected array $static = [];   // [method][path] => ['handler'=>..., 'namespace'=>...]
    protected array $groups = [];   // [method][firstSegment] => [route, ...]
    protected array $types = [];    // type validators
    protected bool $parseParams = true;
    protected array $middlewareGroupStack = [];
    protected array $prefixGroupStack = [];
    protected array $namedRoutes = []; // [name] => ['tokens'=>...]
    protected ?array $lastAddedTokens = null;

    /**
     * Create a new Router instance.
     */
    public function __construct()
    {
        $this->types = [
            'int' => fn($v) => \ctype_digit($v),
            'alpha' => fn($v) => \ctype_alpha($v),
            'alnum' => fn($v) => \ctype_alnum($v),
            'uuid' => function ($v) {
                if (\strlen($v) !== 36) {
                    return false;
                }
                if ($v[8] !== '-' || $v[13] !== '-' || $v[18] !== '-' || $v[23] !== '-') {
                    return false;
                }
                $hex = \str_replace('-', '', $v);
                return \ctype_xdigit($hex);
            },
            '*' => fn($v) => true,
        ];
    }

    /**
     * Check if automatic parameter parsing is enabled. When enabled, the router will attempt to convert parameter values to int, float, bool or null if they match the corresponding string patterns. This can be useful to avoid manual parsing in your controller actions.
     *
     * @return bool True if automatic parameter parsing is enabled, false otherwise
     */
    public function shouldParseParams(): bool
    {
        return $this->parseParams;
    }

    /**
     * Enable or disable automatic parsing of parameters. When enabled, the router will attempt to convert parameter values to int, float, bool or null if they match the corresponding string patterns. This can be useful to avoid manual parsing in your controller actions.
     *
     * @param bool $parseParams Set to true to enable automatic parameter parsing, false to disable
     */
    public function setParseParams(bool $parseParams): static
    {
        $this->parseParams = $parseParams;
        return $this;
    }

    /**
     * Register a custom type validator for route parameters.
     * Predefined types include 'int', 'alpha', 'alnum', 'uuid', and '*' (matches anything). You can add your own types with custom validation logic. For example, you could add a 'slug' type that only allows lowercase letters, numbers, and hyphens. Once a type is registered, you can use it in your route patterns like /blog/{slug:slug}.
     *
     * @param string $name The type name (e.g., 'slug', 'email')
     * @param callable $validator Function that validates a string value, returns bool
     * @return static For method chaining
     *
     * @example
     * $router->addType('slug', fn($v) => preg_match('/^[a-z0-9-]+$/', $v));
     * $router->add('GET', '/blog/{slug:slug}', 'Blog::view');
     */
    public function addType(string $name, callable $validator): static
    {
        $this->types[$name] = $validator;
        return $this;
    }

    /**
     * Add a new route to the router. The route can be defined for specific HTTP methods, a URI pattern, and an optional handler that overrides the default controller/action resolution. The pattern can include static segments, typed parameters, dynamic segments for namespace/controller/action, and wildcard segments for additional parameters. Validators can be applied to dynamic parameters using predefined or custom types. For example: /user/{id:int} or /blog/{slug:slug}
     *
     * @param string|array|null $method HTTP method(s) for the route (e.g., 'GET', ['GET', 'POST'], or null for all methods)
     * @param string $pattern Route pattern (e.g., '/blog/{slug}', '/{:controller}/{:action}/{:params}')
     * @param string|array|null $handler Optional handler definition to override controller/action. Can be a string like 'Admin::dashboard' or an array with keys 'namespace', 'controller', 'action'.
     * @return static For method chaining
     */
    public function add(
        string|array|null $method,
        string $pattern,
        string|array|null $handler = null
    ): static {

        $routeName = null;
        if (\is_array($handler) && isset($handler['name'])) {
            $routeName = (string) $handler['name'];
            unset($handler['name']);
        }

        if (!empty($this->prefixGroupStack)) {
            $prefix = implode('/', $this->prefixGroupStack);
            $pattern = "$prefix/" . ltrim($pattern, '/');
        }
        $tokens = $this->parsePattern($pattern);
        $this->lastAddedTokens = $tokens;

        if ($method === null || $method === '*') {
            $method = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        }

        if (\is_string($method)) {
            $this->storeRoute(strtoupper($method), $tokens, $handler, $this->middlewareGroupStack);
        } else {
            foreach ($method as $m) {
                $this->storeRoute(strtoupper($m), $tokens, $handler, $this->middlewareGroupStack);
            }
        }

        if ($routeName !== null && $routeName !== '') {
            $this->namedRoutes[$routeName] = ['tokens' => $tokens];
        }

        return $this;
    }

    /**
     * Assign a name to the most recently added route. This allows you to generate URLs for this route using the `urlFor()` method.
     *
     * @param string $name The name to assign to the route
     * @return static For method chaining
     * @throws LogicException If no route has been added yet or if the last added route is invalid
     */
    public function setName(string $name): static
    {
        if ($name === '') {
            throw new InvalidArgumentException('Route name cannot be empty');
        }
        if ($this->lastAddedTokens === null) {
            throw new LogicException('Cannot set route name before adding a route');
        }

        $this->namedRoutes[$name] = ['tokens' => $this->lastAddedTokens];
        return $this;
    }

    /**
     * Check if a named route exists.
     *
     * @param string $name The name of the route to check
     * @return bool True if a route with the given name exists, false otherwise
     */
    public function hasNamedRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Generate a URL for a named route, substituting parameters as needed.
     *
     * @param string $name The name of the route to generate a URL for
     * @param array $params Associative array of parameter values to substitute into the route pattern
     * @param array $query Optional associative array of query parameters to append to the URL
     * @return string The generated URL path (e.g., "/blog/hello-world?ref=homepage")
     * @throws RuntimeException If no route with the given name exists or if required parameters are missing/invalid
     */
    public function urlFor(string $name, array $params = [], array $query = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RuntimeException("Unknown route name: $name");
        }

        $path = $this->buildPathFromTokens($this->namedRoutes[$name]['tokens'], $params);

        if ($query) {
            $path .= '?' . http_build_query($query);
        }

        return $path;
    }

    /**
     * Define a group of routes that share a common URI prefix. This allows you to organize related routes together and avoid repeating the same prefix for each route. The callback function receives the router instance as an argument, allowing you to define routes within the group using the same `add()` method. The prefix is automatically prepended to all routes defined within the group. You can also nest groups within groups for more complex route hierarchies.
     *
     * @param string $prefix URI prefix for the group (e.g., "/admin")
     * @param callable $callback Function that receives the router instance to define routes within the group
     *
     * @example
     * $router->prefix('/admin', function($r) {
     *     $r->add('GET', '/dashboard', 'Admin::dashboard');
     *     $r->add('GET', '/users', 'Admin::users');
     * });
     */
    public function prefix(string $prefix, callable $callback): void
    {
        $this->prefixGroupStack[] = trim($prefix, '/');
        $callback($this);
        array_pop($this->prefixGroupStack);
    }

    /**
     * Add group of middleware to be applied to all routes defined within the group. This allows you to easily apply common middleware (e.g., authentication, logging) to related routes without having to specify the middleware for each controller individually. The callback function receives the router instance as an argument, allowing you to define routes within the group using the same `add()` method. Middleware groups can be nested within other groups, and middleware from outer groups will be applied to inner groups as well.
     *
     * @param string|array $name Middleware group name (e.g., "auth")
     * @param callable $callback Function that receives the router instance to define routes within the group
     *
     * @example
     * $router->middleware('auth', function($r) {
     *     $r->add('GET', '/admin/dashboard', 'Admin::dashboard');
     *     $r->add('GET', '/admin/users', 'Admin::users');
     * });
     */
    public function middleware(string|array $name, callable $callback): void
    {
        if (\is_string($name)) {
            $count = 1;
            $this->middlewareGroupStack[] = $name;
        } else {
            $count = \count($name);
            foreach ($name as $n) {
                $this->middlewareGroupStack[] = $n;
            }
        }
        $callback($this);
        array_splice($this->middlewareGroupStack, -$count);
    }

    protected function storeRoute(string $method, array $tokens, string|array|null $handler, array $groups): void
    {
        if ($this->isStaticTokens($tokens)) {
            $path = '/' . implode('/', array_column($tokens, 1));
            $this->static[$method][$path] = [
                'handler' => $handler,
                'tokens' => $tokens,
                'groups' => $groups,
            ];
            return;
        }

        $first = $tokens[0][0] === 'static'
            ? $tokens[0][1]
            : '__DYNAMIC__';

        $specificity = $this->calculateSpecificity($tokens);

        $this->groups[$method][$first][] = [
            'tokens' => $tokens,
            'handler' => $handler,
            'specificity' => $specificity,
            'groups' => $groups,
        ];

        // Sort by specificity (highest first) for automatic priority
        usort(
            $this->groups[$method][$first],
            fn($a, $b) => $b['specificity'] <=> $a['specificity']
        );
    }

    /**
     * Calculate route specificity for automatic priority.
     * Higher score = more specific = checked first.
     * 
     * Scoring:
     * - static segment = 3 points
     * - typed param (not '*' or wildcard) = 2 points
     * - wildcard/dynamic/'*' = 1 point
     */
    protected function calculateSpecificity(array $tokens): int
    {
        $score = 0;
        foreach ($tokens as $token) {
            $kind = $token[0];
            $type = $token[2] ?? null;

            if ($kind === 'static') {
                $score += 3;
            } elseif ($kind === 'param' && $type !== '*') {
                $score += 2;
            } elseif ($kind === 'regex') {
                $score += 2;
            } else {
                // wildcard, dynamic, or param with type '*'
                $score += 1;
            }
        }
        return $score;
    }

    protected function isStaticTokens(array $tokens): bool
    {
        foreach ($tokens as $t) {
            if ($t[0] !== 'static')
                return false;
        }
        return true;
    }

    protected function parsePattern(string $pattern): array
    {
        $segments = explode('/', trim($pattern, '/'));
        $result = [];

        foreach ($segments as $t) {
            if ($t === '') {
                $result[] = ['static', ''];
                continue;
            }

            // normal static
            if ($t[0] !== '{') {
                $result[] = ['static', $t];
                continue;
            }

            $inner = trim($t, '{}');

            $name = strstr($inner, ':', true);
            $hasTypeSeparator = $name !== false;
            if (!$hasTypeSeparator) {
                $name = $inner;
                $type = '*';
            } else {
                $type = \substr($inner, \strlen($name) + 1);
            }

            $optional = false;
            if (str_ends_with($name, '?')) {
                $optional = true;
                $name = substr($name, 0, -1);
            }

            if ($name === '') {
                throw new RuntimeException("Unnamed route parameters are not supported: {$t}");
            }

            if ($hasTypeSeparator && $type === '*') {
                $result[] = ['wildcard', $name];
                continue;
            }

            if (str_starts_with($type, 'regex(') && str_ends_with($type, ')')) {
                $regex = substr($type, 6, -1);
                $result[] = [$optional ? 'optional-regex' : 'regex', $name, $regex];
            } else {
                $result[] = [$optional ? 'optional-param' : 'param', $name, $type];
            }
        }

        return $result;
    }

    protected function buildPathFromTokens(array $tokens, array $params): string
    {
        $segments = [];

        foreach ($tokens as $token) {
            [$kind, $name, $type] = $token + [null, null, null];

            if ($kind === 'static') {
                if ($name !== '') {
                    $segments[] = $name;
                }
                continue;
            }

            if ($kind === 'optional-param' || $kind === 'optional-regex') {
                if (!array_key_exists($name, $params)) {
                    continue;
                }

                $value = (string) $params[$name];
                if ($kind === 'optional-param') {
                    if (!isset($this->types[$type])) {
                        throw new RuntimeException("Unknown validator: $type");
                    }
                    if (!$this->types[$type]($value)) {
                        throw new RuntimeException("Route parameter '$name' does not match type '$type'");
                    }
                } else {
                    if (!mb_ereg('^' . $type . '$', $value)) {
                        throw new RuntimeException("Route parameter '$name' does not match regex '$type'");
                    }
                }

                $segments[] = rawurlencode($value);
                continue;
            }

            if ($kind === 'param' || $kind === 'regex') {
                if (!array_key_exists($name, $params)) {
                    throw new RuntimeException("Missing route parameter: $name");
                }

                $value = (string) $params[$name];
                if ($kind === 'param') {
                    if (!isset($this->types[$type])) {
                        throw new RuntimeException("Unknown validator: $type");
                    }
                    if (!$this->types[$type]($value)) {
                        throw new RuntimeException("Route parameter '$name' does not match type '$type'");
                    }
                } else {
                    if (!mb_ereg('^' . $type . '$', $value)) {
                        throw new RuntimeException("Route parameter '$name' does not match regex '$type'");
                    }
                }

                $segments[] = rawurlencode($value);
                continue;
            }

            if ($kind === 'wildcard') {
                $wildcardValues = [];
                if (array_key_exists($name, $params)) {
                    $wildcardValues = $params[$name];
                } else {
                    foreach ($params as $key => $value) {
                        if (\is_int($key)) {
                            $wildcardValues[] = $value;
                        }
                    }
                }

                if (!\is_array($wildcardValues)) {
                    $wildcardValues = [$wildcardValues];
                }

                foreach ($wildcardValues as $wildcardValue) {
                    $segments[] = rawurlencode((string) $wildcardValue);
                }
            }
        }

        if (empty($segments)) {
            return '/';
        }

        return '/' . implode('/', $segments);
    }

    /**
     * Attempt to match the given URI and HTTP method against the registered routes.
     *
     * @param string $uri The request URI (path) to match, e.g. "/blog/hello-world"
     * @param string $method The HTTP method, e.g. "GET", "POST"
     * @return array|null If a match is found, returns an array with keys 'vars', 'override', 'groups', 'wildcards'. Otherwise, returns null.
     */
    public function match(string $uri, string $method = 'GET'): ?array
    {
        $method = strtoupper($method);
        $uri = '/' . trim($uri, '/');

        // static
        if (isset($this->static[$method][$uri])) {
            $route = $this->static[$method][$uri];
            return $this->resolveHandler(
                $route['handler'],
                [],
                $route['groups']
            );
        }

        $parts = explode('/', trim($uri, '/'));
        $first = $parts[0] ?? '';

        $candidates = [];

        if (isset($this->groups[$method][$first])) {
            $candidates = array_merge($candidates, $this->groups[$method][$first]);
        }
        if (isset($this->groups[$method]['__DYNAMIC__'])) {
            $candidates = array_merge($candidates, $this->groups[$method]['__DYNAMIC__']);
        }

        foreach ($candidates as $route) {
            $tokens = $route['tokens'];

            $hasWildcard = false;
            $minSegments = 0;
            $maxSegments = 0;
            foreach ($tokens as $token) {
                $kind = $token[0];
                if ($kind === 'wildcard') {
                    $hasWildcard = true;
                    continue;
                }

                if ($kind === 'optional-param' || $kind === 'optional-regex') {
                    $maxSegments++;
                    continue;
                }

                $minSegments++;
                $maxSegments++;
            }

            $partCount = \count($parts);
            if ($partCount < $minSegments) {
                continue;
            }
            if (!$hasWildcard && $partCount > $maxSegments) {
                continue;
            }

            $params = [];
            $ok = true;
            $partIndex = 0;

            foreach ($tokens as $token) {
                [$kind, $name, $type] = $token + [null, null, null];

                if ($kind === 'wildcard') {
                    $params[$name] = array_slice($parts, $partIndex);
                    $partIndex = $partCount;
                    break;
                }

                if ($kind === 'optional-param' || $kind === 'optional-regex') {
                    if (!isset($parts[$partIndex])) {
                        continue;
                    }

                    $segment = $parts[$partIndex];

                    if ($kind === 'optional-param') {
                        if (!isset($this->types[$type])) {
                            throw new RuntimeException("Unknown validator: $type");
                        }
                        if ($this->types[$type]($segment)) {
                            $params[$name] = $segment;
                            $partIndex++;
                        }
                    } else {
                        if (mb_ereg('^' . $type . '$', $segment)) {
                            $params[$name] = $segment;
                            $partIndex++;
                        }
                    }
                    continue;
                }

                if (!isset($parts[$partIndex])) {
                    $ok = false;
                    break;
                }

                $segment = $parts[$partIndex];

                switch ($kind) {
                    case 'static':
                        if ($name !== $segment)
                            $ok = false;
                        else
                            $partIndex++;
                        break;

                    case 'param':
                        if (!isset($this->types[$type])) {
                            throw new RuntimeException("Unknown validator: $type");
                        }
                        if (!$this->types[$type]($segment)) {
                            $ok = false;
                        } else {
                            $params[$name] = $segment;
                            $partIndex++;
                        }
                        break;

                    case 'regex':
                        if (!mb_ereg('^' . $type . '$', $segment)) {
                            $ok = false;
                        } else {
                            $params[$name] = $segment;
                            $partIndex++;
                        }
                        break;

                }

                if (!$ok)
                    break;
            }

            if ($ok && $partIndex !== $partCount) {
                $ok = false;
            }

            if ($ok) {
                return $this->resolveHandler(
                    $route['handler'],
                    $params,
                    $route['groups'],
                );
            }
        }

        return null;
    }

    protected function resolveHandler(
        string|array|null $handler,
        array $params,
        array $groups,
    ): array {

        if ($this->parseParams) {
            $params = $this->parseRouteValues($params);
        }

        if ($handler === null) {
            $override = [];
        } elseif (\is_array($handler)) {
            $override = $handler;
        } else {
            $handler = trim($handler);
            if ($handler === '') {
                return [];
            }

            $override = [];

            $controllerPart = strstr($handler, '::', true);
            if ($controllerPart === false) {
                $override['controller'] = $handler;
            } else {
                $override['controller'] = $controllerPart;
                $override['action'] = substr((string) strstr($handler, '::'), 2);
            }
        }

        return [
            'vars' => $params,
            'override' => $override,
            'groups' => $groups,
        ];
    }


    protected function parseRouteValues(array $values): array
    {
        static $conversionMap = [
        'true' => true,
        'on' => true,
        'yes' => true,
        'false' => false,
        'off' => false,
        'no' => false,
        'null' => null,
        ];

        foreach ($values as $key => $value) {
            if (\is_array($value)) {
                $values[$key] = $this->parseRouteValues($value);
                continue;
            }

            $value = \trim($value);

            if (\is_numeric($value)) {
                $values[$key] = $value + 0; // Convert to int or float as appropriate
                continue;
            }

            $value = \strtolower($value);
            if (isset($conversionMap[$value])) {
                $values[$key] = $conversionMap[$value];
                continue;
            }

        }

        return $values;
    }

}
