<?php
/**
 * セキュリティ系ユーティリティクラス
 *
 * @package CodeLapse
 */
class CL_Security
{
    /**
     * 文字列を安全なHTMLへ変換します。
     * @param string $value エスケープする文字列
     * @param boolean $stripslashes (optional) stripslashesを適用するか指定します。標準はfalseです。
     * @param array $config (optional) コンフィグを指定します。指定されていない場合はconfig/security.phpの値が適用されます。
     */
    public static function safeHtml($value, $stripslashes = false, $config = array())
    {
        $config = array_merge(CL_Config::get('security'), $config);


        list($useHtmlEntities, $flags, $encoding, $noDoubleEncode) = array_values(
            CL_Arr::get($config,
                array(
                    'useHtmlEntities',
                    'flags',
                    'encoding',
                    'noDoubleEncode',
                )
            )
        );

        if($useHtmlEntities)
        {
            $value = htmlentities($value, $flags, $encoding, !$noDoubleEncode);
        }
        else
        {
            $value = htmlspecialchars($value, $flags, $encoding, !$noDoubleEncode);
        }

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
