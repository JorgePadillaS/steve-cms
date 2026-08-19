<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\V1\Mobile\WalletController;
use Illuminate\Http\Request;

$user = User::first();
echo "🧪 TEST: Consultando HISTORIAL para el usuario: {$user->name}\n";

try {
    $request = new Request();
    $request->setUserResolver(fn() => $user);
    
    $controller = new WalletController();
    $response = $controller->history($request);
    
    echo "✅ ÉXITO: Status " . $response->getStatusCode() . "\n";
} catch (\Throwable $e) {
    echo "🔥 CRASH: " . $e->getMessage() . "\n";
}
