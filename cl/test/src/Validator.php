<?php
use CodeLapse\Validator;

class CustomValidators
{
    public static function validatePass()
    {
        return true;
    }

    public static function validateIsEmpty($value)
    {
        return empty($value);
    }

    public static function validate_no_empty_value($value)
    {
        return ! empty($value);
    }
}


class ValidatorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Validator::reset();
    }

    public function testSimpleValidation()
    {
        $rules = [
            'name'  => ['required', 'lengthMin' => 4],
            'email' => ['required', 'email']
        ];

        $validData = [
            'name'  => 'Yukari Yuzuki',
            'email' => 'example@example.com'
        ];

        $invalidData = [
            'name'      => 'a',
            'email'     => 'nubesuko@kichi'
        ];

        Validator::def($rules);

        $this->assertTrue(Validator::check($validData), '正常値チェック');
        $this->assertFalse(Validator::check($invalidData), '不正値チェック');

        $errors = Validator::errors();
        $this->assertTrue(isset($errors['name']), 'エラーメッセージ存在チェック');
        $this->assertTrue(isset($errors['email']), 'エラーメッセージ存在チェック');
    }

    public function testCustomValidation()
    {
        $validator = function ($value) { return true; };
        Validator::addRule('alwaysPass', $validator);

        Validator::def(['name' => ['alwaysPass']]);
        $this->assertTrue(Validator::check(['name' => '']), 'カスタム検証チェック');
    }

    public function testCustomValidationClass()
    {
        Validator::addRuleClass('CustomValidators');

        $rules = [
            'name'          => ['pass', 'no_empty_value'],
            'dont write'    => ['isEmpty']
        ];

        $validData = [
            'name'          => 'Yukari',
            'dont write'    => ''
        ];

        $invalidData = [
            'name'          => '',
            'dont write'    => 'HELLO!',
        ];

        Validator::def($rules);
        $this->assertTrue(Validator::check($validData), 'スタティックメソッド カスタム検証チェック（正常値）');
        $this->assertFalse(Validator::check($invalidData), 'スタティックメソッド カスタム検証チェック（異常値）');
    }

    public function testIsCustomRuleNamingByCamelCase()
    {
        Validator::addRuleClass('CustomValidators');

        $this->assertTrue(Validator::hasRule('pass'));
        $this->assertFalse(Validator::hasRule('Pass'));
    }
}
