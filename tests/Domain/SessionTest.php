<?php
namespace Tests\Domain;

use Domain\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Helper method creating an instance of a session
     *
     * @return \Domain\Session
     */
    protected function getSession()
    {
        return new Session($this->getMock('Domain\User', array(), array(), '', false));
    }

    /*
     * has a unique identifier, string of 32 random characters, used to refer to it by clients
     */
    public function testHasId()
    {
        $session = $this->getSession();
        $this->assertEquals(32, strlen(self::readAttribute($session, 'id')));
    }

    /*
     * there is a way to find out a session identifier
     */
    public function testGetId()
    {
        $session = $this->getSession();
        $id = self::readAttribute($session, 'id');
        $this->assertEquals($id, $session->getId());
    }

    /*
     * has last used time, the time when it was created or used last time.
     */
    public function testHasModified()
    {
        $session = $this->getSession();
        $this->assertAttributeInstanceOf('\DateTime', 'modified', $session);
    }

    /*
     * there is a way to find out whether a session is valid or not
     *    1. session stays valid for one day since it was last used
     */
    public function testIsValid()
    {
        $session = $this->getSession();
        $this->assertTrue($session->isValid());
    }

    public function testIsNotValid()
    {
        $session = $this->getSession();
        // Faking the last used time for the session
        $reflection = new \ReflectionObject($session);
        $property = $reflection->getProperty('modified');
        $property->setAccessible(true);

        $modified = new \DateTime();
        $modified->sub(new \DateInterval('P2D'));
        $property->setValue($session, $modified);

        $this->assertFalse($session->isValid());
    }

    /*
     * there is a way to prolong session being valid.
     */
    public function testRefresh()
    {
        // create an invalid session first
        $session = $this->getSession();
        $reflection = new \ReflectionObject($session);
        $property = $reflection->getProperty('modified');
        $property->setAccessible(true);
        $modified = new \DateTime();
        $modified->sub(new \DateInterval('P2D'));
        $property->setValue($session, $modified);
        $this->assertFalse($session->isValid());

        // prolonging it
        $session->refresh();
        // checking whether it became valid
        $this->assertTrue($session->isValid());
    }

    /*
     * is associated with the user who started the session
     */
    public function testHasUser()
    {
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $session = new Session($user);
        $this->assertAttributeEquals($user, 'user', $session);
    }

    /*
     * there is a way to find out the user who started the session
     */
    public function testGetUser()
    {
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $session = new Session($user);
        $this->assertEquals($user, $session->getUser());
    }
}