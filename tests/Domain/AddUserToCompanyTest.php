<?php
namespace Tests\Domain;

class AddUserToCompanyTest extends \PHPUnit_Framework_TestCase
{
    protected $backupStaticAttributes = true;

    protected $sessionsRepositoryMock;

    protected $usersRepositoryMock;

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
        $repositoryMockBuilder = $this->getMockBuilder('Domain\SessionsRepository')
                                      ->disableOriginalConstructor();
        $this->sessionsRepositoryMock = $repositoryMockBuilder->setMethods(array('getValid'))->getMock();
        $this->emMock = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $this->mailerMock = $this->getMock('Infrastructure\Mailer', array(), array(), '', false);
        $repositoryMockBuilder = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                                      ->disableOriginalConstructor();
        $this->usersRepositoryMock = $repositoryMockBuilder->setMethods(array('findByEmail'))->getMock();
        \ServiceLocator::setEm($this->emMock);
        \ServiceLocator::setMailer($this->mailerMock);
        \ServiceLocator::setUsersRepository($this->usersRepositoryMock);
        \ServiceLocator::setSessionsRepository($this->sessionsRepositoryMock);
    }

    /**
     * @return \Services\CompanyService
     */
    protected function getService()
    {
        return \ServiceLocator::getCompanyService();
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

    protected function mockSessionLookup()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isAdmin')
             ->will($this->returnValue(true));
        $user->expects($this->once())
             ->method('getCompany')
             ->will($this->returnValue($this->getMock('Domain\Company', array(), array(), '', false)));
        $session->expects($this->atLeastOnce())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));
    }

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

    // ----------------------------------------------------------------------------------

    public function testGetsValidSession()
    {
        $this->mockUserLookupByEmail();
        $this->mockSessionLookup();

        $this->getService()->addUserToCompany(md5('valid-session-id'), 'John Smith', 'john@example.com');
    }

    public function testCurrentUserMustBeAdmin()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isAdmin')
             ->will($this->returnValue(false));
        $session->expects($this->atLeastOnce())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));
        $this->setExpectedException('DomainException', 'Only admin can add new users');
        $this->getService()->addUserToCompany(md5('valid-session-id'), 'John Smith', 'john@example.com');
    }

    public function testEmailMustBeUnique()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isAdmin')
             ->will($this->returnValue(true));
        $session->expects($this->atLeastOnce())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->anything())
            ->will($this->returnValue($this->getMock('Domain\User', array(), array(), '', false)));
        $this->setExpectedException('DomainException', 'User with email john@example.com has been already registered');
        $this->getService()->addUserToCompany(md5('valid-session-id'), 'John Smith', 'john@example.com');
    }

    public function testAddsUserToCompany()
    {
        $this->mockUserLookupByEmail();
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isAdmin')
             ->will($this->returnValue(true));
        $company = $this->getMock('Domain\Company', array(), array(), '', false);
        $user->expects($this->once())
             ->method('getCompany')
             ->will($this->returnValue($company));
        $session->expects($this->atLeastOnce())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));
        $company->expects($this->once())
                ->method('addUser')
                ->with($this->isInstanceOf('Domain\User'));
        $this->getService()->addUserToCompany(md5('valid-session-id'), 'John Smith', 'john@example.com');
    }

    public function testPersistNewUser()
    {
        $this->mockUserLookupByEmail();
        $this->mockSessionLookup();
        $this->mockEmTransactional();
        
        $this->emMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Domain\User'));

        $this->emMock
            ->expects($this->once())
            ->method('flush');

        $this->getService()->addUserToCompany(md5('valid-session-id'), 'John Smith', 'john@example.com');
    }

    public function testSendsWelcomeEmail()
    {
        $this->mockUserLookupByEmail();
        $this->mockSessionLookup();
        $this->mockEmTransactional();

        $this->mailerMock
            ->expects($this->once())
            ->method('newUserWelcome')
            ->with(
                $this->isInstanceOf('Domain\User'),
                $this->isType('string')
            );

        $this->getService()->addUserToCompany(md5('valid-session-id'), 'John Smith', 'john@example.com');
    }
}

