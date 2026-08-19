<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$page = new \App\Filament\Pages\LibelulaDebugger();
$form = \Filament\Forms\Form::make($page);
$formSchema = $page->form($form);

foreach ($formSchema->getComponents() as $section) {
    echo "SECTION: " . $section->getHeading() . "\n";
    foreach ($section->getChildComponents() as $field) {
        echo "  - FIELD: " . $field->getName() . " | LABEL: " . $field->getLabel() . "\n";
    }
}
