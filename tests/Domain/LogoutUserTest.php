<?php
namespace Tests\Domain;

class LogoutUserTest extends \PHPUnit_Framework_TestCase
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
     */
    public function testGetsValidSession()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));

        $this->getService()->logoutUser(md5('valid-session-id'));
    }

    /*
     * removes session from data storage
     */
    public function testRemovesSession()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));

        $this->emMock
            ->expects($this->once())
            ->method('remove')
            ->with($session);

        $this->emMock
            ->expects($this->once())
            ->method('flush');

        $this->getService()->logoutUser(md5('valid-session-id'));
    }
}

