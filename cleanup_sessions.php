<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ChargingSession;

$u = User::where('email', 'jorge.ps.bo@gmail.com')->first();
if ($u) {
    $sessions = ChargingSession::where('user_id', $u->id)
        ->whereIn('status', ['Active', 'Charging', 'Pending'])
        ->get();
    
    foreach ($sessions as $s) {
        echo "Cleaning session #{$s->id} (Status: {$s->status})...\n";
        $s->status = 'Completed';
        $s->stop_time = now();
        $s->save();
    }
    echo "Done. All pending sessions for jorge.ps.bo@gmail.com are closed.\n";
} else {
    echo "User not found.\n";
}
