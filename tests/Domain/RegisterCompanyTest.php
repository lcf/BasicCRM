<?php
namespace Tests\Domain;

/**
 * We'll need the following annotation in tests where we change static variables of our ServiceLocator class
 * so they'll be restored afterwards.
 * It's not really a problem, because remember:
 * ServiceLocator is the only class with anything "static" we have.
 *
 * @backupStaticAttributes enabled
 */
class RegisterCompanyTest extends \PHPUnit_Framework_TestCase
{
    // TODO: don't forget to tell that they can mix and change order
    // just need to maintain consitency
    // changes should go to all places

    /**
     * We'll create some simple mocks for every test to configure
     *
     * @return void
     */
    protected function setUp()
    {
        $subscriptionRepository = $this->getMockBuilder('Doctrine\Orm\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $em = $this->getMockBuilder('Doctrine\Orm\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        \ServiceLocator::setSubscriptionRepository($subscriptionRepository);
        \ServiceLocator::setEm($em);
    }

    /**
     * @todo add getCompanyService to the ServiceLocator
     * @return \Services\CompanyService
     */
    protected function _getService()
    {
        return new \Services\CompanyService();
    }

    // ----------------------------------------------------------------------------------

    /*
     * finds the subscription plan by its identifier in the data storage
     */
    public function testFindSubscriptionPlanByItsId()
    {
        $subscriptionId = 12;
        \ServiceLocator::getSubscriptionsRepository()
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($subscriptionId))
            ->will($this->returnValue($this->getMock('Domain\Subscription')));

        $this->_getService()->registerCompany($subscriptionId, 'Test Company', 'John Smith', 'valid-email@example.com', '123456', '123456');
    }

    /*
     * error if the plan is for some reason not found
     */
    public function testSubscriptionPlanNotFound()
    {
        $this->setExpectedException('DomainException', 'Subscription is not found');
        \ServiceLocator::getSubscriptionsRepository()
            ->expects($this->once())
            ->method('find')
            ->with($this->anything())
            ->will($this->returnValue(null)); // null means not found

        $this->_getService()->registerCompany(12, 'Test Company', 'John Smith', 'valid-email@example.com', '123456', '123456');
    }

    /*
     * error if two passwords provided are not equal
     */
    public function testPasswordsMustBeEqual()
    {
        \ServiceLocator::getSubscriptionsRepository()
            ->expects($this->once())
            ->method('find')
            ->with($this->anything())
            ->will($this->returnValue($this->getMock('Domain\Subscription')));
        $this->setExpectedException('DomainException', 'Passwords are not equal');
        $this->_getService()->registerCompany(12, 'Test Company', 'John Smith', 'valid-email@example.com', '1234568', '1234567');
    }

    /*
     * creates new user admin account based on the email, name and password provided
     * creates company based on company name provided, new admin user and the subscription plan found
     * saves the new company in the data storage
     */
    public function testPersistNewCompany()
    {
        \ServiceLocator::getSubscriptionsRepository()
            ->expects($this->once())
            ->method('find')
            ->with($this->anything())
            ->will($this->returnValue($this->getMock('Domain\Subscription')));

        \ServiceLocator::getEm()
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Domain\Company'));

        \ServiceLocator::getEm()
            ->expects($this->once())
            ->method('flush');

        $this->_getService()->registerCompany(12, 'Test Company', 'John Smith', 'valid-email@example.com', '1234567', '1234567');
    }
}

