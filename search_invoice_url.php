<?php

function search_in_dir($dir, $pattern) {
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isDir()) continue;
        if ($file->getExtension() !== 'dart') continue;
        
        $content = file_get_contents($file->getPathname());
        if (stripos($content, $pattern) !== false) {
            $lines = explode("\n", $content);
            foreach ($lines as $num => $line) {
                if (stripos($line, $pattern) !== false) {
                    $relPath = str_replace('d:\\EVCE_Project\\e2v-app\\', '', $file->getPathname());
                    echo "{$relPath}:" . ($num + 1) . " -> " . trim($line) . "\n";
                }
            }
        }
    }
}

echo "--- Searching for 'invoice' ---\n";
search_in_dir('d:\EVCE_Project\e2v-app\lib', 'invoice');

echo "\n--- Searching for 'download' ---\n";
search_in_dir('d:\EVCE_Project\e2v-app\lib', 'download');
