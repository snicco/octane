<?php

namespace Ocante\Tests\unit;

use PHPUnit\Framework\TestCase;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Snicco\Octane\PhpSuperGlobalsRequestAdapter;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

class PhpSuperGlobalsRequestAdapterTest extends TestCase
{
	
	use AssetRemovalTestHelpers;
	
	/**
	 * @test
	 */
	public function testPathIsTheSame()
	{
		
		$server_request =
			$this->serverRequest('https://foo.com/bar/baz?biz=foo', ['SCRIPT_NAME' => '/index.php']
			);
		$psr = new Psr7ServerRequestAdapter($server_request);
		
		$_SERVER['REQUEST_URI'] = '/bar/baz?biz=foo';
		
		$globals = new PhpSuperGlobalsRequestAdapter();
		
		$this->assertSame('/bar/baz', $psr->path());
		$this->assertSame('/bar/baz', $globals->path());
		
	}
	
	/**
	 * @test
	 */
	public function testRequestTargetIsTheSame()
	{
		
		$server_request =
			$this->serverRequest('https://foo.com/bar/baz?biz=foo', ['SCRIPT_NAME' => '/index.php']
			);
		$psr = new Psr7ServerRequestAdapter($server_request);
		
		$_SERVER['REQUEST_URI'] = '/bar/baz?biz=foo';
		
		$globals = new PhpSuperGlobalsRequestAdapter();
		
		$this->assertSame('/bar/baz?biz=foo', $psr->requestTarget());
		$this->assertSame('/bar/baz?biz=foo', $globals->requestTarget());
		
	}
	
	/** @test */
	public function testServerIsTheSame()
	{
		
		$pre = $_SERVER['SCRIPT_NAME'];
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		
		$server_request = $this->serverRequest('https://foo.com/bar/baz?biz=foo');
		$psr = new Psr7ServerRequestAdapter($server_request);
		
		$globals = new PhpSuperGlobalsRequestAdapter();
		
		$this->assertSame($_SERVER, $psr->server());
		$this->assertSame($_SERVER, $globals->server());
		
		$_SERVER['SCRIPT_NAME'] = $pre;
		
	}
	
}