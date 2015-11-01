<?php
/**
 * RESTful APIを簡略に用意するためのクラスです。
 *
 * ## コントローラを定義する
 * ``` php
 * <?php
 * // on 'api/group.php'
 * require 'cl/bs.php';
 *
 * CodeLapse\Controller\Rest::create()
 * ->whenGet(function () {
 *     // GETメソッドでアクセスされた時のコールバック
 *     $group_id = Req::get('id');
 *     $rs = DB::query('SELECT * FROM `groups` WHERE `group_id` = ?', array($group_id));
 *     return array(
 *         'result'     => true,
 *         'groups'     => $rs->fetchAll()
 *     );
 * })
 *
 * ->whenPost(function () {
 *     // POSTメソッドでアクセスされた時のコールバック
 *     list($group_name, $group_leader) = Req::post(array('group_name', 'leader_id'));
 *     $result = DB::query('INSERT INTO `groups`(`name`, `leader_id`) VALUES (?, ?)', array(
 *         $group_name, $group_leader
 *     ));
 *     return array('result' => $result !== false);
 * })
 *
 * ->handleException(function ($exception) {
 *     // コントローラの処理中にエラーが起きた際の処理
 *     return array(
 *         'result'     => false,
 *         'message'    => $exception->getMessage()
 *     );
 * })
 *
 * // コントローラによるハンドリングを実行する
 * // （このメソッド実行後、スクリプトは終了します。）
 * ->execute();
 * ```
 *
 * ### コールバック関数
 * コールバック関数は何かしらの値を返すことが出来ます。<br>
 * 返された値が配列であった場合、RestControllerは配列をJSON文字列へ変換し<br>
 * レスポンスとして返します。
 *
 * @package CodeLapse\Controller
 */
class CL_Controller_Rest
{
    /**
     * Restのインスタンスを生成します。
     * スムーズなメソッドチェーンを行うためのメソッドです。
     * @return Rest
     */
    public static function create()
    {
        return new static();
    }

    private $crossOriginAccepted = false;
    private $acceptedOrigins;

    private $handlers = array();
    private $exceptionHandler;
    private $unsupportedHandler;

    private function processResponse($data)
    {
        if (is_array($data)) {
            header('Content-Type: application/json');
            return json_encode($data);
        }
        else {
            return $data;
        }
    }

    private function executeHandler()
    {
        $method     = CL_Request::method();
        $handler    = CL_Arr::get($this->handlers, $method);
        $response   = null;

        if (is_callable($handler)) {
            $response = $handler();
        }
        else {
            $handler = $this->unsupportedHandler;

            if (is_callable($handler)) {
                $response = $this->unsupportedHandler();
            }
        }

        if (! empty($response)) {
            return $this->processResponse($response);
        }
    }

    private function executeExceptionHandler(\Exception $e)
    {
        $handler = $this->exceptionHandler;

        if (is_callable($handler)) {
            $response = $handler($e);

            if (! empty($response)) {
                return $this->processResponse($response);
            }
        }
    }

    /**
     * 現在サーバーで受け付けているリクエストに対してハンドリングを行います。
     * **このメソッドは処理が完了するとスクリプトを終了します。**
     * @param callable?         $before_shutdown        スクリプト終了前に実行されるコールバック関数
     */
    public function execute(callable $before_shutdown = null)
    {
        if ($this->crossOriginAccepted) {
            header('Access-Control-Allow-Origin: ' . $this->acceptedOrigins);
        }

        try {
            $response = $this->executeHandler();
        } catch (\Exception $e) {
            $response =  $this->executeExceptionHandler($e);
        }

        if (! empty($response)) {
            echo $response;
        }

        is_callable($before_shutdown) and $before_shutdown();

        exit;
    }

    /**
     * クロスオリジンのアクセスを許可します。
     *
     * @param string        $origins        許可するオリジン（複数指定は空白区切りで行う）
     * @return Rest
     */
    public function acceptCrossOrigin($origins = null)
    {
        $this->crossOriginAccepted = true;

        $origins === null and $origins = '*';
        $this->acceptedOrigins = $origins;

        return $this;
    }

    /**
     * 指定されたメソッドでアクセスされた時のハンドラを指定します。
     *
     * @param string        $method         ハンドリングするHTTPメソッド名
     * @param callable      $fn             ハンドラ関数
     * @return Rest
     */
    public function when($method, callable $fn)
    {
        $this->handlers[strtoupper($method)] = $fn;
        return $this;
    }

    /**
     * GETメソッドでアクセスされた時のハンドラを指定します。
     *
     * @param callable      $fn             ハンドラ関数
     * @return Rest
     */
    public function whenGet(callable $fn)
    {
        return $this->when('get', $fn);
    }


    /**
     * POSTメソッドでアクセスされた時のハンドラを指定します。
     *
     * @param callable      $fn             ハンドラ関数
     * @return Rest
     */
    public function whenPost(callable $fn)
    {
        return $this->when('post', $fn);
    }

    /**
     * DELETEメソッドでアクセスされた時のハンドラを指定します。
     *
     * @param callable      $fn             ハンドラ関数
     * @return Rest
     */
    public function whenDelete(callable $fn)
    {
        return $this->when('delete', $fn);
    }

    /**
     * PUTメソッドでアクセスされた時のハンドラを指定します。
     *
     * @param callable      $fn             ハンドラ関数
     * @return Rest
     */
    public function whenPut(callable $fn)
    {
        return $this->when('put', $fn);
    }

    /**
     * PATCHメソッドでアクセスされた時のハンドラを指定します。
     *
     * @param callable      $fn             ハンドラ関数
     * @return Rest
     */
    public function whenPatch(callable $fn)
    {
        return $this->when('patch', $fn);
    }

    /**
     * ハンドリングされていないメソッドへアクセスされた時のハンドラを指定します。
     *
     * @param callable      $fn             ハンドラ関数
     * @return Rest
     */
    public function whenUnsupportedMethod(callable $fn)
    {
        $this->unsupportedHandler = $fn;
        return $this;
    }

    /**
     * ハンドラ実行中に例外が発生した時のハンドラを指定します。
     *
     * @param callable      $fn             ハンドラ関数 (Exception $e)
     * @return Rest
     */
    public function handleException(callable $fn)
    {
        $this->exceptionHandler = $fn;
        return $this;
    }
}
