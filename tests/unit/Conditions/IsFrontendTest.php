<?php

namespace Ocante\Tests\unit\Conditions;

use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Conditions\IsFrontend;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

class IsFrontendTest extends WPTestCase
{
	
	use AssetRemovalTestHelpers;
	
	/** @test */
	public function testPassesForFrontend()
	{
		
		$request = new Psr7ServerRequestAdapter(
			$this->serverRequest('/', ['SCRIPT_NAME' => '/index.php'])
		);
		
		$this->assertTrue((new IsFrontend())->passes($request));
		
	}
	
	
	/** @test */
	public function testFailsForAdmin()
	{
		
		$request = new Psr7ServerRequestAdapter(
			$this->serverRequest('/', ['SCRIPT_NAME' => 'wp-admin/index.php'])
		);
		
		$this->assertFalse((new IsFrontend())->passes($request));
		
	}
	
	/** @test */
	public function testFailsForWPJsonApi()
	{
		
		$request = new Psr7ServerRequestAdapter(
			$this->serverRequest('/wp-json/wp/v2/posts/', ['SCRIPT_NAME' => '/index.php'])
		);
		
		$this->assertFalse((new IsFrontend())->passes($request));
		
	}
	
}