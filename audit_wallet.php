<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Wallet;

// Buscamos a Rafael o a cualquier usuario con saldo significativo
$user = User::where('name', 'like', '%Rafael%')->first() 
        ?? User::whereHas('wallet', function($q) { $q->where('balance', '>', 0); })->orderBy('id', 'desc')->first();

echo "📊 AUDITORÍA DE BILLETERA: {$user->name} (ID: {$user->id})\n";
echo "--------------------------------------------------\n";

$wallet = $user->wallet;
if (!$wallet) {
    echo "❌ El usuario no tiene billetera.\n";
    exit;
}

echo "Saldo actual en DB: {$wallet->balance} BOB\n\n";
echo "Últimas 15 transacciones:\n";

$txs = WalletTransaction::where('user_id', $user->id)
    ->orderBy('id', 'desc')
    ->limit(15)
    ->get();

foreach ($txs as $tx) {
    echo "- ID: {$tx->id} | Monto: {$tx->amount} | Tipo: {$tx->type} | Ref: {$tx->reference_id} | Status: {$tx->status} | Fecha: {$tx->created_at}\n";
}

$sum = WalletTransaction::where('user_id', $user->id)
    ->where('status', 'COMPLETED')
    ->where('type', 'RECHARGE')
    ->sum('amount');
    
$spent = WalletTransaction::where('user_id', $user->id)
    ->where('status', 'COMPLETED')
    ->where('type', 'PAYMENT')
    ->sum('amount');

echo "\n📈 Resumen contable (Solo COMPLETED):\n";
echo "Total Recargado: $sum BOB\n";
echo "Total Gastado: $spent BOB\n";
echo "Saldo Calculado: " . ($sum - $spent) . " BOB\n";
