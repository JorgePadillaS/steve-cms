<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Wallet;
use App\Models\WalletTransaction;

$userId = 77;

// 1. Ajustar el saldo de la billetera
$wallet = Wallet::where('user_id', $userId)->first();
if ($wallet) {
    $wallet->balance = 1500;
    $wallet->save();
}

// 2. Borrar la transacción de prueba de 1790 (ID 24)
WalletTransaction::where('id', 24)->delete();

// 3. Borrar cualquier otra transacción de tipo BULK que haya quedado
WalletTransaction::where('user_id', $userId)->where('reference_id', 'like', '%BULK%')->delete();

echo "✅ Saldo de Jorge Padilla (ID: 77) corregido a 1500 BOB.\n";
echo "✅ Transacciones de prueba eliminadas.\n";
