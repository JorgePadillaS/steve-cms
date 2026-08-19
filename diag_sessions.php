<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ChargingSession;
use Illuminate\Support\Facades\DB;

echo "--- SESSION STATUS CHECK ---\n";

$sessions = ChargingSession::whereIn('status', ['Starting', 'Active'])->get();

foreach ($sessions as $session) {
    echo "ID: {$session->id} | Status: {$session->status} | Tx ID: {$session->transaction_id} | User: {$session->user?->name} | Station: {$session->station?->charge_box_id}\n";
    
    if ($session->transaction_id) {
        $steveTx = DB::connection('steve')->table('transaction')
            ->where('transaction_pk', $session->transaction_id)
            ->first();
            
        if ($steveTx) {
            echo "   SteVe: Start: {$steveTx->start_timestamp} | Stop: " . ($steveTx->stop_timestamp ?? 'ACTIVE') . " | Meter: {$steveTx->start_value} -> " . ($steveTx->stop_value ?? 'CURRENT') . "\n";
        } else {
            echo "   SteVe: Transaction NOT FOUND in SteVe database!\n";
        }
    }
}

echo "--- END CHECK ---\n";
