<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- SEARCHING FOR JORGE IN LIBELULA API LOGS ---\n";
// Let's get all logs where request_payload or response_payload contains jorge or bo@gmail.com
$logs = DB::table('libelula_api_logs')->get();

$found = 0;
foreach ($logs as $log) {
    $reqStr = is_string($log->request_payload) ? $log->request_payload : json_encode($log->request_payload);
    $resStr = is_string($log->response_payload) ? $log->response_payload : json_encode($log->response_payload);
    
    if (stripos($reqStr, 'jorge') !== false || stripos($resStr, 'jorge') !== false) {
        $found++;
        echo "ID: {$log->id} | Endpoint: {$log->endpoint} | Created: {$log->created_at}\n";
        echo "REQ: " . substr($reqStr, 0, 500) . "\n";
        echo "RES: " . substr($resStr, 0, 500) . "\n";
        echo "--------------------------------------------------\n";
    }
}

echo "Total matching logs: {$found}\n";
