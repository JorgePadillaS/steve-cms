<?php

$filePath = __DIR__ . '/storage/logs/laravel.log';
if (!file_exists($filePath)) {
    die("laravel.log does not exist.\n");
}

$search = 'jorge.ps.bo';
$handle = fopen($filePath, 'r');
if ($handle) {
    $lineNum = 0;
    $matches = 0;
    while (($line = fgets($handle)) !== false) {
        $lineNum++;
        if (stripos($line, $search) !== false) {
            echo "Line {$lineNum}: " . trim($line) . "\n";
            $matches++;
            if ($matches > 50) {
                echo "Capped at 50 matches.\n";
                break;
            }
        }
    }
    fclose($handle);
    echo "Done. Total matches: {$matches}\n";
} else {
    echo "Failed to open laravel.log\n";
}
