<?php

namespace Ocante\Tests\unit\Removers;

use Nyholm\Psr7\ServerRequest;
use Codeception\TestCase\WPTestCase;
use Snicco\Octane\Removers\HtmlCleanUp;
use Snicco\Octane\Psr7ServerRequestAdapter;
use Ocante\Tests\unit\Concerns\AssetRemovalTestHelpers;

class HtmlCleanUPTest extends WPTestCase
{
	
	use AssetRemovalTestHelpers;
	
	/** @test */
	public function testCanBeConstructedWithoutPsr7Dependency()
	{
		
		$remover = new HtmlCleanUp();
		
		$this->assertInstanceOf(HtmlCleanUp::class, $remover);
		
	}
	
	/** @test */
	public function testDisableEmojis()
	{
		
		$this->getRemover()->removeEmojis();
		
		$this->assertStringNotContainsString('emoji', $this->getWPHead(), 'Emojis not deleted.');
		
		$this->assertStringNotContainsString(
			'emoji',
			$this->printAdminHead(),
			'Emojis not deleted.'
		);
		
		$this->assertNotFalse(has_filter('emoji_svg_url', '__return_false'));
		
	}
	
	protected function getRemover(ServerRequest $server_request = null)
	{
		return new HtmlCleanUp(
			new Psr7ServerRequestAdapter($server_request ?? $this->serverRequest('/'))
		);
	}
	
	/** @test */
	public function testDisableRSD()
	{
		
		$this->getRemover()->removeRSD();
		
		$this->assertStringNotContainsString(
			'<link rel="EditURI" type="application/rsd+xml" title="RSD"',
			$this->getWPHead(),
			'RSD link not removed.'
		);
		
	}
	
	/** @test */
	public function testRemoveWindowsLiveWriter()
	{
		
		$this->getRemover()->removeWindowsLiveWriter();
		$html = $this->getWPHead();
		$this->assertStringNotContainsString(
			'<link rel="wlwmanifest" type="application/wlwmanifest+xml"',
			$html,
			'Windows Live Writer not removed.'
		);
		$this->assertStringNotContainsString(
			'wlwmanifest+xml"',
			$html,
			'Windows Live Writer not removed.'
		);
		
	}
	
	/** @test */
	public function testRemoveOembeds()
	{
		
		$this->setSinglePost();
		
		$this->getRemover()->removeEmbeds();
		
		ob_start();
		wp_oembed_add_discovery_links();
		$link1 = ob_get_clean();
		$this->assertNotSame('', $link1);
		
		$head = $this->getWPHead();
		
		$this->assertStringNotContainsString($link1, $head);
		$this->assertStringNotContainsString('wp-embed.min.js', $head);
		
	}
	
	/** @test */
	public function testRemoveAdjacentPosts()
	{
		// Core does not set these by default.
		add_action('wp_head', 'adjacent_posts_rel_link');
		add_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		
		$this->getRemover()->removeAdjacentPosts();
		
		$this->assertFalse(has_filter('wp_head', 'adjacent_posts_rel_link'));
		$this->assertFalse(has_filter('wp_head', 'adjacent_posts_rel_link_wp_head'));
		
	}
	
	/** @test */
	public function testRemoveFeed()
	{
		
		$this->getRemover()->removeFeed();
		
		$html = $this->getWPHead();
		
		$this->assertStringNotContainsString('Feed', $html);
		$this->assertStringNotContainsString('Comments Feed', $html);
		
	}
	
	/** @test */
	public function testRemoveShortlinks()
	{
		
		// Ensure that rel canonical does not cause a false positive for the ?p={post_id} assertion.
		$this->set_permalink_structure('/%postname%/');
		$post = $this->setSinglePost();
		
		$this->getRemover()->removeShortlinks();
		
		$head = $this->getWPHead();
		
		$this->assertStringNotContainsString('shortlink', $head);
		$this->assertStringNotContainsString("?p={$post->ID}", $head);
		$this->assertFalse(has_filter('template_redirect', 'wp_shortlink_header'));
		
	}
	
	/** @test */
	public function testRemoveGenerators()
	{
		
		$this->getRemover()->removeWPGenerator();
		
		$head = $this->getWPHead();
		
		global $wp_version;
		
		$this->assertStringNotContainsString('generator', $head);
		$this->assertStringNotContainsString("WordPress $wp_version", $head);
		
	}
	
	/** @test */
	public function testRemoveRestLinks()
	{
		
		$post = $this->setSinglePost();
		
		$this->getRemover()->removeRestAPILinks();
		
		$head = $this->getWPHead();
		
		$this->assertStringNotContainsString("/wp-json/wp/v2/posts/{$post->ID}", $head);
		$this->assertFalse(has_filter('template_redirect', 'rest_output_link_header'));
		
	}
	
	protected function tearDown() :void
	{
		parent::tearDown();
		unset($GLOBALS['wp_styles']);
		unset($GLOBALS['wp_scripts']);
		
	}
	
}