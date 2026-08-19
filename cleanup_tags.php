<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RfidTag;
use Illuminate\Support\Facades\DB;

$toDelete = RfidTag::whereNull('user_id')->get();
$count = $toDelete->count();

echo "🧹 INICIANDO LIMPIEZA DE $count TARJETAS...\n";

foreach ($toDelete as $tag) {
    echo "Eliminando {$tag->tag_code}... ";
    
    // 1. Eliminar de SteVe (MySQL)
    try {
        DB::connection('steve')->table('ocpp_tag')->where('id_tag', $tag->tag_code)->delete();
        echo "[SteVe: OK] ";
    } catch (\Exception $e) {
        echo "[SteVe: ERROR - {$e->getMessage()}] ";
    }

    // 2. Eliminar del CMS
    $tag->delete();
    echo "[CMS: OK]\n";
}

echo "\n✅ LIMPIEZA COMPLETADA. Se eliminaron $count tarjetas huérfanas.\n";
