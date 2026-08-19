<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ChargingSession;

$session = ChargingSession::find(83);
if ($session) {
    echo "ID: {$session->id} | Tx ID: {$session->transaction_id} | Status: {$session->status} | Start: {$session->start_time}\n";
} else {
    echo "Session 83 not found\n";
}

$dup = ChargingSession::where('transaction_id', '30')->first();
if ($dup) {
    echo "Duplicate Tx 30 found: ID {$dup->id} | Status: {$dup->status} | Start: {$dup->start_time}\n";
}
