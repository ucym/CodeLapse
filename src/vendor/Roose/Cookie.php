<?php
/**
 * クッキーの読み書きを行うクラス
 * 
 * @package Roose
 * @author うちやま
 * @since PHP 5.2.17
 * @version 1.0.0
 */ 
class Roose_Cookie
{
    //TODO 時間間隔の定数を書く 
    
    /**
     * Cookieから値を取得します。
     * 
     * @param string $name 取得するクッキー名
     * @param mixed|null $default クッキーが存在しない時のデフォルト値
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        return Arr::get($_COOKIE, $name, $default);
    }

    /**
     * Cookieに値を設定します。
     * 
     * @link http://jp2.php.net/manual/ja/function.setcookie.php setcookie関数
     * @param string $name クッキー名
     * @param string $value 設定する値
     * @param int $expire クッキーの有効期間(Unixタイム)
     */
    public static function set($name, $value, $expire)
    {
        setcookie($name, $value, $expire);
    }
    
    /**
     * クッキーから値を削除します。
     * 
     * @param string $name 削除するCookieの名前
     */
    public static function delete($name)
    {
        self::set($name, '', time() - 1);
    }
}