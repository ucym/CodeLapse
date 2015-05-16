<?php
/**
 * Input::get のエイリアス
 *
 * @param string|null $key (optional) 取得するパラメータ名
 * @param mixed|null $default (optional) パラメータが存在しない時のデフォルト値
 * @return mixed
 */
function _get($key, $default = null)
{
    return \CodeLapse\Input::get($key, $default);
}

/**
 * Input::post のエイリアス
 *
 * @param string|null $key (optional) 取得するパラメータ名
 * @param mixed|null $default (optional) パラメータが存在しない時のデフォルト値
 * @return mixed
 */
function _post($key, $default = null)
{
    return \CodeLapse\Input::post($key, $default);
}
