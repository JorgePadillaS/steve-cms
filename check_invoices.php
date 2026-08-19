<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ChargingSession;
use App\Models\WalletTransaction;

echo "--- RECENT CHARGING SESSIONS WITH INVOICE ---\n";
$sessions = ChargingSession::whereNotNull('invoice_url')->latest()->take(5)->get();
foreach ($sessions as $s) {
    echo "ID: {$s->id} | User: {$s->user_id} | Invoice: {$s->invoice_url}\n";
}

echo "\n--- RECENT WALLET TRANSACTIONS WITH INVOICE ---\n";
$txs = WalletTransaction::whereNotNull('invoice_url')->latest()->take(5)->get();
foreach ($txs as $t) {
    echo "ID: {$t->id} | Type: {$t->type} | Invoice: {$t->invoice_url}\n";
}
