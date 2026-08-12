<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();
if (! $user) {
    echo "FAIL: No admin user found\n";
    exit(1);
}

auth()->login($user);

$controller = new App\Http\Controllers\Admin\PosController;
$response = $controller->index(new Illuminate\Http\Request());
$html = $response->render();

$checks = [
    'product_cards' => substr_count($html, 'class="product-card"') + substr_count($html, "class=\"product-card\""),
    'has_css' => str_contains($html, '/css/pos.css'),
    'has_js' => str_contains($html, '/js/pos-app.js'),
    'catalog_panel' => str_contains($html, 'catalog-panel'),
    'checkout_panel' => str_contains($html, 'checkout-panel'),
    'menu_items_db' => App\Models\MenuItem::where('is_available', true)->count(),
];

$api = (new App\Http\Controllers\Api\WebMenuController)->index()->getData(true);
$checks['api_items'] = count($api['items'] ?? []);

$cssFile = public_path('css/pos.css');
$checks['css_file_exists'] = file_exists($cssFile);
$checks['css_has_grid'] = $checks['css_file_exists'] && str_contains(file_get_contents($cssFile), 'grid-template-areas');

echo "POS Verification:\n";
foreach ($checks as $k => $v) {
    $ok = is_bool($v) ? $v : ($v > 0);
    echo sprintf("  [%s] %s: %s\n", $ok ? 'OK' : 'FAIL', $k, is_bool($v) ? ($v ? 'true' : 'false') : $v);
}

$allOk = ($checks['product_cards'] >= 16)
    && $checks['has_css']
    && $checks['has_js']
    && $checks['catalog_panel']
    && $checks['checkout_panel']
    && $checks['css_file_exists']
    && $checks['css_has_grid']
    && $checks['api_items'] >= 16;

echo $allOk ? "\nAll checks passed.\n" : "\nSome checks failed.\n";
exit($allOk ? 0 : 1);
