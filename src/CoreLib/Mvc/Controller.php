<?php
namespace CoreLib\Mvc;

use CoreLib\Http\Request as HttpRequest;

/**
 * MVC Controller class
 */
class Controller
{
	/**
	 * @var HttpRequest
	 */
	protected $request;

	/**
	 * @var View
	 */
	protected $view;

	/**
	 * Controller constructor.
	 */
	public function __construct()
	{
		$this->request = new HttpRequest();
		$this->view = new View();
	}

	public function redirect($location)
	{
		header('Location: ' . $location);
		exit;
	}
}
