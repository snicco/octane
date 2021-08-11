<?php

namespace Snicco\Octane;

use Snicco\Octane\Conditions\Unless;
use Snicco\Octane\Conditions\IsFrontend;
use Snicco\Octane\Conditions\IsExactPath;
use Snicco\Octane\Conditions\IsAdminPage;
use Snicco\Octane\Conditions\HasPathSegment;
use Snicco\Octane\Conditions\IsAdminDashboard;
use Snicco\Octane\Conditions\CallableCondition;

/**
 * Base class that only provides the logic whether some resource should
 * be removed. The removal is delegated to all child classes implementing the contract.
 */
abstract class AbstractRemover
{
	
	/** @var ServerRequestAdapter */
	protected $server_request;
	
	public function __construct(?ServerRequestAdapter $server_request = null)
	{
		
		$this->server_request = $server_request ?? new PhpSuperGlobalsRequestAdapter();
		
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 * @param string|array            $ids
	 */
	public function removeUnless($condition, $ids) :self
	{
		return $this->removeIf(! $condition, $this->toArray($ids));
	}
	
	/**
	 * @param bool|Condition|callable $condition
	 * @param string|array            $ids
	 */
	public function removeIf($condition, $ids) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			return $this->remove($this->toArray($ids));
			
		}
		
		return $this;
	}
	
	/**
	 * @param bool|Condition $condition
	 *
	 * @return bool
	 */
	protected function evaluateCondition($condition) :bool
	{
		
		if( is_callable($condition) ) {
			
			$condition = new CallableCondition($condition);
			
		}
		
		if( is_string($condition) ) {
			
			return ! call_user_func(ltrim($condition, '!'));
			
		}
		
		if( $condition instanceof Condition ) {
			
			return $condition->passes($this->server_request);
			
		}
		
		if( ! is_bool($condition) && ! is_null($condition) ) {
			throw new \InvalidArgumentException(
				'Invalid condition provided for ScriptCleanUp::class'
			);
		}
		
		return (bool)$condition;
		
	}
	
	/**
	 * @param string|array $ids
	 */
	public function remove($ids) :self
	{
		return $this->doRemove($this->toArray($ids));
	}
	
	abstract protected function doRemove(array $ids) :AbstractRemover;
	
	protected function toArray($value) :array
	{
		
		if( is_null($value) ) {
			return [];
		}
		
		return is_array($value) ? $value : [$value];
		
	}
	
	/**
	 * @param string|array $ids
	 */
	public function removeOnFrontend($ids) :self
	{
		return $this->removeIf(new IsFrontend(), $ids);
	}
	
	/**
	 * @param string|array $ids
	 */
	public function removeInAdmin($ids) :self
	{
		return $this->removeIf(new IsAdminDashboard(), $ids);
	}
	
	/**
	 * @param string|array $ids
	 */
	public function removeIfPath(string $exact_path, $ids) :self
	{
		return $this->removeIf(new IsExactPath($exact_path), $ids);
	}
	
	/**
	 * @param string|array $ids
	 */
	public function removeIfPathHasSegment(string $segment, $ids) :self
	{
		return $this->removeIf(new HasPathSegment($segment), $ids);
	}
	
	/**
	 * @param string|string[] $ids
	 */
	public function removeIfAdminPage(string $admin_page_slug, $ids) :self
	{
		return $this->removeIf(new IsAdminPage($admin_page_slug), $ids);
	}
	
	/**
	 * @param string|string[] $ids
	 */
	public function removeUnlessAdminPage(string $admin_page_slug, $ids) :self
	{
		return $this->removeIf(
			new Unless(new IsAdminPage($admin_page_slug)),
			$ids
		);
	}
	
}