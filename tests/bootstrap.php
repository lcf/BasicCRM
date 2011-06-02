<?php
/**
 * Tests suite entry point
 */
// Define path to application directory
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Ensure is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/models',
    get_include_path(),
)));

// Registering the autoloader
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

ServiceLocator::setConfig(new Zend_Config_Ini(APPLICATION_PATH . '/configs/config.ini', 'tests'));