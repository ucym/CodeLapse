<?php
use CL_Form as Form;

class FormTest extends PHPUnit_Framework_TestCase
{
    public function test_get_setValue()
    {
        //-- 値の復元テスト
        $list = array(
            'ジャパン' => 'japan',
            'ジャポン' => 'japon',
        );
        Form::setValue(array(
            'name'  => 'unit',
            'sex'   => 'male',
            'states'=> 'japan'
        ));


        // value属性が設定されていなければならない
        ob_start();
        Form::text('name');
        $this->assertContains('value="unit"', ob_get_clean());


        // checked属性が設定されていなければならない
        ob_start();
        Form::radio('sex', 'male');
        $this->assertContains('checked="checked"', ob_get_clean());


        // checked属性が設定されていてはならない
        ob_start();
        Form::radio('sex', 'female');
        $this->assertNotContains('checked', ob_get_clean());


        // 値が一致する項目が選択されていなければいけない
        // また、一致していない項目が選択されていてはいけない
        ob_start();
        Form::select('states', $list);
        $out = ob_get_clean();
        $this->assertContains('<option selected="selected" value="japan">', $out);
        $this->assertNotContains('<option selected="selected" value="japon">', $out);
    }

    public function test_parseName()
    {
        ob_start();
        Form::text('field #text-field.form-control', 'value');
        $out = ob_get_clean();

        $this->assertContains('class="form-control"', $out);
        $this->assertContains('id="text-field"', $out);

        //-- エラー処理
        // 数字からはじまる不正なID/クラスを無視するかテストする
        ob_start();
        Form::text('field #1text.1form-control.valid', 'value');
        $out = ob_get_clean();
        $this->assertContains('class="valid"', $out);
        $this->assertNotContains('1form-control', $out);
        $this->assertNotContains('1text', $out);
    }

    public function test_hidden()
    {
        ob_start();
        Form::hidden('field', 'value');
        $out = ob_get_clean();
        $this->assertStringStartsWith('<input ', $out);
        $this->assertContains('type="hidden"', $out);
        $this->assertContains('name="field"', $out);
        $this->assertContains('value="value"', $out);
        $this->assertStringEndsWith(' />', $out);
    }


    public function test_textarea()
    {
        ob_start();

        Form::textarea('field');
        $out = ob_get_clean();
        $this->assertStringStartsWith('<textarea ', $out);
        $this->assertContains('name="field"', $out);

        // クラスを指定していないのに属性があったりしてはいけない
        $this->assertNotContains('class', $out);
    }
}
