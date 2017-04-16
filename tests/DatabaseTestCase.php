<?php
require('lib/constants.php');

class DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase {
    public $fixtures = array();
    private $conn = null;

    /*
     * Open database connection and create tables for each fixture.
     */
    public function setUp() {
        $conn = $this->getConnection();
        $pdo = $conn->getConnection();

        // set up tables
        $fixtureDataSet = $this->getDataSet($this->fixtures);
        list($fixtureMeta, $fixtureKeys) = $this->getDataSetMeta($this->fixtures);

        foreach($fixtureDataSet->getTableNames() as $table) {
            // drop table
            $pdo->exec("DROP TABLE IF EXISTS `$table`;");

            // recreate table
            $meta = $fixtureDataSet->getTableMetaData($table);
            $create = "CREATE TABLE IF NOT EXISTS `$table` ";

            $cols = array();
            foreach($meta->getColumns() as $col) {
                if(isset($fixtureMeta[$table][$col])) {
                    $cols[] = $this->createFieldSQL(
                        $col,
                        $fixtureMeta[$table][$col]['@attributes']
                    );
                } else {
                    $cols[] = "`$col` VARCHAR(400)";
                }
            }

            // Set primary key
            if(
                isset($fixtureKeys[$table])
                && isset($fixtureKeys[$table]['PRIMARY'])
            ) {
                $cols[] = 'PRIMARY KEY (`' . $fixtureKeys[$table]['PRIMARY']['@attributes']['Column_name'] . '`)';
            }

            $create .= '('.implode(',', $cols).');';

            try {
                $pdo->exec($create);
            } catch(\Exception $e) {
                echo $create."\n";
                throw $e;
            }
        }

        parent::setUp();
    }

    /*
     * Disconnect from database, after deleting old data.
     */
    public function tearDown() {
        $allTables = $this->getDataSet($this->fixtures)->getTableNames();

        foreach($allTables as $table) {
            // drop table
            $conn = $this->getConnection();
            $pdo = $conn->getConnection();
            $pdo->exec("DROP TABLE IF EXISTS `$table`;");
        }

        $conn = $this->getConnection();
        $pdo = $conn->getConnection();

        $this->conn = null;

        parent::tearDown();
    }

    /*
     * Open new database connection.
     * Set DB login details via DB_USER and DB_PASS environment variables.
     */
    public function getConnection() {
        if($this->conn === null) {
            try {
                $dbname = "test_media_felix";
                $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
                $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';
                $pdo = new PDO(
                    'mysql:host=localhost;dbname='.$dbname,
                    $dbuser,
                    $dbpass
                );
                $this->conn = $this->createDefaultDBConnection($pdo, 'test');
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return $this->conn;
    }

    /*
     * For each fixture file, load the file into the PHPUnit database dataset.
     */
    public function getDataSet($fixtures = array()) {
        if(empty($fixtures)) {
            $fixtures = $this->fixtures;
        }

        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());
        $fixturePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixtures';

        foreach($fixtures as $fixture) {
            $path = $fixturePath . DIRECTORY_SEPARATOR . "$fixture.xml";
            $ds = $this->createMySQLXMLDataSet($path);
            $compositeDs->addDataSet($ds);
        }

        return $compositeDs;
    }

    /*
     * For each fixture file, extract fixture and key information.
     */
    public function getDataSetMeta($fixtures = array()) {
        $fixturePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixtures';

        $meta = array();
        $keys = array();

        foreach($fixtures as $fixture) {
            $file =  $fixturePath . DIRECTORY_SEPARATOR . "$fixture.xml";
            $xmlFileContents = simplexml_load_file($file);

            foreach(
                $xmlFileContents->xpath('./database/table_structure')
                as $tableElement
            ) {
                $tableName = (string) $tableElement['name'];

                if (!isset($meta[$tableName])) {
                    $meta[$tableName] = array();
                }

                if (!isset($keys[$tableName])) {
                    $keys[$tableName] = array();
                }

                foreach($tableElement->xpath('./field') as $fieldElement) {
                    if(empty($fieldElement['Field'])) {
                        throw new PHPUnit_Extensions_Database_Exception(
                            '<field> elements must include a Field attribute'
                        );
                    }

                    $columnName = (string) $fieldElement['Field'];

                    if(!isset($meta[$tableName][$columnName])) {
                        $meta[$tableName][$columnName] = (array) $fieldElement;
                    }
                }

                // get primary key
                foreach(
                    $tableElement->xpath('./key[@Key_name="PRIMARY"]')
                    as $primaryKey
                ) {
                    $keys[$tableName]['PRIMARY'] = (array) $primaryKey;
                }
            }
        }

        return array($meta, $keys);
    }

    /*
     * Based on the field information extracted in getDataSetMeta,
     * generate a column creation statement.
     */
    public function createFieldSQL($column, $meta) {
        $arr = array(
            "`" . $column . "`"
        );

        if(isset($meta['Type'])) {
            $arr[] = $meta['Type'];
        } else {
            $arr[] = "VARCHAR(400)";
        }

        if(isset($meta['Null']) && $meta['Null'] == 'NO') {
            $arr[] = "NOT NULL";
        }

        if(isset($meta['Default'])) {
            $arr[] = "DEFAULT " . $meta['Default'];
        }

        if(isset($meta['Extra'])) {
            $arr[] = $meta['Extra'];
        }

        return implode(" ", $arr);
    }

    public function loadDataSet($dataSet) {
        // set the new dataset
        $this->getDatabaseTester()->setDataSet($dataSet);
        // call setUp whateverhich adds the rows
        $this->getDatabaseTester()->onSetUp();
    }
}
