<?php
class ServiceLocator 
{
    
    protected static $em;

    /**
     * @var Doctrine\DBAL\Connection
     */
    protected static $db;

    protected static $cache;

    /**
     * @var Zend_Controller_Front
     */
    protected static $frontController;

    /**
     * @var Zend_Config
     */
    protected static $config;

    /**
     * @var Services\CompanyService
     */
    protected static $companyService;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected static $subscriptionRepository;

    public static function getCompanyService()
    {
        if (self::$companyService === null) {
            self::$companyService = new \Services\CompanyService();
        }

        return self::$companyService;
    }

    public static function getFrontController()
    {
        if (self::$frontController === null) {
            self::$frontController = Zend_Controller_Front::getInstance()
                ->setControllerDirectory(APPLICATION_PATH . '/controllers');
            Zend_Layout::startMvc(
                array('layoutPath' => APPLICATION_PATH . '/views/layouts', 'layout' => 'index'));
        }

        return self::$frontController;
    }

    public static function getDb()
    {
        if (self::$db === null) {
            $dbConfig = self::getConfig()->get('doctrine')->get('db');
            self::$db = Doctrine\DBAL\DriverManager::getConnection($dbConfig->toArray());
        }

        return self::$db;
    }

    public static function getCache()
    {
        if (self::$cache === null) {
            $doctrineConfig = self::getConfig()->get('doctrine');
            $cacheClass = $doctrineConfig->get('cacheClass');
            self::$cache = new $cacheClass;
        }

        return self::$cache;
    }

    /**
     * @return Zend_Config
     */
    public static function getConfig()
    {
        if (self::$config === null) {
            self::$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/config.ini', 'main');
        }

        return self::$config;
    }

    public static function setConfig(Zend_Config $config)
    {
        self::$config = $config;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public static function getEm()
    {
        if (self::$em === null) {
            $cache = self::getCache();
            $db = self::getDb();
            $config = new \Doctrine\ORM\Configuration();
            $config->setMetadataCacheImpl($cache);
            $config->setQueryCacheImpl($cache);
            $config->setMetadataDriverImpl(
                $config->newDefaultAnnotationDriver(APPLICATION_PATH . '/models'));
            $config->setProxyDir(APPLICATION_PATH . '/models/Infrastructure/Proxies');
            $config->setProxyNamespace('Infrastructure\Proxies');
            $config->setAutoGenerateProxyClasses(false);
            self::$em = \Doctrine\ORM\EntityManager::create($db, $config);
        }

        return self::$em;
    }

    public static function setEm(Doctrine\Orm\EntityManager $em)
    {
        self::$em = $em;
    }

    public static function setSubscriptionRepository(Doctrine\Orm\EntityRepository $repository)
    {
        self::$subscriptionRepository = $repository;
    }

    public static function getSubscriptionsRepository()
    {
        if (self::$subscriptionRepository === null) {
            self::$subscriptionRepository = self::getEm()->getRepository('\Domain\Subscription');
        }
        
        return self::$subscriptionRepository;
    }
}
