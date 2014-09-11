<?php
class D5_ArrTest extends PHPUnit_Framework_TestCase
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
     * @covers D5_Arr::get
     * @dataProvider provider_personInfo
     */
    public function testGet($person)
    {
        $expected = 'James Bond';
        $this->assertEquals(
            D5_Arr::get($person, 'name'),
            $expected,
            'getテスト');

        $expected = 'EU';
        $this->assertEquals(
            D5_Arr::get($person, 'location.country'),
            $expected,
            '多次元配列取得テスト');

        $expected = 'spy';
        $this->assertEquals(
            D5_Arr::get($person, 'tags.0'),
            $expected,
            '数値インデックス取得テスト');

        $this->assertNull(
            D5_Arr::get($person, 'no_exists_index'),
            '未定義のインデックス指定時の挙動テスト 1');

        $expected = 'Default fallback value';
        $this->assertEquals(
            D5_Arr::get($person, 'no_exists_index', $expected),
            $expected,
            'デフォルト値指定テスト');

        $expected = array(
            'name' => 'James Bond',
            'location' => array('country' => 'EU'));
        $this->assertEquals(
            D5_Arr::get($person, array('name', 'location')),
            $expected,
            '指定されたフィールドの取得テスト');

        // 不正な引数に対して例外をスローするかテスト。
        try {
            D5_Arr::get('', 'a');
            $this->fail('不正な引数に対しての例外テスト');
        }
        catch (Exception $e) {}
    }

    /**
     * @covers D5_Arr::set
     */
    public function testSet()
    {
        $arr = array();

        $expected = 'value';
        D5_Arr::set($arr, 'key', $expected);
        $this->assertEquals(
            $arr['key'],
            $expected,
            'setテスト');

        $expected = 'value';
        D5_Arr::set($arr, 'key.deep', $expected);
        $this->assertTrue(
            isset($arr['key']['deep']),
            '多次元配列の値設定テスト');

        $this->assertEquals(
            D5_Arr::get($arr, 'key.deep'),
            $expected,
            '多次元配列の値設定テスト');
    }

    /**
     * @covers D5_Arr::delete
     * @dataProvider provider_personInfo
     */
    public function testDelete($arr)
    {

    }

    public function testMapRecursive()
    {

    }
}
