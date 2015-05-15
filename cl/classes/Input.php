<?php
namespace CodeLapse;

/**
 * リクエストに含まれるパラメータを取得します。（POST, GETなど）
 *
 * @package CodeLapse
 */
class Input {

    /**
     * リクエストメソッドの種類を取得します。
     * （POST, GET, etcetc...)
     * @return string
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * ajaxからのアクセスか調べます。
     *
     * @return boolean
     */
    public static function isAjax()
    {
        return strtolower(D5_Input::server('HTTP_X_REQUESTED_WITH', '')) === 'xmlhttprequest';
    }


    /**
     * POSTパラメータの値を取得します。
     *
     * @param string|null $key (optional) 取得するパラメータ名
     * @param mixed|null $default (optional) パラメータが存在しない時のデフォルト値
     * @return mixed
     */
    public static function post($key = null, $default = null)
    {
        return Arr::get($_POST, $key, $default);
    }


    /**
     * GETパラメータの値を取得します。
     *
     * @param string|null $key (optional) 取得するパラメータ名
     * @param mixed|null $default (optional) パラメータが存在しない時のデフォルト値
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        return Arr::get($_GET, $key, $default);
    }


    /**
     * $_SERVER変数の値を取得します。
     *
     * @param string|null $key (optional) 取得する属性名
     * @param mixed|null $default (optional)パラメータが存在しない時のデフォルト値
     * @return mixed
     */
    public static function server($key = null, $default = null)
    {
        return Arr::get($_SERVER, $key, $default);
    }
}
