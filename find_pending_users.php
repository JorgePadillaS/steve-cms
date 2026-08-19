<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$userIds = DB::table('wallet_transactions')
    ->where('status', 'PENDING')
    ->where('payment_method', 'LIBELULA')
    ->pluck('user_id')
    ->unique();

echo "🔍 USUARIOS CON PENDIENTES: " . $userIds->implode(', ') . "\n";
