<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = App\Models\Product::all();

foreach($products as $product) {
    echo "ID: {$product->id}\n";
    echo "Name: {$product->name}\n";
    echo "Images raw: {$product->attributes['images']}\n";
    echo "Images casted: " . json_encode($product->images) . "\n";
    echo "Images type: " . gettype($product->images) . "\n";
    echo "---\n";
}