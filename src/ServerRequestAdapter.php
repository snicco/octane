<?php

namespace Snicco\Octane;

interface ServerRequestAdapter
{
	
	public function path() :string;
	
	public function requestTarget() :string;
	
	public function server() :array;
	
}