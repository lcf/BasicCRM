<?php
namespace Tests\Domain;

class LoginUserTest extends \PHPUnit_Framework_TestCase
{
    protected $backupStaticAttributes = true;

    protected $usersRepositoryMock;

    protected $emMock;

    /**
     * We'll create some simple mocks for every test to configure
     *
     * @return void
     */
    protected function setUp()
    {
        $this->emMock = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $repositoryMockBuilder = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                                      ->disableOriginalConstructor();
        $this->usersRepositoryMock = $repositoryMockBuilder->setMethods(array('findOneByEmail'))->getMock();
        \ServiceLocator::setEm($this->emMock);
        \ServiceLocator::setUsersRepository($this->usersRepositoryMock);
    }

    /**
     * General expectation for users repository is to find a user
     *
     * @return void
     */
    protected function mockUserLookupByEmail()
    {
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with($this->anything())
             ->will($this->returnValue(true));
        $user->expects($this->once())
             ->method('isActivated')
             ->will($this->returnValue(true));
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('smith@example.com')
            ->will($this->returnValue($user));
    }

    /**
     * @return \Services\AuthService
     */
    protected function getService()
    {
        return \ServiceLocator::getAuthService();
    }

    // ----------------------------------------------------------------------------------

    /*
     * finds user by email passed
     */
    public function testFindsUserByEmail()
    {
        $this->mockUserLookupByEmail();
        $this->getService()->loginUser('smith@example.com', '123456');
    }

    /*
     * error if user is not found
     */
    public function testUserNotFound()
    {
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('not-exist@example.com')
            ->will($this->returnValue(null));
        $this->setExpectedException('DomainException', 'User with such email is not registered');
        $this->getService()->loginUser('not-exist@example.com', '123456');
    }

    /*
     * error if found user in is not activated
     */
    public function testUserNotActivated()
    {
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isActivated')
             ->will($this->returnValue(false));
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('smith@example.com')
            ->will($this->returnValue($user));

        $this->setExpectedException('DomainException', 'User is not activated');
        $this->getService()->loginUser('smith@example.com', 'invalid-pass');
    }

    /*
     * checks if password is a valid one
     */
    public function testChecksPassword()
    {
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isActivated')
             ->will($this->returnValue(true));
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with('123456')
             ->will($this->returnValue(true));
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('smith@example.com')
            ->will($this->returnValue($user));

        $this->getService()->loginUser('smith@example.com', '123456');
    }

    /*
     * error if password is not valid
     */
    public function testPasswordInvalid()
    {
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isActivated')
             ->will($this->returnValue(true));
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with($this->anything())
             ->will($this->returnValue(false));
        $this->usersRepositoryMock
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('smith@example.com')
            ->will($this->returnValue($user));

        $this->setExpectedException('DomainException', 'Password is wrong');
        $this->getService()->loginUser('smith@example.com', 'invalid-pass');
    }

    /*
     * starts new session for the user
     * persists session in the data storage
     */
    public function testPersistsNewSession()
    {
        $this->mockUserLookupByEmail();
        $this->emMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Domain\Session'));

        $this->emMock
            ->expects($this->once())
            ->method('flush');

        $this->getService()->loginUser('smith@example.com', 'valid-pass');
    }

    /*
     * returns new session data
     */
    public function testReturnsSession()
    {
        $this->mockUserLookupByEmail();
        $this->assertInstanceOf('Domain\Session', $this->getService()->loginUser('smith@example.com', 'valid-pass'));
    }
}

