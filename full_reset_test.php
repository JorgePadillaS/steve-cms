<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RfidTag;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

// 1. Identificar y eliminar las tarjetas del último lote
$tags = RfidTag::where('name', 'like', 'Tarjeta %')->get();
$tagCount = $tags->count();

foreach ($tags as $tag) {
    echo "Eliminando tarjeta {$tag->tag_code}... ";
    
    // Eliminar de SteVe
    try {
        DB::connection('steve')->table('ocpp_tag')->where('id_tag', $tag->tag_code)->delete();
        echo "[SteVe: OK] ";
    } catch (\Exception $e) {
        echo "[SteVe: ERROR] ";
    }

    // Eliminar de CMS
    $tag->delete();
    echo "[CMS: OK]\n";
}

// 2. Eliminar transacciones de lote
$txCount = WalletTransaction::where('reference_id', 'like', '%BULK%')->delete();

echo "\n✅ LIMPIEZA COMPLETADA:\n";
echo "- Tarjetas eliminadas: $tagCount\n";
echo "- Transacciones eliminadas: $txCount\n";
