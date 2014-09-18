<?php
class D5_LateBindingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers D5_LateBinding::getCalledClass()
     */
    public function testCallFromFuction()
    {
        // D5_LateBindingTest からコールされているので、このクラスの名前が返されなければなりません。
        $expected = 'D5_LateBindingTest';
        $this->assertEquals(D5_LateBinding::getCalledClass(), $expected, 'テストクラスからコール');
    }

    public function testCallFromSubclass()
    {
        $expected = '_D5_LateBindingTest_Child';
        $this->assertEquals(
            _D5_LateBindingTest_Child::staticCallme(), $expected,
            'サブクラス経由のスタティックコール');

        $instance = new _D5_LateBindingTest_Child();
        $this->assertEquals(
            $instance->callme(), get_class($instance),
            'サブクラスインスタンス経由のコール');
    }

    public function testCallFromSelfClass()
    {
        $expected = '_D5_LateBindingTest_Parent';
        $this->assertEquals(
        _D5_LateBindingTest_Parent::staticCallme(), $expected,
        '親クラス経由のコール');

        $instance = new _D5_LateBindingTest_Parent();
        $this->assertEquals(
            $instance->callme(), get_class($instance),
            '親クラスインスタンス経由のコール');
    }

    public function testDelegateCall()
    {
        $instance = new _D5_LateBindingTest_Child();

        $expected = '_D5_LateBindingTest_Child';
        $this->assertEquals(
            $instance->delegateCall($instance->delegateCall()),
            $expected,
            '委譲（１メソッド経由のコール）');
    }

}



class _D5_LateBindingTest_Parent
{
    public static function staticCallme()
    {
        return D5_LateBinding::getCalledClass();
    }

    public function callme()
    {
        return D5_LateBinding::getCalledClass();
    }

    public function delegateCall()
    {
        return $this->callme();
    }
}


class _D5_LateBindingTest_Child extends _D5_LateBindingTest_Parent {}
