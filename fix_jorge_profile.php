<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$u = User::find(78);
if ($u) {
    $u->billing_document = '10668542';
    $u->billing_razon_social = 'Jorge Padilla Sanchez';
    $u->billing_doc_type = 'CI';
    $u->save();
    echo "✅ Perfil de {$u->email} actualizado con éxito.\n";
} else {
    echo "❌ Usuario no encontrado.\n";
}
