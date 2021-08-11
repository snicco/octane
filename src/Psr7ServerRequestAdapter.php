<?php

namespace Snicco\Octane;

use Psr\Http\Message\ServerRequestInterface as Psr7Request;

class Psr7ServerRequestAdapter implements ServerRequestAdapter
{
	
	/** @var Psr7Request */
	private $server_request;
	
	public function __construct(Psr7Request $server_request)
	{
		$this->server_request = $server_request;
	}
	
	public function path() :string
	{
		return $this->server_request->getUri()->getPath();
	}
	
	public function requestTarget() :string
	{
		return $this->server_request->getRequestTarget();
	}
	
	public function server() :array
	{
		return $this->server_request->getServerParams();
	}
	
}