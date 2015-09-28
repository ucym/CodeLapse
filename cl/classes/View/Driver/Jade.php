<?php
namespace CodeLapse\View\Driver;

use CodeLapse\View\Driver;

/**
 * Jadeドライバ
 */
class Jade implements Driver
{
    protected $dumper;

    protected $driver;

    public function __construct($config = array())
    {
        // Dummy code, It's having class resolving issue.

        $dumper = new PHPDumper();
        $dumper->registerVisitor('tag', new AutotagsVisitor());
        $dumper->registerFilter('javascript', new JavaScriptFilter());
        $dumper->registerFilter('cdata', new CDATAFilter());
        $dumper->registerFilter('php', new PHPFilter());
        $dumper->registerFilter('style', new CSSFilter());

        // Initialize parser & Jade
        $parser = new Parser(new Lexer());
        $this->driver = new Jade($parser, $dumper);
    }

    public function getNativeDriver()
    {
        return $this->driver;
    }

    public function render($filePath, $variables)
    {
        // Parse a template (both string & file containers)
        return $jade->render($filePath);
    }
}
