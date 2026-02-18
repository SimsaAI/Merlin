<?php

namespace Merlin\Mvc;

use Merlin\AppContext;
use Merlin\RoutingResult;
use Merlin\Http\Response;
use InvalidArgumentException;
use Merlin\Mvc\Exceptions\ActionNotFoundException;
use Merlin\Mvc\Exceptions\InvalidControllerException;
use Merlin\Mvc\Exceptions\ControllerNotFoundException;

class Dispatcher
{

    protected AppContext $context;

    public function __construct(?AppContext $context = null)
    {
        $this->context = $context ?? AppContext::instance();
        $this->baseNamespace = 'App\\Controllers';
    }

    protected array $globalMiddleware = [];

    public function addMiddleware(MiddlewareInterface $mw): void
    {
        $this->globalMiddleware[] = $mw;
    }

    protected array $middlewareGroups = [];

    public function defineMiddlewareGroup(string $name, array $middleware): void
    {
        $this->middlewareGroups[$name] = $middleware;
    }

    protected string $baseNamespace;
    protected string $defaultController = "IndexController";
    protected string $defaultAction = "indexAction";


    public function getBaseNamespace(): string
    {
        return $this->baseNamespace;
    }

    public function setBaseNamespace(string $baseNamespace): static
    {
        $this->baseNamespace = rtrim($baseNamespace, '\\');
        return $this;
    }

    /**
     * Get the default controller name used when a route doesn't provide one.
     *
     * @return string Default controller class name (without namespace)
     */
    public function getDefaultController(): string
    {
        return $this->defaultController;
    }

    /**
     * Set the default controller name.
     *
     * @param string $defaultController Controller class name to use as default
     * @throws InvalidArgumentException If given name is empty
     */
    public function setDefaultController(string $defaultController): static
    {
        if (empty($defaultController)) {
            throw new InvalidArgumentException("Default controller cannot be empty");
        }
        $this->defaultController = $defaultController;
        return $this;
    }

    /**
     * Get the default action name used when a route doesn't provide one.
     *
     * @return string Default action method name
     */
    public function getDefaultAction(): string
    {
        return $this->defaultAction;
    }

    /**
     * Set the default action name.
     *
     * @param string $defaultAction Action method name to use as default
     * @throws InvalidArgumentException If given name is empty
     */
    public function setDefaultAction(string $defaultAction): static
    {
        if (empty($defaultAction)) {
            throw new InvalidArgumentException("Default action cannot be empty");
        }
        $this->defaultAction = $defaultAction;
        return $this;
    }

    public function dispatch(array $routeInfo): Response
    {
        $groups = $routeInfo['groups'] ?? null;
        $vars = $routeInfo['vars'] ?? null;
        $override = $routeInfo['override'] ?? null;
        $params = $vars;
        unset(
            $params['controller'],
            $params['action'],
            $params['namespace'],
            $params['params'],
        );
        if (!empty($params)) {
            if (isset($vars['params']))
                $params += (array) ($vars['params'] ?? null);
            $params = array_values($params);
        } else {
            $params = (array) ($vars['params'] ?? null);
        }

        if (isset($override['namespace'])) {
            $namespace = rtrim((string) $override['namespace'], '\\');
        } else {
            $namespace = $this->baseNamespace;
            if (isset($vars['namespace'])) {
                if ($namespace !== '') {
                    $namespace .= '\\';
                }
                $namespace .= $this->camelize((string) $vars['namespace']);
            }
        }

        if (!empty($override['controller'])) {
            $controllerName = (string) $override['controller'];
        } elseif (!empty($vars['controller'])) {
            $controllerName = $this->camelize((string) $vars['controller']) . 'Controller';
        } else {
            $controllerName = $this->defaultController;
        }

        if (!empty($override['action'])) {
            $actionName = (string) $override['action'];
        } elseif (!empty($vars['action'])) {
            $actionName = $this->camelize((string) $vars['action'], false) . 'Action';
        } else {
            $actionName = $this->defaultAction;
        }

        $controllerClass = $namespace !== ''
            ? $namespace . '\\' . $controllerName
            : $controllerName;

        $this->context->route = new RoutingResult(
            $controllerClass,
            $actionName,
            $namespace,
            $vars,
            $params,
            $groups,
            $override
        );

        if (!class_exists($controllerClass)) {
            throw new ControllerNotFoundException("Controller {$controllerClass} not found");
        }

        $controller = $this->controllerFactory
            ? ($this->controllerFactory)($controllerClass, $this->context)
            : new $controllerClass($this->context);

        if (!$controller instanceof Controller) {
            throw new InvalidControllerException("{$controllerClass} is not a Controller");
        }

        if (!method_exists($controller, $actionName)) {
            throw new ActionNotFoundException("Action {$controllerClass}->{$actionName} not found");
        }

        $pipeline = $this->buildPipeline(
            $controller,
            $actionName,
            $params,
            $groups,
            $this->context
        );

        $response = $pipeline();

        return $response ?? Response::status(204);
    }

    protected function camelize(string $string, bool $beginUpper = true): string
    {
        $string = str_replace(['-', '_', '.'], ' ', $string);

        $result = '';
        foreach (explode(' ', $string) as $part) {
            if ($part === '') {
                continue;
            }

            if (!$beginUpper && $result === '') {
                $result .= strtolower($part);
                continue;
            }

            $result .= strtoupper($part[0]) . strtolower(substr($part, 1));
        }

        return $result;
    }

    /**
     * Build the middleware pipeline for the given controller action. The pipeline will include global middleware, controller-specific middleware, and action-specific middleware, in that order. Each middleware will be normalized to an instance of MiddlewareInterface, allowing for flexible definitions (class name, instance, closure, or array with arguments). The final callable returned by this method will execute the entire middleware stack and ultimately invoke the controller action.
     *
     * @param Controller $controller The controller instance to invoke
     * @param string $action The action method name to invoke on the controller
     * @param array $params Parameters to pass to the controller action
     * @param ?array $groups Middleware groups to apply to the pipeline
     * @param AppContext $context The application context to pass to middleware and controller
     * @return callable A callable that executes the middleware pipeline and invokes the controller action when called
     */
    protected function buildPipeline(Controller $controller, string $action, array $params, ?array $groups, AppContext $context): callable
    {
        $middleware = [];

        // 1) Global Middleware
        if (!empty($this->globalMiddleware)) {
            foreach ($this->globalMiddleware as $mw) {
                $middleware[] = $this->normalizeMiddleware($mw);
            }
        }

        // 2) Middleware from Route Groups
        if (!empty($groups)) {
            foreach ($groups as $groupName) {
                if (!empty($this->middlewareGroups[$groupName])) {
                    foreach ($this->middlewareGroups[$groupName] as $mw) {
                        $middleware[] = $this->normalizeMiddleware($mw);
                    }
                }
            }
        }

        // 2) Controller-based Middleware
        $controllerMiddleware = $controller->getMiddleware();
        if (!empty($controllerMiddleware)) {
            foreach ($controllerMiddleware as $mw) {
                $middleware[] = $this->normalizeMiddleware($mw);
            }
        }

        // 3) Action-based Middleware
        $actionMiddleware = $controller->getActionMiddleware($action);
        if (!empty($actionMiddleware)) {
            foreach ($actionMiddleware as $mw) {
                $middleware[] = $this->normalizeMiddleware($mw);
            }
        }

        // Core-Handler
        $core = fn() => $this->invokeController(
            $controller,
            $action,
            $params,
            $context
        );

        // If no middleware, return direct controller invocation
        if (empty($middleware)) {
            return $core;
        }

        // Run the pipeline
        $next = $core;

        foreach (array_reverse($middleware) as $mw) {
            $current = $mw;
            $next = fn() => $current->process($context, $next);
        }

        return $next;
    }

    private function normalizeMiddleware($mw): MiddlewareInterface
    {
        if ($mw instanceof MiddlewareInterface) {
            return $mw;
        }

        if ($mw instanceof \Closure) {
            return new class ($mw) implements MiddlewareInterface {
                public function __construct(private \Closure $fn)
                {}
                public function process($ctx, $next): ?Response
                {
                    return ($this->fn)($ctx, $next);
                }
            };
        }

        if (\is_array($mw)) {
            if (!isset($mw[0])) {
                throw new InvalidArgumentException("Middleware array must have a class name at index 0");
            }
            if (!isset($mw[1])) {
                $mw[1] = [];
            }
            return new $mw[0](...$mw[1]);
        }

        return new $mw();
    }

    protected function invokeController(Controller $controller, string $action, array $params, AppContext $context): Response
    {
        $before = $controller->beforeAction($action, $params);
        if ($before instanceof Response) {
            return $before;
        }

        $result = $controller->$action(...$params);

        $after = $controller->afterAction($action, $params);
        if ($after instanceof Response) {
            return $after;
        }

        if ($result instanceof Response) {
            return $result;
        }

        if (\is_array($result))
            return Response::json($result);
        elseif ($result instanceof \JsonSerializable)
            return Response::json($result->jsonSerialize());
        elseif (\is_string($result))
            return Response::text($result);
        elseif (\is_int($result))
            return Response::status($result);
        elseif ($result === null)
            return Response::status(204); // No Content
        else
            throw new \UnexpectedValueException("Unsupported controller action return type: " . \get_debug_type($result));
    }

    /** @var null|callable(string, AppContext): Controller */
    protected $controllerFactory = null;

    public function setControllerFactory(callable $factory): void
    {
        $this->controllerFactory = $factory;
    }

}
