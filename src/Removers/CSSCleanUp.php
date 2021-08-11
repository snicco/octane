<?php

namespace Snicco\Octane\Removers;

use WP_Styles;
use Snicco\Octane\Condition;
use Snicco\Octane\Conditions\Unless;
use Snicco\Octane\ServerRequestAdapter;
use Snicco\Octane\Conditions\InGutenberg;

use function wp_styles;

class CSSCleanUp extends IDBased
{
	
	/** @var WP_Styles */
	private $styles;
	
	public function __construct(?ServerRequestAdapter $server_request = null)
	{
		
		parent::__construct($server_request);
		
		$this->styles = wp_styles();
		
	}
	
	public function allIdentifiers() :array
	{
		return $this->styles->queue;
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 */
	public function removeGutenberg($condition = true) :CSSCleanUp
	{
		
		if( $this->evaluateCondition($condition) ) {
			
			$this->removeAllForVendor('wp-block-library', new Unless(new InGutenberg()));
			
		}
		
		return $this;
		
	}
	
	protected function removeIDs(array $ids)
	{
		$this->styles->dequeue($ids);
	}
	
}