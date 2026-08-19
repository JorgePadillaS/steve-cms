<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- TOTAL LOGS --- \n";
$count = DB::table('libelula_api_logs')->count();
echo "Count: {$count}\n";

$logs = DB::table('libelula_api_logs')->latest('id')->limit(20)->get();
foreach ($logs as $log) {
    echo "ID: {$log->id} | Endpoint: {$log->endpoint} | Status: {$log->http_status} | Created: {$log->created_at}\n";
    echo "REQ: " . substr(is_string($log->request_payload) ? $log->request_payload : json_encode($log->request_payload), 0, 200) . "\n";
    echo "RES: " . substr(is_string($log->response_payload) ? $log->response_payload : json_encode($log->response_payload), 0, 200) . "\n";
    echo "----------------------------------------------\n";
}
