<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\WalletTransaction;

$users = User::where('email', 'like', '%jorge%')->get();
if ($users->isEmpty()) {
    echo "NO USER WITH EMAIL LIKE jorge FOUND\n";
    exit(1);
}

foreach ($users as $user) {
    echo "\n=========================================\n";
    echo "Found User: {$user->name} (ID: {$user->id}, Email: {$user->email})\n";
    echo "Billing Doc: '{$user->billing_document}' | Doc Type: '{$user->billing_doc_type}' | Razon Social: '{$user->billing_razon_social}'\n";

    $wallet = \App\Models\Wallet::where('user_id', $user->id)->first();
    if (!$wallet) {
        echo "NO WALLET FOR USER\n";
        continue;
    }
    echo "Wallet ID: {$wallet->id} | Balance: {$wallet->balance} | Currency: {$wallet->currency}\n";

    echo "--- ALL TRANSACTIONS FOR WALLET ---\n";
    $txs = WalletTransaction::where('wallet_id', $wallet->id)->orderBy('id', 'desc')->get();
    if ($txs->isEmpty()) {
        echo "No transactions found.\n";
    } else {
        foreach ($txs as $tx) {
            echo "ID: {$tx->id} | Type: {$tx->type} | Amount: {$tx->amount} | Status: {$tx->status} | Ref: {$tx->reference_id} | Ext ID: {$tx->external_payment_id} | Method: {$tx->payment_method} | Invoice Number: " . ($tx->invoice_number ?: 'NULL') . " | Invoice URL: " . ($tx->invoice_url ?: 'NULL') . " | Date: {$tx->created_at}\n";
        }
    }
}
