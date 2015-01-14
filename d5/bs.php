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
    'Arr'       => 'D5_Arr',
    'Calendar'  => 'D5_Calendar',
    'Config'    => 'D5_Config',
    'Cookie'    => 'D5_Cookie',
    'DB'        => 'D5_DB',
    'File'      => 'D5_File',
    'Form'      => 'D5_Form',
    'Input'     => 'D5_Input',
    'Mail'      => 'D5_Mail',
    'Security'  => 'D5_Security',
    'Session'   => 'D5_Session'
));

// クラスのパスを設定
D5_Autoloader::addClass(array(
    'Smarty' => D5_THIRDPARTY . 'smarty/Smarty.class.php'
));

// オートローダを登録
D5_Autoloader::regist();

// ライブラリ初期化処理
D5_Core::init();
