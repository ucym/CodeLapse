<?php
use \CodeLapse\LateBinding;

class LateBindingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers LateBinding::getCalledClass()
     */
    public function testCallFromFuction()
    {
        // LateBindingTest からコールされているので、このクラスの名前が返されなければなりません。
        $expected = 'LateBindingTest';
        $this->assertEquals(LateBinding::getCalledClass(), $expected, 'テストクラスからコール');
    }

    public function testCallFromSubclass()
    {
        $expected = 'LateBindingTest_Child';
        $this->assertEquals(
            LateBindingTest_Child::staticCallme(), $expected,
            'サブクラス経由のスタティックコール');

        $instance = new LateBindingTest_Child();
        $this->assertEquals(
            $instance->callme(), get_class($instance),
            'サブクラスインスタンス経由のコール');
    }

    public function testCallFromSelfClass()
    {
        $expected = 'LateBindingTest_Parent';
        $this->assertEquals(
        LateBindingTest_Parent::staticCallme(), $expected,
        '親クラス経由のコール');

        $instance = new LateBindingTest_Parent();
        $this->assertEquals(
            $instance->callme(), get_class($instance),
            '親クラスインスタンス経由のコール');
    }

    public function testDelegateCall()
    {
        $instance = new LateBindingTest_Child();

        $expected = 'LateBindingTest_Child';
        $this->assertEquals(
            $instance->delegateCall($instance->delegateCall()),
            $expected,
            '委譲（１メソッド経由のコール）');
    }

}



class LateBindingTest_Parent
{
    public static function staticCallme()
    {
        return LateBinding::getCalledClass();
    }

    public function callme()
    {
        return LateBinding::getCalledClass();
    }

    public function delegateCall()
    {
        return $this->callme();
    }
}


class LateBindingTest_Child extends LateBindingTest_Parent {}
