<?php

namespace Snicco\Octane\Removers;

use Snicco\Octane\ServerRequestAdapter;

class PluginCleanUp extends IDBased
{
	
	/**
	 * @var array
	 */
	private $active_plugins;
	
	/**
	 * @var array
	 */
	private $plugins_to_remove = [];
	
	public function __construct(?ServerRequestAdapter $server_request = null)
	{
		parent::__construct($server_request);
		$this->active_plugins = get_option('active_plugins', []);
	}
	
	public function allIdentifiers() :array
	{
		return $this->active_plugins;
	}
	
	public function __destruct()
	{
		add_filter('option_active_plugins', function(array $active_plugins) {
			
			foreach( $this->plugins_to_remove as $handle ) {
				
				if( ($key = array_search($handle, $active_plugins, true)) !== false ) {
					unset($active_plugins[$key]);
				}
				
			}
			
			return $active_plugins;
			
		},         123122, 1);
	}
	
	protected function removeIDs(array $ids)
	{
		foreach( $ids as $plugin_id ) {
			
			$this->plugins_to_remove[] = $plugin_id;
			
		}
	}
	
}