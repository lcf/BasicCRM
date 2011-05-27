<?php
namespace Tests\Domain;

use Domain\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testHasValidEmail()
    {
        $this->setExpectedException('DomainException', 'Email is not valid');
        new User('invalid-email-example.com', 'John Smith', '123456');
    }

    public function testHasPasswordNotShorterThan6Characters()
    {
        $this->setExpectedException('DomainException', 'Wrong password length');
        new User('valid-email@example.com', 'John Smith', '12345');
    }

    public function testHasPasswordHashed()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $this->assertAttributeEquals(sha1('123456'), 'passwordHash', $user);
    }

    public function testMayBeAdmin()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456', true);
        $this->assertAttributeEquals(true, 'isAdmin', $user);
    }
    
    public function testIsNotAdminByDefault()
    {
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $this->assertAttributeEquals(false, 'isAdmin', $user);
    }

    public function testBelongsToSingleCompany()
    {
        $this->setExpectedException('DomainException', 'The user already belongs to a company');
        $user = new User('valid-email@example.com', 'John Smith', '123456');
        $companyMockBuilder = $this->getMockBuilder('Domain\Company')
             ->disableOriginalConstructor();
        $company1 = $companyMockBuilder->getMock();
        $company2 = $companyMockBuilder->getMock();
        $user->setCompany($company1);
        $this->assertAttributeEquals($company1, 'company', $user);
        $user->setCompany($company2);
    }
}

