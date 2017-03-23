<?php
class UtilityTest extends PHPUnit_Framework_TestCase
{
    public function testUrliseText()
    {
        $title = "Tara Jane O’Neil - Where Shine New Lights";

        $url = \FelixOnline\Core\Utility::urliseText($title);

        $this->assertEquals($url, 'tara-jane-oneil---where-shine-new-lights');
    }
}
