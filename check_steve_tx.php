<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- STEVE TRANSACTION CHECK ---\n";

try {
    $activeTxs = DB::connection('steve')->table('transaction')
        ->whereNull('stop_timestamp')
        ->get();

    echo "Found " . $activeTxs->count() . " active transactions in SteVe.\n";

    foreach ($activeTxs as $tx) {
        echo "Tx PK: {$tx->transaction_pk} | Charge Box: {$tx->connector_pk} | Start: {$tx->start_timestamp} | Tag: {$tx->id_tag}\n";
    }

    $lastTxs = DB::connection('steve')->table('transaction')
        ->orderByDesc('transaction_pk')
        ->limit(5)
        ->get();

    echo "\nLast 5 transactions in SteVe:\n";
    foreach ($lastTxs as $tx) {
        echo "Tx PK: {$tx->transaction_pk} | Start: {$tx->start_timestamp} | Stop: " . ($tx->stop_timestamp ?? 'ACTIVE') . " | Tag: {$tx->id_tag}\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "--- END CHECK ---\n";
