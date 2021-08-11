<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class HasPathSegment implements Condition
{
	
	/** @var string */
	private $segment;
	
	public function __construct(string $segment)
	{
		$this->segment = trim($segment, '/');
	}
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		$segments = explode('/', $server_request->path());
		
		return in_array($this->segment, $segments, true);
	}
	
}