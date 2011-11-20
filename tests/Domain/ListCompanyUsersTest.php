<?php
namespace Tests\Domain;

class ListCompanyUsers extends \PHPUnit_Framework_TestCase
{
    protected $backupStaticAttributes = true;

    protected $sessionsRepositoryMock;

    protected $emMock;

    /**
     * We'll create some simple mocks for every test to configure
     *
     * @return void
     */
    protected function setUp()
    {
        $this->emMock = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $repositoryMockBuilder = $this->getMockBuilder('Domain\SessionsRepository')
                                      ->disableOriginalConstructor();
        $this->sessionsRepositoryMock = $repositoryMockBuilder->setMethods(array('getValid'))->getMock();
        \ServiceLocator::setEm($this->emMock);
        \ServiceLocator::setSessionsRepository($this->sessionsRepositoryMock);
    }
    
    /**
     * @return \Services\CompanyService
     */
    protected function getService()
    {
        return \ServiceLocator::getCompanyService();
    }

    // ----------------------------------------------------------------------------------

    public function testListsUsers()
    {
        $session = $this->getMock('Domain\Session', array(), array(), '', false);
        $user = $this->getMock('Domain\User', array(), array(), '', false);
        $company = $this->getMock('Domain\Company', array(), array(), '', false);
        $company->expects($this->once())
                ->method('getUsers');

        $user->expects($this->once())
             ->method('getCompany')
             ->will($this->returnValue($company));
        $session->expects($this->atLeastOnce())
                ->method('getUser')
                ->will($this->returnValue($user));
        $this->sessionsRepositoryMock
            ->expects($this->once())
            ->method('getValid')
            ->with(md5('valid-session-id'))
            ->will($this->returnValue($session));

        $this->getService()->listCompanyUsers(md5('valid-session-id'));
    }
}

