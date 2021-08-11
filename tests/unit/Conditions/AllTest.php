<?php

namespace Ocante\Tests\unit\Conditions;

use PHPUnit\Framework\TestCase;
use Snicco\Octane\Conditions\All;
use Snicco\Octane\ServerRequestAdapter;
use Snicco\Octane\Conditions\CallableCondition;

class AllTest extends TestCase
{
	
	/** @test */
	public function testCanPass()
	{
		
		$condition1 = new CallableCondition(function() {
			return true;
		});
		
		$condition2 = new CallableCondition(function() {
			return true;
		});
		
		$and_condition = new All([$condition1, $condition2]);
		$this->assertTrue($and_condition->passes($this->request()));
		
	}
	
	private function request() :ServerRequestAdapter
	{
		return \Mockery::mock(ServerRequestAdapter::class);
	}
	
	/** @test */
	public function testFailsIfOneConditionFails()
	{
		
		$condition1 = new CallableCondition(function() {
			return true;
		});
		
		$condition2 = new CallableCondition(function() {
			return false;
		});
		
		$and_condition = new All([$condition1, $condition2]);
		$this->assertFalse($and_condition->passes($this->request()));
		
	}
	
}

