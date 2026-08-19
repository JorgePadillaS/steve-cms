<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$log = DB::table('libelula_api_logs')->latest()->first();
if ($log) {
    echo "ID: {$log->id}\n";
    echo "ENDPOINT: {$log->endpoint}\n";
    echo "STATUS: {$log->http_status}\n";
    echo "REQUEST:\n" . json_encode($log->request_payload, JSON_PRETTY_PRINT) . "\n";
    echo "RESPONSE:\n" . json_encode($log->response_payload, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No logs found.\n";
}
