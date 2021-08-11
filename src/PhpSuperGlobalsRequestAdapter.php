<?php

namespace Snicco\Octane;

class PhpSuperGlobalsRequestAdapter implements ServerRequestAdapter
{
	
	/**
	 * @var array
	 */
	private $server;
	
	/**
	 * @var array
	 */
	private $parts;
	
	public function __construct()
	{
		$this->server = $_SERVER;
		$this->parts = parse_url($this->server['REQUEST_URI'] ?? '');
	}
	
	public function path() :string
	{
		return $this->parts['path'];
	}
	
	public function requestTarget() :string
	{
		return $this->server['REQUEST_URI'];
	}
	
	public function server() :array
	{
		return $this->server;
	}
	
}