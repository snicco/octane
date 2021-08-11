<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class Any implements Condition
{
	
	/**
	 * @var Condition[] $conditions
	 */
	private $conditions;
	
	/**
	 * @param Condition[] $conditions
	 */
	public function __construct(array $conditions)
	{
		
		$this->conditions = $conditions;
	}
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		foreach( $this->conditions as $condition ) {
			
			if( $condition->passes($server_request) ) {
				return true;
			}
			
		}
		
		return false;
	}
	
}