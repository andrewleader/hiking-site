<?php
	
require_once('postEntity.php');
	
class Peak extends PostEntity {
	private $routes; // Array of Route
	private $plans;
	
	public function __construct($post) {
		parent::__construct($post);
	}

	// Returns array of posts, or null if none
	public function getRoutes() {
		
		if (!$this->routes) {
			$this->routes = array();
		
			foreach($this->getChildPosts('routes', 'peak') as $routePost) {
				$this->routes[] = new Route($routePost);
			}
		}
		
		return $this->routes;
	}

	public function getPlans() {
		if (!$this->plans) {
			$this->plans = array();
		}
	}
}
	
?>