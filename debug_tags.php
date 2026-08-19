<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RfidTag;

$total = RfidTag::count();
$noUser = RfidTag::whereNull('user_id')->count();
$virtual = RfidTag::where('is_virtual', true)->count();
$physical = RfidTag::where('is_virtual', false)->count();

$noUserVirtual = RfidTag::whereNull('user_id')->where('is_virtual', true)->count();
$noUserPhysical = RfidTag::whereNull('user_id')->where('is_virtual', false)->count();

echo "📊 ESTADO DE TARJETAS RFID:\n";
echo "---------------------------\n";
echo "Total de tarjetas: $total\n";
echo "Tarjetas físicas: $physical\n";
echo "Tarjetas virtuales (App): $virtual\n";
echo "Tarjetas SIN usuario (TOTAL): $noUser\n";
echo "  -> Virtuales sin usuario (Basura): $noUserVirtual\n";
echo "  -> Físicas sin usuario (Por asignar): $noUserPhysical\n";

if ($noUserPhysical > 0) {
    echo "\n📂 Muestra de tarjetas FÍSICAS por asignar:\n";
    $samples = RfidTag::whereNull('user_id')->where('is_virtual', false)->limit(10)->get();
    foreach ($samples as $s) {
        echo "- Código: {$s->tag_code} | Nombre: {$s->name}\n";
    }
}
