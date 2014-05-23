<?php
require 'vendor/Roose/Autoloader.php';

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('ROOSE_COREPATH', dirname(__FILE__) . DS . "vendor" . DS);
//define('ROOSE_CLASSES', ROOSE_COREPATH . 'classes/');

// クラスのパスを設定
/*
Roose_Autoloader::addClass(array(
    'Roose_Arr'     => ROOSE_CLASSES . 'Arr.php',
    'Roose_File'    => ROOSE_CLASSES . 'File.php',
    'Roose_Input'   => ROOSE_CLASSES . 'Input.php',
    'Roose_Cookie'  => ROOSE_CLASSES . 'Cookie.php',
    'Roose_Session' => ROOSE_CLASSES . 'Session.php',
    'Roose_Security'=> ROOSE_CLASSES . 'Security.php'
));
 */

Roose_Autoloader::setBasePath(ROOSE_COREPATH);

// クラスの別名を設定
Roose_Autoloader::classAlias(array(
    'Roose_Arr'     => 'Arr',
    'Roose_File'    => 'File',
    'Roose_Input'   => 'Input',
    'Roose_Cookie'  => 'Cookie',
    'Roose_Session' => 'Session',
    'Roose_Security'=> 'Security'
));

Roose_Autoloader::regist();