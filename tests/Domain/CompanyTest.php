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
    protected function getCompany()
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
        $company = $this->getCompany();
        // just checking that the attribute exists
        $this->assertAttributeEmpty('id', $company);
    }

    /*
     * there is a way define a company's unique identifier
     */
    public function testGetId()
    {
        $company = $this->getCompany();
        $this->assertAttributeEquals($company->getId(), 'id', $company);
    }

    /*
     * has a not empty name
     * aspect: has a name
     */
    public function testHasName()
    {
        $company = $this->getCompany();
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
        $company = $this->getCompany();
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
        $company = $this->getCompany();
        $this->assertAttributeInternalType('boolean', 'isActivated', $company);
    }

    /*
     * is not activated by default
     */
    public function testIsNotActivatedByDefault()
    {
        $company = $this->getCompany();
        $this->assertAttributeEquals(false, 'isActivated', $company);
    }
    
    /*
     * there is a way to figure out whether a company is activated
     */
    public function testIsActivated()
    {
        $company = $this->getCompany();
        $this->assertFalse($company->isActivated());
        $salt = 'TestSalt';
        $confirmationCode = $company->getConfirmationCode($salt);
        $company->activate($confirmationCode, $salt);
        $this->assertTrue($company->isActivated());
    }

    /*
     * there is a way to calculate the code required for company registration confirmation
     *     1. to calculate the code security salt is required
     *     2. code is a hash function from company id, security salt and company name
     */
    public function testGetConfirmationCode()
    {
        $company = $this->getCompany();
        $id = \PHPUnit_Framework_Assert::readAttribute($company, 'id');
        $name = \PHPUnit_Framework_Assert::readAttribute($company, 'name');
        $salt = 'SomeRandomSalt';

        $confirmationCode = sha1($id . $salt . $name);
        $this->assertEquals($confirmationCode, $company->getConfirmationCode($salt));
    }

    /*
     * may be activated with a confirmation code
     */
    public function testMayBeActivatedWithConfirmationCode()
    {
        $company = $this->getCompany();
        $salt = 'TestSalt';
        $confirmationCode = $company->getConfirmationCode($salt);
        $company->activate($confirmationCode, $salt);
        $this->assertAttributeEquals(true, 'isActivated', $company);
    }

    /*
     * error if attempt to activate an already activated company
     */
    public function testCanBeActivatedOnlyOnce()
    {
        $company = $this->getCompany();
        $salt = 'TestSalt';
        $confirmationCode = $company->getConfirmationCode($salt);
        $company->activate($confirmationCode, $salt);
        // attempt to activate it again
        $this->setExpectedException('DomainException', 'Company\'s been activated already');
        $company->activate($confirmationCode, $salt);
    }

    /*
     * error if attempt to activate an already activated company
     */
    public function testValidConfirmationCode()
    {
        $company = $this->getCompany();
        $salt = 'TestSalt';
        $confirmationCode = $company->getConfirmationCode($salt);
        $this->setExpectedException('DomainException', 'Confirmation code is not valid');
        $company->activate($confirmationCode . 'wrong', $salt);
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

    /*
     * there is a way to add a user to a company
     * aspect: adds user to the collection of users belonging to it
     */
    public function testAddUser()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $subscription->expects($this->once())
                     ->method('getUsersLimit')
                     ->will($this->returnValue(30));
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);

        $user = new \Domain\User('another-valid-email@example.com', 'Alex Smith', '654321');
        $company->addUser($user);
        $users = new \Doctrine\Common\Collections\ArrayCollection(array($admin, $user));
        $this->assertAttributeEquals($users, 'users', $company);
    }

    /*
     * there is a way to add a user to a company
     * aspect: error if user is admin
     */
    public function testOnlyOneAdminAllowed()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);

        $user = new \Domain\User('another-valid-email@example.com', 'Alex Smith', '654321', true);
        $this->setExpectedException('DomainException', 'Only one administrator is allowed');
        
        $company->addUser($user);
    }

    /*
     * there is a way to add a user to a company
     * aspect: error if users limit for the company subscription plan is reached
     */
    public function testAddUserLimitExceeded()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $subscription->expects($this->once())
                     ->method('getUsersLimit')
                     ->will($this->returnValue(1));
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);

        $user = new \Domain\User('another-valid-email@example.com', 'Alex Smith', '654321', false);
        $this->setExpectedException('DomainException', 'Users limit reached');

        $company->addUser($user);
    }

    /*
     * there is a way to add a user to a company
     * aspect: associates user with the company
     */
    public function testAddUserAssociatesUserWithCompany()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $subscription->expects($this->once())
                     ->method('getUsersLimit')
                     ->will($this->returnValue(30));
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);

        $user = new \Domain\User('another-valid-email@example.com', 'Alex Smith', '654321');
        $company->addUser($user);
        $this->assertEquals($company, $user->getCompany());
    }

    /*
     * 2. removes admin rights from the current user
     * 3. grants admin privileges to the new user
     */
    public function testSwitchAdminTo()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $subscription->expects($this->once())
                     ->method('getUsersLimit')
                     ->will($this->returnValue(30));
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $user = new \Domain\User('another-valid-email@example.com', 'Alex Smith', '654321');
        // users get their IDs set when persisted. we pretend they were:
        $reflection = new \ReflectionObject($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($admin, 1);
        $property->setValue($user, 2);

        $company = new Company('Test Company', $subscription, $admin);
        $company->addUser($user);
        // switching
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($admin->isAdmin());
        $company->switchAdminTo(2);
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($admin->isAdmin());
    }

    public function testSwitchAdminToNewAdminNotFound()
    {
        $subscription = $this->getMock('Domain\Subscription');
        $subscription->expects($this->once())
                     ->method('getUsersLimit')
                     ->will($this->returnValue(30));
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $user = new \Domain\User('another-valid-email@example.com', 'Alex Smith', '654321');
        // users get their IDs set when persisted. we pretend they were:
        $reflection = new \ReflectionObject($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($admin, 1);
        $property->setValue($user, 2);
        $company = new Company('Test Company', $subscription, $admin);
        $company->addUser($user);
        $this->setExpectedException('DomainException', 'New administrator account is not found');
        $company->switchAdminTo(3);
    }
}