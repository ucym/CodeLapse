<?php
class D5_PagerTest extends PHPUnit_Framework_TestCase
{
    // public static function setUpBeforeClass()
    // {
    //     D5_Pager::init(
    //         /* current page */ 2,
    //         /* all count */ 300,
    //         /* per page */ 30
    //     );
    // }

    /**
     * @covers D5_Pager::hasPrev
     */
    public function testHasPrev()
    {
        // 前のページがないべき
        D5_Pager::init(1, 100, 30);
        $this->assertFalse(D5_Pager::hasPrev(), '前ページ判定テスト 1');

        // 前ページがあるべき
        D5_Pager::init(2, 100, 30);
        $this->assertTrue(D5_Pager::hasPrev(), '前ページ判定テスト 2');

        // TODO Output Testing
    }


    /**
     * @covers D5_Pager::hasNext
     */
    public function testHasNext()
    {
        // 次のページがないべき
        D5_Pager::init(3, 90, 30); // 90件中 61-90件目
        $this->assertFalse(D5_Pager::hasNext(), '次ページ判定テスト 1');

        D5_Pager::init(1, 0, 30); // ヒット件数 0
        $this->assertFalse(D5_Pager::hasNext(), '次ページ判定テスト 2');

        D5_Pager::init(1, 10, 30); // 10件中 1-10件目
        $this->assertFalse(D5_Pager::hasNext(), '次ページ判定テスト 3');

        // 次ページがあるべき
        D5_Pager::init(1, 100, 30); // 100件中 0-30件目
        $this->assertTrue(D5_Pager::hasNext(), '次ページ判定テスト 4');

        D5_Pager::init(2, 100, 30); // 100件中 60-90件目
        $this->assertTrue(D5_Pager::hasNext(), '次ページ判定テスト 5');

        // TODO Output Testing
    }


    /**
     * @covers D5_Pager::pages
     */
    public function testPages()
    {
        D5_Pager::init(0, 90, 30); // 全90件 / 30 = 3ページ
        $expected = 3;
        $this->assertEquals($expected, D5_Pager::pages(), 'ページ数計算テスト 1');

        D5_Pager::init(0, 20, 30); // 全20件 / 30 = 1ページ
        $expected = 1;
        $this->assertEquals($expected, D5_Pager::pages(), 'ページ数計算テスト 2');

        D5_Pager::init(0, 0, 30); // 0ページ
        $expected = 0;
        $this->assertEquals($expected, D5_Pager::pages(), 'ページ数計算テスト 3');

        D5_Pager::init(0, 1100, 100); // 11 ページ
        $expected = 11;
        $this->assertEquals($expected, D5_Pager::pages(), 'ページ数計算テスト 4');
    }


    /**
     * @covers D5_Pager::relateRange
     */
    public function testRelateRange()
    {
        // 1ページの現在地+前後5ページ（最大と最小の範囲を超えない）
        D5_Pager::init(1, 90, 30);
        $expected = array(1, 2, 3);
        $this->assertEquals($expected, D5_Pager::relateRange(5), '相対範囲ページネーションテスト 1');

        // 2ページの現在地+前後5ページ（最大と最小の範囲を超えない）
        D5_Pager::init(2, 1100, 100);
        $expected = array(1, /* current */ 2, 3, 4, 5, 6, 7);
        $this->assertEquals($expected, D5_Pager::relateRange(5), '相対範囲ページネーションテスト 2');

        // 5ページ目の現在地+前後5ページ（最大と最小の範囲を超えない）
        D5_Pager::init(5, 1100, 100);
        $expected = array(1, 2, 3, 4, /* current */ 5, 6, 7, 8, 9, 10);
        $this->assertEquals($expected, D5_Pager::relateRange(5), '相対範囲ページネーションテスト 3');

        // 10ページ目の現在地+前後5ページ = （最大と最小の範囲を超えない）
        D5_Pager::init(9, 1100, 100); // 全11ページ
        $expected = array(4, 5, 6, 7, 8, /* current */ 9, 10, 11);
        $this->assertEquals($expected, D5_Pager::relateRange(5), '相対範囲ページネーションテスト 4');
    }
}
