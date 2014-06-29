<?php
require 'vendor/Roose/Autoloader.php';

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOSE_COREPATH') or define('ROOSE_COREPATH', dirname(__FILE__) . DS);
defined('ROOSE_CLASSES') or define('ROOSE_CLASSES', ROOSE_COREPATH . 'classes' .DS);


//-- オートローダを初期化
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

// オートローダを登録
Roose_Autoloader::regist();

// ライブラリ初期化処理
Roose_Roose::init();
