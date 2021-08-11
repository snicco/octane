<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class IsExactPath implements Condition
{
	
	/** @var string */
	private $exact_path;
	
	public function __construct(string $exact_path)
	{
		
		$this->exact_path = $exact_path;
	}
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		return trim($this->exact_path, '/') === trim($server_request->path(), '/');
	}
	
}