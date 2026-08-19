<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WalletTransaction;

$count = WalletTransaction::where('reference_id', 'like', '%BULK%')->count();
WalletTransaction::where('reference_id', 'like', '%BULK%')->delete();

echo "✅ Limpieza completada. Se eliminaron $count transacciones de creación por lote.\n";
