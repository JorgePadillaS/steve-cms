<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tx = DB::table('wallet_transactions')->where('id', 1)->first();
if ($tx) {
    echo "ID: {$tx->id} | Status: {$tx->status} | Monto: {$tx->amount}\n";
    $wallet = DB::table('wallets')->where('id', $tx->wallet_id)->first();
    echo "Saldo Actual Billetera: " . ($wallet->balance ?? 'N/A') . "\n";
} else {
    echo "Transacción no encontrada.\n";
}
