<?php

	class RoledRouter {
		protected $user = null;
		protected $roles = null;
		protected $select = [];
		protected $bind = [];

		protected $routes = [];

		private function __construct() {
			$this->user = Auth::user();
			if ($this->user)
				foreach ($this->user->roles()->get() as $role)
					$this->roles[] = $role->id;
		}

		protected function pickController() {
			if ($this->roles) {
				foreach ($this->select as $selector) 
					if (
							is_callable($selector[1])
							? $selector[1]($this->roles)
							: array_intersect($this->roles, $selector[1])
						) return $selector[0];
			}

			return false;
		}

		protected function pickAlias($route) {
			$route = preg_replace('"^[\\/]"', '', $route);
			return explode('/', $route)[0];
		}

		protected function bindRoutes($root) {			
			if (!($controller = $this->pickController()))
				return false;

			$alias = $this->pickAlias($root);

			foreach ($this->routes as $routea) {
				$method = $routea['method'];
				$route  = $routea['route'];
				$name   = $routea['action'] ?: $this->pickAlias($route);
				$action = $method . ucfirst($name);
				// echo "Route::{$method}(\"$root/$route\", ['uses' => \"$controller@$action\", 'as' => \"$alias.$method.$name\"])<br/>";
				Route::{$method}("$root/$route", ['uses' => "$controller@$action", 'as' => "$alias.$method.$name"]);
			}

			// die();

			foreach ($this->bind as $entity)
				Route::controller($entity, $controller);

			return true;
		}

		protected function routed($method, $route, $action) {
			$this->routes[] = ['method' => $method, 'route' => $route, 'action' => $action];
		}

		/**
		 *	User interface.
		 */

		public static function route($root, $closure) {
			$closure($router = new self);
			return $router->bindRoutes($root);
		}

		public function role($controller, $callback) {
			$this->select[] = [$controller, $callback];
		}

		public function bind($entity, Closure $closure = null) {
			$this->bind[] = $entity;

			if ($closure)
				Route::bind($entity, $closure);
		}

		public function get($route, $action = null) {
			$this->routed('get', $route, $action);
		}

		public function post($route, $action = null) {
			$this->routed('post', $route, $action);
		}

		public function any($route, $action = null) {
			$this->routed('any', $route, $action);
		}

		public function with($methods, $route, $action = null) {
			foreach ($methods as $method)
				$this->routed($method, $route, $action);
		}

	}