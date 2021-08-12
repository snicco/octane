<?php

namespace Ocante\Tests\unit\Conditions;

use Snicco\Octane\Conditions\All;
use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Snicco\Octane\Conditions\CallableCondition;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

class AllTest extends WPTestCase
{
	
	use AssetRemovalTestHelpers;
	
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
		$this->assertTrue(
			$and_condition->passes(new Psr7ServerRequestAdapter($this->serverRequest()))
		);
		
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
		$this->assertFalse(
			$and_condition->passes(new Psr7ServerRequestAdapter($this->serverRequest()))
		);
		
	}
	
}

