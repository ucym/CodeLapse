<?php
use \CodeLapse\Arr;

class ArrTest extends PHPUnit_Framework_TestCase
{
    public function provider_personInfo()
    {
        return array(
            array(
                array(
                    'name'  => 'James Bond',
                    'age'   => '30',
                    'location'  => array(
                        'country'   => 'EU'
                    ),
                    'tags'  => array(
                        'spy', 'man', 'ナターリア死ぬな', 'get down'
                    )
                )
            )
        );
    }

    /**
     * @covers \CodeLapse\Arr::get
     * @dataProvider provider_personInfo
     */
    public function testGet($person)
    {
        $expected = 'James Bond';
        $this->assertEquals(
            Arr::get($person, 'name'),
            $expected,
            'getテスト');

        $expected = 'EU';
        $this->assertEquals(
            Arr::get($person, 'location.country'),
            $expected,
            '多次元配列取得テスト');

        $expected = 'spy';
        $this->assertEquals(
            Arr::get($person, 'tags.0'),
            $expected,
            '数値インデックス取得テスト');

        $this->assertNull(
            Arr::get($person, 'no_exists_index'),
            '未定義のインデックス指定時の挙動テスト 1');

        $expected = 'Default fallback value';
        $this->assertEquals(
            Arr::get($person, 'no_exists_index', $expected),
            $expected,
            'デフォルト値指定テスト');

        $expected = array(
            'name' => 'James Bond',
            'location' => array('country' => 'EU'));
        $this->assertEquals(
            Arr::get($person, array('name', 'location')),
            $expected,
            '指定されたフィールドの取得テスト');

        // 不正な引数に対して例外をスローするかテスト。
        try {
            Arr::get('', 'a');
            $this->fail('不正な引数に対しての例外テスト');
        }
        catch (Exception $e) {}
    }

    /**
     * @covers \CodeLapse\Arr::set
     */
    public function testSet()
    {
        $arr = array();

        $expected = 'value';
        Arr::set($arr, 'key', $expected);
        $this->assertEquals(
            $arr['key'],
            $expected,
            'setテスト');

        $expected = 'value';
        Arr::set($arr, 'key.deep', $expected);
        $this->assertTrue(
            isset($arr['key']['deep']),
            '多次元配列の値設定テスト');

        $this->assertEquals(
            Arr::get($arr, 'key.deep'),
            $expected,
            '多次元配列の値設定テスト');
    }

    /**
     * @covers \CodeLapse\Arr::delete
     * @dataProvider provider_personInfo
     */
    public function testDelete($arr)
    {

    }

    public function testMapRecursive()
    {

    }
}
