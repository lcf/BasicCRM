<?php
/**
 * Tests suite entry point
 */

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('APPLICATION_ENV', 'tests');

set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/models',
    get_include_path(),
)));

// Registering the autoloader
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

// a workaround in order to make PhpUnit's @backupStaticAttributes
ServiceLocator::getConfig();