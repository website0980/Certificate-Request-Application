<?php
require_once __DIR__ . '/fpdf/fpdf.php';

spl_autoload_register(function (string $class) {
    $prefix = 'setasign\\Fpdi\\';
    $baseDir = __DIR__ . '/fpdi-full/src/';
    $prefixLength = strlen($prefix);

    if (strncmp($prefix, $class, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLength);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
