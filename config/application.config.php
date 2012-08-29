<?php
return array(
	'modules' => array(
		'Application',
		'ZfcTwig',
	),
	'module_listener_options' => array(
		'config_glob_paths'    => array(
			'config/autoload/{,*.}{global,local}.php',
		),
		'config_cache_enabled' => false,
		'cache_dir'            => 'data/cache',
		'module_paths' => array(
			'./module',
			'./vendor',
		),
	),
);
