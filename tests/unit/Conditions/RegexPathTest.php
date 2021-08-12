<?php

namespace Ocante\Tests\unit\Conditions;

use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Conditions\RegexPath;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

class RegexPathTest extends WPTestCase
{
	
	use AssetRemovalTestHelpers;
	
	/** @test */
	public function testPasses()
	{
		
		$condition = new RegexPath('[abc]\d{2,3}');
		
		$request = new Psr7ServerRequestAdapter($this->serverRequest('/a11'));
		$this->assertTrue($condition->passes($request));
		
		$request = new Psr7ServerRequestAdapter($this->serverRequest('/c111'));
		$this->assertTrue($condition->passes($request));
		
		$request = new Psr7ServerRequestAdapter($this->serverRequest('/b22'));
		$this->assertTrue($condition->passes($request));
		
	}
	
	/** @test */
	public function testCanFail()
	{
		
		$condition = new RegexPath('[abc]\d{2,3}');
		
		$request = new Psr7ServerRequestAdapter($this->serverRequest('/1aa1'));
		$this->assertFalse($condition->passes($request));
		
		$request = new Psr7ServerRequestAdapter($this->serverRequest('/d22'));
		$this->assertFalse($condition->passes($request));
		
	}
	
}