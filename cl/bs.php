<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('CL_COREPATH') or define('CL_COREPATH', dirname(__FILE__) . DS);
defined('CL_THIRDPARTY') or define('CL_THIRDPARTY', CL_COREPATH . 'thirdparty' . DS);
defined('CL_CLASSES') or define('CL_CLASSES', CL_COREPATH . 'classes' .DS);

require CL_CLASSES . 'AutoLoader.php';
require CL_COREPATH . 'functions.php';

//-- オートローダを初期化
CL_AutoLoader::addNamespace('CL', CL_CLASSES);
CL_AutoLoader::addBasePath(CL_CLASSES);

// クラスの別名を設定
CL_AutoLoader::classAlias(array(
    'Arr'       => 'CL_Arr',
    'Config'    => 'CL_Config',
    'Cookie'    => 'CL_Cookie',
    'DB'        => 'CL_DB',
    'File'      => 'CL_File',
    'Form'      => 'CL_Form',
    'Req'       => 'CL_Request',
    'Mail'      => 'CL_Mail',
    'Pager'     => 'CL_Pager',
    'Security'  => 'CL_Security',
    'Session'   => 'CL_Session',
    'RestController'    => 'CL_Controller\Rest',
    'Upload'    => 'CL_Upload',
    'Validator' => 'CL_Validator'
));

// クラスのパスを設定
CL_AutoLoader::addClass(array(
    'Smarty'        => CL_THIRDPARTY . 'smarty/Smarty.class.php',
    'CodeLapse\Valitron\Validator'
                    => CL_THIRDPARTY . 'cl-valitron/src/CodeLapse/Valitron/Validator.php'
));

// オートローダを登録
CL_AutoLoader::regist();

// 設定ファイルのディレクトリを設定
Config::addLoadPath(CL_COREPATH . 'config/');

// 出力バッファリングを始める
// （コマンドラインで実行中はバッファリングしない。表示結果が得られなくなってしまうため）
PHP_SAPI !== 'cli' and ob_start();
