<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ChargingSession;

$s = ChargingSession::find(287);
if ($s) {
    $s->status = 'Completed';
    $s->transaction_id = '30';
    $s->save();
    echo "Session 287 fixed.\n";
} else {
    echo "Session 287 not found.\n";
}
