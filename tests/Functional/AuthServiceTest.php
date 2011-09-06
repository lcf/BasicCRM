<?php
namespace Functional;

class AuthServiceTest extends \PHPUnit_Extensions_Database_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        // As we use random data fixtures and they may interfere
        // we drop our unit of work so it'll start over every time
        // and identity map will behave as expected
        \ServiceLocator::getEm()->getUnitOfWork()->clear();
    }

    protected function getConnection()
    {
        $pdo = \ServiceLocator::getDb()->getWrappedConnection();
        return $this->createDefaultDBConnection($pdo);
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/auth-service.xml');
    }

    protected function getSetUpOperation()
    {
        return new \PHPUnit_Extensions_Database_Operation_Composite(array(
            new \TestsExtensions\TruncateDatabaseOperation(),
            \PHPUnit_Extensions_Database_Operation_Factory::INSERT()
        ));
    }

    public function testLoginUser()
    {
        $session = \ServiceLocator::getAuthService()->loginUser('john-smith@example.com', '1234567');
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        // check if a session record was created for the user
        $actual->addTable('sessions');
        $table = $actual->getTable('sessions');
        $this->assertEquals(1, $table->getRowCount());
        $this->assertEquals(1, $table->getValue(0, 'user_id'));
        $this->assertEquals($session->getId(), $table->getValue(0, 'id'));
    }

    public function testChangeUserPassword()
    {
        $session = \ServiceLocator::getAuthService()->loginUser('john-smith@example.com', '1234567');
        \ServiceLocator::getAuthService()->changeUserPassword($session->getId(), '1234567', 'abcdefgh', 'abcdefgh');
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actual->addTable('users');
        $table = $actual->getTable('users');
        $this->assertEquals(sha1('abcdefgh'), $table->getValue(0, 'password_hash'));
    }

    public function testViewSession()
    {
        // just add new session to the db and check if the same is retrieved
        $session = \ServiceLocator::getAuthService()->loginUser('john-smith@example.com', '1234567');
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actual->addTable('sessions');
        $table = $actual->getTable('sessions');
        $modified = $table->getValue(0, 'modified');
        sleep(3); // assuming user doing something...
        $readSession = \ServiceLocator::getAuthService()->viewSession($session->getId());
        // checking whether we retrieved the same session, just like this
        $this->assertEquals($session->getId(), $readSession->getId());
        // checking whether modified has changed
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actual->addTable('sessions');
        $table = $actual->getTable('sessions');
        $this->assertGreaterThan($modified, $table->getValue(0, 'modified'));
    }

    public function testLogoutUser()
    {
        $session = \ServiceLocator::getAuthService()->loginUser('john-smith@example.com', '1234567');
        // there should be no records in the session table after we logout
        \ServiceLocator::getAuthService()->logoutUser($session->getId());
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actual->addTable('sessions');
        $table = $actual->getTable('sessions');
        $this->assertEquals(0, $table->getRowCount());
    }
}