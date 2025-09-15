<?php

require 'vendor/autoload.php';

// This will output the barcode as HTML output to display in the browser
//$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
//echo $generator->getBarcode('081231723897', $generator::TYPE_CODE_128);
//echo $generator->getBarcode('CP450062216DR', $generator::TYPE_CODE_128);

$color = [0, 0, 0];

$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
file_put_contents('barcode.png', $generator->getBarcode('CP450062216DR', $generator::TYPE_CODE_128, 3, 50, $color));

print('done');