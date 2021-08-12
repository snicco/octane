<?php

namespace Ocante\Tests\unit\Removers;

use Nyholm\Psr7\ServerRequest;
use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Removers\ScriptCleanUp;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Psr\Http\Message\ServerRequestInterface;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

use function wp_scripts;
use function wp_enqueue_script;

class ScriptCleanUpTest extends WPTestCase
{
	
	use AssetRemovalTestHelpers;
	
	/** @test */
	public function testCanBeConstructedWithoutPsr7Dependency()
	{
		
		$remover = new ScriptCleanUp();
		
		$this->assertInstanceOf(ScriptCleanUp::class, $remover);
		
	}
	
	/** @test */
	public function testDoRemove()
	{
		
		wp_enqueue_script('foo', 'foo.js');
		wp_enqueue_script('bar', 'foo.js');
		
		$this->newRemover()->remove(['foo']);
		$this->newRemover()->remove(['bar']);
		
		$this->assertNotContains('foo', wp_scripts()->queue);
		$this->assertNotContains('bar', wp_scripts()->queue);
		
	}
	
	private function newRemover(ServerRequestInterface $server_request = null)
	{
		return new ScriptCleanUp(
			new Psr7ServerRequestAdapter($server_request ?? new ServerRequest('GET', '/foo'))
		);
	}
	
	/** @test */
	public function testAllIdentifiers()
	{
		
		// reset global state.
		wp_scripts()->queue = [];
		
		wp_enqueue_script('foo', 'foo.js');
		wp_enqueue_script('bar', 'foo.js');
		
		$identifiers = $this->newRemover()->allIdentifiers();
		$this->assertSame(['foo', 'bar'], $identifiers);
		
	}
	
	/** @test */
	public function testMoveToFooter()
	{
		
		wp_enqueue_script('foo', '/foo.js', ['jquery'], false, false);
		wp_enqueue_script('bar', '/bar.js', ['jquery'], false, false);
		wp_enqueue_script('baz', '/biz.js', ['jquery'], false, false);
		
		$this->newRemover()->moveToFooter(['foo', 'bar']);
		
		$head = $this->printHead();
		
		$this->assertStringNotContainsString('/foo.js', $head);
		$this->assertStringNotContainsString('/bar.js', $head);
		$this->assertStringContainsString('/biz.js', $head);
		
		$footer = $this->printFooter();
		
		$this->assertStringContainsString('/foo.js', $footer);
		$this->assertStringContainsString('/bar.js', $footer);
		$this->assertStringNotContainsString('/biz.js', $footer);
		
	}
	
	/** @test */
	public function testMoveAllToFooter()
	{
		
		wp_enqueue_script('foo', '/foo.js', ['jquery'], false, false);
		wp_enqueue_script('bar', '/bar.js', ['jquery'], false, false);
		wp_enqueue_script('baz', '/biz.js', ['jquery'], false, false);
		
		$this->newRemover()->moveAllToFooter();
		
		$header = $this->printHead();
		
		$this->assertStringNotContainsString('/foo.js', $header);
		$this->assertStringNotContainsString('/bar.js', $header);
		$this->assertStringNotContainsString('/biz.js', $header);
		
		$footer = $this->printFooter();
		
		$this->assertStringContainsString('/foo.js', $footer);
		$this->assertStringContainsString('/bar.js', $footer);
		$this->assertStringContainsString('/biz.js', $footer);
		
	}
	
	/** @test */
	public function testMoveVendorToFooter()
	{
		
		wp_enqueue_script('my_vendor_script1', '/vendor_script1.js');
		wp_enqueue_script('my_vendor_script2', '/vendor_script2.js');
		wp_enqueue_script('another_vendor_script1', '/another_vendor_script1.js');
		wp_enqueue_script('yet_another_vendor_script1', '/yet_another_vendor_script1.js');
		
		$this->newRemover()->moveVendorToFooter('my_vendor');
		
		$header = $this->printHead();
		
		$this->assertStringContainsString('/another_vendor_script1.js', $header);
		$this->assertStringContainsString('/yet_another_vendor_script1.js', $header);
		$this->assertStringNotContainsString('/vendor_script1.js', $header);
		$this->assertStringNotContainsString('/vendor_script2.js', $header);
		
		$footer = $this->printFooter();
		
		$this->assertStringContainsString('/vendor_script1.js', $footer);
		$this->assertStringContainsString('/vendor_script2.js', $footer);
		
	}
	
	/** @test */
	public function testJQueryCDN()
	{
		
		wp_enqueue_script('foo', '/foo.js', ['jquery']);
		$version = wp_scripts()->registered['jquery']->ver;
		$expected_version = str_replace(['-wp', 'wp'], '', $version);
		
		$this->newRemover()->jQueryCDN();
		
		$html = $this->printHead();
		
		$this->assertStringContainsString('/foo.js', $html);
		
		$this->assertStringContainsString(
			"https://ajax.googleapis.com/ajax/libs/jquery/$expected_version/jquery.min.js",
			$html
		);
		$this->assertStringNotContainsString(
			"/wp-includes/js/jquery/jquery.min.js?ver=$expected_version",
			$html
		);
		
	}
	
	/** @test */
	public function testJQueryNotRemovedInAdmin()
	{
		
		wp_enqueue_script('foo', '/foo.js', ['jquery']);
		$version = wp_scripts()->registered['jquery']->ver;
		
		$this->newRemover($this->serverRequest('/wp-admin/admin.php?page=foo'))->jQueryCDN();
		
		$header = $this->printHead();
		
		$this->assertStringContainsString('/foo.js', $header);
		$this->assertStringNotContainsString(
			"https://ajax.googleapis.com/ajax/libs/jquery/$version/jquery.min.js",
			$header
		);
		$this->assertStringContainsString(
			"/wp-includes/js/jquery/jquery.min.js?ver=$version",
			$header
		);
		
	}
	
	/** @test */
	public function testRemoveCommentReplyJs()
	{
		
		$this->setSinglePost();
		$this->newRemover()->removeCommentReply();
		
		$footer = $this->getWPHead();
		
		$this->assertStringNotContainsString('comment-reply.min.js', $footer);
		
	}
	
	protected function tearDown() :void
	{
		
		unset($GLOBALS['wp_styles']);
		unset($GLOBALS['wp_scripts']);
		
		parent::tearDown();
		
	}
	
}