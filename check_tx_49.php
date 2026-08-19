<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$txId = 49;
$meterValues = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('transaction_pk', $txId)
    ->orderByDesc('value_timestamp')
    ->limit(30)
    ->get();

echo "METER VALUES FOR TX {$txId}:\n";
foreach ($meterValues as $mv) {
    echo " - {$mv->value_timestamp} | {$mv->measurand}: {$mv->value} {$mv->unit}\n";
}
