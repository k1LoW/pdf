<?php
App::uses('Pdf', 'Pdf.Lib');
class PdfTestCase extends CakeTestCase{

    /**
     * __construct
     *
     */
    public function __construct(){
        parent::__construct();
        ini_set('memory_limit', -1);
        $this->pdf = new Pdf();
        $font = $this->pdf->addTTFfont(dirname(__FILE__) . '/../../../Test/File/ipag00303/ipag.ttf', 'TrueTypeUnicode');
        Configure::write('Pdf.font', $font);
        Configure::write('Pdf.fontSize', 10);
    }

    /**
     * setUp
     *
     */
    public function setUp(){
    }

    /**
     * tearDown
     *
     */
    public function tearDown(){
    }

    /**
     * testWrite
     *
     */
    public function testWrite(){
        $fileName = 'cookbook.pdf';
        $this->inputFilePath = TMP . 'tests' . DS . $fileName;
        $this->outputFilePath = TMP . 'tests' . DS . 'output.pdf';
        $this->_setTestFile($fileName, $this->inputFilePath);

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
            ->write($this->outputFilePath);
        $this->assertTrue($result);
        pr('Look ' . $this->outputFilePath);
        pr("Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB");
    }

    /**
     * _setTestFile
     *
     * @return
     */
    private function _setTestFile($fileName, $to = null){
        if (!$fileName || !$to) {
            return false;
        }
        $from = dirname(__FILE__) . '/../../../Test/File/' . $fileName;
        return copy($from, $to);
    }
}