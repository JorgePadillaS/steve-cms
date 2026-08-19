<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latest = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('measurand', 'like', 'Power%')
    ->orderByDesc('value_timestamp')
    ->limit(10)
    ->get();

foreach ($latest as $mv) {
    echo "Measurand: {$mv->measurand}, Unit: {$mv->unit}, Value: {$mv->value}, Time: {$mv->value_timestamp}\n";
}
