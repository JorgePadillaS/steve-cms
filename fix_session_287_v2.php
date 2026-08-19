<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ChargingSession;

// 1. Find the old session that is blocking Tx 30
$old = ChargingSession::where('transaction_id', '30')->where('id', '!=', 287)->first();
if ($old) {
    $old->transaction_id = "30-OLD-COLLISION-" . $old->id;
    $old->save();
    echo "Old session {$old->id} renamed to free up Tx 30.\n";
}

// 2. Fix the current session
$s = ChargingSession::find(287);
if ($s) {
    $s->status = 'Completed';
    $s->transaction_id = '30';
    $s->save();
    echo "Session 287 fixed as Completed with Tx 30.\n";
} else {
    echo "Session 287 not found.\n";
}
