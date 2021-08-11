<?php

namespace Snicco\Octane\Removers;

use Snicco\Octane\AbstractRemover;

abstract class HookBased extends AbstractRemover
{
	
	protected function doRemove(array $ids) :AbstractRemover
	{
		$this->removeHooks($ids);
		return $this;
	}
	
	/**
	 * [
	 *   'wp_head' => [
	 *       example_function',
	 *       [ 'example_function', 5 ],
	 *       [ [$object, 'method'], 5 ]
	 * ]
	 *
	 * @param array<string,mixed> $hooks
	 */
	private function removeHooks(array $hooks)
	{
		
		foreach( $hooks as $hook => $callbacks ) {
			
			$hook = is_string($hook) ? $hook : $this->defaultHooks();
			
			foreach( $this->toArray($callbacks) as $callback ) {
				
				$hook_definition = $this->toArray($callback);
				
				$callable = $hook_definition[0];
				$priority = $hook_definition[1] ?? 10;
				
				remove_filter($hook, $callable, $priority);
				
			}
			
		}
		
	}
	
	/**
	 * The default hook where filters should be removed if not explicitly provided.
	 *
	 * @return string
	 */
	abstract protected function defaultHooks() :string;
	
}