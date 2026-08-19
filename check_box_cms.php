<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$steveBoxes = ['EPSCBLP12', 'EPSCBLP34', 'EPSCBLP56', 'MVLP0001'];
foreach ($steveBoxes as $box) {
    $exists = App\Models\Station::where('charge_box_id', $box)->exists();
    echo "Box: {$box}, Exists in CMS: " . ($exists ? 'YES' : 'NO') . "\n";
}
