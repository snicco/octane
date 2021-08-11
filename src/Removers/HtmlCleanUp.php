<?php

namespace Snicco\Octane\Removers;

use Snicco\Octane\Condition;

// This class removes default hooks created by WordPress.
// These are always run before any mu-plugins or themes are created, so It's safe
// to just remove them here.
class HtmlCleanUp extends HookBased
{
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeEmojis($condition = true) :self
	{
		
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'wp_head' => [['print_emoji_detection_script', 7]],
				                'admin_print_scripts' => 'print_emoji_detection_script',
				                'wp_print_styles' => 'print_emoji_styles',
				                'admin_print_styles' => 'print_emoji_styles',
			                ]);
			
			add_filter('emoji_svg_url', '__return_false');
			
		}
		
		return $this;
		
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeRSD($condition = true) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'wp_head' => 'rsd_link',
			                ]);
			
		}
		
		return $this;
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeWindowsLiveWriter($condition = true) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'wp_head' => 'wlwmanifest_link',
			                ]);
			
		}
		
		return $this;
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeEmbeds($condition = true) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'wp_oembed_add_host_js',
				                'wp_oembed_add_discovery_links',
			                ]);
			
		}
		
		return $this;
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeAdjacentPosts($condition = true) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'adjacent_posts_rel_link_wp_head',
				                'adjacent_posts_rel_link',
			                ]);
			
		}
		
		return $this;
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeFeed($condition = true) :self
	{
		
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'wp_head' => [
					                ['feed_links', 2],
					                ['feed_links', 3],
				                ],
			                ]);
			
		}
		
		return $this;
		
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeShortlinks($condition = true) :self
	{
		
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'wp_shortlink_wp_head',
				                'template_redirect' => [['wp_shortlink_header', 11]],
			                ]);
			
		}
		
		return $this;
		
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeWPGenerator($condition = true) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'wp_generator',
			                ]);
			
		}
		
		return $this;
	}
	
	public function removeRestAPILinks($condition = true) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			$this->doRemove([
				                'rest_output_link_wp_head',
				                'template_redirect' => [['rest_output_link_header', 11]],
			                ]);
			
		}
		
		return $this;
	}
	
	protected function defaultHooks() :string
	{
		return 'wp_head';
	}
	
}