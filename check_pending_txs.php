<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "⏳ BUSCANDO TRANSACCIONES PENDIENTES...\n";
$txs = DB::table('wallet_transactions')->where('status', 'PENDING')->get();

if ($txs->isEmpty()) {
    echo "❌ No hay transacciones pendientes en este momento.\n";
} else {
    foreach ($txs as $tx) {
        echo "ID: {$tx->id} | Usuario: {$tx->user_id} | Monto: {$tx->amount} | Ref: {$tx->reference_id} | Creado: {$tx->created_at}\n";
    }
}
