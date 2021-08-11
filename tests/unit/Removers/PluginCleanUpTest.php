<?php

namespace Ocante\Tests\unit\Removers;

use Nyholm\Psr7\ServerRequest;
use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Removers\PluginCleanUp;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Psr\Http\Message\ServerRequestInterface;

class PluginCleanUpTest extends WPTestCase
{
	
	/** @test */
	public function testCanBeConstructedWithoutPsr7Dependency()
	{
		
		$remover = new PluginCleanUp();
		
		$this->assertInstanceOf(PluginCleanUp::class, $remover);
		
	}
	
	/** @test */
	public function testAllIdentifiers()
	{
		
		$remover = $this->newRemover();
		$this->assertSame([
			                  'akismet/akismet.php',
			                  'hello-dolly/hello.php',
		                  ], $remover->allIdentifiers());
		
	}
	
	private function newRemover(ServerRequestInterface $server_request = null)
	{
		return new PluginCleanUp(
			new Psr7ServerRequestAdapter($server_request ?? new ServerRequest('GET', '/foo'))
		);
	}
	
	/** @test */
	public function testDoRemove()
	{
		
		$remover = $this->newRemover();
		$remover->remove('akismet/akismet.php');
		
		// The remover runs when object is destructed.
		unset($remover);
		
		$active_plugins = get_option('active_plugins');
		
		$this->assertContains('hello-dolly/hello.php', $active_plugins);
		$this->assertNotContains('akismet/akismet.php', $active_plugins);
		
	}
	
	/** @test */
	public function testNothingBreaksIfPluginsWasAlreadyRemoved()
	{
		
		$remover = $this->newRemover();
		$remover->remove('akismet/akismet.php');
		
		add_filter('option_active_plugins', function($plugins) {
			
			// remove akismet
			unset($plugins[0]);
			return $plugins;
			
		},         10, 1);
		
		unset($remover);
		
		$active_plugins = get_option('active_plugins');
		
		$this->assertContains('hello-dolly/hello.php', $active_plugins);
		$this->assertNotContains('akismet/akismet.php', $active_plugins);
		
	}
	
	/** @test */
	public function a_php_notice_gets_triggered_if_trying_to_remove_a_plugins_that_doesnt_exist()
	{
		
		$remover = $this->newRemover();
		$remover->remove('foobar/foobar.php');
		
		$error_function = function($errno, $errstr, $errfile, $errline, $errcontext) {
			
			$this->assertStringContainsString(
				'Tried to remove plugin [foobar/foobar.php]',
				$errstr
			);
			
			$this->assertSame($errno, E_USER_NOTICE);
			
		};
		
		set_error_handler($error_function, E_USER_NOTICE);
		
		unset($remover);
		
	}
	
	protected function setUp() :void
	{
		parent::setUp();
		$this->seedActivePlugins();
		// This filter is set by wp browser.
		remove_filter("pre_option_active_plugins", 'wp_tests_options', 10);
	}
	
	private function seedActivePlugins()
	{
		update_option('active_plugins', [
			
			'akismet/akismet.php',
			'hello-dolly/hello.php',
		
		]);
	}
	
	protected function tearDown() :void
	{
		
		unset($GLOBALS['wp_styles']);
		unset($GLOBALS['wp_scripts']);
		
		update_option('active_plugins', false);
		parent::tearDown();
	}
	
}