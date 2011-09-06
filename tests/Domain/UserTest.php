<?php
namespace Tests\Domain;

use Domain\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /*
     * has a unique identifier for reference
     */
    public function testHasId()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        // just checking that the attribute exists
        $this->assertAttributeEmpty('id', $user);
    }

    /*
     * has a name
     */
    public function testHasName()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $this->assertAttributeEquals('John Smith', 'name', $user);
    }

    /*
     * has valid email
     * aspect: has email
     */
    public function testHasEmail()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $this->assertAttributeEquals('valid-email@example.com', 'email', $user);
    }

    /*
     * has valid email
     * aspect: email must be valid
     */
    public function testHasValidEmail()
    {
        $this->setExpectedException('DomainException', 'Email is not valid');
        new User('invalid-email-example.com', 'John Smith', '123456');
    }

    /*
     * there is a way to set a password
     * aspect: not shorter than 6 characters
     */
    public function testSetPasswordNotShorterThan6Characters()
    {
        $this->setExpectedException('DomainException', 'Wrong password length');
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $user->setPassword('12345');
    }

    /*
     * there is a way to set a password
     * aspect: hashed
     */
    public function testSetPasswordHashed()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $user->setPassword('abcdef');
        $this->assertAttributeEquals(sha1('abcdef'), 'passwordHash', $user);
    }

    /*
     * has a password set on creation
     */
    public function testHasPasswordSetOnCreation()
    {
        $user = new User('valid-email@example.com', 'John Smith', '12345678');
        $this->assertAttributeEquals(sha1('12345678'), 'passwordHash', $user);
    }

    /*
     * may be either admin or not admin
     * aspect: may be admin
     */
    public function testMayBeAdmin()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456', true);
        $this->assertAttributeEquals(true, 'isAdmin', $user);
    }

    /*
     * may be either admin or not admin
     * aspect: may be admin
     */
    public function testMayBeNotAdmin()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456', false);
        $this->assertAttributeEquals(false, 'isAdmin', $user);
    }

    /*
     * is not admin by default
     */
    public function testIsNotAdminByDefault()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $this->assertAttributeEquals(false, 'isAdmin', $user);
    }

    /*
     * belongs to a single company
     * aspect: belongs to a company
     */
    public function testBelongsToCompany()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        /*
         * We use PHPUnit's mock builder to create Companies
         * because it's not the Company we're testing now and
         * we don't need real ones.
         *
         * PHPUnit's mock builder is explained in the
         * official manual.
         */
        $company = $this->getMock('Domain\Company', array(), array(), '', false);
        $user->setCompany($company);
        $this->assertAttributeEquals($company, 'company', $user);
    }


    /*
     * there is a way to find out whether a user is activated
     *     user is considered activated if the company they're in is activated
     */
    public function testIsActivated()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $company = $this->getMock('Domain\Company', array(), array(), '', false);
        $company->expects($this->once())
                ->method('isActivated')
                ->will($this->returnValue(true));
        $user->setCompany($company);
        $this->assertTrue($user->isActivated());
        // not activated
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $company = $this->getMock('Domain\Company', array(), array(), '', false);
        $company->expects($this->once())
                ->method('isActivated')
                ->will($this->returnValue(false));
        $user->setCompany($company);
        $this->assertFalse($user->isActivated());
    }

    /*
     * belongs to a single company
     * (there will be an error on attempt to associate a user with
     *  more than just one company)
     */
    public function testBelongsToSingleCompany()
    {
        $this->setExpectedException('DomainException', 'The user already belongs to a company');
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $companyMockBuilder = $this->getMockBuilder('Domain\Company')->disableOriginalConstructor();
        $company1 = $companyMockBuilder->getMock();
        $company2 = $companyMockBuilder->getMock();

        $user->setCompany($company1);
        $user->setCompany($company2);
    }

    /*
     * there is a way to define whether a user is an admin or not
     */
    public function testIsAdmin()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456', true);
        $this->assertTrue($user->isAdmin());
    }

    /*
     * there is a way to find out user name
     */
    public function testGetName()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456', true);
        $this->assertEquals('John Smith', $user->getName());
    }

    /*
     * there is a way to find out user email
     */
    public function testGetEmail()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456', true);
        $this->assertEquals('valid-email@example.com', $user->getEmail());
    }

    /*
     * there is a way to define whether a user is an admin or not
     */
    public function testIsNotAdmin()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456', false);
        $this->assertFalse($user->isAdmin());
    }
}

