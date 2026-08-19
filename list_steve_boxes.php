<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$boxes = DB::connection('steve')->table('charge_box')->get();
foreach ($boxes as $b) {
    echo "ID: {$b->charge_box_id}\n";
}
