<?php

namespace Snicco\Octane\Conditions;

use WP_Screen;
use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class InGutenberg implements Condition
{
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		
		$script = $this->scriptName($server_request->server());
		
		if( ! function_exists('get_current_screen') && $script === 'index.php' ) {
			return false;
		}
		
		$screen = get_current_screen();
		
		if( $screen instanceof WP_Screen ) {
			return $screen->is_block_editor();
		}
		
		trigger_error(
			"Tried to evaluate condition [InGutenberg] before WP_SCREEN is available.",
			E_USER_NOTICE
		);
		
		return $this->isPostEditScreen($server_request);
		
	}
	
	private function scriptName(array $server) :string
	{
		
		return trim($server['SCRIPT_NAME'] ?? '', '/');
		
	}
	
	private function isPostEditScreen(ServerRequestAdapter $server_request) :bool
	{
		
		$target = $server_request->requestTarget();
		
		if( strpos($target, 'wp-admin/post.php') === false ) {
			
			return false;
			
		}
		
		if( strpos($target, 'action=edit') === false ) {
			
			return false;
			
		}
		
		return true;
		
	}
	
}