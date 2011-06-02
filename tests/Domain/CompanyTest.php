<?php
namespace Tests\Domain;

use Domain\Company;

class CompanyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Helper method creating an instance of company
     *
     * @return Domain\Company
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
     * has a collection of users belonging to it
     * aspect: users belonging to a company must be associated with the company
     */
    public function testNewUsersMayBeAdded()
    {
        // here is we cheating again:
        // tru way is to create a mock of a User and set it expecting setCompany to be called upon
        $subscription = $this->getMock('Domain\Subscription');
        $admin = new \Domain\User('valid-email@example.com', 'John Smith', '123456', true);
        $company = new Company('Test Company', $subscription, $admin);
        $this->assertAttributeEquals($company, 'company', $admin);
    }






    //        $users = new \Doctrine\Common\Collections\ArrayCollection(array($user));
        //$this->assertAttributeEquals($users, 'users', $company);


//    public function testHasName()
//    {
//
//        // TODO: may be here and for users we should check both HAS and VALID in two different tests
//        // test the existence, test the validity, yeah, I think so !
//        /// ALso this way we'll test Has Name for users, not we missed it!!!
//
//        $this->setExpectedException('DomainException', 'Email is not valid');
//        new User('invalid-email-example.com', 'John Smith', '123456');
//    }
//
//    public function testHasValidEmail()
//    {
//        $this->setExpectedException('DomainException', 'Email is not valid');
//        new User('invalid-email-example.com', 'John Smith', '123456');
//    }
//
//    public function testHasPasswordNotShorterThan6Characters()
//    {
//        $this->setExpectedException('DomainException', 'Wrong password length');
//        new User('valid-email@example.com', 'John Smith', '12345');
//    }
//
//    public function testHasPasswordHashed()
//    {
//        $user = new User('valid-email@example.com', 'John Smith', '123456');
//        $this->assertAttributeEquals(sha1('123456'), 'passwordHash', $user);
//    }
//
//    public function testMayBeAdmin()
//    {
//        $user = new User('valid-email@example.com', 'John Smith', '123456', true);
//        $this->assertAttributeEquals(true, 'isAdmin', $user);
//    }
//
//    public function testIsNotAdminByDefault()
//    {
//        $user = new User('valid-email@example.com', 'John Smith', '123456');
//        $this->assertAttributeEquals(false, 'isAdmin', $user);
//    }
//
//    public function testBelongsToSingleCompany()
//    {
//        $this->setExpectedException('DomainException', 'The user already belongs to a company');
//        $user = new User('valid-email@example.com', 'John Smith', '123456');
//        $companyMockBuilder = $this->getMockBuilder('\Domain\Company')
//             ->disableOriginalConstructor();
//        $company1 = $companyMockBuilder->getMock();
//        $company2 = $companyMockBuilder->getMock();
//        $user->setCompany($company1);
//        $this->assertAttributeEquals($company1, 'company', $user);
//        $user->setCompany($company2);
//    }
}
