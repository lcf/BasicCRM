<?php
namespace Functional;

class CompanyServiceTest extends \PHPUnit_Extensions_Database_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->cleanTempFilesDir();
    }

    protected function getConnection()
    {
        $pdo = \ServiceLocator::getDb()->getWrappedConnection();
        return $this->createDefaultDBConnection($pdo);
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

    protected function _registerCompany()
    {
        $companyService = \ServiceLocator::getCompanyService();
        $companyService->registerCompany(1, 'New Company', 'John Smith',
                                         'john-smith@example.com', '1234567', '1234567');
    }

    protected function cleanTempFilesDir($tempFilesDir = null)
    {
        if ($tempFilesDir === null) {
            $tempFilesDir = APPLICATION_PATH . '/../tests/_files';
        }
        foreach (scandir($tempFilesDir) as $entry) {
            if ($entry != '.' && $entry != '..') {
                $name = $tempFilesDir . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($name)) {
                    $this->cleanTempFilesDir($name);
                    rmdir($name);
                } else {
                    unlink($name);
                }
            }
        }
    }

    protected function getMailMessageText()
    {
        $tempFilesDir = APPLICATION_PATH . '/../tests/_files';
        $content = null;
        foreach (scandir($tempFilesDir) as $entry) {
            if ($entry != '.' && $entry != '..') {
                $content =  file_get_contents($tempFilesDir. DIRECTORY_SEPARATOR . $entry);
                break;
            }
        }
        if ($content) {
            list(, $body) = explode("\r\n\r\n", $content, 2);
            return quoted_printable_decode($body);
        } else {
            $this->fail('No files found');
        }
    }

    public function testRegisterCompany()
    {
        $this->_registerCompany();
        $expected = $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/register-company.xml');
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        // we're listing tables that matter 
        $actual->addTable('users');
        $actual->addTable('companies');
        $this->assertDataSetsEqual($expected, $actual);
        // also checking whether the confirmation email has been sent and contains expected link
        $salt = \ServiceLocator::getDomainConfig()->get('confirmationCodeSalt');
        $link = '/company/confirm/id/1/code/' . sha1(1 . $salt . 'New Company');
        $this->assertContains($link, $this->getMailMessageText());
    }

    public function testConfirmCompanyRegistration()
    {
        $this->_registerCompany();
        $companyService = \ServiceLocator::getCompanyService();
        $salt = \ServiceLocator::getDomainConfig()->get('confirmationCodeSalt');
        $confirmationCode = sha1(1 . $salt . 'New Company');
        $companyService->confirmCompanyRegistration(1, $confirmationCode);
        $expected = $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/confirm-company-registration.xml');
        $actual = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        // we're listing tables that matter
        $actual->addTable('companies');
        $this->assertDataSetsEqual($expected, $actual);
    }
}