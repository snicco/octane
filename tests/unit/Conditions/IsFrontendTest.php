<?php

namespace Ocante\Tests\unit\Conditions;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Snicco\Octane\Conditions\IsFrontend;
use Snicco\Octane\Psr7ServerRequestAdapter;

class IsFrontendTest extends TestCase
{
	
	/** @test */
	public function testPassesForFrontend()
	{
		
		$request = $this->serverRequest('/', ['SCRIPT_NAME' => '/index.php']);
		
		$this->assertTrue((new IsFrontend())->passes($request));
		
	}
	
	private function serverRequest(string $path, array $server = []) :Psr7ServerRequestAdapter
	{
		return new Psr7ServerRequestAdapter(
			new ServerRequest('GET', $path, [], '', '1.1', $server)
		);
	}
	
	/** @test */
	public function testFailsForAdmin()
	{
		
		$request = $this->serverRequest('/', ['SCRIPT_NAME' => 'wp-admin/index.php']);
		
		$this->assertFalse((new IsFrontend())->passes($request));
		
	}
	
	/** @test */
	public function testFailsForWPJsonApi()
	{
		
		$request = $this->serverRequest('/wp-json/wp/v2/posts/', ['SCRIPT_NAME' => '/index.php']);
		
		$this->assertFalse((new IsFrontend())->passes($request));
		
	}
	
}