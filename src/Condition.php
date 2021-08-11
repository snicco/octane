<?php

namespace Snicco\Octane;

interface Condition
{
	
	public function passes(ServerRequestAdapter $server_request) :bool;
	
}