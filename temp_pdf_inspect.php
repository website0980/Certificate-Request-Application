<?php
require __DIR__ . '/vendor/autoload.php';

$pdf = new \setasign\Fpdi\Fpdi();
$pageCount = $pdf->setSourceFile(__DIR__ . '/pdf/template.pdf');
echo "pages={$pageCount}\n";
$tpl = $pdf->importPage(1);
$size = $pdf->getTemplateSize($tpl);
print_r($size);
