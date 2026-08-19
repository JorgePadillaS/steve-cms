<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🚀 RE-INTENTANDO LIMPIEZA PROFUNDA (MASTER FLUSH)...\n";

try {
    // Disable foreign key checks for the whole session
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    $tablesCMS = [
        'charging_sessions',
        'wallet_transactions',
        'wallets',
        'libelula_api_logs',
        'rfid_tags',
        'notifications',
        'failed_jobs',
        'personal_access_tokens',
    ];

    foreach ($tablesCMS as $table) {
        echo " - Limpiando CMS: {$table}\n";
        DB::table($table)->delete(); // Use delete instead of truncate for better compatibility
    }

    echo " - Eliminando usuarios clientes (excepto admins)...\n";
    DB::table('users')
        ->whereNotIn('id', function($query) {
            $query->select('model_id')
                ->from('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'super_admin');
        })
        ->where('id', '>', 1)
        ->delete();

    // STEVE TABLES (using the 'steve' connection)
    $steveConn = DB::connection('steve');
    $tablesSteve = [
        'connector_meter_value',
        'transaction',
        'ocpp_tag_activity',
        'reservation',
    ];

    foreach ($tablesSteve as $table) {
        echo " - Limpiando SteVe: {$table}\n";
        try {
            $steveConn->table($table)->delete();
        } catch (\Exception $e) {
            echo "   ⚠️ Advertencia en SteVe {$table}: " . $e->getMessage() . "\n";
        }
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "✅ LIMPIEZA COMPLETADA.\n";

} catch (\Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
