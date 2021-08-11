<?php

namespace Snicco\Octane\Removers;

use WP_Scripts;
use _WP_Dependency;
use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

use function wp_scripts;

class ScriptCleanUp extends IDBased
{
	
	/** @var WP_Scripts */
	private $scripts;
	
	public function __construct(?ServerRequestAdapter $server_request = null)
	{
		parent::__construct($server_request);
		$this->scripts = wp_scripts();
		
	}
	
	public function allIdentifiers() :array
	{
		return $this->scripts->queue;
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function moveAllToFooter($condition = true) :self
	{
		
		if( ! $this->evaluateCondition($condition) ) {
			return $this;
		}
		
		foreach( $this->scripts->queue as $handle ) {
			
			$this->moveToFooter($handle);
			
		}
		
		return $this;
		
	}
	
	/**
	 * @param string|string[]         $handles
	 * @param bool|Condition|callable $condition
	 */
	public function moveToFooter($handles, $condition = true) :self
	{
		
		if( ! $this->evaluateCondition($condition) ) {
			return $this;
		}
		
		$handles = $this->toArray($handles);
		
		foreach( $handles as $handle ) {
			
			/** @var _WP_Dependency $dependency */
			if( ! $dependency = $this->scripts->registered[$handle] ?? null ) {
				
				continue;
				
			}
			
			$dependency->extra['group'] = 1;
			
		}
		
		return $this;
		
	}
	
	/**
	 * @param string|string[]         $vendor_prefixes
	 * @param bool|Condition|callable $condition
	 */
	public function moveVendorToFooter($vendor_prefixes, $condition = true) :self
	{
		
		if( ! $this->evaluateCondition($condition) ) {
			return $this;
		}
		
		$remove = [];
		
		foreach( $this->toArray($vendor_prefixes) as $vendor_prefix ) {
			
			foreach( $this->getAllForVendor($vendor_prefix) as $handle ) {
				
				$remove[] = $handle;
				
			}
			
		}
		
		$this->moveToFooter($remove);
		
		return $this;
		
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 * @param bool                    $to_footer Whether to load jQuery in the footer.
	 *
	 * @return $this
	 */
	public function jQueryCDN(bool $condition = true, bool $to_footer = false) :self
	{
		
		if( ! $this->evaluateCondition($condition) ) {
			return $this;
		}
		
		// WordPress doesn't allow removing jquery in admin pages.
		if( strpos($this->server_request->path(), 'wp-admin') ) {
			return $this;
		}
		
		$jquery = $this->scripts->registered['jquery'];
		$version = str_replace(['-wp', 'wp'], '', $jquery->ver);
		wp_deregister_script('jquery');
		wp_register_script(
			'jquery',
			"https://ajax.googleapis.com/ajax/libs/jquery/$version/jquery.min.js",
			[],
			null,
			$to_footer
		);
		
		return $this;
		
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeCommentReply($condition = true) :self
	{
		$this->removeIf($condition, 'comment-reply');
		return $this;
	}
	
	protected function removeIDs(array $handles)
	{
		$this->scripts->dequeue($handles);
		
	}
	
}