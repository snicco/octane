<?php

namespace Ocante\Tests\unit\Concerns;

use WP_Post;
use Nyholm\Psr7\ServerRequest;

trait AssetRemovalTestHelpers
{
	
	protected function serverRequest(string $path = '/', array $server = []) :ServerRequest
	{
		return new ServerRequest('GET', $path, [], '', '1.1', array_merge($_SERVER, $server));
	}
	
	protected function getWPHead() :string
	{
		ob_start();
		do_action('wp_head');
		return ob_get_clean();
	}
	
	protected function getHeadAndFooter() :string
	{
		ob_start();
		do_action('wp_head');
		do_action('wp_footer');
		return ob_get_clean();
	}
	
	protected function printHeaderAndFooter() :string
	{
		$head = $this->printHead();
		$footer = $this->printFooter();
		return $head.$footer;
	}
	
	protected function printHead() :string
	{
		ob_start();
		wp_print_head_scripts();
		wp_print_styles();
		return ob_get_clean();
	}
	
	protected function printFooter() :string
	{
		ob_start();
		wp_print_footer_scripts();
		return ob_get_clean();
	}
	
	protected function getAdminHead() :string
	{
		
		ob_start();
		
		do_action('admin_enqueue_scripts', '');
		
		do_action('admin_print_styles');
		
		do_action('admin_print_scripts');
		
		return ob_get_clean();
		
	}
	
	protected function printAdminHead()
	{
		
		ob_start();
		
		do_action('admin_print_styles');
		
		do_action('admin_print_scripts');
		
		return ob_get_clean();
	}
	
	protected function setSinglePost(WP_Post $post = null) :WP_Post
	{
		
		global $wp_query, $post;
		$post = $post ?? $this->factory()->post->create_and_get();
		$wp_query->post = $post;
		$wp_query->is_singular = true;
		$wp_query->is_single = true;
		
		return $post;
		
	}
	
}