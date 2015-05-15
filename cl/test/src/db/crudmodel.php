<?php
class _\CodeLapse\CrudModel_Dummy extends \CodeLapse\DB_CrudModel
{
    public static $_tableName = 'crud_test';
    public static $_properties = array('id', 'name', 'age');
    public static $_primaryKey = array('id');
}

class \CodeLapse\DB_CrudModelTest extends PHPUnit_Framework_TestCase
{
    private static $con;

    public static function setUpBeforeClass()
    {
        // テーブルの設定
        self::$con = \CodeLapse\DB::connect(DB_HOST, DB_USER, DB_PASS, false, 'default');
        self::$con->useDB(DB_NAME);

        $result = self::$con->query(
            'CREATE TABLE IF NOT EXISTS crud_test('
                . 'id INT PRIMARY KEY AUTO_INCREMENT,'
                . 'name VARCHAR(10) NOT NULL,'
                . 'age VARCHAR(5) NOT NULL'
            . ') ENGINE=InnoDB');
    }

    private $instance;

    protected function setUp()
    {
        $this->instance = new _\CodeLapse\CrudModel_Dummy();
    }

    /**
     * @cover \CodeLapse\DB_CrudModel::tableName
     */
    public function testTableName()
    {
        $expected = 'crud_test';
        $this->assertEquals(
            _\CodeLapse\CrudModel_Dummy::tableName(),
            $expected,
            '::tableNameメソッド 遅延束縛チェック');
    }


    /**
     * @cover \CodeLapse\DB_CrudModel::properties
     */
    public function testProperties()
    {
        $expected = _\CodeLapse\CrudModel_Dummy::$_properties;
        $this->assertEquals(
            _\CodeLapse\CrudModel_Dummy::properties(),
            $expected,
            '::propertiesメソッド 遅延束縛チェック');
    }


    /**
     * @cover \CodeLapse\DB_CrudModel::properties
     */
    public function testPrimaryKey()
    {
        $expected = _\CodeLapse\CrudModel_Dummy::$_primaryKey;
        $this->assertEquals(
            _\CodeLapse\CrudModel_Dummy::primaryKey(),
            $expected,
            '::primaryKeyメソッド 遅延束縛チェック');
    }


    public function testSave()
    {
        $m = new _\CodeLapse\CrudModel_Dummy();
        $m
            ->set('name', 'steve')
            ->set('age', 24)
            ->save();
    }

    /**
     * @depends testSave
     */
    public function testFind()
    {
        // @TODO
        //_\CodeLapse\CrudModel_Dummy::find();
    }
}
