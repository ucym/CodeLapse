<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOSE_COREPATH') or define('ROOSE_COREPATH', dirname(__FILE__) . DS);
defined('ROOSE_THIRDPARTY') or define('ROOSE_THIRDPARTY', ROOSE_COREPATH . 'thirdparty' . DS);
defined('ROOSE_CLASSES') or define('ROOSE_CLASSES', ROOSE_COREPATH . 'classes' .DS);

require ROOSE_CLASSES . 'autoloader.php';

//-- オートローダを初期化
Roose_Autoloader::addNamespace('Roose', ROOSE_CLASSES);
Roose_Autoloader::addBasePath(ROOSE_CLASSES);

// クラスの別名を設定
Roose_Autoloader::classAlias(array(
    'Roose_Arr'     => 'Arr',
    'Roose_Calendar' => 'Calendar',
    'Roose_Config'  => 'Config',
    'Roose_Cookie'  => 'Cookie',
    'Roose_File'    => 'File',
    'Roose_Input'   => 'Input',
    'Roose_Session' => 'Session',
    'Roose_Security'=> 'Security'
));

// クラスのパスを設定
Roose_Autoloader::addClass(array(
    'Smarty' => ROOSE_THIRDPARTY . 'smarty/Smarty.class.php'
));

// オートローダを登録
Roose_Autoloader::regist();

// ライブラリ初期化処理
Roose_Core::init();
