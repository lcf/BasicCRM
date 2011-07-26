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
     * @var Services\AuthService
     */
    protected static $authService;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected static $subscriptionRepository;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected static $usersRepository;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected static $sessionsRepository;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected static $companiesRepository;

    /**
     * @var Infrastructure\Mailer
     */
    protected static $mailer;

    /**
     * @return \Infrastructure\Mailer
     */
    public static function getMailer()
    {
        if (self::$mailer === null) {
            // getting the config for the mailer
            $mailConfig = self::getConfig()->get('mail');
            // particular transport class and transport options
            $transportClass = $mailConfig->get('transportClass');
            $options = $mailConfig->get('options');
            // this little stupidity here thanks to Zend_Mail_Transport design defects
            if ($transportClass == '\Zend_Mail_Transport_Smtp') {
                $sender = new \Zend_Mail_Transport_Smtp($options->get('host'), $options->toArray());
            } else {
                $sender = new $transportClass($options->toArray());
            }
            // Defining where templates for the mailer are located
            $builder = new \Zend_View(array('scriptPath' => APPLICATION_PATH . '/templates'));
            self::$mailer = new \Infrastructure\Mailer($sender, $builder,
            // also pass default from email and name to the mailer constructor
                $mailConfig->get('fromEmail'), $mailConfig->get('fromName'));
        }

        return self::$mailer;
    }

    public static function setMailer(Infrastructure\Mailer $mailer)
    {
        self::$mailer = $mailer;
    }

    public static function getCompanyService()
    {
        if (self::$companyService === null) {
            self::$companyService = new \Services\CompanyService();
        }

        return self::$companyService;
    }

    public static function getAuthService()
    {
        if (self::$authService === null) {
            self::$authService = new \Services\AuthService();
        }

        return self::$authService;
    }

    public static function getFrontController()
    {
        if (self::$frontController === null) {
            self::$frontController = Zend_Controller_Front::getInstance()
                ->setControllerDirectory(APPLICATION_PATH . '/controllers');
            Zend_Loader::loadClass('SessionActionHelper', APPLICATION_PATH . '/controllers/helpers');
            Zend_Controller_Action_HelperBroker::addHelper(new SessionActionHelper());
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

    /**
     * @return Zend_Config
     */
    public static function getDomainConfig()
    {
        return self::getConfig()->get('domain');
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
            self::$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/config.ini', APPLICATION_ENV, true);
            if (is_readable(APPLICATION_PATH . '/configs/config.local.ini')) {
                self::$config->merge(new Zend_Config_Ini(APPLICATION_PATH . '/configs/config.local.ini',
                                                         APPLICATION_ENV));
            }
            self::$config->setReadOnly();
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

    public static function setSubscriptionsRepository(Doctrine\Orm\EntityRepository $repository)
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

    public static function setUsersRepository(Doctrine\Orm\EntityRepository $repository)
    {
        self::$usersRepository = $repository;
    }

    public static function getUsersRepository()
    {
        if (self::$usersRepository === null) {
            self::$usersRepository = self::getEm()->getRepository('\Domain\User');
        }

        return self::$usersRepository;
    }

    public static function setSessionsRepository(Doctrine\Orm\EntityRepository $repository)
    {
        self::$sessionsRepository = $repository;
    }

    /**
     * @static
     * @return \Domain\SessionsRepository
     */
    public static function getSessionsRepository()
    {
        if (self::$sessionsRepository === null) {
            self::$sessionsRepository = self::getEm()->getRepository('\Domain\Session');
        }

        return self::$sessionsRepository;
    }

    public static function setCompaniesRepository(Doctrine\Orm\EntityRepository $repository)
    {
        self::$companiesRepository = $repository;
    }

    public static function getCompaniesRepository()
    {
        if (self::$companiesRepository === null) {
            self::$companiesRepository = self::getEm()->getRepository('\Domain\Company');
        }

        return self::$companiesRepository;
    }
}
