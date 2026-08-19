<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = \App\Models\WalletTransaction::where('type', 'CREDIT')
    ->where('description', 'like', 'Reembolso Diferencia Carga %')
    ->orderBy('amount', 'desc')
    ->get(['id', 'amount', 'description', 'created_at']);

$totalDevuelto = 0;
$count = count($results);

echo "Total de Transacciones Erroneas (CREDIT): " . $count . PHP_EOL;
foreach($results as $r) {
    $totalDevuelto += $r->amount;
    if ($r->amount > 10) { // Solo imprimimos las más graves para no saturar
        echo "Tx: {$r->id} | Monto: {$r->amount} Bs | {$r->description} | Fecha: {$r->created_at}\n";
    }
}
echo "Monto Total Devuelto Injustificadamente: {$totalDevuelto} Bs\n";
