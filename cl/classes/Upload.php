<?php
namespace CodeLapse;

use CodeLapse\Arr;
use CodeLapse\Exception\UploadException;

class Upload
{
    protected static $finfo;

    protected static function getFinfo()
    {
        if (empty(self::$finfo)) {
            self::$finfo = new \finfo(FILEINFO_MIME_TYPE);
        }

        return self::$finfo;
    }

    /**
     * @param array     $file       アップロードされたファイル情報(`$_FILES`内の要素)
     * @return bool
     */
    protected static function validFile($file)
    {
        return ! empty($file)
            and isset($file['error'])
            and is_int($file['error']);
    }


    /**
     * $_FILES内から配列形式のファイル情報を取得します。
     * @param string    $name       複数のアップロードファイル情報を持つ$_FILESの要素のキー名
     * @return CodeLapse\Upload[]
     */
    public static function getArrayFiles($name)
    {
        $rawFileInfos = Arr::get($_FILES, $name);
        $errors = Arr::get($rawFileInfos, 'error');
        
        if (! is_array($errors)) {
            return [];
        }

        $files = [];
        foreach ($rawFileInfos as $attr => $values) {
            foreach ($values as $index => $value) {
                Arr::set($files, "$index.$attr", $value);
            }
        }

        $instances = [];
        foreach ($files as $f) {
            if (! self::validFile($f)) { continue; }
            $instances[] = new self($f);
        }

        return $instances;
    }

    /**
     * @param string    $name       $_FILES内のフィールド名
     * @return Upload
     */
    public static function get($name)
    {
        $file = Arr::get($_FILES, $name);
        if (! self::validFile($file)) { return; }

        return new self($file);
    }




    protected $file;

    protected function __construct(array $file)
    {
        $this->file = $file;
    }

    /**
     * クライアントからアップロードされた時のファイル名を取得します。
     * @return string
     */
    public function uploadFileName()
    {
        return $this->file['name'];
    }

    /**
     * 保存されたテンポラリファイル名を取得します。
     * @param string
     */
    public function tmpPath()
    {
        return $this->file['tmp_name'];
    }

    /**
     * アップロードされたファイルのバイト単位のサイズを取得します。
     * @param int   バイト単位のファイルサイズ
     */
    public function byteSize()
    {
        return $this->file['size'];
    }

    /**
     * アップロードされたファイルのMIMETypeを取得します。
     * @return string
     */
    public function mimeType()
    {
        if (! class_exists('finfo')) {
            return mime_content_type($this->tmpPath());
        }

        return self::getFinfo()->file($this->tmpPath());
    }

    /**
     * ファイルにエラーがないかチェックし、エラーがあれば例外をスローします。
     * @throws CodeLapse\Exception\UploadException
     */
    public function checkError()
    {
        $err = $this->file['error'];

        if ($err === UPLOAD_ERR_OK) { return; }

        $humanizeError = array(
            UPLOAD_ERR_INI_SIZE     => 'UPLOAD_ERR_INI_SIZE',
            UPLOAD_ERR_FORM_SIZE    => 'UPLOAD_ERR_FORM_SIZE',
            UPLOAD_ERR_PARTIAL      => 'UPLOAD_ERR_PARTIAL',
            UPLOAD_ERR_NO_FILE      => 'UPLOAD_ERR_NO_FILE',
            UPLOAD_ERR_NO_TMP_DIR   => 'UPLOAD_ERR_NO_TMP_DIR',
            UPLOAD_ERR_CANT_WRITE   => 'UPLOAD_ERR_CANT_WRITE',
            UPLOAD_ERR_EXTENSION    => 'UPLOAD_ERR_EXTENSION'
        );

        throw new UploadException($humanizeError[$err], $err);
    }

    /**
     * エラーコードを取得します。
     * @return int
     */
    public function errorCode()
    {
        return $this->file['error'];
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->file['error'] !== UPLOAD_ERR_OK;
    }

    /**
     * ファイルがアップロードされているか確認します。
     * @return bool
     */
    public function hasFile()
    {
        return $this->file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * アップロードされたファイルのMIMETypeが
     * 指定されたMIMETypeのどれかと一致するか確認します。
     * @param string|array      $mimeTypes      確認するMIMEType
     * @return bool
     */
    public function isMimeIn($mimeTypes)
    {
        $mimes = (array) $mimeTypes;
        $match = array_search($this->mimeType(), $mimeTypes, true);
        return $match !== false;
    }

    /**
     * アップロードされたファイルを指定されたパスへ移動します。
     * @param string    $saveTo     保存先のフルパス
     * @return bool
     */
    public function saveTo($saveTo)
    {
        if (! file_exists($this->tmpPath())) {
            return false;
        }

        return move_uploaded_file($this->tmpPath(), $saveTo);
    }
}
