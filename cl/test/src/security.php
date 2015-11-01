<?php
use CL_Security as Security;

class SecurityTest extends PHPUnit_Framework_TestCase
{
    public function testSafeHtml()
    {
        // doublequote and ampersand
        $output = Security::safeHtml('"H&M"');
        $expected = '&quot;H&amp;M&quot;';
        $this->assertEquals($output, $expected);

        // singlequote
        $output = Security::safeHtml("'");
        $expected = array('&#039;', '&apos;');
        $this->assertContains($output, $expected);


        // no double encode
        $output = Security::safeHtml('You must write & as &amp;');
        $expected = 'You must write &amp; as &amp;';
        $this->assertEquals($output, $expected);
    }
}
