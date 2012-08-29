<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\Router\RouteMatch;

use Zend\Mvc\MvcEvent;

use Zend\Mvc\ModuleRouteListener;

class Module
{
	const DEFAULT_SUBMODULE = __NAMESPACE__;
	const CONTROLLER_AFFIX = 'Controller';
	const CLASS_EXTENSION = '.php';

	public function onBootstrap($e)
	{
		// load default module configuration
		//self::loadSubmoduleConfiguration();

		$e->getApplication()->getServiceManager()->get('translator');
		$eventManager        = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		$eventManager->attach( MvcEvent::EVENT_ROUTE, function($e){
			$routeMatch = $e->getRouteMatch();
			$submodule = $routeMatch->getParam( '__SUBMODULE__', false );

			self::loadSubmoduleConfiguration( $submodule );
			die;
		});
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		$modules = self::getSubmodules();
		$namespaces = array(
			__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
		);
		foreach( $modules as $m ):
			$namespaces[ $m ] = __DIR__ . '/src/' . $m;
		endforeach;
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => $namespaces,
			),
		);
	}

	public static function invokableExists( $invokable )
	{
		$pieces = explode( "\\", $invokable );
		$pieces[ 0 ] = self::DEFAULT_SUBMODULE;
		$controller = implode( "\\", $pieces );
		if( array_key_exists( $controller, $invokables = self::getInvokables() ) ):
			return $invokables[ $controller ];
		endif;
		return false;
	}

	public static function loadSubmoduleConfiguration( $submodule = self::DEFAULT_SUBMODULE )
	{
		$path = realpath( __DIR__ . '/src/' . $submodule . '/Config.php' );
		require_once $path;
		$class = "$submodule\Config";
		$config = new $class;
		//$config = new $submodule\Config;
		var_dump( $config );
		//echo 'loading ' . $subdmodule;
	}

	public static function getCurrentSubmodule()
	{
		$module = self::DEFAULT_SUBMODULE;
		$modules = self::getSubmodules();
		$rexp = sprintf( '!^(?P<module>%s)!i', implode( '|', $modules ) );
		if( preg_match( $rexp, $_SERVER[ 'HTTP_HOST' ], $matches ) )
		{
			$module = ucfirst( strtolower( $matches[ 'module' ] ) );
		}
		else
		{
			$module = 'Notfound';
		}
		return $module;
	}

	public static function getSubmodules()
	{
		$dirs = glob( realpath( __DIR__ . '/src/' ) . '/*', GLOB_ONLYDIR );
		$dirs = array_map( basename, $dirs );
		return $dirs;
	}

	public static function getSubmoduleControllers( $module = self::DEFAULT_SUBMODULE )
	{
		$controllers = glob( realpath( __DIR__ . '/src/' . $module . '/Controller/' ) . '/*' . self::CONTROLLER_AFFIX . self::CLASS_EXTENSION );
		$controllers = array_map( function( $f ){ return basename( $f, self::CLASS_EXTENSION ); }, $controllers );
		return $controllers;
	}

	public static function getInvokables()
	{
		$invokables = array();
		$folder_tree = '%s/Controller/%s';
		foreach( self::getSubmodules() as $m ):
			foreach( self::getSubmoduleControllers() as $f ):
				$name = str_replace( self::CONTROLLER_AFFIX, '', $f );
				$invokables[ sprintf( '%s\Controller\%s', $m, $name ) ] = sprintf( '%s\Controller\%s', self::DEFAULT_SUBMODULE, $f );
			endforeach;
			foreach( self::getSubmoduleControllers( $m ) as $f ):
				$name = str_replace( self::CONTROLLER_AFFIX, '', $f );
				$invokables[ sprintf( '%s\Controller\%s', $m, $name ) ] = sprintf( '%s\Controller\%s', $m, $f );
			endforeach;
		endforeach;
		return $invokables;
	}
}
