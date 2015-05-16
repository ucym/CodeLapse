<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('CL_COREPATH') or define('CL_COREPATH', dirname(__FILE__) . DS);
defined('CL_THIRDPARTY') or define('CL_THIRDPARTY', CL_COREPATH . 'thirdparty' . DS);
defined('CL_CLASSES') or define('CL_CLASSES', CL_COREPATH . 'classes' .DS);

require CL_CLASSES . 'AutoLoader.php';
require CL_COREPATH.'functions.php';

//-- オートローダを初期化
\CodeLapse\AutoLoader::addNamespace('CodeLapse', CL_CLASSES);
\CodeLapse\AutoLoader::addBasePath(CL_CLASSES);

// クラスの別名を設定
\CodeLapse\AutoLoader::classAlias(array(
    'Arr'       => '\CodeLapse\Arr',
    'Calendar'  => '\CodeLapse\Calendar',
    'Config'    => '\CodeLapse\Config',
    'Cookie'    => '\CodeLapse\Cookie',
    'DB'        => '\CodeLapse\DB',
    'File'      => '\CodeLapse\File',
    'Form'      => '\CodeLapse\Form',
    'Input'     => '\CodeLapse\Input',
    'Mail'      => '\CodeLapse\Mail',
    'Pager'     => '\CodeLapse\Pager',
    'Security'  => '\CodeLapse\Security',
    'Session'   => '\CodeLapse\Session'
));

// クラスのパスを設定
\CodeLapse\AutoLoader::addClass(array(
    'Smarty' => CL_THIRDPARTY . 'smarty/Smarty.class.php'
));

// オートローダを登録
\CodeLapse\AutoLoader::regist();

// ライブラリ初期化処理
\CodeLapse\Core::init();
