<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "📊 ÚLTIMOS LOGS DE LIBÉLULA:\n";
$logs = DB::table('libelula_api_logs')->latest('id')->limit(10)->get();

if ($logs->isEmpty()) {
    echo "❌ No hay logs registrados en la tabla libelula_api_logs.\n";
} else {
    foreach ($logs as $log) {
        echo "ID: {$log->id} | Endpoint: {$log->endpoint} | Status: {$log->http_status} | Fecha: {$log->created_at}\n";
        echo "Request: " . substr(json_encode($log->request_payload), 0, 100) . "...\n";
        echo "--------------------------------------------------\n";
    }
}
