<?php
namespace Tests\Domain;

use Domain\Subscription;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    // TODO: add constraints as they are in the domain model description
    // and add which aspect of them you're testing
    public function testHasName()
    {
        $subscription = new Subscription();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('name', $subscription);
    }
    
    public function testHasId()
    {
        $subscription = new Subscription();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('id', $subscription);
    }
}

