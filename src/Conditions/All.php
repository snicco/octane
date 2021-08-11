<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class All implements Condition
{
	
	/**
	 * @var Condition[]
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
			
			if( ! $condition->passes($server_request) ) {
				return false;
			}
			
		}
		
		return true;
		
	}
	
}