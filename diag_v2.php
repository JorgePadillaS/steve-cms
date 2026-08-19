<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ChargingSession;
use App\Models\RfidTag;
use Illuminate\Support\Facades\DB;

echo "--- COMPREHENSIVE STATUS CHECK ---\n";

$sessions = ChargingSession::whereIn('status', ['Starting', 'Active'])->get();

foreach ($sessions as $session) {
    echo "CMS Session: ID {$session->id} | Status: {$session->status} | User: " . ($session->user->name ?? 'N/A') . " | Tag Code: " . ($session->rfidTag->tag_code ?? 'N/A') . "\n";
}

echo "\n--- STEVE ACTIVE TRANSACTIONS ---\n";
try {
    $activeTxs = DB::connection('steve')->table('transaction')
        ->whereNull('stop_timestamp')
        ->get();

    foreach ($activeTxs as $tx) {
        $tag = RfidTag::where('tag_code', $tx->id_tag)->first();
        echo "SteVe Tx: {$tx->transaction_pk} | Start: {$tx->start_timestamp} | Tag: {$tx->id_tag} | CMS User Match: " . ($tag->user->name ?? 'NONE') . "\n";
    }
} catch (\Exception $e) {
    echo "Error SteVe: " . $e->getMessage() . "\n";
}

echo "\n--- SYSTEM LOGS (Last 20) ---\n";
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        if (str_contains($line, 'Monitor') || str_contains($line, 'Charging')) {
            echo $line;
        }
    }
}

echo "--- END CHECK ---\n";
