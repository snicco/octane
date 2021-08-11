<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class CallableCondition implements Condition
{
	
	/**
	 * @var callable
	 */
	private $condition;
	
	/**
	 * @param callable $condition
	 */
	public function __construct($condition)
	{
		$this->condition = $condition;
	}
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		return call_user_func($this->condition, $server_request);
	}
	
}