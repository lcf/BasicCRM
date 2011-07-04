<?php
namespace Tests\Domain;

use Domain\Company;

class CompanyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Helper method creating an instance of company
     *
     * @return \Domain\Company
     */
    protected function _getCompany()
    {
        $subscription = $this->getMock('Domain\Subscription');
        // We should create a mocked user here, too, but it's easier to create a real one.
        // In my projects I use my own version of Mock Builder. we'll get to it someday
        // (You can also try to take a look at "mockery" by Brady)
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        return new Company('Test Company', $subscription, $admin);
    }

    // ---------------------------------------------------------------------------------------

    /*
     * has a unique identifier for reference
     */
    public function testHasId()
    {
        $company = $this->_getCompany();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('id', $company);
    }

    /*
     * there is a way define a company's unique identifier
     */
    public function testGetId()
    {
        $company = $this->_getCompany();
        $this->assertAttributeEquals($company->getId(), 'id', $company);
    }

    /*
     * has a not empty name
     * aspect: has a name
     */
    public function testHasName()
    {
        $company = $this->_getCompany();
        // just checking that the attribute exists
        $this->assertAttributeEquals('Test Company', 'name', $company);
    }

    /*
     * has a not empty name
     * aspect: name can not be empty
     */
    public function testHasNotEmptyName()
    {
        $this->setExpectedException('DomainException', 'Company name cannot be empty');
        $subscription = $this->getMock('Domain\Subscription');
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        return new Company('', $subscription, $admin);
    }

    /*
     * has an associated subscription plan
     */
    public function testHasSubscription()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);
        $this->assertAttributeEquals($subscription, 'subscription', $company);
    }

    /*
     * has at least one user and that user must be administrator
     * aspect: has at least one user
     */
    public function testHasAtLeastOneUser()
    {
        $company = $this->_getCompany();
        $this->assertAttributeNotEmpty('users', $company);
    }

    /*
     * has at least one user and that user must be administrator
     * aspect: one user must be administrator
     */
    public function testOneUserMustBeAdmin()
    {
        $this->setExpectedException('DomainException', 'User must be a new admin in order to create a company');
        $subscription = $this->getMock('Domain\Subscription');
        $notAdmin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', false);
        new Company('Test Company', $subscription, $notAdmin);
    }

    /*
     * may be either activated or not activated
     */
    public function testMayBeActivatedOrNot()
    {
        $company = $this->_getCompany();
        $this->assertAttributeInternalType('boolean', 'isActivated', $company);
    }

    /*
     * is not activated by default
     */
    public function testIsNotActivatedByDefault()
    {
        $company = $this->_getCompany();
        $this->assertAttributeEquals(false, 'isActivated', $company);
    }

    /*
     * may be activated with a confirmation code
     */
    public function testMayBeActivatedWithConfirmationCode()
    {
        $company = $this->_getCompany();
        $confirmationCode = $company->getConfirmationCode();
        $company->activate($confirmationCode);
        $this->assertAttributeEquals(true, 'isActivated', $company);
    }

    /*
     * error if attempt to activate an already activated company
     */
    public function testCanBeActivatedOnlyOnce()
    {
        $company = $this->_getCompany();
        $confirmationCode = $company->getConfirmationCode();
        $company->activate($confirmationCode);
        // attempt to activate it again
        $this->setExpectedException('DomainException', 'Company\'s been activated already');
        $company->activate($confirmationCode);
    }

    /*
     * error if attempt to activate an already activated company
     */
    public function testValidConfirmationCode()
    {
        $company = $this->_getCompany();
        $confirmationCode = $company->getConfirmationCode();
        $this->setExpectedException('DomainException', 'Confirmation code is not valid');
        $company->activate($confirmationCode . 'wrong');
    }

    /*
     * has a collection of users belonging to it
     * aspect: has users
     */
    public function testHasUsers()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);
        $users = new \Doctrine\Common\Collections\ArrayCollection(array($admin));
        $this->assertAttributeEquals($users, 'users', $company);
    }

    /*
     * there is a way to figure out who's the administrator of a company
     */
    public function testGetAdmin()
    {
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $this->getMock('Domain\Subscription'), $admin);
        $this->assertEquals($admin, $company->getAdmin());
    }

    /*
     * has a collection of users belonging to it
     * aspect: users belonging to a company must be associated with the company
     */
    public function testBelongingUsersAssociated()
    {
        // here is we cheating again:
        // tru way is to create a mock of a User and set it expecting setCompany to be called upon
        $subscription = $this->getMock('Domain\Subscription');
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);
        $this->assertAttributeEquals($company, 'company', $admin);
    }
}