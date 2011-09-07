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

    // ----------------------------------------------------------------------------------

    /*
     * gets valid session by its identifier
     */
    public function testGetsValidSession()
    {
        $this->mockUserLookupByEmail();
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
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->anything())
            ->will($this->returnValue($this->getMock('Domain\User', array(), array(), '', false)));
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
        $this->setExpectedException('DomainException', 'User with email john@example.com has been already registered');
        $this->getService()->addUserToCompany(md5('valid-session-id'), 'John Smith', 'john@example.com');
    }

    public function testCreatesUser()
    {
        // TODO: refactor objects creation, recheck older scenarios and refactor them as well
        // either use ServiceLocator::create('ClassName', list of args);
        // or just static methods like User::new(list of args)
        // both can be mocked. first is a standard, think about possible problems with the second one
        // which seem to be nicer :)
    }

}

