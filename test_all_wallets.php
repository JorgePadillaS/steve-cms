<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\V1\Mobile\WalletController;
use Illuminate\Http\Request;

$users = User::all();
echo "🧪 TEST: Probando " . $users->count() . " usuarios...\n";

foreach ($users as $user) {
    try {
        $request = new Request();
        $request->setUserResolver(fn() => $user);
        
        $controller = new WalletController();
        $response = $controller->balance($request);
        
        if ($response->getStatusCode() !== 200) {
            echo "❌ ERROR Usuario {$user->id} ({$user->email}): Status " . $response->getStatusCode() . "\n";
            echo "📄 RESPONSE: " . $response->getContent() . "\n";
        }
    } catch (\Throwable $e) {
        echo "🔥 CRASH Usuario {$user->id} ({$user->email}): " . $e->getMessage() . "\n";
        echo "📍 LINEA: " . $e->getLine() . " en " . $e->getFile() . "\n";
    }
}

echo "🏁 FIN DEL TEST\n";
