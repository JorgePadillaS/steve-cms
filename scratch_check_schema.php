<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "--- COLUMN DETAILS FOR charging_sessions ---\n";
$cols = DB::select("SHOW COLUMNS FROM charging_sessions");
foreach ($cols as $c) {
    if (in_array($c->Field, ['session_fee', 'debited_amount', 'discount_amount', 'total_cost'])) {
        print_r($c);
    }
}
