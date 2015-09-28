<?php
namespace CodeLapse;

use CodeLapse\Arr;

/**
 * Viewクラス
 */
class View
{
    protected static $assigns = [];

    /**
     * @param mixed     $value
     * @return mixed
     */
    public static function escape($value)
    {
        is_string($value)
            and $value = htmlspecialchars($value, ENT_QUOTES);

        return $value;
    }

    /**
     * ファイル名の拡張子を元にテンプレートドライバを取得します。
     * @return CodeLapse\View\Driver
     */
    protected function getDriverByFileName($fileName)
    {
        $matches = [];
        mb_ereg('\\.(.+?)$', $fileName, $matches);

    }

    /**
     * テンプレートに変数を割り当てます。
     * 割り当てられた変数内の文字列は全てHTMLエスケープされます。
     * @param string    $name
     * @param mixed     $value
     */
    public static function set($name, $value = null)
    {
        is_array($name) and $value = $name;

        if (is_array($value)) {
            $value = Arr::mapRecursive($value, array('CodeLapse\\View', 'escape'));
        }
        else if (is_string($value)) {
            $value = static::escape($value);
        }

        Arr::set(static::$assigns, $name, $value);
    }

    /**
     * テンプレートに変数を割り当てます。
     * 割り当てられた変数はエスケープされません。
     * @param string    $name
     * @param string    $value
     */
    public static function setRaw($name, $value = null)
    {
        Arr::set(static::$assigns, $name, $value);
    }

    /**
     * @param string    $name       取得する変数名
     * @param mixed     $default    変数が設定されていない時のデフォルト値
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        return Arr::get(static::$assigns, $name, $default);
    }

    /**
     * テンプレートを出力します。
     * @param string    $fileName   テンプレートファイル名
     */
    public static function out($fileName)
    {
        echo static::fetch($fileName);
    }

    /**
     * テンプレートの出力を取得します。
     * @param string    $fileName   テンプレートファイル名
     * @return string
     */
    public static function fetch($fileName)
    {
        $driver = static::getDriverByFileName($fileName);
        // Resolve path
        $driver->render($filePath, static::$assigns);
    }
}
