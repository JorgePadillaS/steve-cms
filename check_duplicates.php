<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$nit = '10668542';
echo "🔍 BUSCANDO USUARIOS CON NIT/CI: $nit\n";

$users = User::where('billing_document', $nit)->get();

if ($users->isEmpty()) {
    echo "✅ No hay usuarios con ese NIT.\n";
} else {
    foreach ($users as $user) {
        echo "ID: {$user->id} | Nombre: {$user->name} | Email: {$user->email}\n";
    }
}
