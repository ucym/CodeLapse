<?php
/**
 * Rooseライブラリの初期化処理を担うクラス
 */
class Roose_Core
{
    private static $instance;

    /**
     * @private
     * @ignore
     */
    private function __construct() {}

    /**
     * ライブラリを初期化します。
     * @private
     */
    public static function init()
    {
        if (self::$instance !== null) {
            return;
        }

        // コアの設定ファイルを読み込む
        Roose_Config::addLoadPath(ROOSE_COREPATH . 'config/');

        // 出力バッファリングを始める
        ob_start();

        self::$instance = new self();
    }
}
