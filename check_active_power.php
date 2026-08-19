<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tx = DB::connection('steve')
    ->table('transaction')
    ->whereNull('stop_timestamp')
    ->orderByDesc('start_timestamp')
    ->first();

if (!$tx) {
    echo "No active transaction found in SteVe.\n";
    exit;
}

echo "Active Tx PK: {$tx->transaction_pk}\n";

$latestPower = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('transaction_pk', $tx->transaction_pk)
    ->where('measurand', 'like', 'Power%')
    ->orderByDesc('value_timestamp')
    ->first();

if ($latestPower) {
    echo "Measurand: {$latestPower->measurand}, Unit: {$latestPower->unit}, Value: {$latestPower->value}, Time: {$latestPower->value_timestamp}\n";
} else {
    echo "No power meter values for this Tx yet.\n";
}
