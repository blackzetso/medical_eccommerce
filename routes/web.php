<?php

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\web\WebController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\ClientAccountController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\LanguageController;
use App\Http\Controllers\admin\AttributeController;
use App\Http\Controllers\admin\DashboardController;
// Client Login & Register
Route::get('/login', [ClientAuthController::class, 'showLoginForm'])->name('client.login');
Route::post('/login', [ClientAuthController::class, 'login']);
Route::get('/register', [ClientAuthController::class, 'showRegisterForm'])->name('client.register');
Route::post('/register', [ClientAuthController::class, 'register']);

// Admin Login

// Client Account Routes
Route::prefix('client')->middleware('auth:web')->group(function () {
    Route::get('/dashboard', [ClientAccountController::class, 'dashboard'])->name('client.dashboard');
    Route::get('/cart', [ClientAccountController::class, 'cart'])->name('client.cart');
    Route::get('/myorders', [ClientAccountController::class, 'myOrders'])->name('client.myorders');
    Route::get('/favorites', [ClientAccountController::class, 'favorites'])->name('client.favorites');
    // إضافة منتج إلى السلة
    Route::post('/cart/add', [ClientAccountController::class, 'addToCart'])->name('cart.add');
    // تحديث كمية منتج في السلة
    Route::post('/cart/update', [ClientAccountController::class, 'updateCartItem'])->name('cart.update');
    // حذف منتج من السلة
    Route::post('/cart/remove', [ClientAccountController::class, 'removeCartItem'])->name('cart.remove');
    // أضف المزيد من الروتات هنا حسب الحاجة
});


Route::get('/', [WebController::class, 'home'])->name('/');
Route::get('/categories', [WebController::class, 'categories'])->name('web.categories');
Route::get('/category/{id}', [WebController::class, 'category'])->name('web.category');
Route::get('/products', [WebController::class, 'products'])->name('web.products');
Route::get('/product/{id}', [WebController::class, 'product'])->name('web.product');


Route::get('set-locale/{locale}', function ($locale) {
    session(['locale' => $locale]);
    app()->setLocale($locale);
    return back();
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('client')->as('client.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Client/Dashboard');
    })->name('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->as('admin.')->group(function () {
    Route::resource('dashboard', DashboardController::class);

    //start e-commerce
    Route::resource('products', ProductController::class);
    Route::patch('/products/{id}/status', [ProductController::class, 'toggleStatus'])->name('products.status');

    Route::resource('brands', BrandController::class);
    Route::patch('/brands/{id}/status', [BrandController::class, 'toggleStatus'])->name('brands.status');

    Route::resource('attributes', AttributeController::class);
    Route::patch('/attributes/{id}/status', [AttributeController::class, 'toggleStatus'])->name('attributes.status');

    Route::resource('orders', OrderController::class);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::patch('/orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');

    Route::resource('sliders', SliderController::class);
    Route::patch('/sliders/{id}/status', [SliderController::class, 'toggleStatus'])->name('sliders.status');
    //end e-commerce

    //start categories
    Route::resource('categories', CategoryController::class);
    Route::patch('/categories/{id}/status', [CategoryController::class, 'toggleStatus'])->name('categories.status');
    Route::get('/categories/search/{phrase}', [CategoryController::class, 'search'])->name('categories.search');
    //end categories

    //Start Admin settings
    Route::get('settings',[SettingController::class,'index'])->name('settings.index');
    Route::resource('language', LanguageController::class);
    Route::patch('/language/{id}/status', [LanguageController::class, 'toggleStatus'])->name('language.status');
    Route::get('/language/search/{phrase}', [LanguageController::class, 'search'])->name('language.search');
    //End Admin Settings

});

Route::post('/change-language', function (Request $request) {
    $lang = $request->input('lang', 'en');
    session(['locale' => $lang]);
    app()->setLocale($lang);

    return back();
})->name('change.language');
