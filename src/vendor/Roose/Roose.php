<?php
/**
 * Rooseライブラリの初期化処理を担うクラス
 */
class Roose
{
    private $instance;

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

        // 出力バッファリングを始める
        ob_start();

        self::$instance = new self;
    }
}
