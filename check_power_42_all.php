<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('transaction_pk', 42)
    ->where('measurand', 'like', 'Power%')
    ->get();

foreach ($rows as $mv) {
    echo "Measurand: {$mv->measurand}, Unit: {$mv->unit}, Value: {$mv->value}, Time: {$mv->value_timestamp}\n";
}

if ($rows->isEmpty()) {
    echo "No power rows for Tx 42 found.\n";
}
