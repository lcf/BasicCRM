<?php
namespace TestsExtensions;

/**
 * Instead of executing a truncate does two things
 * 1) deletes all records from every table in the set
 * 2) alters auto increment to 1 for everybody
 *
 * @package    TestsExtensions
 * @author     Alexander Steshenko <lcfsoft@gmail.com>
 * @copyright  2010 Alexander Steshenko <lcfsoft@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TruncateDatabaseOperation implements \PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{
    public function execute(\PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        // TODO: make it work with any possible order of tables coming

        /* @var $table PHPUnit_Extensions_Database_DataSet_ITable */
        foreach ($dataSet->getReverseIterator() as $table) {
            $query = "
                DELETE FROM {$connection->quoteSchemaObject($table->getTableMetaData()->getTableName())}
            ";

            try {
                $connection->getConnection()->query($query);
            } catch (\PDOException $e) {
                throw new \PHPUnit_Extensions_Database_Operation_Exception('CUSTOM_TRUNCATE', $query, array(), $table, $e->getMessage());
            }

            $query = "
                ALTER TABLE {$connection->quoteSchemaObject($table->getTableMetaData()->getTableName())} AUTO_INCREMENT=1
            ";

            try {
                $connection->getConnection()->query($query);
            } catch (\PDOException $e) {
                throw new \PHPUnit_Extensions_Database_Operation_Exception('CUSTOM_TRUNCATE', $query, array(), $table, $e->getMessage());
            }



        }
    }
}
