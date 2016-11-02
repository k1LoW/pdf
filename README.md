# pdf: TCPDF and FPDI wrapper [![Build Status](https://secure.travis-ci.org/k1LoW/pdf.png?branch=master)](http://travis-ci.org/k1LoW/pdf)

## Usage

```php
<?php
use Pdf\Pdf;

$pdf = new Pdf();
$pdf->appendTTFfont('/path/to/ipag.ttf');
    ->read('/path/to/template.pdf')
    ->setValue('あいうえお', ['x' => 10, 'y' => 20])
    ->setValue('6ページ目', ['x' => 10, 'y' => 20, 'page' => 5])
    ->setValue('あいうえお', [
        'x' => 120,
        'y' => 45,
        'width' => 100,
        'height' => 230,
        'fontSize' => 24
    ])
    ->write('/path/to/output.pdf');
```

## License

under MIT License
