<?php

namespace Pdf\tests;

use Pdf\Pdf;

class PdfTest extends \PHPUnit_Framework_TestCase
{
    public $pdf;

    /**
     * setUp.
     */
    public function setUp()
    {
        ini_set('memory_limit', -1);
        $this->pdf = new Pdf();
        $this->pdf->appendTTFfont(dirname(__FILE__) . '/ipag00303/ipag.ttf', 'IPAG');
    }

    /**
     * testWrite
     *
     */
    public function testWrite(){
        $fileName = 'cookbook.pdf';
        $this->inputFilePath = '/tmp/' . $fileName;
        $this->outputFilePath = '/tmp/output.pdf';
        $this->setTestFile($fileName, $this->inputFilePath);

        $result = $this->pdf->read($this->inputFilePath)
            ->setValue('あいうえおかきくけこさしすせそ', array('x' => 10,
                                                               'y' => 20))
            ->setValue('ABCDEFGHIJKLMNOPQRSTUVWXYZ', array('width' => 10,
                                                               'x' => 10,
                                                               'y' => 30))
            ->setValue("アイウエオ\nカキクケコ\nサシスセソ", array('x' => 30,
                                                                   'y' => 30))
            ->setValue('6ページ目に表示されています', array('x' => 10,
                                                            'y' => 20,
                                                            'page' => 5))
            ->write()
            ->output($this->outputFilePath);
        $this->assertTrue($result);
        var_dump('Look ' . $this->outputFilePath);
        var_dump("Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB");
    }

    /**
     * testChangeFontSize
     *
     */
    public function testChangeFontSize(){
        $fileName = 'cookbook.pdf';
        $this->inputFilePath = '/tmp/' . $fileName;
        $this->outputFilePath = '/tmp/output_change_font.pdf';
        $this->setTestFile($fileName, $this->inputFilePath);

        $result = $this->pdf->read($this->inputFilePath)
            ->setValue('あいうえおかきくけこさしすせそ', array('x' => 10,
                                                               'y' => 20))
            ->setValue('あいうえおかきくけこさしすせそ', array(
                    'x' => 10,
                    'y' => 30,
                    'fontSize' => 20))
            ->setValue('あいうえおかきくけこさしすせそ', array(
                    'x' => 10,
                    'y' => 50,
                    'fontSize' => 40))
            ->write()
            ->output($this->outputFilePath);
        $this->assertTrue($result);
        var_dump('Look ' . $this->outputFilePath);
        var_dump("Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB");
    }
    
    /**
     * tearDown.
     */
    public function tearDown()
    {
        unset($this->pdf);
    }

    /**
     * setTestFile
     *
     * @return
     */
    private function setTestFile($fileName, $to = null){
        if (!$fileName || !$to) {
            return false;
        }
        $from = dirname(__FILE__) . '/' . $fileName;
        return copy($from, $to);
    }
}
