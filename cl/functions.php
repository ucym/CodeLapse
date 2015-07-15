<?php
/**
 * CodeLapse\Request::get のエイリアス
 *
 * @param string|null $key (optional) 取得するパラメータ名
 * @param mixed|null $default (optional) パラメータが存在しない時のデフォルト値
 * @return mixed
 */
function _get($key, $default = null)
{
    return \CodeLapse\Request::get($key, $default);
}

/**
 * CodeLapse\Request::post のエイリアス
 *
 * @param string|null $key (optional) 取得するパラメータ名
 * @param mixed|null $default (optional) パラメータが存在しない時のデフォルト値
 * @return mixed
 */
function _post($key, $default = null)
{
    return \CodeLapse\Request::post($key, $default);
}
