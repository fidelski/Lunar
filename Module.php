<?php
/**
 * Lunar Toolkit ()
 *
 * @link      https://github.com/fidelski/lunar
 * @copyright Copyright (c) 2013 David Daniel <david@daniels.li>
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Lunar;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Provides module and autoloader configuration.
 */
class Module
{
    /**
     * @return array
     */
    public function getConfig()
    { return include __DIR__ . '/config/module.config.php'; }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array (
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ),
            ),
        );
    }
}
