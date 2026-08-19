<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ChargingSession;
use Illuminate\Support\Facades\DB;

$u = User::where('email', 'jorge.ps.bo@gmail.com')->first();
$session = ChargingSession::where('user_id', $u->id)->latest()->first();

if (!$session) {
    die("No session found for user.\n");
}

echo "SESSION_ID: {$session->id}\n";
echo "TX_ID: {$session->transaction_id}\n";
echo "STATUS: {$session->status}\n";

$meterValues = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('transaction_pk', $session->transaction_id)
    ->orderByDesc('value_timestamp')
    ->limit(10)
    ->get();

echo "LATEST METER VALUES:\n";
foreach ($meterValues as $mv) {
    echo " - Time: {$mv->value_timestamp}, Measurand: {$mv->measurand}, Value: {$mv->value}, Unit: {$mv->unit}\n";
}

$tariff = \App\Models\Tariff::resolveForStation($session->station);
$prices = $tariff ? $tariff->getCurrentPrices() : ['price_session' => 0, 'price_kwh' => 0];
echo "TARIFF PRICES: Session: {$prices['price_session']}, kWh: {$prices['price_kwh']}\n";
