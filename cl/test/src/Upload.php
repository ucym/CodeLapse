<?php
use CodeLapse\Upload;

/**
 * - 正規のアップロードファイルではないのでsaveToメソッドの動作確認ができない
 */
class UploadTest extends PHPUnit_Framework_TestCase
{
    protected static $inited;
    protected static $tmpDir;
    protected static $assetDir;

    public static function init()
    {
        if (self::$inited) { return; }

        self::$inited = true;
        self::$tmpDir = __DIR__.'/../tmp/';
        self::$assetDir = __DIR__.'/../asset/';

        mkdir(self::$tmpDir, 0666);
    }

    public static function tearDownAfterClass()
    {
        rmdir(self::$tmpDir);
    }

    public function fileInitSingle()
    {
        self::init();

        $filename = self::$assetDir.'test.txt';

        $_FILES = [
            'file'      => [
                'name'      => 'test.txt',
                'type'      => 'text/plain',
                'size'      => filesize($filename),
                'tmp_name'  => $filename,
                'error'     => UPLOAD_ERR_OK
            ]
        ];

        return [[$filename]];
    }

    public function fileInitFakeType()
    {
        self::init();

        $filename = self::$assetDir.'test.jpg';

        $_FILES = [
            'file'      => [
                'name'      => 'test.jpg',
                'type'      => 'application/json',
                'size'      => filesize($filename),
                'tmp_name'  => $filename,
                'error'     => UPLOAD_ERR_OK
            ]
        ];

        return [[$filename]];
    }

    public function fileInitUploadError()
    {
        self::init();

        $_FILES = [
            'file'      => [
                'name'      => '',
                'type'      => '',
                'size'      => 0,
                'tmp_name'  => '',
                'error'     => UPLOAD_ERR_NO_FILE
            ]
        ];
    }

    public function fileInitMultiFile()
    {
        self::init();

        $file1 = self::$assetDir.'test.jpg';
        $file2 = self::$assetDir.'test.txt';

        $_FILES = [
            'file'      => [
                'name'      => ['test.jpg', 'test.txt', ''],
                'type'      => ['image/jpeg', 'type', ''],
                'size'      => [filesize($file1), filesize($file2), ''],
                'tmp_name'  => [$file1, $file2, ''],
                'error'     => [UPLOAD_ERR_OK, UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE],
            ]
        ];
    }

    /**
     * @covers CodeLapse\Upload::get
     * @covers CodeLapse\Upload::uploadFileName
     * @covers CodeLapse\Upload::tmpPath
     * @covers CodeLapse\Upload::byteSize
     * @covers CodeLapse\Upload::mimeType
     * @covers CodeLapse\Upload::checkError
     * @covers CodeLapse\Upload::errorCode
     * @covers CodeLapse\Upload::hasError
     * @covers CodeLapse\Upload::hasFile
     * @covers CodeLapse\Upload::isMimeIn
     * @covers CodeLapse\Upload::saveTo
     *
     */
    public function testGet($filename)
    {
        $this->fileInitSingle();
        $file = Upload::get('file');

        var_dump($file);

        // 正常系チェック
        $this->assertEquals('test.txt', $file->uploadFileName(), 'ファイル名一致チェック');
        $this->assertEquals($filename, $file->tmpPath(), 'テンポラリファイルパス一致チェック');
        $this->assertEquals(filesize($filename), $file->byteSize(), 'ファイルサイズ 一致チェック');
        $this->assertEquals('text/plain', $file->mimeType(), 'MIME-Type一致チェック');
        $this->assertNull($file->checkError(), 'エラーチェック正常チェック');
        $this->assertEquals(UPLOAD_ERR_OK, $file->errorCode(), 'エラーコードチェック');
        $this->assertFalse($file->hasError(), 'アップロードエラーチェック');
        $this->assertTrue($file->hasFile(), 'ファイルアップロード判定チェック');
        $this->assertTrue($file->isMimeIn(array('image/jpeg', 'image/png', 'text/plain')), 'MIMEType判定チェック 1');
        $this->assertFalse($file->isMimeIn(array('image/jpeg', 'image/png')), 'MIMEType判定チェック 1');


        $this->assertFalse($file->saveTo(self::$tmpDir.'moved.txt'), 'ファイル移動チェック');
    }

    /**
     * @dataProvider fileInitMultiFile
     */
    public function testGetArrayFiles()
    {
        // TODO
    }

    /**
     * @dataProvider fileInitFakeType
     */
    public function testGivenFakeInfo()
    {
        $file = Upload::get('file');
        $this->assertEquals('image/jpeg', $file->mimeType(), '正規MIME-Type検出チェック');
    }

    /**
     * @dataProvider fileInitMultiFile
     */
    public function testRejectCorruption()
    {
        // $_FILES['file'] に単一のファイル情報を期待したが
        // 複数ファイルが送られてきた場合にインスタンスを返さないかチェック
        $file = Upload::get('file');
        $this->assertNull($file, '$_FILES Corruption対応チェック');
    }

    /**
     * @dataProvider fileInitUploadError
     * @expectedException CodeLapse\Exception\UploadException
     */
    public function testThrowException()
    {
        $file = Upload::get('file');
        $file->checkError();
    }
}
