<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\LibelulaPaymentService;
use App\Models\WalletTransaction;

$libelula = app(LibelulaPaymentService::class);

echo "--- Verifying status for recharge transactions with NULL invoice_url ---\n";

$txs = WalletTransaction::where('type', 'RECHARGE')
    ->where('status', 'COMPLETED')
    ->whereNull('invoice_url')
    ->get();

if ($txs->isEmpty()) {
    echo "No completed recharge transactions with NULL invoice_url found.\n";
} else {
    foreach ($txs as $tx) {
        echo "Verifying Tx ID: {$tx->id} (Ref: {$tx->reference_id})...\n";
        $res = $libelula->verifyStatus((int)$tx->id);
        if ($res) {
            $tx->refresh();
            echo "  ✅ Synced successfully! New Invoice URL: " . ($tx->invoice_url ?: 'STILL NULL') . "\n";
        } else {
            echo "  ❌ Verification failed or did not return paid status.\n";
        }
    }
}
