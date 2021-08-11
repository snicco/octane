<?php

namespace Ocante\Tests\unit\Removers;

use Nyholm\Psr7\ServerRequest;
use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Removers\CSSCleanUp;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Psr\Http\Message\ServerRequestInterface;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

use function wp_styles;
use function wp_enqueue_style;

class CSSCleanUpTest extends WPTestCase
{
	
	use AssetRemovalTestHelpers;
	
	/** @test */
	public function testCanBeConstructedWithoutPsr7Dependency()
	{
		
		$remover = new CSSCleanUp();
		
		$this->assertInstanceOf(CSSCleanUp::class, $remover);
		
	}
	
	/** @test */
	public function testDoRemove()
	{
		
		wp_enqueue_style('foo', 'foo.css');
		
		$this->assertContains('foo', wp_styles()->queue);
		
		$this->newRemover()->remove(['foo']);
		
		$this->assertNotContains('foo', wp_styles()->queue);
		
	}
	
	private function newRemover(ServerRequestInterface $server_request = null)
	{
		return new CSSCleanUp(
			new Psr7ServerRequestAdapter($server_request ?? new ServerRequest('GET', '/foo'))
		);
	}
	
	/** @test */
	public function testAllIdentifiers()
	{
		
		wp_styles()->queue = [];
		
		wp_enqueue_style('foo', 'foo.css');
		wp_enqueue_style('bar', 'bar.css');
		
		$this->assertContains('foo', wp_styles()->queue);
		$this->assertContains('bar', wp_styles()->queue);
		
		$this->newRemover()->remove(['foo', 'bar']);
		
		$this->assertNotContains('foo', wp_styles()->queue);
		$this->assertNotContains('bar', wp_styles()->queue);
		
	}
	
	/** @test */
	public function testRemoveGutenbergCss()
	{
		
		set_current_screen('foo');
		$screen = get_current_screen();
		$screen->is_block_editor(false);
		
		add_action('wp_enqueue_scripts', function() {
			
			$this->newRemover()->removeGutenberg();
			
		},         9999);
		
		$header = $this->getWPHead();
		
		$this->assertStringNotContainsString('wp-block-library', $header);
		$this->assertStringNotContainsString('block-library/style.min.css', $header);
		$this->assertStringNotContainsString('wp-block-library-theme', $header);
		
	}
	
	/** @test */
	public function testRemoveGutenbergDoesntWorkInsideGutenberg()
	{
		
		set_current_screen('foo');
		$screen = get_current_screen();
		$screen->is_block_editor(true);
		
		// This needs to enqueued explicitly because it depends on current_theme_support().
		wp_enqueue_style('wp-block-library-theme');
		
		add_action('admin_enqueue_scripts', function() {
			
			$this->newRemover()->removeGutenberg();
			
		},         9999);
		
		$admin_header = $this->getAdminHead();
		
		$this->assertStringContainsString('block-library/style.min.css', $admin_header);
		$this->assertStringContainsString('wp-block-library-theme', $admin_header);
		
	}
	
	protected function tearDown() :void
	{
		
		unset($this->scripts);
		unset($this->styles);
		unset($GLOBALS['wp_styles']);
		unset($GLOBALS['wp_scripts']);
		unset($GLOBALS['current_screen']);
		
		parent::tearDown();
		
	}
	
}