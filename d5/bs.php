<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('D5_COREPATH') or define('D5_COREPATH', dirname(__FILE__) . DS);
defined('D5_THIRDPARTY') or define('D5_THIRDPARTY', D5_COREPATH . 'thirdparty' . DS);
defined('D5_CLASSES') or define('D5_CLASSES', D5_COREPATH . 'classes' .DS);

require D5_CLASSES . 'autoloader.php';

//-- オートローダを初期化
D5_Autoloader::addNamespace('D5', D5_CLASSES);
D5_Autoloader::addBasePath(D5_CLASSES);

// クラスの別名を設定
D5_Autoloader::classAlias(array(
    'D5_Arr'     => 'Arr',
    'D5_Calendar' => 'Calendar',
    'D5_Config'  => 'Config',
    'D5_Cookie'  => 'Cookie',
    'D5_DB'      => 'DB',
    'D5_File'    => 'File',
    'D5_Input'   => 'Input',
    'D5_Session' => 'Session',
    'D5_Security'=> 'Security'
));

// クラスのパスを設定
D5_Autoloader::addClass(array(
    'Smarty' => D5_THIRDPARTY . 'smarty/Smarty.class.php'
));

// オートローダを登録
D5_Autoloader::regist();

// ライブラリ初期化処理
D5_Core::init();
