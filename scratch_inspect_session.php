<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ChargingSession;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

$userId = 105;
$user = User::find($userId);
if (!$user) {
    die("User not found!\n");
}

echo "User: {$user->name} ({$user->email})\n";

$wallet = Wallet::where('user_id', $userId)->first();
echo "Wallet Balance: " . ($wallet ? $wallet->balance : 'N/A') . " BOB\n\n";

echo "--- LATEST SESSIONS ---\n";
$sessions = ChargingSession::where('user_id', $userId)
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

foreach ($sessions as $s) {
    echo "Session ID: {$s->id} | Tx ID: {$s->transaction_id} | Status: {$s->status}\n";
    echo "  Start: {$s->start_time} | Stop: {$s->stop_time}\n";
    echo "  Energy: {$s->total_energy_kwh} kWh | Fee: {$s->session_fee} | Cost: {$s->total_cost} | Debited: {$s->debited_amount}\n";
    echo "  Discount: {$s->discount_amount} | Invoice URL: {$s->invoice_url}\n";
    echo "  Snapshot: " . json_encode($s->applied_tariff_snapshot) . "\n\n";
}

echo "--- LATEST WALLET TRANSACTIONS ---\n";
$txs = WalletTransaction::where('user_id', $userId)
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

foreach ($txs as $t) {
    $ref = $t->reference_id ?? $t->reference;
    echo "Tx ID: {$t->id} | Type: {$t->type} | Amount: {$t->amount} | Ref: {$ref} | Bal After: {$t->balance_after} | Desc: {$t->description}\n";
}
