<?php

namespace Ocante\Tests\unit;

use _WP_Dependency;
use Nyholm\Psr7\ServerRequest;
use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Removers\ScriptCleanUp;
use Snicco\Octane\Conditions\IsExactPath;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Psr\Http\Message\ServerRequestInterface;

use function wp_scripts;
use function wp_enqueue_script;

class AbstractRemoverTest extends WPTestCase
{
	
	/** @test */
	public function testCanBeConstructedWithoutPsr7Dependency()
	{
		
		$remover = new ScriptCleanUp();
		
		$this->assertInstanceOf(ScriptCleanUp::class, $remover);
		
	}
	
	/** @test */
	public function testDependenciesAreSetUp()
	{
		
		$this->assertNotEmpty(wp_scripts()->queue);
		$this->assertContains('foo', wp_scripts()->queue);
		$this->assertContains('bar', wp_scripts()->queue);
		$this->assertArrayHasKey('foo', wp_scripts()->registered);
		$this->assertArrayHasKey('bar', wp_scripts()->registered);
		$this->assertInstanceOf(_WP_Dependency::class, wp_scripts()->registered['foo']);
		$this->assertInstanceOf(_WP_Dependency::class, wp_scripts()->registered['bar']);
		
	}
	
	/** @test */
	public function testRemoveScript()
	{
		
		$this->newRemover()->remove('foo');
		$this->assertScriptDequeued('foo');
	}
	
	private function newRemover(ServerRequestInterface $server_request = null)
	{
		return new ScriptCleanUp(
			new Psr7ServerRequestAdapter($server_request ?? new ServerRequest('GET', '/foo'))
		);
	}
	
	private function assertScriptDequeued(string $handle)
	{
		$this->assertNotContains($handle, wp_scripts()->queue, "Script [$handle] not dequeued.");
	}
	
	/** @test */
	public function testRemoveIf()
	{
		
		$this->newRemover()->removeIf(false, 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$this->newRemover()->removeIf(true, 'foo');
		$this->assertScriptDequeued('foo');
		
	}
	
	private function assertScriptNotDequeued(string $handle)
	{
		$this->assertContains($handle, wp_scripts()->queue, "Script [$handle] was dequeued.");
	}
	
	/** @test */
	public function testRemoveIfWithClosure()
	{
		
		$this->newRemover()->removeIf(function() { return false; }, 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$this->newRemover()->removeIf(function() { return true; }, 'foo');
		$this->assertScriptDequeued('foo');
		
	}
	
	/** @test */
	public function testRemoveUnless()
	{
		$this->newRemover()->removeUnless(true, 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$this->newRemover()->removeUnless(false, 'foo');
		$this->assertScriptDequeued('foo');
	}
	
	/** @test */
	public function removeMultiple()
	{
		
		$this->newRemover()->remove(['foo', 'bar']);
		$this->assertScriptDequeued('foo');
		$this->assertScriptDequeued('bar');
		
		$this->enqueueTestScripts();
		
		$this->assertScriptNotDequeued('foo');
		$this->assertScriptNotDequeued('bar');
		
	}
	
	private function enqueueTestScripts()
	{
		wp_enqueue_script('foo', 'foo.js');
		wp_enqueue_script('bar', 'bar.js');
	}
	
	/** @test */
	public function testRemoveIfFrontend()
	{
		
		$remover = $this->newRemover(
			$this->serverRequest('/foo', ['SCRIPT_NAME' => 'wp-admin/index.php'])
		);
		$remover->removeOnFrontend('foo');
		
		$this->assertScriptNotDequeued('foo');
		
		$remover = $this->newRemover(
			$this->serverRequest('/foo', ['SCRIPT_NAME' => 'index.php'])
		
		);
		$remover->removeOnFrontend('foo');
		
		$this->assertScriptDequeued('foo');
		
	}
	
	private function serverRequest(string $path, array $server = []) :ServerRequest
	{
		return new ServerRequest('GET', $path, [], '', '1.1', $server);
	}
	
	/** @test */
	public function testRemoveInAdmin()
	{
		
		$remover =
			$this->newRemover($this->serverRequest('/foobar', ['SCRIPT_NAME' => 'index.php']));
		$remover->removeInAdmin('foo');
		
		$this->assertScriptNotDequeued('foo');
		
		$remover = $this->newRemover(
			$this->serverRequest(
				'/wp-admin/admin.php?page=foo',
				['SCRIPT_NAME' => 'wp-admin/index.php']
			)
		
		);
		$remover->removeInAdmin('foo');
		
		$this->assertScriptDequeued('foo');
		
	}
	
	/** @test */
	public function testOnlyKeep()
	{
		
		wp_enqueue_script('baz', 'baz.js');
		wp_enqueue_script('biz', 'biz.js');
		
		$remover = $this->newRemover();
		$remover->onlyKeep('biz');
		
		$this->assertScriptNotDequeued('biz');
		$this->assertScriptDequeued('foo');
		$this->assertScriptDequeued('bar');
		$this->assertScriptDequeued('baz');
		
	}
	
	/** @test */
	public function testOnlyKeepVendor()
	{
		
		wp_enqueue_script('v1_foo', '/v1_foo.js');
		wp_enqueue_script('v1_bar', '/v1_bar.js');
		wp_enqueue_script('v1_biz', '/v1_biz.js');
		wp_enqueue_script('v2_foo', '/v2_foo.js');
		wp_enqueue_script('v2_bar', '/v2_bar.js');
		wp_enqueue_script('v2_biz', '/v1_biz.js');
		
		$remover = $this->newRemover();
		$remover->onlyKeepVendor('v2');
		
		$this->assertScriptDequeued('v1_foo');
		$this->assertScriptDequeued('v1_bar');
		$this->assertScriptDequeued('v1_biz');
		$this->assertScriptNotDequeued('v2_foo');
		$this->assertScriptNotDequeued('v2_bar');
		$this->assertScriptNotDequeued('v2_biz');
		
	}
	
	/** @test */
	public function testOnlyKeepWithCondition()
	{
		
		wp_enqueue_script('baz', 'baz.js');
		wp_enqueue_script('biz', 'biz.js');
		
		$remover = $this->newRemover();
		$remover->onlyKeep('biz', false);
		
		$this->assertScriptNotDequeued('biz');
		$this->assertScriptNotDequeued('foo');
		$this->assertScriptNotDequeued('bar');
		$this->assertScriptNotDequeued('baz');
		
		$remover->onlyKeep('biz', true);
		
		$this->assertScriptNotDequeued('biz');
		$this->assertScriptDequeued('foo');
		$this->assertScriptDequeued('bar');
		$this->assertScriptDequeued('baz');
		
	}
	
	/** @test */
	public function testRemoveByPath()
	{
		
		$remover = $this->newRemover($this->serverRequest('/foo/bar/baz'));
		
		$remover->removeIfPath('/foo', 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$remover->removeIfPath('/foo/bar/baz', 'foo');
		$this->assertScriptDequeued('foo');
		
	}
	
	/** @test */
	public function testRemoveIfPathSegment()
	{
		
		$remover = $this->newRemover($this->serverRequest('/foo/bar/baz'));
		
		$remover->removeIfPathHasSegment('/biz', 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$remover->removeIfPathHasSegment('/baz', 'foo');
		$this->assertScriptDequeued('foo');
		
		$remover->removeIfPathHasSegment('/bar', 'bar');
		$this->assertScriptDequeued('bar');
		
	}
	
	/** @test */
	public function testNegatedCondition()
	{
		
		global $wp_query;
		
		$wp_query->is_singular = true;
		$remover = $this->newRemover();
		$remover->removeIf('!is_singular', 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$wp_query->is_singular = false;
		$remover->removeIf('!is_singular', 'foo');
		$this->assertScriptDequeued('foo');
		
	}
	
	/** @test */
	public function testRemoveIfAdminPage()
	{
		
		$remover = $this->newRemover($this->serverRequest('/wp-admin/admin.php?page=foo_page'));
		
		$remover->removeIfAdminPage('bar_page', 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$remover->removeIfAdminPage('foo_page', 'foo');
		$this->assertScriptDequeued('foo');
		
	}
	
	/** @test */
	public function testRemoveUnlessAdminPage()
	{
		
		$remover = $this->newRemover($this->serverRequest('/wp-admin/admin.php?page=foo_page'));
		
		$remover->removeUnlessAdminPage('foo_page', 'foo');
		$this->assertScriptNotDequeued('foo');
		
		$remover->removeUnlessAdminPage('bar_page', 'foo');
		$this->assertScriptDequeued('foo');
		
	}
	
	/** @test */
	public function testRemoveAllFromVendor()
	{
		
		wp_enqueue_script('my_vendor_script1', 'vendor_script1.js');
		wp_enqueue_script('my_vendor_script2', 'vendor_script2.js');
		wp_enqueue_script('another_vendor_script1', 'vendor_script1.js');
		wp_enqueue_script('another_vendor_script2', 'vendor_script2.js');
		
		$remover = $this->newRemover($this->serverRequest('/foo'));
		
		$remover->removeAllForVendor('my_vendor', new IsExactPath('/foo'));
		
		$this->assertScriptDequeued('my_vendor_script1');
		$this->assertScriptDequeued('my_vendor_script2');
		$this->assertScriptNotDequeued('another_vendor_script1');
		$this->assertScriptNotDequeued('another_vendor_script2');
		
	}
	
	protected function setUp() :void
	{
		parent::setUp();
		$this->enqueueTestScripts();
	}
	
	protected function tearDown() :void
	{
		
		unset($GLOBALS['wp_styles']);
		unset($GLOBALS['wp_scripts']);
		
		parent::tearDown();
		
	}
	
}