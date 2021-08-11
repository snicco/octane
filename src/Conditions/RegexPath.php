<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class RegexPath implements Condition
{
	
	/** @var string */
	private $pattern;
	
	public function __construct(string $pattern)
	{
		
		$this->pattern = "#".$pattern."#";
		
	}
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		
		$path = trim($server_request->path(), '/');
		
		$match = preg_match($this->pattern, $path);
		
		return $match === 1;
	}
	
}