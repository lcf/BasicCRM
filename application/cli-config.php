<?php
/**
 * Doctrine console tools entry point
 */
// Define path to application directory
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('APPLICATION_ENV', 'web');

// Ensure is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/models',
    APPLICATION_PATH . '/../tests',
    get_include_path(),
)));

// Registering the autoloader
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

$helperSet = new Symfony\Component\Console\Helper\HelperSet(array(
    'em' => new Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper(ServiceLocator::getEm()),
));