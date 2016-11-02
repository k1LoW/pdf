<?php

namespace Pdf;

if (file_exists(dirname(__FILE__).'/../vendor/autoload.php')) {
    require_once dirname(__FILE__).'/../vendor/autoload.php';
}

use Pdf\Exception\PdfException;
use \FPDI;
use \TCPDF_FONTS;

class Pdf
{
    public $fpdi;
    private $templateFilePath;
    private $fonts = [];
    private $data = [];
    const DEFAULT_FONT_SIZE = 10;
    
    /**
     * __construct
     *
     */
    public function __construct($templateFilePath = null, $init = true)
    {
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
    public function __call($method, $args)
    {
        if (!$this->fpdi) {
            return;
        }
        return call_user_func_array([$this->fpdi, $method], $args);
    }

    /**
     * appendTTFfont
     *
     */
    public function appendTTFfont($fontFilePath, $alias = null)
    {
        $tcpdfFonts = new TCPDF_FONTS();
        $font = $tcpdfFonts->addTTFfont($fontFilePath);
        if (empty($alias)) {
            $alias = $font;
        }
        $this->fonts[$alias] = $font;
        return $this;
    }
    
    /**
     * read
     *
     */
    public function read($templateFilePath)
    {
        if(!file_exists($templateFilePath)) {
            throw new PdfException('Not found template PDF file');
        }
        $this->templateFilePath = $templateFilePath;
        $this->fpdi->setFontSubsetting(true);
        return $this;
    }

    /**
     * setValue
     *
     * @param $arg
     */
    public function setValue($value, Array $option = ['x' => 0,
                                                      'y' => 0,
                                                      'page' => 0])
    {
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
    public function write()
    {
        if (!$this->templateFilePath) {
            throw new PdfException('Not found template PDF file.');
        }
        if(empty($this->fonts)) {
            throw new PdfException('Not found font. Use Pdf::appendTTFfont()');
        }
        $page = $this->fpdi->setSourceFile($this->templateFilePath);

        $keys = array_keys($this->fonts);
        $font = $this->fonts[$keys[0]];
        $defaultFontSize = self::DEFAULT_FONT_SIZE;
        $this->fpdi->SetFont($font, '', $defaultFontSize);
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
        $this->data = [];
        return $this;
    }

    /**
     * output
     *
     * @param $outputFilePath
     */
    public function output($outputFilePath)
    {
        $this->fpdi->Output($outputFilePath, 'F');
        if(!file_exists($outputFilePath)) {
            throw new PdfException('Could not output PDF file.');
        }
        return true;
    }
}