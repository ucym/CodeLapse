<?php
use CL_Arr as Arr;

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
    public function testDelete($person)
    {
        // Simple
        $deletion1 = $person;
        Arr::delete($deletion1, 'name');
        $this->assertFalse(array_key_exists('name', $deletion1), 'Arr::delete 1');

        // Nested
        $deletion2 = $person;
        Arr::delete($deletion2, 'location.coutry');
        $this->assertTrue(array_key_exists('location', $deletion2), 'Arr::delete 2-1');
        $this->assertFalse(array_key_exists('country', $deletion2), 'Arr::delete 2-2');

        // Multiple deletion with Simple & Nested
        $deletion3 = $person;
        Arr::delete($deletion3, array('location.country', 'tags'));
        $this->assertTrue(array_key_exists('location', $deletion3), 'Arr::delete 3-1');
        $this->assertFalse(array_key_exists('country', $deletion3['location']), 'Arr::delete 3-2');
        $this->assertFalse(array_key_exists('tags', $deletion3), 'Arr::delete 3-3');

        // Delete non exists key
        $deletion4 = $person;
        Arr::delete($deletion4, array('noExists', 'deep.noExists'));
        $this->assertTrue(empty(Arr::diffRecursive($deletion4, $person)), 'Arr::delete 4');

        Arr::delete($deletion4, 'name');
        $this->assertFalse(empty(Arr::diffRecursive($deletion4, $person)), 'Arr::delete 5');
    }

    /**
     * @covers \CodeLapse\Arr::except
     * @dataProvider provider_personInfo
     */
    public function testExcept($person)
    {
        $original = $person;

        // Simple except
        $excepted1 = Arr::except($person, 'location');
        $this->assertFalse(array_key_exists('location', $excepted1), 'Arr::except 1');

        // Deep except
        $excepted2 = Arr::except($person, 'location.country');
        $this->assertTrue(array_key_exists('location', $excepted2), 'Arr::except 2-1');
        $this->assertFalse(array_key_exists('country', $excepted2['location']), 'Arr::except 2-2');

        // Multiple simple & deep except
        $excepted3 = Arr::except($person, ['tags', 'location.country']);
        $this->assertFalse(array_key_exists('tags', $excepted3), 'Arr::except 3-1');
        $this->assertTrue(array_key_exists('location', $excepted3), 'Arr::except 3-2');
        $this->assertFalse(array_key_exists('country', $excepted3['location']), 'Arr::except 3-3');

        // Check non-destructive except
        $this->assertEmpty(Arr::diffRecursive($original, $person), 'ARR::except 4');
    }

    public function testMapRecursive()
    {

    }
}
