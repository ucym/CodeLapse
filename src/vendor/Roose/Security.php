<?php
/**
 * セキュリティ系ユーティリティクラス
 * 
 * @package Roose
 * @author うちやま
 * @since PHP 5.2.17
 * @version 1.0.0
 */ 
class Roose_Security
{
    /**
     * 文字列を安全なHTMLへ変換します。
     * @param string $value エスケープする文字列
     * @param boolean $stripslashes (optional) stripslashesを適用するか指定します。標準はfalseです。
     */
    public static function safeHtml($value, $stripslashes = false)
    {
        $value = htmlspecialchars($value);
        $stripslashes and ($value = stripslashes($value));
        return $value;
    }

    /**
     * 安全なCSV形式になるように、カンマ・改行をエスケープします。
     * @param string $value エスケープする文字列
     * @param boolean|null $use_nl2br (optional) trueを指定すると、改行をbrタグに置換します。標準はfalseです。
     * @return string
     */
    public static function safeCsv($value, $use_nl2br = false)
    {
        $use_nl2br and ($value = nl2br($m));
        $value = preg_replace("/(\r|\n|\r\n)/", "", $value);
        $value = preg_replace('/,/', '&#44;', $m);
        return $value;
    }
}
