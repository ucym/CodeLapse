<?php
namespace CodeLapse\View\Driver;

use CodeLapse\Arr;
use CodeLapse\Config;
use CodeLapse\View\Driver;

/**
 * Smartyドライバ
 */
class Smarty implements Driver
{
    protected $smarty;

    public function __construct()
    {
        $this->smarty = $sm = new Smarty();

        $conf = Config::get('view.smarty');
        array_merge($conf, $config);

        $sm->template_dir = Arr::get($conf, 'template_dir') . DS;
        $sm->compile_dir  = Arr::get($conf, 'compile_dir') . DS;
        $sm->config_dir   = Arr::get($conf, 'config_dir') . DS;
        $sm->cache_dir    = Arr::get($conf, 'cache_dir') . DS;
        $sm->caching      = Arr::get($conf, 'caching', true);
    }

    public function getNativeDriver()
    {
        return $this->smarty;
    }

    public function render($filePath, $variables)
    {
        $this->smarty->assign($variables);
        $buffer = $this->smarty->fetch($filePath);
        $this->smarty->clearAllAssign();

        return $buffer;
    }
}
