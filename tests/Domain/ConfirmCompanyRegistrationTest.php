<?php
namespace Tests\Domain;

class ConfirmCompanyRegistrationTest extends \PHPUnit_Framework_TestCase
{
    protected $backupStaticAttributes = true;

    protected $companiesRepositoryMock;

    protected $emMock;

    /**
     * We'll create some simple mocks for every test to configure
     *
     * @return void
     */
    protected function setUp()
    {
        $this->emMock = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $repositoryMockBuilder = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                                      ->disableOriginalConstructor();
        $this->companiesRepositoryMock = $repositoryMockBuilder->getMock();
        \ServiceLocator::setEm($this->emMock);
        \ServiceLocator::setCompaniesRepository($this->companiesRepositoryMock);
    }

    /**
     * General expectation for subscription repository is to return one
     *
     * @return void
     */
//    protected function mockSubscriptionLookup()
//    {
//        $this->subscriptionsRepositoryMock
//            ->expects($this->once())
//            ->method('find')
//            ->with($this->anything())
//            ->will($this->returnValue($this->getMock('Domain\Subscription'))); // subscription is found
//    }

    /**
     * @return \Services\CompanyService
     */
    protected function getService()
    {
        return \ServiceLocator::getCompanyService();
    }

    // ----------------------------------------------------------------------------------

    /*
     * finds company by its identifier
     */
    public function testFindsCompanyByItsId()
    {
        $company = $this->getMockBuilder('Domain\Company')->disableOriginalConstructor()->getMock();
        $companyId = 12;
        $this->companiesRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($companyId))
            ->will($this->returnValue($company));

        $this->getService()->confirmCompanyRegistration($companyId, 'code');
    }

    /*
     * error if company isn't found
     */
    public function testCompanyNotFound()
    {
        $company = $this->getMockBuilder('Domain\Company')->disableOriginalConstructor()->getMock();
        $companyId = 12;
        $this->companiesRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($companyId))
            ->will($this->returnValue(null));
        $this->setExpectedException('DomainException', 'Company is not found');
        $this->getService()->confirmCompanyRegistration($companyId, 'code');
    }

    /*
     * activates company with the confirmation code given
     */
    public function testActivatesCompanyWithConfirmationCodeGiven()
    {
        $confirmationCode = 'asdfjksjdaflkjsdflkjasdf';
        $company = $this->getMockBuilder('Domain\Company')->disableOriginalConstructor()->getMock();
        $company->expects($this->once())
                ->method('activate')
                ->with($this->equalTo($confirmationCode));

        $this->companiesRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->anything())
            ->will($this->returnValue($company));

        $this->getService()->confirmCompanyRegistration(123, $confirmationCode);
    }

    /*
     * persists changes in the data storage
     */
    public function testPersistChanges()
    {
        $this->emMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Domain\Company'));
        $this->emMock
            ->expects($this->once())
            ->method('flush');
        $this->companiesRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->anything())
            ->will($this->returnValue($this->getMockBuilder('Domain\Company')
                                           ->disableOriginalConstructor()->getMock()));

        $this->getService()->confirmCompanyRegistration(123, 'code');

    }
}

