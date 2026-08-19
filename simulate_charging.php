<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ChargingSession;
use App\Models\User;
use App\Models\Station;
use App\Models\Tariff;
use Illuminate\Support\Facades\DB;

// --- CONFIGURACIÓN DE LA SIMULACIÓN ---
$userId = 69; // Tu ID de usuario
$stationId = 1; // Un cargador existente
$connectorId = 1;
$durationSeconds = 60; // Duración de la prueba (1 minuto)
$targetKwh = 5.0; // Energía total a simular
$powerKw = 7.4; // Potencia simulada
// --------------------------------------

echo "🚀 Iniciando Simulación de Carga Virtual...\n";

$user = User::find($userId);
$station = Station::find($stationId);
if (!$user || !$station) {
    die("❌ Error: Usuario o Estación no encontrados.\n");
}

// 1. Crear la sesión en el CMS
$session = ChargingSession::create([
    'user_id' => $user->id,
    'station_id' => $station->id,
    'connector_id' => $connectorId,
    'status' => 'Active',
    'start_time' => now(),
    'energy_price_per_kwh' => 2.5, // Precio base
    'transaction_id' => 9999, // ID de transacción ficticio
]);

echo "✅ Sesión CMS creada (ID: {$session->id}). Ahora puedes abrir tu App.\n";

// 2. Simular datos en SteVe
// Limpiar datos previos del simulador si existen
DB::connection('steve')->table('connector_meter_value')->where('transaction_pk', 9999)->delete();

$startValue = 100000; // Valor inicial del medidor en Wh
$currentValue = $startValue;

for ($i = 0; $i <= $durationSeconds; $i += 5) {
    $elapsedFraction = $i / $durationSeconds;
    $currentKwh = $targetKwh * $elapsedFraction;
    $currentValue = $startValue + ($currentKwh * 1000);
    $currentSoc = 20 + (60 * $elapsedFraction);
    
    echo "📊 Simulando: {$currentKwh} kWh | SoC: " . (int)$currentSoc . "% | Potencia: {$powerKw} kW\n";

    // Inyectar Energía
    DB::connection('steve')->table('connector_meter_value')->insert([
        'transaction_pk' => 9999,
        'value_timestamp' => now()->subSeconds($durationSeconds - $i),
        'value' => (string) $currentValue,
        'measurand' => 'Energy.Active.Import.Register',
        'unit' => 'Wh'
    ]);

    // Inyectar SoC
    DB::connection('steve')->table('connector_meter_value')->insert([
        'transaction_pk' => 9999,
        'value_timestamp' => now()->subSeconds($durationSeconds - $i),
        'value' => (string) (int)$currentSoc,
        'measurand' => 'SoC',
        'unit' => 'Percent'
    ]);

    // Inyectar Potencia (A veces en W, a veces en kW según el cargador)
    DB::connection('steve')->table('connector_meter_value')->insert([
        'transaction_pk' => 9999,
        'value_timestamp' => now()->subSeconds($durationSeconds - $i),
        'value' => (string) ($powerKw * 1000), // En Watts
        'measurand' => 'Power.Active.Import',
        'unit' => 'W'
    ]);

    // Esperar un poco para que el monitor procese
    sleep(2);
}

echo "🏁 Simulación completada. La sesión sigue 'Active' en el CMS para que la veas.\n";
echo "💡 Para detenerla, puedes cambiar el status en la BD o usar la App.\n";
