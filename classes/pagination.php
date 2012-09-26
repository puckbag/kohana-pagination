<?php defined('SYSPATH') or die('No direct script access.');

class Pagination {

	protected $result, $per_page, $page, $pages, $start, $total;

	public $route_page_param = 'page';
	public $show_max_pages   = 10;
	public $per_page_choices = array(10, 50, 100, 'all');

	public function __construct(ORM $result, $page=1, $route, array $route_params=array(), $per_page=10) {
		$this->result       = clone($result);
		$this->route        = $route;
		$this->route_params = $route_params;
		$this->per_page     = intval($per_page); // intval('all') == 0
		$this->page         = ($this->per_page) ? $page : 1 ;
		$this->start        = $this->per_page * ($page-1);
		$this->total        = $result->find_all()->count();
		$this->pages        = (!$this->per_page) ? 1 : ceil($this->total / $this->per_page);
	}

	public function count() {
		return $this->get_result_set()->find_all()->count();
	}

	public function result() {
		return $this->get_result_set()->find_all();
	}

	protected function get_result_set() {
		$result = clone($this->result);
		if ($this->per_page)
		{
			$result = $result->offset($this->start);
			$result = $result->limit($this->per_page);
		}
		return $result;
	}
	
	public function get_pager_constraints($show_max_pages=NULL, $page=NULL, $pages=NULL)
	{
		if (NULL === $show_max_pages) $show_max_pages = $this->show_max_pages;
		if (NULL === $page) $page = $this->page;
		if (NULL === $pages) $pages = $this->pages;
		
		$show_max_pages = round($show_max_pages);       // show_max_pages must be integer
		if ($show_max_pages < 5) $show_max_pages = 5;   // show_max_pages must be atleast 5
		$left = ceil($show_max_pages/2) - 1;            // ... 10:4, 9:4, 8:3, 7:3, 6:2, 5:2
		$right = floor($show_max_pages/2);              // ... 10:5, 9:4, 8:4, 7:3, 6:3, 5:2
		$start_page = $page - $left;
		$end_page = $page + $right;
		
		if ($page > $left and $page < $pages - $right)
		{
			$start_page += 2;
			$end_page -= 2;
		}
		if ($start_page < 2)
		{
			$start_page = 2;
			$end_page = $start_page + $show_max_pages - 4;
		}
		if ($end_page > $pages - 2)
		{
			$end_page = $pages - 1;
			$start_page = $end_page - $show_max_pages + 4;
		}
		if ($start_page < 2)
		{
			$start_page = 2;
		}
		
		return compact('show_max_pages','start_page','end_page');
	}

	public function render() {
		$constraints = $this->get_pager_constraints();
		$view = View::factory('pagination/render');
		$view->page               = $this->page;
		$view->pages              = $this->pages;
		$view->per_page           = $this->per_page;
		$view->per_page_choices   = $this->per_page_choices;
		$view->route              = $this->route;
		$view->route_params       = $this->route_params;
		$view->route_page_param   = $this->route_page_param;
		$view->show_max_pages     = $constraints['show_max_pages'];
		$view->start_page         = $constraints['start_page'];
		$view->end_page           = $constraints['end_page'];
		return $view->render();
	}

}