<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = \App\Models\WalletTransaction::with('wallet.user')
    ->where('type', 'CREDIT')
    ->where('description', 'like', 'Reembolso Diferencia Carga %')
    ->orderBy('created_at', 'desc')
    ->get();

$csv = "ID,Fecha,Usuario,Correo,Monto Devuelto (Bs),Descripcion,Causa Raiz\n";
foreach($results as $r) {
    $user = $r->wallet && $r->wallet->user ? $r->wallet->user->name : 'N/A';
    $email = $r->wallet && $r->wallet->user ? $r->wallet->user->email : 'N/A';
    $causa = "Error: El sistema uso la hora final de SteVe (UTC) contra el inicio local, provocando un falso tiempo de recarga y emitiendo un reembolso automatico.";
    $csv .= "{$r->id},{$r->created_at},\"{$user}\",\"{$email}\",{$r->amount},\"{$r->description}\",\"{$causa}\"\n";
}

file_put_contents('reporte_devoluciones.csv', $csv);
echo "CSV Generado con " . count($results) . " registros.";
