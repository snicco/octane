<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class IsFrontend implements Condition
{
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		
		if( strpos($server_request->path(), '/wp-json') !== false ) {
			return false;
		}
		
		$script_name = trim($server_request->server()['SCRIPT_NAME'] ?? '', '/') ?? '';
		return $script_name === 'index.php';
	}
	
}