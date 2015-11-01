<?php
/**
 * Mail用の例外クラス
 * @ignore
 */
class CL_Mail_Exception extends Exception {}


/**
 * 不正なメールアドレスが渡された時の例外クラス
 * @ignore
 */
class CL_Mail_InvalidSubjectException extends CL_Mail_Exception {}


/**
 * 不正な添付ファイルが指定された時の例外クラス
 * @ignore
 */
class CL_Mail_AttachFailedException extends CL_Mail_Exception {}


/**
 * メールの送信を行います。
 *
 * @TODO 送信者名、受信者名の設定サポート
 * @TODO 半角文字の取り扱いについて調査
 * @TODO メール送信フック機能（ロギング用）の実装について
 */
class CL_Mail
{
    private $from;

    private $to;
    private $cc;
    private $bcc;

    private $subject;

    private $body;
    private $bodyType;
    private $bodyCharset;

    /**
     * @var array(array(string, string)) [0]=>ファイルパス, [1]=>MIME Type
     */
    private $attaches;
    private $boundary;


    /**
     * 文字列が正しいメールアドレスか検証します。
     *
     * @param string $address 検証する文字列
     * @return bool
     */
    private static function isValidEmailAddress($address)
    {
        return is_string($address) and filter_var($address, FILTER_VALIDATE_EMAIL);
    }


    /**
     * 指定されたファイルのMIME Content-Typeを取得します。
     *
     * @param string    $filepath   調べるファイルのパス
     * @return string
     */
    private static function getMimeType($filepath)
    {
        // @TODO mime_content_typeは非推奨なので、いつか実装を変える
        return mime_content_type($filePath);
    }


    public function __construct($config = array())
    {
        if (is_string($config)) {
            $config = CL_Config::get('mail');
        }

        $config = array_merge(array(
            'from'          => null,
            'from_name'     => null
        ), $config);

        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->attaches = array();
        $this->bodyCharset = mb_internal_encoding();
    }


    /**
     * 与えられた文字列に不正なEmailアドレスが含まれていないか検証します。
     *
     * すべての文字列がEmailアドレスとして正当であればEmailアドレスの配列を返し、
     * 一つでもEmailアドレスとして不正な文字列があれば、例外をスローします。
     *
     * 例外のエラーメッセージは第２引数に指定します。
     *
     * @param array|string  $values     検証する文字列もしくは、文字列の配列
     * @param string        $errorMsg   エラーメッセージ
     * @throws CL_Mail_Exception
     * @return array(string)
     */
    private function validAddresses($values, $errorMsg)
    {
        $noInvalid   = true;
        $addresses  = (array) $values;

        foreach ($addresses as $val) {
            $noInvalid = self::isValidEmailAddress($val) and $noInvalid;
        }

        if ($noInvalid === false) {
            throw new CL_Mail_Exception('不正なメールアドレスが渡されました。 ('.$errorMsg.')');
        }

        return $addresses;
    }


    /**
     * @ignore
     */
    private function addAddressToList(&$store, &$list, $errMsg)
    {
        $list = $this->validAddresses($list, $errMsg);
        $store = array_merge($store, $list);
    }


    /**
     * メールの送信者を設定します。
     *
     * @param string  $address    有効なメールアドレス
     * @return Mail
     * @throws CL_Mail_Exception
     * @TODO Add second arguments ($from_name)
     */
    public function from($from)
    {
        $list = $this->validAddresses($from, 'from');
        $this->from = $list[0];
        return $this;
    }


    // #to(), #cc(), #bcc() is push email address to interal list.
    //   join in before mail sending.

    /**
     * メールの送信先を追加します。
     *
     * @param string|array  $to
     *      有効なメールアドレスか、メールアドレスの配列
     * @return Mail
     * @throws CL_Mail_Exception
     */
    public function to($to)
    {
        $this->addAddressToList($this->to, $to, 'to');
        return $this;
    }


    /**
     * CC送信先を追加します。
     *
     * @param string|array  $cc
     *      有効なメールアドレスか、メールアドレスの配列
     * @return Mail
     * @throws CL_Mail_Exception
     */
    public function cc($cc)
    {
        $this->addAddressToList($this->cc, $cc, 'cc');
        return $this;
    }


    /**
     * BCC送信先を追加します。
     *
     * @param string|array  $bcc
     *      有効なメールアドレスか、メールアドレスの配列
     * @return Mail
     * @throws CL_Mail_Exception
     */
    public function bcc($bcc)
    {
        $this->addAddressToList($this->bcc, $bcc, 'bcc');
        return $this;
    }


    /**
     * 件名を設定します。
     *
     * 件名に改行を含むことは出来ません。
     * 改行を含んだ文字列が渡された時、例外をスローします。
     *
     * @param string    $subject    件名
     * @return Mail
     * @throws CL_Mail_InvalidSubjectException
     */
    public function subject($subject)
    {
        if (preg_match('/\r|\n|\r\n/', $subject) === 1) {
            throw new CL_Mail_InvalidSubjectException('件名に改行が含まれています。');
        }

        $this->subject = $subject;
        return $this;
    }


    /**
     * メールの本文を設定します。
     *
     * @param string    $body   メール本文
     * @return Mail
     */
    public function body($body)
    {
        $this->bodyType = 'text/plain';
        $this->body = $body;
        return $this;
    }


    /**
     * メールの本文にHTMLを設定します。
     *
     * @param string    $body   メール本文
     * @return Mail
     */
    public function htmlBody($body)
    {
        $this->bodyType = 'text/html';
        $this->body = $body;
        return $this;
    }


    /**
     * 添付ファイルを追加します。
     *
     * @param array|File $file
     *      ファイルパスか、Fileインスタンス、もしくは、それらを含んだ配列
     * @param string        $fileName=null
     *      (任意) 添付後のファイル名。省略された時はファイル名をそのまま設定します。
     * @return Mail
     */
    public function attachFile($file, $fileName = null)
    {
        $files = is_object($file) ? array($file) : (array) $file;
        $fileList = array();

        // 配列が渡されたら分割して処理
        if (is_array($file)) {
            foreach ($file as $arg) {
                if (is_array($arg) and count($arg)) {
                    $this->attachFile($arg[0], $arg[1]);
                }
                else {
                    $arg = (array) $arg;
                    $this->attachFile($arg[0]);
                }
            }

            return $this;
        }

        // ファイルパスの取り出し
        if (is_string($file)) {
            $filePath = $file;
        }
        else if ($file instanceOf File) {
            $filePath = $file->getFilename();
        }
        else {
            throw new InvalidArgumentException('許可されていない型の値が渡されました。');
        }

        // 存在チェック
        if (! file_exists($filePath)) {
            throw new CL_Mail_AttachFailedException("添付されたファイルが存在しません。");
        }

        // 添付時のファイル名を決定してリストに追加
        is_string($fileName) or $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $this->attaches[] = array($filePath, $fileName);

        return $this;
    }


    // send the mail.
    // if address list contains invalid address, or No exists file attached, throw CL_Mail_Exception
    // if mail submit failed, throw CL_Mail_Exception
    /**
     * メールを送信します。
     *
     * @see http://web-terminal.blogspot.jp/2014/04/php-file-mail-pear.html パクリ元
     */
    public function send()
    {
        $result = mail(
            $this->buildTo(),
            $this->buildSubject(),
            $this->buildBody(),
            $this->buildHeader()
        );

        if ($result === false) {
            throw new CL_Mail_Exception("メールの送信に失敗しました。");
        }
    }


    /**
     *
     */
    private function buildBody()
    {
        if (empty($this->attaches)) {
            return mb_convert_encoding($this->body, 'JIS', $this->bodyCharset);
        }
        else {
            return $this->buildAttachedBody();
        }
    }


    /**
     * @ignore
     * @see
     */
    private function buildHeader()
    {
        $header = "";

        $header .= "X-Mailer: PHP5\r\n";
        $header .= $this->buildFrom();
        // $header .= "Return-Path: " . $this->buildFrom() . "\r\n";
        $header .= $this->buildCc();
        $header .= $this->buildBcc();
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n";
        $header .= $this->buildContentTypeHeader();

        return $header;
    }


    /**
     * Boundary文字列を生成し、返します。
     * @return string
     */
    private function boundary()
    {
        if (empty($this->boundary)) {
            $this->boundary = 'CodeLapseBoundary'.md5(rand());
        }

        return $this->boundary;
    }


    /**
    *
    */
    private function buildTo()
    {
        return implode(',', $this->to);
    }


    /**
     * @ignore
     */
    private function buildFrom()
    {
        //$from = mb_encode_mimeheader($this->from, 'ISO-')
        return 'From: '.$this->from."\r\n";
    }


    /**
     *
     */
    private function buildCc()
    {
        $cc = '';
        if (! empty($this->cc)) {
            $cc = 'Cc: '.implode(',', $this->cc)."\r\n";
        }
        return $cc;
    }


    /**
     *
     */
    private function buildBcc()
    {
        $bcc = '';
        if (! empty($this->bcc)) {
            $bcc = 'Bcc: '.implode(',', $this->bcc)."\r\n";
        }
        return $bcc;
    }


    /**
     *
     */
    private function buildSubject()
    {
        return mb_encode_mimeheader($this->subject, 'ISO-2022-JP', 'B');
    }


    /**
     *
     */
    private function buildContentTypeHeader()
    {
        if (! empty($this->attaches)) {
            return "Content-Type: multipart/mixed; boundary=\"".$this->boundary()."\"\n";
        }
        else {
            return "Content-Type: text/plain; charset=\"iso-2022-jp\"\n";
        }
    }


    /**
     *
     */
    private function buildAttachedBody()
    {
        $boundary = $this->boundary();
        $body = '';

        // メール本文を設定
        $body .= "--".$boundary."\n";
        $body .= "Content-Type: ".$this->bodyType."; charset=\"iso-2022-jp\"\n";
        $body .= "Content-Transfer-Encoding: 7bit\n";
        $body .= "\n";
        $body .= mb_convert_encoding($this->body, 'JIS', $this->bodyCharset)."\n";

        //
        foreach($this->attaches as $attach) {
            $file = $attach[0];
            $attachName = mb_encode_mimeheader($attach[1], 'ISO-2022-JP', 'B');

            if (! file_exists($file)) {
                throw new CL_Mail_AttachFailedException('添付されたファイルが存在しません。');
            }

            $body .= "\n";
            $body .= "--".$boundary."\n";
            $body .= "Content-Type: application/octet-stream; charset=\"iso-2022-jp\" name=\"".$attachName."\"\n";
            $body .= "Content-Transfer-Encoding: base64\n";
            $body .= "Content-Disposition: attachment; filename=\"".$attachName."\"\n";
            $body .= "\n";
            $body .= chunk_split(base64_encode(file_get_contents($file)));
            $body .= "\n";
        }

        $body .= '--'.$boundary.'--';
        return $body;
    }
}
