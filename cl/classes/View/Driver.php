<?php
namespace CodeLapse\View;

/**
 * Viewで利用されるドライバの基底クラス
 */
interface Driver
{
    public function __construct($config = array());

    public function getNativeDriver();

    public function render($filePath, $variables = array());
}
