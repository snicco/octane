<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class IsAdminPage implements Condition
{
	
	/** @var string */
	private $admin_page_slug;
	
	public function __construct(string $admin_page_slug)
	{
		$this->admin_page_slug = $admin_page_slug;
	}
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		parse_str(
			parse_url($server_request->requestTarget(), PHP_URL_QUERY),
			$query
		);
		
		$page = $query['page'] ?? '';
		
		return $page === $this->admin_page_slug;
		
	}
	
}