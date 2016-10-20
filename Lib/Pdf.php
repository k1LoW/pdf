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
        if(!file_exists($templateFilePath)) {
            throw new Exception();
        }
        $this->templateFilePath = $templateFilePath;
        $this->fpdi->setFontSubsetting(true);
        $font = Configure::read('Pdf.font');
        $fontSize = Configure::read('Pdf.fontSize');
        $fontSize = empty($fontSize) ? 10 : (double)$fontSize;
        if ($font) {
            $this->fpdi->SetFont($font, '', $fontSize);
        }
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
    public function write(){
        if (!$this->templateFilePath) {
            throw new Exception();
        }
        $page = $this->fpdi->setSourceFile($this->templateFilePath);

        $font = Configure::read('Pdf.font');
        $defaultFontSize = Configure::read('Pdf.fontSize');
        $defaultFontSize = empty($defaultFontSize) ? 10 : (double)$defaultFontSize;
        $changed = false;

        for ($i = 0; $i < $page; $i++) {
            $templateIndex = $this->fpdi->importPage($i + 1);
            $pageSize = $this->fpdi->getTemplateSize($i + 1);
            $pageSizeW = $pageSize['w'];
            $pageSizeH = $pageSize['h'];
            if ($pageSizeW<=$pageSizeH) {
                $pageOrientation = "P";
            } else {
                $pageOrientation = "L";
            }
            $this->fpdi->AddPage($pageOrientation, array($pageSizeW,$pageSizeH));
            $this->fpdi->useTemplate($templateIndex);
            if (!empty($this->data[$i])) {
                foreach ($this->data[$i] as $value) {
                    $width = empty($value[1]['width']) ? 0 : $value[1]['width'];
                    $height = empty($value[1]['height']) ? 0 : $value[1]['height'];
                    $align = empty($value[1]['align']) ? 'J' : $value[1]['align'];
                    if (!empty($value[1]['fontSize']) && $value[1]['fontSize'] != $defaultFontSize) {
                        $fontSize = $value[1]['fontSize'];
                        if ($font) {
                            $this->fpdi->SetFont($font, '', $fontSize);
                            $changed = true;
                        }
                    }
                    $this->fpdi->MultiCell($width,
                                           $height,
                                           $value[0],
                                           0,
                                           $align,
                                           0,
                                           1,
                                           $value[1]['x'],
                                           $value[1]['y']
                                           );
                    if ($changed) {
                        if ($font) {
                            $this->fpdi->SetFont($font, '', $defaultFontSize);
                            $changed = false;
                        }
                    }
                }
            }
        }
        $this->data = array();
        return $this;
    }

    /**
     * output
     *
     * @param $outputFilePath
     */
    public function output($outputFilePath){
        $this->fpdi->Output($outputFilePath, 'F');
        if(!file_exists($outputFilePath)) {
            throw new Exception();
        }
        return true;
    }
}