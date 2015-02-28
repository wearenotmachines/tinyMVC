<?php namespace WeAreNotMachines\Utilities;

use \RuntimeException;
class Router {

	public $routes = [];

	public function hello() {
		echo "hello";
	}

	public function add($url, $action) {
		$this->routes[$url] = $action;
	}

	public function run($route = null) {
		if (empty($route)) {
			$route = URLParser::parseCurrentURLSegments();
		}

		$routeCalled = null;
		$routeString = is_array($route) ? "/".implode("/", $route) : $route;
		if (!is_array($route)) $route = explode("/", $route);
		//match specific routes first
		if (array_key_exists($routeString, $this->routes)) {
			$routeCalled = $this->routes[$routeString];
		}
		//finally search for routes by regex
		foreach ($this->routes AS $r=>$action) {	
			//check for wildcards
			$matches = array();
			if (preg_match("/({:)([A-z0-9]+)(})/", $r, $matches)) {
				//replace wildcard matches with char classes
				$subbed = preg_replace("/({:)([A-z0-9]+)(})/", '(?P<$2>[A-z0-9]+)', $r);
				if (preg_match("%^".$subbed."$%", $routeString, $matches)) {
					//now swap the wildcards out for params
					foreach (range(0, floor(count($matches) / 2)) as $index) {
					    unset($matches[$index]);
					}
					$route = count($matches)>1 ? $matches : current($matches);
					$routeCalled =  $this->routes[$r];
					break;
				}
			}
			if (preg_match("%^".$r."$%", $routeString)) {
				$routeCalled =  $this->routes[$r];
				break;
			}			
		}
		if (empty($routeCalled)) throw new RuntimeException("Route ".$routeString." is not defined");

		if (is_array($route) && count($route)==1) $route = array();
		if (is_callable($routeCalled)) {;
			call_user_func_array($routeCalled, array($route));
			exit();
		}
		//is this a class route
		if (preg_match("/@/", $routeCalled)) {
			$call = explode("@", $routeCalled);
			if (is_array($route)) {
				call_user_func_array(array(new $call[0], $call[1]), $route);
			} else {
				(new $call[0])->{$call[1]}($route);
			}	
		}
	}

}