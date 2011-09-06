<?php
namespace Tests\Domain;

use Domain\Subscription;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    /*
     * has a name
     */
    public function testHasName()
    {
        $subscription = new Subscription();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('name', $subscription);
    }

    /*
     * has an id
     */
    public function testHasId()
    {
        $subscription = new Subscription();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('id', $subscription);
    }

    /*
     * has a certain number of users allowed
     */
    public function testHasUsersLimit()
    {
        $subscription = new Subscription();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('usersLimit', $subscription);
    }

    /*
     * has a certain number of clients allowed
     */
    public function testHasClientsLimit()
    {
        $subscription = new Subscription();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('usersLimit', $subscription);
    }

    /*
     * there is a way to get users limit
     */
    public function testGetUsersLimit()
    {
        $subscription = new Subscription;
        $reflectionObject = new \ReflectionObject($subscription);
        $property = $reflectionObject->getProperty('usersLimit');
        $property->setAccessible(true);
        $property->setValue($subscription, 30);
        $this->assertEquals(30, $subscription->getUsersLimit());
    }

    /*
     * there is a way to get clients limit
     */
    public function testGetClientsLimit()
    {
        $subscription = new Subscription;
        $reflectionObject = new \ReflectionObject($subscription);
        $property = $reflectionObject->getProperty('clientsLimit');
        $property->setAccessible(true);
        $property->setValue($subscription, 300);
        $this->assertEquals(300, $subscription->getClientsLimit());
    }
}

