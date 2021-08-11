<?php

namespace Ocante\Tests\unit\Conditions;

use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Conditions\InGutenberg;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

class InGutenbergTest extends WPTestCase
{
	
	use AssetRemovalTestHelpers;
	
	/** @test */
	public function testCanPass()
	{
		
		set_current_screen('foo');
		$current_screen = get_current_screen();
		$current_screen->is_block_editor(true);
		
		$this->assertTrue(
			(new InGutenberg())->passes(new Psr7ServerRequestAdapter($this->serverRequest()))
		);
		
	}
	
	/** @test */
	public function testCanFail()
	{
		
		set_current_screen('foo');
		$current_screen = get_current_screen();
		$current_screen->is_block_editor(false);
		
		$this->assertFalse(
			(new InGutenberg())->passes(new Psr7ServerRequestAdapter($this->serverRequest()))
		);
		
	}
	
	/** @test */
	public function testTriggerNoticeIfCalledToEarly()
	{
		
		set_error_handler(function($err, $message) {
			
			$this->assertStringStartsWith("Tried to evaluate condition", $message);
			
		}, E_USER_NOTICE);
		
		$this->assertFalse(
			(new InGutenberg())->passes(new Psr7ServerRequestAdapter($this->serverRequest()))
		);
		
		$request = $this->serverRequest(
			'https://foo.com/wp-admin/post.php=35&action=edit',
			['SCRIPT_NAME' => '/wp-admin/post.php']
		);
		
		$this->assertTrue((new InGutenberg())->passes(new Psr7ServerRequestAdapter($request)));
		
	}
	
	protected function setUp() :void
	{
		parent::setUp();
		global $current_screen;
		$current_screen = null;
	}
	
	protected function tearDown() :void
	{
		parent::tearDown();
		
		unset($GLOBALS['wp_styles']);
		unset($GLOBALS['wp_scripts']);
		unset($GLOBALS['current_screen']);
		
	}
	
}