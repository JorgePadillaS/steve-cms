<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ChargingSession;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

$userId = 105;
$sessionId = 2;

DB::transaction(function() use ($userId, $sessionId) {
    $session = ChargingSession::find($sessionId);
    if (!$session || $session->user_id != $userId) {
        die("Session #{$sessionId} not found for User #{$userId}!\n");
    }

    $wallet = Wallet::where('user_id', $userId)->first();
    if (!$wallet) {
        die("Wallet not found!\n");
    }

    // New pricing rules:
    $sessionFee = 10.00;
    $energyCost = 3.30;
    $discountAmount = 10.00; // Capped at session fee
    $subtotal = 13.30;
    $total = 3.30; // Paid: only energy cost

    echo "Initial Wallet Balance: {$wallet->balance} BOB\n";

    // Deduct from wallet
    $wallet->balance -= $total;
    $wallet->save();

    echo "New Wallet Balance: {$wallet->balance} BOB\n";

    // Update charging session
    $session->total_cost = $total;
    $session->session_fee = $sessionFee;
    $session->discount_amount = $discountAmount;
    $session->energy_cost = $energyCost;
    $session->debited_amount = $total;
    $session->applied_tariff_snapshot = [
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'billing_breakdown' => [
            [
                'cost' => $energyCost,
                'rate' => 1.773,
                'block' => 2,
                'seconds' => 143,
                'energy_kwh' => 1.86
            ]
        ]
    ];
    $session->save();

    echo "Session #{$sessionId} updated.\n";

    // Insert wallet transaction
    $refCol = Schema::hasColumn('wallet_transactions', 'reference_id') ? 'reference_id' : 'reference';
    
    $txId = DB::table('wallet_transactions')->insertGetId([
        'wallet_id' => $wallet->id,
        'user_id' => $userId,
        'type' => 'CHARGE',
        'amount' => round(-$total, 2),
        $refCol => (string) $session->transaction_id,
        'description' => "Carga #{$session->transaction_id} (1.86 kWh) | Subt: " . number_format($subtotal, 2) . " | Desc: -" . number_format($discountAmount, 2) . " | Total: " . number_format($total, 2) . " BOB",
        'currency' => 'BOB',
        'balance_after' => $wallet->balance,
        'status' => 'COMPLETED',
        'metadata' => json_encode([
            'billing_details' => [
                'total_amount' => (float) $total,
                'parking_fee' => (float) $sessionFee,
                'discount_amount' => (float) $discountAmount,
                'time_fee' => 0.0,
                'energy_kwh' => 1.86,
                'energy_cost' => (float) $energyCost,
                'breakdown' => $session->applied_tariff_snapshot['billing_breakdown'],
            ]
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "Inserted wallet transaction #{$txId}.\n";

    // Clear Cache
    Cache::forget("wallet_balance_{$userId}");
    echo "Redis Cache cleared for user #{$userId}.\n";
});
