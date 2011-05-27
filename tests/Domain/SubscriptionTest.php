<?php
namespace Tests\Domain;

use Domain\Subscription;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
   public function testHasName()
   {
       // just checking that the attribute exists
       $subscription = new Subscription();
       $this->assertAttributeEmpty('name', $subscription);
   }
}

