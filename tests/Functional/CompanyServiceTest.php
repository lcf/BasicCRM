<?php
namespace Functional;

class CompanyServiceTest extends \PHPUnit_Extensions_Database_TestCase
{
    protected function getConnection()
    {
        $pdo = \ServiceLocator::getDb()->getWrappedConnection();
        return $this->createDefaultDBConnection($pdo, 'basiccrm_tests');
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/company-service.xml');
    }

    protected function getSetUpOperation()
    {
        return new \PHPUnit_Extensions_Database_Operation_Composite(array(
            new \TestsExtensions\TruncateDatabaseOperation(),
            \PHPUnit_Extensions_Database_Operation_Factory::INSERT()
        ));
    }

    public function testRegisterCompany()
    {
        $companyService = new \Services\CompanyService();
        $companyService->registerCompany(1, 'New Company', 'John Smith', 'john-smith@example.com', '1234567', '1234567');
        $expected = $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/register-company.xml');
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actual->addTable('users');
        $actual->addTable('companies');
        $this->assertDataSetsEqual($expected, $actual);
    }
}