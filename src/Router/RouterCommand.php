<?php
/*
*
* @ Package: Router - simple router class for php
* @ Class: RouterCommand
* @ Author: izni burak demirtas / @izniburak <info@burakdemirtas.org>
* @ Web: http://burakdemirtas.org
* @ URL: https://github.com/izniburak/php-router
* @ Licence: The MIT License (MIT) - Copyright (c) - http://opensource.org/licenses/MIT
*
*/
namespace Buki\Router;

use Buki\Router\RouterException;

class RouterCommand
{
  /**
	* Run Route Middlewares
	*
	* @return true | false
	*/
	public static function beforeAfter($command, $middleware, $path = '', $namespace = '')
	{
		if(!is_null($command))
		{
			if(is_array($command))
				foreach ($command as $key => $value)
					self::beforeAfter($value, $middleware);

			elseif(is_object($command))
				return call_user_func($command);

			elseif(strstr($command, '@'))
			{
				$parts = explode('/', $command);
				$segments = explode('@', end($parts));

				$middlewareFile = realpath($path . (str_replace($namespace, '', $segments[0])) . '.php');

				if(count($parts) > 1)
					$middlewareFile = realpath($path . $parts[0] . '/' . (str_replace($namespace, '', $segments[0])) .'.php');

				if(!file_exists($middlewareFile))
          return new RouterException($segments[0] . ' middleware file is not found. Please, check file.');

				require_once($middlewareFile);
				$controller = new $segments[0]();

				if(in_array($segments[1], get_class_methods($controller)))
					return $controller->$segments[1]();
				else
          return new RouterException($segments[1] . ' method is not found in <b>'.$segments[0].'</b> middleware. Please, check file.');
			}
			else
			{
				if(!is_null($middleware[$command]) && isset($middleware[$command]))
					self::beforeAfter($middleware[$command], $middleware);
				else
					return false;
			}
		}
		else
				return false;
	}

  /**
	* Run Route Command; Controller or Closure
	*
	* @return null
	*/
	public static function runRoute($command, $params = null, $path = '', $namespace = '')
	{
		if(!is_object($command))
		{
			$parts = explode('/', $command);
			$segments = explode('@', end($parts));

			$controllerFile = realpath($path . (str_replace($namespace, '', $segments[0])) . '.php');

			if(count($parts) > 1)
				$controllerFile = realpath($path . $parts[0] . '/' . ($segments[0]).'.php');

			if(!file_exists($controllerFile))
				return new RouterException($segments[0] . ' Controller File is not found. Please, check file.');

			require_once($controllerFile);
			$controller = new $segments[0]();

			if(!is_null($params) && in_array($segments[1], get_class_methods($controller)))
				echo call_user_func_array([$controller, $segments[1]], $params);
			elseif(is_null($params) && in_array($segments[1], get_class_methods($controller)))
				echo call_user_func([$controller, $segments[1]]);
			else
        return new Exception($segments[1] . ' method is not found in '.$segments[0].' controller. Please, check file.');
		}
		else
			if(!is_null($params))
				echo call_user_func_array($command, $params);
			else
				echo call_user_func($command);
	}
}
