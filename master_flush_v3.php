<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🚀 FINALIZANDO RESET TOTAL (MASTER FLUSH v3)...\n";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // 1. STEVE BASE TABLES CLEANUP
    echo "--- Limpiando Tablas Base de SteVe ---\n";
    $steveConn = DB::connection('steve');
    
    $steveBaseTables = [
        'transaction_start',
        'transaction_stop',
        'transaction_stop_failed',
        'connector_meter_value',
        'reservation',
        // 'user', // SteVe uses 'user' for tags, but we might want to keep it if they are hardware tags
        // 'user_ocpp_tag'
    ];

    foreach ($steveBaseTables as $table) {
        echo " - Borrando datos reales en SteVe: {$table}\n";
        $steveConn->table($table)->delete();
    }

    echo "--- Limpiando CMS (Sesiones y Billeteras) ---\n";
    $cmsTables = [
        'charging_sessions',
        'wallet_transactions',
        'wallets',
        'libelula_api_logs',
        'rfid_tags',
    ];

    foreach ($cmsTables as $table) {
        echo " - Borrando datos en CMS: {$table}\n";
        DB::table($table)->delete();
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "✅ RESET COMPLETO. SteVe y CMS están listos.\n";

} catch (\Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
