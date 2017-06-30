<?php
/**
 * MVC application entry point
 */

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('APPLICATION_ENV', 'web');

set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/models',
    get_include_path()))
);
require_once APPLICATION_PATH . '/../vendor/autoload.php';
// Registering the autoloader
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

// Running the front controller
$frontController = ServiceLocator::getFrontController();
$frontController->dispatch();
