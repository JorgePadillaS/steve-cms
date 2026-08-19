<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$txs = App\Models\WalletTransaction::where('user_id', 69)->orderByDesc('created_at')->limit(5)->get();
foreach ($txs as $tx) {
    echo "ID: {$tx->id}, Type: {$tx->type}, Amount: {$tx->amount}, BalanceAfter: {$tx->balance_after}, Ref: {$tx->reference}, Invoice: {$tx->invoice_url}\n";
}
