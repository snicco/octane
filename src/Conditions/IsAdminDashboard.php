<?php

namespace Snicco\Octane\Conditions;

use Snicco\Octane\Condition;
use Snicco\Octane\ServerRequestAdapter;

class IsAdminDashboard implements Condition
{
	
	public function passes(ServerRequestAdapter $server_request) :bool
	{
		return strpos($server_request->path(), 'wp-admin');
	}
	
}