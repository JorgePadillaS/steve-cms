<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "📋 ÚLTIMOS LOGS DE LIBELULA:\n";
$logs = DB::table('libelula_api_logs')->latest()->limit(10)->get();

foreach ($logs as $log) {
    echo "ID: {$log->id} | Método: {$log->method} | Status: {$log->http_status} | Creado: {$log->created_at}\n";
    echo "Endpoint: " . ($log->endpoint ?? 'N/A') . "\n";
    echo "REQ: " . substr($log->request_payload, 0, 300) . "...\n";
    echo "RES: " . substr($log->response_payload, 0, 300) . "...\n";
    echo "--------------------------------------------------\n";
}
