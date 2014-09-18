<?php
class _D5_CrudModel_Dummy extends D5_DB_CrudModel
{
    public static $_tableName = 'crud_test';
    public static $_properties = array('id', 'name', 'age');
    public static $_primaryKey = array('id');
}

class D5_DB_CrudModelTest extends PHPUnit_Framework_TestCase
{
    private static $con;

    public static function setUpBeforeClass()
    {
        // テーブルの設定
        self::$con = D5_DB::connect(DB_HOST, DB_USER, DB_PASS, false, 'default');
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
        $this->instance = new _D5_CrudModel_Dummy();
    }

    /**
     * @cover D5_DB_CrudModel::tableName
     */
    public function testTableName()
    {
        $expected = 'crud_test';
        $this->assertEquals(
            _D5_CrudModel_Dummy::tableName(),
            $expected,
            '::tableNameメソッド 遅延束縛チェック');
    }


    /**
     * @cover D5_DB_CrudModel::properties
     */
    public function testProperties()
    {
        $expected = _D5_CrudModel_Dummy::$_properties;
        $this->assertEquals(
            _D5_CrudModel_Dummy::properties(),
            $expected,
            '::propertiesメソッド 遅延束縛チェック');
    }


    /**
     * @cover D5_DB_CrudModel::properties
     */
    public function testPrimaryKey()
    {
        $expected = _D5_CrudModel_Dummy::$_primaryKey;
        $this->assertEquals(
            _D5_CrudModel_Dummy::primaryKey(),
            $expected,
            '::primaryKeyメソッド 遅延束縛チェック');
    }


    public function testSave()
    {
        $m = new _D5_CrudModel_Dummy();
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
        //_D5_CrudModel_Dummy::find();
    }
}
