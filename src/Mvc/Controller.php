<?php
namespace Merlin\Mvc;

use Merlin\AppContext;
use Merlin\Http\Cookies;
use Merlin\Http\Request;
use Merlin\Http\Session;
use Merlin\Http\Response;

/**
 * MVC Controller class
 */
abstract class Controller
{
	/**
	 * Controller-wide middleware
	 * Example:
	 * protected array $middleware = [
	 *     AuthMiddleware::class,
	 *     [RoleMiddleware::class, ['admin']],
	 * ];
	 */
	protected array $middleware = [];

	/**
	 * Action-specific middleware
	 * Example:
	 * protected array $actionMiddleware = [
	 *     'editAction' => [
	 *         AuthMiddleware::class,
	 *         [RoleMiddleware::class, ['admin']],
	 *     ],
	 * ];
	 */
	protected array $actionMiddleware = [];

	public function beforeAction(string $action = null, array $params = []): ?Response
	{
		return null;
	}

	public function afterAction(string $action = null, array $params = []): ?Response
	{
		return null;
	}

	// --- Middleware getters ---

	public function getMiddleware(): array
	{
		return $this->middleware;
	}

	public function getActionMiddleware(string $action): array
	{
		return $this->actionMiddleware[$action] ?? [];
	}

	// --- Helpers ---

	protected function context(): AppContext
	{
		return AppContext::instance();
	}
	protected function request(): Request
	{
		return $this->context()->request();
	}

	protected function view(): ViewEngine
	{
		return $this->context()->view();
	}

	protected function session(): ?Session
	{
		return $this->context()->session();
	}

	protected function cookies(): Cookies
	{
		return $this->context()->cookies();
	}
}
