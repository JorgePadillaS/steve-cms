<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Wallet;

$wallets = Wallet::with('user')->get();
echo "💰 ESTADO DE BILLETERAS:\n";
foreach ($wallets as $w) {
    echo "ID: {$w->id} | User: {$w->user->email} | Balance: {$w->balance}\n";
}
