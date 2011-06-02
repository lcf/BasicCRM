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
}

