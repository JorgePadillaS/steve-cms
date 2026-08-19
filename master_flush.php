<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🚀 INICIANDO LIMPIEZA PROFUNDA (MASTER FLUSH)...\n";

try {
    DB::transaction(function() {
        // 1. CMS DATABASE CLEANUP
        echo "--- Limpiando Base de Datos CMS ---\n";
        
        // Disable foreign key checks for truncation
        Schema::disableForeignKeyConstraints();

        $tablesToTruncate = [
            'charging_sessions',
            'wallet_transactions',
            'wallets',
            'libelula_api_logs',
            'rfid_tags',
            'notifications',
            'failed_jobs',
            'personal_access_tokens',
        ];

        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                echo " - Truncando tabla: {$table}\n";
                DB::table($table)->truncate();
            }
        }

        // 2. Selective User Deletion (Keep Super Admins)
        echo " - Eliminando usuarios clientes (manteniendo administradores)...\n";
        // Assuming we use Spatie roles, we check for users without specific roles or just delete non-admins
        // To be safe, we'll delete users who don't have the 'super_admin' role if Spatie is used.
        // If not, we'll keep the user with ID 1 (usually the creator).
        DB::table('users')
            ->whereNotIn('id', function($query) {
                $query->select('model_id')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'super_admin');
            })
            ->where('id', '>', 1) // Also keep ID 1 just in case
            ->delete();

        echo "--- Limpiando Base de Datos SteVe ---\n";
        // 3. STEVE DATABASE CLEANUP (Transactions and Meter Values)
        $steveConn = DB::connection('steve');
        
        $steveTables = [
            'connector_meter_value',
            'transaction',
            'ocpp_tag_activity',
            'reservation',
        ];

        foreach ($steveTables as $table) {
            echo " - Truncando tabla en SteVe: {$table}\n";
            $steveConn->table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    });

    echo "✅ LIMPIEZA COMPLETADA EXITOSAMENTE.\n";
    echo "Hardware, Sitios y Tarifas han sido PRESERVADOS.\n";

} catch (\Exception $e) {
    echo "❌ ERROR DURANTE LA LIMPIEZA: " . $e->getMessage() . "\n";
    Schema::enableForeignKeyConstraints();
}
