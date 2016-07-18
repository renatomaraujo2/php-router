<?php
/*
*
* @ Package: Router - simple router class for php
* @ Class: Router
* @ Author: izni burak demirtas / @izniburak <info@burakdemirtas.org>
* @ Web: http://burakdemirtas.org
* @ URL: https://github.com/izniburak/php-router
* @ Licence: The MIT License (MIT) - Copyright (c) - http://opensource.org/licenses/MIT
*
*/
namespace Buki;

class Router
{
	protected $baseFolder;
	protected $group = '';
	protected $routes = [];
	protected $filters = [];
	protected $controllersFolder = null;
	protected $filtersFolder = null;
	protected $errorCallback;
	protected $patterns = [
		'{a}' => '([^/]+)',
		'{i}' => '([0-9]+)',
		'{s}' => '([a-zA-Z]+)',
		'{u}' => '([a-zA-Z0-9_-]+)',
		'{*}' => '(.*)'
	];

	function __construct($params = [])
	{
		$this->baseFolder = realpath(__DIR__ . '/../../../../');

		if(is_null($params) || !is_array($params))
			return;

		$this->baseFolder			= (isset($params['base']) ? trim($params['base'], '/') : $this->baseFolder);
		$this->controllersFolder	= (isset($params['controllers']) ? $this->baseFolder . '/' . trim($params['controllers'], '/') : null);
		$this->filtersFolder		= (isset($params['filters']) ? $this->baseFolder . '/' . trim($params['filters']) : null);
	}

	public function __call($method, $params)
	{
		if(is_null($params))
			return;

		$route = $params[0];
		$callback = $params[1];
		$settings = null;

		if(count($params) > 2)
		{
			$settings = $params[1];
			$callback = $params[2];
		}

		if(strstr($route, '{'))
		{
			$x = $y = '';
			foreach(explode('/', $route) as $key => $value)
			{
				if($value != '')
				{
					if(!strpos($value, '?'))
						$x .= '/' . $value;
					else
					{
						if($y == '')
							$this->addRoute($x, $method, $callback, $settings);

						$y = $x . '/' . str_replace('?', '', $value);
						$this->addRoute($y, $method, $callback, $settings);
						$x = $y;
					}
				}
			}

			if($y == '')
				$this->addRoute($x, $method, $callback, $settings);
		}
		else
			$this->addRoute($route, $method, $callback, $settings);

		return;
	}

	public function add($methods, $route, $settings, $callback = null)
	{
		if(is_null($callback))
		{
			$callback = $settings;
			$settings = null;
		}

		if(strstr($methods, '|'))
			foreach (array_unique(explode('|', $methods)) as $method)
				if($method != '')
					$this->{strtolower($methods)}($route, $settings, $callback);
		else
			$this->{strtolower($methods)}($route, $settings, $callback);

		return;
	}

	public function pattern($pattern, $attr = null)
	{
		if(is_array($pattern))
		{
			foreach ($pattern as $key => $value)
				if(!in_array('{' . $key . '}', array_keys($this->patterns)))
					$this->patterns['{' . $key . '}'] = '(' . $value . ')';
				else
					$this->message('Opps! Error :(', '<b>' . $key . '</b> filter cannot be changed.');
		}
		else
		{
			if(!in_array('{' . $pattern . '}', array_keys($this->patterns)))
				$this->patterns['{' . $pattern . '}'] = '(' . $attr . ')';
			else
				$this->message('<h2>Opps! Error :(', '<b>' . $pattern . '</b> filter cannot be changed.');
		}

		return;
	}

	public function filter($name, $command)
	{
		$this->filters[$name] = $command;
	}

	private function beforeAfterCommand($command)
	{
		if(!is_null($command))
		{
			if(is_object($command))
				return call_user_func($command);

			elseif(strstr($command, '#'))
			{
				$parts = explode('/', $command);
				$segments = explode('#', end($parts));

				$filterFile = $this->filtersFolder . '/' . strtolower($segments[0]).'.php';

				if(count($parts) > 1)
					$filterFile = $this->filtersFolder . '/' . $parts[0] . '/' . strtolower($segments[0]).'.php';

				if(!file_exists($filterFile))
					$this->message('Oppps! Error :(', '<b>'. $segments[0] .'</b> filter file is not found. Please, check file.');

				require_once($filterFile);
				$filter = new $segments[0]();

				if(in_array($segments[1], get_class_methods($filter)))
					return $filter->$segments[1]();
				else
					$this->message('Oppps! Error :(', '<b>' . $segments[1] . '</b> method is not found in <b>' . $segments[0] . '</b> filter. Please, check file.');
			}
			else
			{
				if(!is_null($this->filters[$command]) && isset($this->filters[$command]))
				{
					$this->beforeAfterCommand($this->filters[$command]);
				}
				else
					return false;
			}
		}
		else
			return false;
	}

	public function run()
	{
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		if((substr($uri, -1) == '/'))
			$uri = substr($uri, 0, (strlen($uri)-1));

		$method = strtoupper(isset($_POST['_method']) ? $_POST['_method'] : $_SERVER['REQUEST_METHOD']);

		$searches = array_keys($this->patterns);
		$replaces = array_values($this->patterns);

		$foundRoute = false;

		$routes = [];
		foreach ($this->routes as $data)
			array_push($routes, $data['route']);

		// check if route is defined without regex
		if (in_array($uri, array_values($routes)))
		{
			foreach ($this->routes as $data)
			{
				if ($this->validMethod($data['method'], $method) && ($data['route'] == $uri))
				{
					$foundRoute = true;

					$this->beforeAfterCommand($data['before']);

					if(!is_object($data['callback']))
					{
						$parts = explode('/', $data['callback']);
						$segments = explode('#', end($parts));

						$controllerFile = 	$this->controllersFolder . '/' . strtolower($segments[0]).'.php';

						if(count($parts) > 1)
							$controllerFile = $this->controllersFolder . '/' . $parts[0] . '/' . strtolower($segments[0]).'.php';

						if(!file_exists($controllerFile))
							$this->message('Oppps! Error :(', '<b>' . $segments[0] . '</b> controller file is not found. Please, check file.');

						require_once($controllerFile);
						$controller = new $segments[0]();

						if(in_array($segments[1], get_class_methods($controller)))
							echo $controller->$segments[1]();
						else
							$this->message('Oppps! Error :(', '<b>' . $segments[1] . '</b> method is not found in <b>' . $segments[0] . '</b> controller. Please, check file.');
					}
					else
						echo call_user_func($data['callback']);

					$this->beforeAfterCommand($data['after']);

					break;
				}
			}
		}
		else
		{
			foreach ($this->routes as $data)
			{
				$route = $data['route'];

				if (strpos($route, '{') !== false)
					$route = str_replace($searches, $replaces, $route);

				if (preg_match('#^' . $route . '$#', $uri, $matched))
				{
					if ($this->validMethod($data['method'], $method))
					{
						$foundRoute = true;

						$this->beforeAfterCommand($data['before']);

						array_shift($matched);

						$newMatched = [];
						foreach ($matched as $key => $value)
						{
							if(strstr($value, '/'))
								foreach (explode('/', $value) as $k => $v)
									$newMatched[] = trim(urldecode($v));
							else
								$newMatched[] = trim(urldecode($value));
						}

						$matched = $newMatched;

						if(!is_object($data['callback']))
						{
							$parts = explode('/', $data['callback']);
							$segments = explode('#', end($parts));

							$controllerFile = 	$this->controllersFolder . '/' . strtolower($segments[0]).'.php';

							if(count($parts) > 1)
								$controllerFile = $this->controllersFolder . '/' . $parts[0] . '/' . strtolower($segments[0]).'.php';

							if(!file_exists($controllerFile))
								$this->message('Opps! Error :(', '<b>' . $segments[0] . '</b> Controller File is not found. Please, check file.');

							require_once($controllerFile);
							$controller = new $segments[0]();

							if(in_array($segments[1], get_class_methods($controller)))
								echo call_user_func_array([$controller, $segments[1]], $matched);
							else
								$this->message('Oppps! Error :(', '<b>' . $segments[1] . '</b> method is not found in <b>' . $segments[0] . '</b> controller. Please, check file.');
						}
						else
							echo call_user_func_array($data['callback'], $matched);

						$this->beforeAfterCommand($data['after']);

						break;
					}
				}
			}
		}

		if ($foundRoute == false)
		{
			if (!$this->errorCallback)
			{
				$this->errorCallback = function()
				{
					header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
					$this->message('Bad Request :(', 'Looks like something went wrong. Please try again.');
				};
			}

			call_user_func($this->errorCallback);
		}
	}

	public function group($name, $obj = null)
	{
		$this->group .= '/' . $name;

		if(is_object($obj))
			call_user_func($obj);

		$this->endGroup();
	}

	public function controller($route, $controller)
	{
		$controllerFile = $this->controllersFolder . '/' . strtolower($controller) . '.php';

		if(!class_exists($controller))
			$req = require_once($controllerFile);

		$class_methods = get_class_methods($controller);

		foreach ($class_methods as $method_name)
		{
			if(!strstr($method_name, '__'))
			{
				$r = new \ReflectionMethod($controller, $method_name);
				$param_num = $r->getNumberOfRequiredParameters();
				$param_num2 = $r->getNumberOfParameters();

				$value = ($method_name == 'main' ? $route : $route . '/' . $method_name);
				
				$this->any(($value . str_repeat('/{a}', $param_num) . str_repeat('/{a?}', $param_num2 - $param_num)), ($controller . '@' . $method_name));
			}
		}

		unset($r);
		$req = null;
	}

	public function error($callback)
	{
		$this->errorCallback = $callback;
	}

	public function getRoutes()
	{
		echo '<pre style="border:1px solid #eee;margin:0;padding:0 10px;width:960px;max-height:700;margin:20px auto; font-size:15px;overflow:auto;">';
		var_dump($this->routes);
		echo '</pre>';
		die();
	}

	private function addRoute($uri, $method, $callback, $settings)
	{
		$route = dirname($_SERVER['PHP_SELF']) . $this->group . '/' . trim($uri, '/');
		if( ((substr($route, -1) == '/')) || ($this->group != '' && $uri == '/') )
			$route = substr($route, 0, (strlen($route)-1));
		$route = str_replace(['///', '//'], '/', $route);

		$data = [
			'route'		=> $route,
			'method'	=> strtoupper($method),
			'callback'	=> $callback,
			'alias'		=> (isset($settings['alias']) ? $settings['alias'] : (isset($settings['as']) ? $settings['as'] : null)),
			'before'	=> (isset($settings['before']) ? $settings['before'] : null),
			'after'		=> (isset($settings['after']) ? $settings['after'] : null)
		];
		array_push($this->routes, $data);
	}

	private function endGroup()
	{
		if(substr_count($this->group, '/') > 1)
		{
			$explode = explode('/', $this->group);
			unset($explode[0]);
			unset($explode[count($explode)]);

			$this->group = '';

			foreach ($explode as $key => $value)
				$this->group .= '/' . $value;
		}
		else
			$this->group = '';
	}

	private function validMethod($data, $method)
	{
		$valid = false;

		if(strstr($data, '|'))
			foreach (explode('|', $data) as $value)
			{
				$valid = $this->checkMethods($value, $method);

				if($valid)
					break;
			}
		else
			$valid = $this->checkMethods($data, $method);

		return $valid;
	}

	private function checkMethods($value, $method)
	{
		$valid = false;
		$validMethods = 'GET|POST|PUT|DELETE|HEAD|OPTION|PATCH|ANY|AJAX';

		if($value == 'AJAX' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $value == $method)
			$valid = true;

		elseif (in_array($value, explode('|', $validMethods)) && ($value == $method || $value == 'ANY'))
			$valid = true;

		return $valid;
	}

	private function message($title, $content = null)
	{
		die('<h2>'.$title.'</h2> ' . (!is_null($content) ? $content : ''));
	}
}
