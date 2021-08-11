<?php

namespace Snicco\Octane\Removers;

use Snicco\Octane\Condition;
use Snicco\Octane\AbstractRemover;

/**
 * Base class that can be used to remove resources which can clearly
 * identified by some unique id.
 */
abstract class IDBased extends AbstractRemover
{
	
	/**
	 * Remove all resources that start with the given vendor prefix
	 *
	 * @param string                  $vendor_prefix
	 * @param bool|Condition|callable $condition
	 *
	 * @return IDBased
	 */
	public function removeAllForVendor(string $vendor_prefix, $condition = true) :self
	{
		
		if( $this->evaluateCondition($condition) ) {
			
			$this->remove($this->getAllForVendor($vendor_prefix));
			
		}
		
		return $this;
		
	}
	
	/**
	 * Returns an array containing all identifier that start with the given prefix.
	 *
	 * @param string $vendor_prefix
	 *
	 * @return string[]
	 */
	public function getAllForVendor(string $vendor_prefix) :array
	{
		return array_filter($this->allIdentifiers(), function($handle) use ($vendor_prefix) {
			
			$prefix = substr($handle, 0, strlen($vendor_prefix));
			return $prefix === $vendor_prefix;
			
		});
	}
	
	/**
	 * Return an array containing all identifier for the implementation.
	 *
	 * @return string[]
	 */
	abstract protected function allIdentifiers() :array;
	
	/**
	 * Only keep resources that start with the given vendor prefix.
	 *
	 * @param string|string[]         $ids_to_keep
	 * @param bool|Condition|callable $condition
	 *
	 * @return IDBased
	 */
	public function onlyKeep($ids_to_keep, $condition = true) :self
	{
		
		$ids_to_keep = $this->toArray($ids_to_keep);
		
		if( $this->evaluateCondition($condition) ) {
			
			$delete = array_diff($this->allIdentifiers(), $ids_to_keep);
			$this->remove($delete);
			
		}
		
		return $this;
		
	}
	
	/**
	 * Only keep resources that start with the given vendor prefix.
	 *
	 * @param string                  $vendor_prefix
	 * @param bool|Condition|callable $condition
	 *
	 * @return IDBased
	 */
	public function onlyKeepVendor(string $vendor_prefix, $condition = true) :self
	{
		if( $this->evaluateCondition($condition) ) {
			
			$delete = array_diff($this->allIdentifiers(), $this->getAllForVendor($vendor_prefix));
			$this->remove($delete);
			
		}
		
		return $this;
	}
	
	protected function doRemove(array $ids) :AbstractRemover
	{
		$this->removeIDs($ids);
		return $this;
	}
	
	/**
	 * Remove all resources for the given ids.
	 *
	 * @param array $ids
	 */
	abstract protected function removeIDs(array $ids);
	
}