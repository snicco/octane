<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class Unless implements Condition
{
	
	/** @var Condition */
	private $condition;
	
	public function __construct(Condition $condition)
	{
		
		$this->condition = $condition;
	}
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		return ! $this->condition->passes($server_request);
	}
	
}