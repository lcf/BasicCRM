<?php
namespace Tests\Domain;

class ChangeUserPasswordTest extends \PHPUnit_Framework_TestCase
{
    protected $backupStaticAttributes = true;

    protected $sessionsRepositoryMock;

    protected $emMock;

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
        \ServiceLocator::setEm($this->emMock);
        \ServiceLocator::setSessionsRepository($this->sessionsRepositoryMock);
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
     * gets valid session by its identifier
     * gets current user from the session
     */
    public function testGetsValidSessionAndCurrentUser()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with($this->anything())
             ->will($this->returnValue(true));
        $session->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));

        $this->getService()->changeUserPassword(md5('valid-session-id'), '123456', 'abcdef', 'abcdef');
    }

    /*
     * error if provided current user's password is not valid
     */
    public function testCurrentPasswordInvalid()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with($this->anything())
             ->will($this->returnValue(false));
        $session->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));

        $this->setExpectedException('DomainException', 'Entered current password is not valid');
        $this->getService()->changeUserPassword(md5('valid-session-id'), '123456', 'abcdef', 'abcdef');
    }

    /*
     * error if new password provided twice is not repeated correctly
     */
    public function testNewPasswordsMustBeEqual()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with($this->anything())
             ->will($this->returnValue(true));
        $session->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));
        
        $this->setExpectedException('DomainException', 'Passwords are not equal');
        $this->getService()->changeUserPassword(md5('valid-session-id'), '123456', 'abcdefg', 'abcdefh');
    }

    /*
     * changes current user's password
     */
    public function testChangesCurrentUserPassword()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with($this->anything())
             ->will($this->returnValue(true));
        $user->expects($this->once())
             ->method('setPassword')
             ->with('abcdef');
        $session->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));

        $this->getService()->changeUserPassword(md5('valid-session-id'), '123456', 'abcdef', 'abcdef');
    }

    /*
     * saves the user in the data storage
     */
    public function testPersistsUser()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $user->expects($this->once())
             ->method('isPasswordValid')
             ->with($this->anything())
             ->will($this->returnValue(true));
        $user->expects($this->once())
             ->method('setPassword')
             ->with('abcdef');
        $session->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));


        $this->emMock
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->emMock
            ->expects($this->once())
            ->method('flush');

        $this->getService()->changeUserPassword(md5('valid-session-id'), '123456', 'abcdef', 'abcdef');
    }
}

