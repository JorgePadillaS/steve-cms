<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ChargingSession;
use App\Services\BillingService;

$txId = 49;
$session = ChargingSession::where('transaction_id', (string)$txId)->first();

if (!$session) {
    die("Session for Tx #{$txId} not found.\n");
}

echo "RE-TRIGGERING INVOICE FOR SESSION #{$session->id} (TX #{$txId})\n";
echo "Current Status: {$session->status}\n";
echo "Clearing financial lock and previous invoice data...\n";

// Clear the lock so we can re-process
$session->update([
    'financial_locked_at' => null,
    'invoice_url' => null,
    'external_payment_id' => null
]);

$billing = app(BillingService::class);
echo "Calling triggerInvoice with NEW logic...\n";

$billing->finalizeBilling($session);

$session->refresh();
echo "New Invoice URL: " . ($session->invoice_url ?? 'STILL NULL - Check logs') . "\n";
echo "Financial Locked At: " . ($session->financial_locked_at ? 'YES' : 'NO') . "\n";
