<?php
namespace CodeLapse;

use \CodeLapse\Input;
use \PhpQuickProfiler;
use \Pqp_Console;

class Profiler
{
    protected static $pqp;


    /**
     * プロファイラへデータを出力します。
     *
     * @param string    $level      ログレベル
     * @param mixed[]   $vars       出力する変数
     */
    private static function logging($level, $vars)
    {
        foreach ($vars as & $var) {
            Pqp_Console::logCustom($level, $var);
        }
    }

    /**
     * プロファイラーを有効化します。
     */
    public static function active()
    {
        self::$pqp = new PhpQuickProfiler(
            PhpQuickProfiler::getMicroTime()//,
            //DS.'lib'.DS.'pqp'.DS
        );

        set_exception_handler(array(__CLASS__, 'exceptionHandler'));
        set_error_handler(array(__CLASS__, 'errorHandler'),  E_ALL | E_STRICT);
        register_shutdown_function(array(__CLASS__, 'end'));
    }


    /**
     * プロファイラーを表示します。
     *
     * @private
     */
    public static function end()
    {
        if (Input::isAjax() === false and self::$pqp) {
            echo self::$pqp->display();
        }
    }


    /**
     * PHPエラーのハンドリングを行います。
     *
     * @private
     * @param int       $errno      エラーコード
     * @param string    $errmsg     エラーメッセージ
     * @param string    $errfile    エラーが起きたファイルのパス
     * @param int       $errline    エラーが起きた行番号
     * @return bool
     */
    public static function errorHandler($errno, $errmsg, $errfile, $errline)
    {
        $trace = debug_backtrace();
        array_shift($trace);

        Pqp_Console::logCustom(Pqp_Console::LEVEL_ERROR, null, array(
            'line'      => $errline,
            'file'      => $errfile,
            'data'      => $errmsg,
            'stack'     => $trace
        ));

        die();
        return false;
    }


    /**
     * PHPの例外ハンドリングを行います。
     *
     * @private
     * @param Exception     $e      例外オブジェクト
     */
    public static function exceptionHandler(Exception $e)
    {
        Pqp_Console::logCustom(Pqp_Console::LEVEL_ERROR, $e->getMessage(), array(
            'file'  => $e->getFile(),
            'line'  => $e->getLine()
        ));
    }


    /**
     * コンソールにlogレベルのメッセージを出力します。
     *
     * @param mixed     $objects...     コンソールに記録するオブジェクトまたはデータ
     */
    public static function log()
    {
        return self::logging('log', func_get_args());
    }


    /**
     * コンソールに warningレベルのメッセージを出力します。
     *
     * @param mixed     $objects...     コンソールに記録するオブジェクトまたはデータ
     */
    public static function warn()
    {
        return self::logging('warn', func_get_args());
    }


    /**
     * コンソールにerrorレベルのメッセージを出力します。
     *
     * @param mixed     $objects...     コンソールに記録するオブジェクトまたはデータ
     */
    public static function error()
    {
        return self::logging('error', func_get_args());
    }


    /**
     * コンソールにdebugレベルのメッセージを出力します。
     *
     * @param mixed     $objects...     コンソールに記録するオブジェクトまたはデータ
     */
    public static function debug()
    {
        return self::logging('debug', func_get_args());
    }


    /**
     * コンソールにinfoレベルのメッセージを出力します。
     *
     * @param mixed     $objects...     コンソールに記録するオブジェクトまたはデータ
     */
    public static function info()
    {
        return self::logging('info', func_get_args());
    }
}
