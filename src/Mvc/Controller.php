<?php
namespace Merlin\Mvc;

use Merlin\AppContext;
use Merlin\Http\Session;
use Merlin\Http\Response;
use Merlin\Http\Request;

/**
 * MVC Controller class
 */
abstract class Controller
{
	protected Request $request;
	protected AppContext $context;

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

	public function __construct(?AppContext $context = null)
	{
		$this->context = $context ?? AppContext::instance();
		$this->request = $this->context->getRequest();
		$this->onInit();
	}

	protected function onInit(): void
	{
	}

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

	public function getContext(): AppContext
	{
		return $this->context;
	}

	protected function view(): ViewEngine
	{
		return $this->context->getView();
	}

	protected function session(): ?Session
	{
		return $this->context->getSession();
	}

}
