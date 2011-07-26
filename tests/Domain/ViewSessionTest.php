<?php
namespace Tests\Domain;

class ViewSessionTest extends \PHPUnit_Framework_TestCase
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

        $this->getService()->viewSession(md5('valid-session-id'));
    }

    public function testReturnsSession()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));

        $this->assertEquals($session, $this->getService()->viewSession(md5('valid-session-id')));
    }
}

