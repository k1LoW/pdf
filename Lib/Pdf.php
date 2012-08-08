<?php
App::import('Vendor', 'TCPDF', array('file' => 'TCPDF' . DS . 'tcpdf.php'));
App::import('Vendor', 'FPDI', array('file' => 'FPDI' . DS . 'fpdi.php'));

/**
 * Pdf
 *
 *
 */
class Pdf {

    public $fpdi;
    private $templateFilePath;
    private $data = array();

    /**
     * __construct
     *
     */
    public function __construct($templateFilePath = null, $init = true){
        $this->fpdi = new FPDI();
        if ($init) {
            $this->fpdi->setPageUnit('mm');
            $this->fpdi->SetMargins(0, 0, 0);     // 用紙の余白を設定
            $this->fpdi->SetCellPadding(0);       // セルのパディングを設定
            $this->fpdi->SetAutoPageBreak(false); // 自動改ページ

            $this->fpdi->setPrintHeader(false);   // ヘッダを使用しない
            $this->fpdi->setPrintFooter(false);   // フッタを使用しない
        }
        if (file_exists($templateFilePath)) {
            $this->read($templateFilePath);
        }
    }

    /**
     * __call
     *
     * @param $method, $args
     */
    public function __call($method, $args){
        if (!$this->fpdi) {
            return;
        }
        return call_user_func_array(array($this->fpdi, $method), $args);
    }

    /**
     * read
     *
     */
    public function read($templateFilePath){
        $this->templateFilePath = $templateFilePath;
        return $this;
    }

    /**
     * setValue
     *
     * @param $arg
     */
    public function setValue($value, $option = array('x' => 0,
                                                     'y' => 0,
                                                     'page' => 0)){
        if (!array_key_exists('x', $option)
            || !array_key_exists('y', $option)
            ) {
            return false;
        }
        if(!array_key_exists('page', $option)) {
            $option['page'] = 0;
        }
        if (!array_key_exists($option['page'], $this->data)) {
            $this->data[$option['page']] = array();
        }
        $this->data[$option['page']][] = array($value, $option);

        return $this;
    }

    /**
     * write
     *
     * @param $outputFilePath
     */
    public function write($outputFilePath){
        if (!$this->templateFilePath) {
            $this->read($outputFilePath);
        }

        $this->fpdi->setFontSubsetting(true);
        $page = $this->fpdi->setSourceFile($this->templateFilePath);

        $font = Configure::read('Pdf.font');
        $fontSize = Configure::read('Pdf.fontSize');
        $fontSize = empty($fontSize) ? 10 : (double)$fontSize;
        if ($font) {
            $this->fpdi->SetFont($font, '', $fontSize);
        }

        for ($i = 0; $i < $page; $i++) {
            $this->fpdi->AddPage();
            $this->fpdi->useTemplate($this->fpdi->importPage($i + 1));
            if (!empty($this->data[$i])) {
                foreach ($this->data[$i] as $value) {
                    $width = empty($value[1]['width']) ? 0 : $value[1]['width'];
                    $height = empty($value[1]['height']) ? 0 : $value[1]['height'];
                    $this->fpdi->MultiCell($width,
                                           $height,
                                           $value[0],
                                           0,
                                           'J',
                                           0,
                                           1,
                                           $value[1]['x'],
                                           $value[1]['y']
                                           );
                }
            }
        }

        $this->fpdi->Output($outputFilePath, 'F');
        if(!file_exists($outputFilePath)) {
            throw new Exception();
        }
        return true;
    }
}