<?php
namespace Tests\Domain;

use Domain\Session;

class SessionsRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $emMock;

    /**
     * @var \Domain\SessionsRepository
     */
    protected $sessionsRepository;

    protected function setUp()
    {
        $this->emMock = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        // Faking all methods but ones that we test
        $this->sessionsRepository = $this->getMock(
            'Domain\SessionsRepository',
            array('find'),
            array($this->emMock, $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array(), array(), '', false)));
    }

    /**
     * Helper method creating an instance of a session
     *
     * @return \Domain\Session
     */
    protected function getSession()
    {
        return new Session($this->getMock('Domain\User', array(), array(), '', false));
    }

    protected function mockSessionLookUp()
    {
        $this->sessionsRepository
            ->expects($this->once())
            ->method('find')
            ->with(md5('existing session id'))
            ->will($this->returnValue($this->getSession()));
    }

    // ----------------------------------------------------------------------------------

    /*
     * getValid()
     * there is a way to retrieve a valid session by its id
     */

    /*
     * finds session by its id
     */
    public function testGetValidFindsSessionById()
    {
        $this->mockSessionLookUp();
        $this->sessionsRepository->getValid(md5('existing session id'));
    }

    /*
     * error if session isn't found
     */
    public function testGetValidSessionNotFound()
    {
        $this->sessionsRepository
            ->expects($this->once())
            ->method('find')
            ->with(md5('non existing session id'))
            ->will($this->returnValue(null)); // null means not found

        $this->setExpectedException('DomainException', 'Session is not found');
        $this->sessionsRepository->getValid(md5('non existing session id'));
    }

    /*
     * error if session isn't valid
     */
    public function testGetValidSessionInvalid()
    {
        $session = $this->getSession();
        $reflection = new \ReflectionObject($session);
        $property = $reflection->getProperty('modified');
        $property->setAccessible(true);
        $modified = new \DateTime();
        $modified->sub(new \DateInterval('P2D'));
        $property->setValue($session, $modified);
        $this->assertFalse($session->isValid()); // making sure it's become invalid now

        $this->sessionsRepository
            ->expects($this->once())
            ->method('find')
            ->with(md5('existing session id'))
            ->will($this->returnValue($session));

        $this->setExpectedException('DomainException', 'Session is no longer valid');
        $this->sessionsRepository->getValid(md5('existing session id'));
    }

    /*
     * refreshes session
     */
    public function testGetValidSessionRefreshed()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $session->expects($this->once())
                ->method('isValid')
                ->will($this->returnValue(true));
        $session->expects($this->once())
                ->method('refresh');
        $this->sessionsRepository
            ->expects($this->once())
            ->method('find')
            ->with(md5('existing session id'))
            ->will($this->returnValue($session));

        $this->sessionsRepository->getValid(md5('existing session id'));
    }

    /*
     * persists changes
     */
    public function testGetValidPersistsChanges()
    {
        $this->mockSessionLookUp();
        $this->emMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Domain\Session'));
        $this->sessionsRepository->getValid(md5('existing session id'));
    }

    /*
     * returns session
     */
    public function testGetValidReturnsSession()
    {
        $session = $this->getSession();
        $this->sessionsRepository
            ->expects($this->once())
            ->method('find')
            ->with(md5('existing session id'))
            ->will($this->returnValue($session));

        $this->assertEquals($session, $this->sessionsRepository->getValid(md5('existing session id')));
    }
}