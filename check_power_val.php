<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$val = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('measurand', 'Power.Active.Import')
    ->orderByDesc('value_timestamp')
    ->first();

if ($val) {
    echo "Measurand: {$val->measurand}, Unit: {$val->unit}, Value: {$val->value}, Time: {$val->value_timestamp}\n";
} else {
    echo "No Power.Active.Import found.\n";
}
