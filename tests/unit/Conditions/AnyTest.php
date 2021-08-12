<?php

namespace Ocante\Tests\unit\Conditions;

use Snicco\Octane\Conditions\Any;
use Codeception\TestCase\WPTestCase;
use Snicco\Octane\ServerRequestAdapter;
use Snicco\Octane\Conditions\CallableCondition;

class AnyTest extends WPTestCase
{
	
	/** @test */
	public function testCanPassIfAllPass()
	{
		
		$condition1 = new CallableCondition(function() {
			return true;
		});
		
		$condition2 = new CallableCondition(function() {
			return true;
		});
		
		$and_condition = new Any([$condition1, $condition2]);
		$this->assertTrue($and_condition->passes($this->request()));
		
	}
	
	private function request() :ServerRequestAdapter
	{
		return \Mockery::mock(ServerRequestAdapter::class);
	}
	
	/** @test */
	public function testFailsPassesIfAtLeastOnePasses()
	{
		
		$condition1 = new CallableCondition(function() {
			return false;
		});
		$condition2 = new CallableCondition(function() {
			return false;
		});
		$condition3 = new CallableCondition(function() {
			return false;
		});
		$condition4 = new CallableCondition(function() {
			return true;
		});
		
		$and_condition = new Any([$condition1, $condition2, $condition3, $condition4]);
		$this->assertTrue($and_condition->passes($this->request()));
		
	}
	
	/** @test */
	public function testFailsIfAllFail()
	{
		
		$condition1 = new CallableCondition(function() {
			return false;
		});
		
		$condition2 = new CallableCondition(function() {
			return false;
		});
		
		$and_condition = new Any([$condition1, $condition2]);
		$this->assertFalse($and_condition->passes($this->request()));
		
	}
	
}