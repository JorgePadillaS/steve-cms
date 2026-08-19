<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$latestTx = DB::connection('steve')->table('transaction')->orderByDesc('transaction_pk')->first();
if ($latestTx) {
    echo "LATEST_TX: {$latestTx->transaction_pk}\n";
    echo "START: {$latestTx->start_timestamp}\n";
    echo "STOP: " . ($latestTx->stop_timestamp ?? 'ACTIVE') . "\n";
    
    $meterValues = DB::connection('steve')
        ->table('connector_meter_value')
        ->where('transaction_pk', $latestTx->transaction_pk)
        ->orderByDesc('value_timestamp')
        ->limit(20)
        ->get();
        
    echo "METER VALUES FOR TX {$latestTx->transaction_pk}:\n";
    foreach ($meterValues as $mv) {
        echo " - {$mv->value_timestamp} | {$mv->measurand}: {$mv->value} {$mv->unit}\n";
    }
} else {
    echo "No transactions found.\n";
}
