<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Station;
use App\Models\Location;
use App\Models\RfidTag;
use App\Models\ChargingSession;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

echo "==================================================\n";
echo "📊 REPORTE DE VERIFICACIÓN POST-LIMPIEZA DE PRODUCCIÓN\n";
echo "==================================================\n";

$errors = 0;

// 1. Verificar cargadores de Santa Cruz
echo "\n[1] Verificando Cargadores de Santa Cruz:\n";
$stations = Station::all();
foreach ($stations as $s) {
    // Si tiene ubicación, verificar que sea Santa Cruz
    if ($s->location) {
        if (str_contains(strtolower($s->location->city), 'santa cruz')) {
            echo "  ✅ Estación CMS '{$s->charge_box_id}' mantenida correctamente (Ubicación: {$s->location->name}, Ciudad: {$s->location->city})\n";
        } else {
            echo "  ❌ ERROR: Estación CMS '{$s->charge_box_id}' pertenece a una ciudad que no es Santa Cruz: '{$s->location->city}'\n";
            $errors++;
        }
    } else {
        // Si no tiene ubicación en CMS, verificar en SteVe
        $steveBox = DB::connection('steve')->table('charge_box')
            ->join('address', 'charge_box.address_pk', '=', 'address.address_pk')
            ->where('charge_box_id', $s->charge_box_id)
            ->first();
            
        if ($steveBox && str_contains(strtolower($steveBox->city), 'santa cruz')) {
            echo "  ✅ Estación CMS '{$s->charge_box_id}' mantenida correctamente (Dirección SteVe: {$steveBox->street}, Ciudad: {$steveBox->city})\n";
        } else {
            echo "  ❌ ERROR: Estación CMS '{$s->charge_box_id}' no tiene ubicación o no pertenece a Santa Cruz.\n";
            $errors++;
        }
    }
}

// 2. Verificar transacciones y sesiones
echo "\n[2] Verificando eliminación de Historial y Sesiones:\n";
$sessionCount = ChargingSession::count();
$txCount = WalletTransaction::count();
$steveTxStart = DB::connection('steve')->table('transaction_start')->count();
$steveTxStop = DB::connection('steve')->table('transaction_stop')->count();
$meterValues = DB::connection('steve')->table('connector_meter_value')->count();

if ($sessionCount === 0 && $txCount === 0 && $steveTxStart === 0 && $steveTxStop === 0 && $meterValues === 0) {
    echo "  ✅ Todas las sesiones de carga, transacciones financieras y lecturas OCPP fueron purgadas exitosamente (0 registros).\n";
} else {
    echo "  ❌ ERROR: Se detectaron registros residuales:\n";
    echo "    - Sesiones CMS: {$sessionCount}\n";
    echo "    - Transacciones CMS: {$txCount}\n";
    echo "    - Transacciones SteVe (Start): {$steveTxStart}\n";
    echo "    - Transacciones SteVe (Stop): {$steveTxStop}\n";
    echo "    - Lecturas de Medidor SteVe: {$meterValues}\n";
    $errors++;
}

// 3. Verificar tarjetas RFID
echo "\n[3] Verificando eliminación de Tarjetas RFID:\n";
$cmsTags = RfidTag::count();
$steveTags = DB::connection('steve')->table('ocpp_tag')->count();

if ($cmsTags === 0 && $steveTags === 0) {
    echo "  ✅ Todas las tarjetas físicas y virtuales fueron eliminadas por completo (0 registros).\n";
} else {
    echo "  ❌ ERROR: Tarjetas RFID detectadas:\n";
    echo "    - Tarjetas CMS: {$cmsTags}\n";
    echo "    - Tarjetas SteVe: {$steveTags}\n";
    $errors++;
}

// 4. Verificar usuarios y roles
echo "\n[4] Verificando Usuarios preservados:\n";
$users = User::all();
$illegalUsers = 0;
foreach ($users as $u) {
    $isSuperAdmin = $u->hasRole('super_admin');
    $isSap = $u->email === 'sap-integration@evce.com.bo';
    
    if ($isSuperAdmin || $isSap) {
        $type = $isSuperAdmin ? "SUPER ADMIN" : "INTEGRACIÓN SAP";
        echo "  ✅ Usuario preservado: '{$u->name}' ({$u->email}) - Rol/Función: {$type}\n";
    } else {
        echo "  ❌ ERROR: Usuario no autorizado preservado: '{$u->name}' ({$u->email})\n";
        $illegalUsers++;
        $errors++;
    }
}

if ($illegalUsers === 0) {
    echo "  ✅ No existen cuentas de clientes o usuarios de pruebas residuales.\n";
}

// 5. Verificar monederos
echo "\n[5] Verificando saldos de Monederos a 0 BOB:\n";
$wallets = Wallet::all();
$nonZeroWallets = 0;
foreach ($wallets as $w) {
    if ((float)$w->balance !== 0.0) {
        echo "  ❌ ERROR: El monedero del usuario ID {$w->user_id} tiene un saldo de {$w->balance} BOB.\n";
        $nonZeroWallets++;
        $errors++;
    }
}

if ($nonZeroWallets === 0) {
    echo "  ✅ Todos los monederos activos fueron reseteados exitosamente a 0.00 BOB.\n";
}

echo "\n==================================================\n";
if ($errors === 0) {
    echo "🎉 RESULTADO: LA BASE DE DATOS ESTÁ 100% LISTA PARA PRODUCCIÓN.\n";
} else {
    echo "⚠️ SE DETECTARON {$errors} ERRORES DE INTEGRIDAD. REVISAR LOGS.\n";
}
echo "==================================================\n";
