<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ChargingSession;
use App\Models\WalletTransaction;

echo "--- CHARGE TRANSACTIONS ---\n";
$txs = WalletTransaction::where('type', 'CHARGE')->latest()->take(10)->get();
if ($txs->isEmpty()) {
    echo "No CHARGE transactions found.\n";
}
foreach ($txs as $t) {
    echo "ID: {$t->id} | Ref: {$t->reference_id} | Amount: {$t->amount} | Invoice: " . ($t->invoice_url ?: 'NULL') . "\n";
}

echo "\n--- ALL RECENT SESSIONS ---\n";
$sessions = ChargingSession::latest()->take(10)->get();
foreach ($sessions as $s) {
    echo "ID: {$s->id} | Status: {$s->status} | Tx ID: {$s->transaction_id} | Total Cost: {$s->total_cost} | Invoice: " . ($s->invoice_url ?: 'NULL') . "\n";
}
