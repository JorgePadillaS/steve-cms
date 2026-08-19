<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\V1\Mobile\WalletController;
use Illuminate\Http\Request;

// Find a user that might have a wallet (using ID 1 or first available)
$user = User::first();
if (!$user) {
    die("No users found");
}

echo "🧪 TEST: Consultando billetera para el usuario: {$user->name} (ID: {$user->id})\n";

try {
    $request = new Request();
    $request->setUserResolver(fn() => $user);
    
    $controller = new WalletController();
    $response = $controller->balance($request);
    
    echo "✅ ÉXITO: Status " . $response->getStatusCode() . "\n";
    echo "📄 RESPONSE: " . $response->getContent() . "\n";
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 LINEA: " . $e->getLine() . " en " . $e->getFile() . "\n";
    echo "📜 TRACE: " . $e->getTraceAsString() . "\n";
}
