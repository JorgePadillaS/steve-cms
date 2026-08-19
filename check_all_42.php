<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('transaction_pk', 42)
    ->get();

$measurands = [];
foreach ($rows as $mv) {
    $measurands[$mv->measurand] = [
        'value' => $mv->value,
        'unit' => $mv->unit
    ];
}

print_r($measurands);
