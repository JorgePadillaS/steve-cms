<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Station;
use App\Models\Connector;
use App\Models\Location;
use App\Models\RfidTag;
use App\Models\ChargingSession;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🚀 INICIANDO LIMPIEZA DE BASE DE DATOS PARA PRODUCCIÓN...\n";

try {
    DB::beginTransaction();
    DB::connection('steve')->beginTransaction();

    // Desactivar restricciones de llaves foráneas
    Schema::disableForeignKeyConstraints();
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::connection('steve')->statement('SET FOREIGN_KEY_CHECKS=0;');

    // ----------------------------------------------------
    // PASO 1: Identificar y preservar cargadores de Santa Cruz
    // ----------------------------------------------------
    echo "\n[Paso 1] Analizando cargadores de Santa Cruz...\n";
    
    // Obtener IDs de estaciones de Santa Cruz en el CMS (a través de Locations con city = 'Santa Cruz')
    $santaCruzLocationIds = Location::where('city', 'LIKE', '%Santa Cruz%')->pluck('id')->toArray();
    $cmsSantaCruzStations = Station::whereIn('location_id', $santaCruzLocationIds)->get();
    $cmsSantaCruzStationIds = $cmsSantaCruzStations->pluck('id')->toArray();
    $cmsSantaCruzChargeBoxIds = $cmsSantaCruzStations->pluck('charge_box_id')->toArray();

    // Obtener IDs de cargadores en SteVe que tienen dirección en Santa Cruz
    $steveSantaCruzAddressIds = DB::connection('steve')
        ->table('address')
        ->where('city', 'LIKE', '%Santa Cruz%')
        ->pluck('address_pk')
        ->toArray();
        
    $steveSantaCruzChargeBoxIds = DB::connection('steve')
        ->table('charge_box')
        ->whereIn('address_pk', $steveSantaCruzAddressIds)
        ->pluck('charge_box_id')
        ->toArray();

    // Consolidar todos los ChargeBoxIDs que debemos mantener
    $chargeBoxIdsToKeep = array_unique(array_merge($cmsSantaCruzChargeBoxIds, $steveSantaCruzChargeBoxIds));
    
    // Si la base de datos está vacía de ubicaciones o cargadores de prueba pero tiene 'SimulatedCP001' asociado a Santa Cruz, mantenerlo
    if (empty($chargeBoxIdsToKeep)) {
        $hasSimulated = DB::connection('steve')->table('charge_box')->where('charge_box_id', 'SimulatedCP001')->exists();
        if ($hasSimulated) {
            $chargeBoxIdsToKeep[] = 'SimulatedCP001';
            echo "ℹ️ Manteniendo cargador de prueba 'SimulatedCP001' por estar configurado en Santa Cruz.\n";
        }
    }

    echo "Cargadores que se MANTENDRÁN en producción (" . count($chargeBoxIdsToKeep) . "):\n";
    foreach ($chargeBoxIdsToKeep as $id) {
        echo "  - {$id}\n";
    }

    // ----------------------------------------------------
    // PASO 2: Eliminar cargadores que NO sean de Santa Cruz
    // ----------------------------------------------------
    echo "\n[Paso 2] Dando de baja estaciones que no son de Santa Cruz...\n";

    // CMS Deletions
    $cmsStationsToDelete = Station::whereNotIn('charge_box_id', $chargeBoxIdsToKeep)->get();
    foreach ($cmsStationsToDelete as $station) {
        echo "  - Eliminando estación CMS: {$station->charge_box_id} (ID: {$station->id})\n";
        Connector::where('station_id', $station->id)->delete();
        $station->delete();
    }

    // SteVe Deletions
    $steveStationsToDelete = DB::connection('steve')
        ->table('charge_box')
        ->whereNotIn('charge_box_id', $chargeBoxIdsToKeep)
        ->get();
        
    foreach ($steveStationsToDelete as $s) {
        echo "  - Eliminando estación SteVe: {$s->charge_box_id}\n";
        DB::connection('steve')->table('connector')->where('charge_box_id', $s->charge_box_id)->delete();
        DB::connection('steve')->table('charge_box')->where('charge_box_id', $s->charge_box_id)->delete();
    }

    // Limpiar direcciones de SteVe que quedaron huérfanas y no son de Santa Cruz
    $activeAddressPks = DB::connection('steve')->table('charge_box')->pluck('address_pk')->toArray();
    DB::connection('steve')
        ->table('address')
        ->whereNotIn('address_pk', $activeAddressPks)
        ->where('city', 'NOT LIKE', '%Santa Cruz%')
        ->delete();

    // Limpiar ubicaciones de CMS que no tienen estaciones y no pertenecen a Santa Cruz
    $activeLocationIds = Station::pluck('location_id')->toArray();
    Location::whereNotIn('id', $activeLocationIds)
        ->where('city', 'NOT LIKE', '%Santa Cruz%')
        ->delete();

    // ----------------------------------------------------
    // PASO 3: Limpiar todas las Transacciones y Sesiones
    // ----------------------------------------------------
    echo "\n[Paso 3] Purgando todas las transacciones, bitácoras y sesiones de carga...\n";

    // CMS Truncates
    $cmsTablesToTruncate = [
        'charging_sessions',
        'wallet_transactions',
        'libelula_api_logs',
        'notifications',
        'failed_jobs',
        'personal_access_tokens'
    ];
    
    foreach ($cmsTablesToTruncate as $table) {
        if (Schema::hasTable($table)) {
            echo "  - Truncando tabla CMS: {$table}\n";
            DB::table($table)->truncate();
        }
    }

    // SteVe Truncates (Historial de transacciones OCPP)
    $steveTablesToTruncate = [
        'connector_meter_value',
        'transaction',
        'transaction_start',
        'transaction_stop',
        'transaction_stop_failed',
        'ocpp_tag_activity',
        'reservation'
    ];

    foreach ($steveTablesToTruncate as $table) {
        if (Schema::connection('steve')->hasTable($table)) {
            echo "  - Truncando tabla SteVe: {$table}\n";
            DB::connection('steve')->table($table)->truncate();
        } else {
            echo "  - Omitiendo tabla SteVe (no existe): {$table}\n";
        }
    }

    // ----------------------------------------------------
    // PASO 4: Limpiar todas las Tarjetas (RFID Tags)
    // ----------------------------------------------------
    echo "\n[Paso 4] Purgando todas las tarjetas RFID...\n";
    if (Schema::hasTable('rfid_tags')) {
        DB::table('rfid_tags')->truncate();
    }
    if (Schema::connection('steve')->hasTable('ocpp_tag')) {
        DB::connection('steve')->table('ocpp_tag')->truncate();
    }
    echo "  - Tarjetas purgadas con éxito.\n";

    // ----------------------------------------------------
    // PASO 5: Eliminar usuarios que no son administradores ni sistema
    // ----------------------------------------------------
    echo "\n[Paso 5] Limpiando usuarios de pruebas/clientes...\n";
    
    // Obtener IDs de usuarios con rol 'super_admin'
    $superAdminUserIds = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('roles.name', 'super_admin')
        ->pluck('model_id')
        ->toArray();

    // Por seguridad, también buscaremos por correo del administrador principal y de integración de SAP
    $usersToKeep = User::whereIn('id', $superAdminUserIds)
        ->orWhere('email', 'admin@evce.com')
        ->orWhere('email', 'sap-integration@evce.com.bo')
        ->get();
        
    $keepUserIds = $usersToKeep->pluck('id')->toArray();

    echo "Usuarios que se MANTENDRÁN en producción:\n";
    foreach ($usersToKeep as $u) {
        echo "  - ID: {$u->id} | Name: {$u->name} | Email: {$u->email}\n";
    }

    // Eliminar a todos los usuarios que no están en la lista de preservados
    $usersToDelete = User::whereNotIn('id', $keepUserIds)->get();
    foreach ($usersToDelete as $user) {
        echo "  - Eliminando usuario cliente: {$user->name} ({$user->email})\n";
        // Eliminar billetera
        Wallet::where('user_id', $user->id)->delete();
        // Eliminar vehículos
        DB::table('vehicles')->where('user_id', $user->id)->delete();
        // Eliminar usuario
        $user->delete();
    }

    // Resetear a 0 BOB las billeteras de los usuarios preservados para un inicio contable limpio
    Wallet::whereIn('user_id', $keepUserIds)->update(['balance' => 0]);
    echo "  - Saldos de las billeteras activas reseteados a 0.00 BOB.\n";

    // Re-habilitar las restricciones de llaves foráneas
    Schema::enableForeignKeyConstraints();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    DB::connection('steve')->statement('SET FOREIGN_KEY_CHECKS=1;');

    DB::commit();
    DB::connection('steve')->commit();
    
    echo "\n✅ LIMPIEZA DE PRODUCCIÓN FINALIZADA CON ÉXITO.\n";
    echo "Solo se han mantenido los cargadores de Santa Cruz y las cuentas administrativas/sistema esenciales.\n";

} catch (\Exception $e) {
    DB::rollBack();
    DB::connection('steve')->rollBack();
    
    Schema::enableForeignKeyConstraints();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    DB::connection('steve')->statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "\n❌ ERROR CRÍTICO DURANTE LA LIMPIEZA: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
