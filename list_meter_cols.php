<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::connection('steve')->select("DESCRIBE connector_meter_value");
foreach ($cols as $idx => $col) {
    echo "[$idx] Field: {$col->Field}, Null: {$col->Null}, Default: " . (is_null($col->Default) ? 'NULL' : $col->Default) . "\n";
}
