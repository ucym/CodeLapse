<?php
class LateBindingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \CodeLapse\LateBinding::getCalledClass()
     */
    public function testCallFromFuction()
    {
        // \CodeLapse\LateBindingTest からコールされているので、このクラスの名前が返されなければなりません。
        $expected = '\CodeLapse\LateBindingTest';
        $this->assertEquals(\CodeLapse\LateBinding::getCalledClass(), $expected, 'テストクラスからコール');
    }

    public function testCallFromSubclass()
    {
        $expected = '_\CodeLapse\LateBindingTest_Child';
        $this->assertEquals(
            _\CodeLapse\LateBindingTest_Child::staticCallme(), $expected,
            'サブクラス経由のスタティックコール');

        $instance = new _\CodeLapse\LateBindingTest_Child();
        $this->assertEquals(
            $instance->callme(), get_class($instance),
            'サブクラスインスタンス経由のコール');
    }

    public function testCallFromSelfClass()
    {
        $expected = '_\CodeLapse\LateBindingTest_Parent';
        $this->assertEquals(
        _\CodeLapse\LateBindingTest_Parent::staticCallme(), $expected,
        '親クラス経由のコール');

        $instance = new _\CodeLapse\LateBindingTest_Parent();
        $this->assertEquals(
            $instance->callme(), get_class($instance),
            '親クラスインスタンス経由のコール');
    }

    public function testDelegateCall()
    {
        $instance = new _\CodeLapse\LateBindingTest_Child();

        $expected = '_\CodeLapse\LateBindingTest_Child';
        $this->assertEquals(
            $instance->delegateCall($instance->delegateCall()),
            $expected,
            '委譲（１メソッド経由のコール）');
    }

}



class _\CodeLapse\LateBindingTest_Parent
{
    public static function staticCallme()
    {
        return \CodeLapse\LateBinding::getCalledClass();
    }

    public function callme()
    {
        return \CodeLapse\LateBinding::getCalledClass();
    }

    public function delegateCall()
    {
        return $this->callme();
    }
}


class _\CodeLapse\LateBindingTest_Child extends _\CodeLapse\LateBindingTest_Parent {}
