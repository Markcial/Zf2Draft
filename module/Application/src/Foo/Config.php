<?php
namespace Foo;

use Application\Config as BaseConfig;

class Config extends BaseConfig
{
	public function __construct()
	{
		echo 'Whoho foo!';
	}
}