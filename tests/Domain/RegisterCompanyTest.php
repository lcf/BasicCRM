<?php
namespace Tests\Domain;

class RegisterCompanyTest extends \PHPUnit_Framework_TestCase
{
    protected $backupStaticAttributes = true;

    protected $usersRepositoryMock;

    protected $subscriptionsRepositoryMock;

    protected $emMock;

    protected $mailerMock;

    /**
     * We'll create some simple mocks for every test to configure
     *
     * @return void
     */
    protected function setUp()
    {
        $this->emMock = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $this->mailerMock = $this->getMock('Infrastructure\Mailer', array(), array(), '', false);
        $repositoryMockBuilder = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                                      ->disableOriginalConstructor();
        $this->subscriptionsRepositoryMock = $repositoryMockBuilder->getMock();
        $this->usersRepositoryMock = $repositoryMockBuilder->setMethods(array('findByEmail'))->getMock();
        \ServiceLocator::setEm($this->emMock);
        \ServiceLocator::setMailer($this->mailerMock);
        \ServiceLocator::setSubscriptionsRepository($this->subscriptionsRepositoryMock);
        \ServiceLocator::setUsersRepository($this->usersRepositoryMock);
    }

    /**
     * General expectation for subscription repository is to return one
     *
     * @return void
     */
    protected function mockSubscriptionLookup()
    {
        $this->subscriptionsRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->anything())
            ->will($this->returnValue($this->getMock('Domain\Subscription'))); // subscription is found
    }

    /**
     * General expectation for users repository is not to find anything
     *
     * @return void
     */
    protected function mockUserLookupByEmail()
    {
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->anything())
            ->will($this->returnValue(null)); // email is unique
    }

    /**
     * In order to properly test the code hidden inside the transactional closure
     *
     * @return void
     */
    protected function mockEmTransactional()
    {
        $em = $this->emMock;
        $this->emMock
            ->expects($this->once())
            ->method('transactional')
            ->with($this->anything())
            ->will($this->returnCallback(
                function($value) use($em) {$value($em);}));
    }

    /**
     * @return \Services\CompanyService
     */
    protected function getService()
    {
        return \ServiceLocator::getCompanyService();
    }

    // ----------------------------------------------------------------------------------

    /*
     * finds the subscription plan by its identifier in the data storage
     */
    public function testFindSubscriptionPlanByItsId()
    {
        $this->mockUserLookupByEmail();
        $subscriptionId = 12;
        $this->subscriptionsRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($subscriptionId))
            ->will($this->returnValue($this->getMock('Domain\Subscription')));

        $this->getService()->registerCompany($subscriptionId, 'Test Company', 'John Smith',
                                              'valid-email@example.com', '123456', '123456');
    }

    /*
     * error if the plan is for some reason not found
     */
    public function testSubscriptionPlanNotFound()
    {
        $this->subscriptionsRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->anything())
            ->will($this->returnValue(null)); // null means nothing found
        $this->setExpectedException('DomainException', 'Subscription is not found');
        $this->getService()->registerCompany(12, 'Test Company', 'John Smith',
                                              'valid-email@example.com', '123456', '123456');
    }

    /*
     * error if two passwords provided are not equal
     */
    public function testPasswordsMustBeEqual()
    {
        $this->mockSubscriptionLookup();
        $this->setExpectedException('DomainException', 'Passwords are not equal');
        $this->getService()->registerCompany(12, 'Test Company', 'John Smith',
                                              'valid-email@example.com', '1234568', '1234567');
    }

    /*
     * error if email is already registered in the system
     */
    public function testEmailMustBeUnique()
    {
        $this->mockSubscriptionLookup();
        $alreadyRegisteredEmail = 'not-unique-email@example.com';
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($alreadyRegisteredEmail)
            ->will($this->returnValue($this->getMock('Domain\User', array(), array(), '', false)));
        $this->setExpectedException('DomainException',
            'User with email ' . $alreadyRegisteredEmail . ' has been already registered');
        $this->getService()->registerCompany(12, 'Test Company', 'John Smith',
                                              $alreadyRegisteredEmail, '1234567', '1234567');
    }

    /*
     * creates new user admin account based on the email, name and password provided
     * creates company based on company name provided, new admin user and the subscription plan found
     * saves the new company in the data storage
     */
    public function testPersistNewCompany()
    {
        $this->mockUserLookupByEmail();
        $this->mockSubscriptionLookup();
        $this->mockEmTransactional();

        $this->emMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Domain\Company'));

        $this->emMock
            ->expects($this->once())
            ->method('flush');

        $this->getService()->registerCompany(12, 'Test Company', 'John Smith',
                                              'valid-email@example.com', '1234567', '1234567');
    }

    /*
     * sends out a confirmation email to confirm the email address
     */
    public function testSendsConfirmationEmail()
    {
        $this->mockUserLookupByEmail();
        $this->mockSubscriptionLookup();
        $this->mockEmTransactional();

        $this->mailerMock
            ->expects($this->once())
            ->method('registrationConfirmation')
            ->with($this->isInstanceOf('Domain\Company'));

        $this->getService()->registerCompany(12, 'Test Company', 'John Smith',
                                              'valid-email@example.com', '1234567', '1234567');
    }
}

