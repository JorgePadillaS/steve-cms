<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Station;
use App\Models\ChargingSession;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;

// --- CONFIG ---
$email = 'jorge.ps.bo@gmail.com'; // Usuario para la prueba
$chargeBoxId = 'EPSCBLP12';
$connectorPk = 4407;
// --------------

$u = User::where('email', $email)->first();
$station = Station::where('charge_box_id', $chargeBoxId)->first();

if (!$u || !$station) {
    die("❌ Error: Usuario o Estación de simulación no encontrados.\n");
}

echo "🚀 Iniciando Simulación de Carga EN VIVO (v2) para {$email}\n";
echo "💰 Saldo Inicial: {$u->wallet->balance} BOB\n";

// 1. Limpiar sesiones activas previas para este usuario
ChargingSession::where('user_id', $u->id)->where('status', 'Active')->update(['status' => 'Completed']);

// 2. Crear sesión
$txId = random_int(100000, 999999);
$session = ChargingSession::create([
    'user_id' => $u->id,
    'station_id' => $station->id,
    'connector_id' => 1,
    'transaction_id' => $txId,
    'start_time' => now(),
    'status' => 'Active',
    'total_energy_kwh' => 0,
    'debited_amount' => 0,
    'energy_price_per_kwh' => 2.5,
]);

echo "✅ Sesión #{$session->id} Iniciada (TX #{$txId})\n";

$billing = app(BillingService::class);

// 3. Procesar Fee Inicial (Parqueo)
echo "⌛ Procesando Fee Inicial...\n";
$billing->processInitialFee($session);
$session->refresh();
echo "💵 Monto Debitado tras Parqueo: {$session->debited_amount} BOB (Descuento aplicado: {$session->discount_amount})\n";

// 4. Simular Carga Incremental (10 pasos)
$totalKwh = 0;
$steps = 5;

for ($i = 1; $i <= $steps; $i++) {
    $increment = 0.5; // 0.5 kWh cada paso
    $totalKwh += $increment;
    
    echo "\n⚡ PASO $i: +{$increment} kWh (Total: {$totalKwh} kWh)\n";
    
    // Simular datos en SteVe (para que el controlador de la App los vea)
    DB::connection('steve')->table('connector_meter_value')->insert([
        'transaction_pk' => $txId,
        'connector_pk' => $connectorPk,
        'value_timestamp' => now(),
        'value' => (string) (100000 + ($totalKwh * 1000)), // Wh
        'measurand' => 'Energy.Active.Import.Register',
        'unit' => 'Wh'
    ]);
    
    // SoC subiendo
    DB::connection('steve')->table('connector_meter_value')->insert([
        'transaction_pk' => $txId,
        'connector_pk' => $connectorPk,
        'value_timestamp' => now(),
        'value' => (string) (20 + ($i * 5)),
        'measurand' => 'SoC',
        'unit' => 'Percent'
    ]);

    // Ejecutar lógica de débito incremental (lo que hace el monitor)
    echo "🔄 Ejecutando processIncrementalDebit...\n";
    $billing->processIncrementalDebit($session, $totalKwh);
    
    $session->refresh();
    echo "📱 App vería -> Energía: {$session->total_energy_kwh} kWh | Costo: {$session->total_cost} BOB\n";
    
    sleep(1);
}

// 5. Finalizar
echo "\n🏁 Finalizando sesión...\n";
$session->stop_time = now();
$session->status = 'Completed';
$session->save();

$billing->finalizeBilling($session);
$session->refresh();

echo "\n📊 RESULTADO FINAL:\n";
echo "Total Energía: {$session->total_energy_kwh} kWh\n";
echo "Costo Total: {$session->total_cost} BOB\n";
echo "Factura Libélula: " . ($session->invoice_url ?: 'Pendiente/Error') . "\n";
echo "💰 Saldo Final: {$u->wallet->balance} BOB\n";
