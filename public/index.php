<?php
/**
 * MVC application entry point
 */

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/models',
    get_include_path()))
);

// Registering the autoloader
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

// Running the front controller
$frontController = ServiceLocator::getFrontController();
$frontController->dispatch();