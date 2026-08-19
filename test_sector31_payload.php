<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\LibelulaPaymentService;
use App\Models\SystemSetting;

echo "=== VERIFICACIÓN DE CONFIGURACIÓN Y PAYLOAD LIBÉLULA SECTOR 31 ===\n\n";

$settings = SystemSetting::get();
echo "1. SystemSetting Sector Code: " . ($settings->libelula_sector_code ?? 'N/A') . "\n";
echo "   AppKey: " . ($settings->libelula_app_key ?? 'N/A') . "\n";
echo "   Canal Caja: " . ($settings->libelula_canal_caja ?? 'N/A') . "\n\n";

$service = new LibelulaPaymentService();

echo "2. Probando testInvoiceRequest (Simulación Sector 31)...\n";
$testResult = $service->testInvoiceRequest([
    'email_cliente' => 'cliente_test@evce.bo',
    'nombre_cliente' => 'Cliente Prueba Sector 31',
    'razon_social' => 'Cliente Prueba EVCE',
    'numero_documento' => '1234567',
    'concepto' => 'Carga de Energía Sector 31 Test',
    'monto' => 15.50,
    'descuento' => 0.0,
    'placa_vehiculo' => '5318FPG',
    'codigo_producto' => '1',
]);

echo "HTTP Status: " . ($testResult['http_status'] ?? 'N/A') . "\n";
echo "Payload Generado:\n" . json_encode($testResult['request_payload'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
echo "Respuesta Libélula:\n" . json_encode($testResult['response_body'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
