<?php
namespace Tests\Domain;

use Domain\Company;

class CompanyTest extends \PHPUnit_Framework_TestCase
{

    public function testEmptyTest()
    {
        $this->assertTrue(true);
    }
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

