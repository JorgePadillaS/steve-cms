<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sessions = App\Models\ChargingSession::where('status', 'Active')->get();
foreach ($sessions as $s) {
    echo "ID: {$s->id}, TxID: {$s->transaction_id}, User: {$s->user_id}, Cost: {$s->total_cost}, Energy: {$s->total_energy_kwh}, Status: {$s->status}\n";
}
