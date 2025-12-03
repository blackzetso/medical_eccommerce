<?php

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckAdmin;
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
use App\Http\Controllers\admin\ClientController;
use App\Http\Controllers\admin\LeadController;
use App\Http\Controllers\admin\ReportController;


// Admin Login

// Client Account Routes
Route::prefix('client')->middleware('auth:web')->group(function () {
    Route::get('/dashboard', [ClientAccountController::class, 'dashboard'])->name('client.dashboard');
    Route::get('/cart', [ClientAccountController::class, 'cart'])->name('client.cart');
    Route::get('/myorders', [ClientAccountController::class, 'myOrders'])->name('client.myorders');
    Route::get('/favorites', [ClientAccountController::class, 'favorites'])->name('client.favorites');
    Route::get('/account/settings', [ClientAccountController::class, 'accountSettings'])->name('client.account.settings');
    Route::put('/account/update', [ClientAccountController::class, 'updateAccount'])->name('client.account.update');
    Route::post('/favorites/add', [ClientAccountController::class, 'addToFavorites'])->name('favorites.add');
    Route::post('/favorites/remove', [ClientAccountController::class, 'removeFromFavorites'])->name('favorites.remove');
    // إضافة منتج إلى السلة
    Route::post('/cart/add', [ClientAccountController::class, 'addToCart'])->name('cart.add');
    // تحديث كمية منتج في السلة
    Route::post('/cart/update', [ClientAccountController::class, 'updateCartItem'])->name('cart.update');
    // حذف منتج من السلة
    Route::post('/cart/remove', [ClientAccountController::class, 'removeCartItem'])->name('cart.remove');
    // إنشاء أوردر جديد مباشرة من السلة
    Route::post('/order/create', [ClientAccountController::class, 'createOrder'])->name('order.create');
    //Route::get('/checkout', [ClientAccountController::class, 'checkout'])->name('client.checkout');
    Route::get('/order/{id}', [ClientAccountController::class, 'showOrder'])->name('client.order.show');
    Route::get('/tracking/{id}', [ClientAccountController::class, 'tracking'])->name('client.tracking');
});
// Client Login & Register
Route::get('/login', [ClientAuthController::class, 'showLoginForm'])->name('client.login');
Route::post('/login', [ClientAuthController::class, 'login']);
Route::get('/register', [ClientAuthController::class, 'showRegisterForm'])->name('client.register');
Route::post('/register', [ClientAuthController::class, 'register']);

Route::get('/', [WebController::class, 'home'])->name('/');
Route::get('/categories', [WebController::class, 'categories'])->name('web.categories');
Route::get('/category/{id}', [WebController::class, 'category'])->name('web.category');
Route::get('/products', [WebController::class, 'products'])->name('web.products');
Route::get('/product/{id}', [WebController::class, 'product'])->name('web.product');
Route::get('/contact', [WebController::class, 'contact'])->name('web.contact');
Route::post('/contact', [WebController::class, 'submitContact'])->name('web.contact.submit');
Route::get('/about', [WebController::class, 'about'])->name('web.about');
Route::get('/privacy', [WebController::class, 'privacy'])->name('web.privacy');
Route::get('/terms', [WebController::class, 'terms'])->name('web.terms');
Route::get('/refund', [WebController::class, 'refund'])->name('web.refund');


Route::get('set-locale/{locale}', function ($locale) {
    // تأمين القيمة المسموحة فقط
    $locale = in_array($locale, ['ar', 'en']) ? $locale : config('app.locale', 'ar');

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
    CheckAdmin::class,
])->prefix('admin')->as('admin.')->group(function () {
    Route::resource('dashboard', DashboardController::class);

    //start e-commerce
    // Import routes must be before resource routes to avoid route conflicts
    Route::get('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template');
    Route::post('/products/import', [ProductController::class, 'processImport'])->name('products.import.process');
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

    //start clients
    Route::resource('clients', ClientController::class);
    Route::patch('/clients/{id}/status', [ClientController::class, 'toggleStatus'])->name('clients.status');
    Route::get('/clients/{id}/statistics', [ClientController::class, 'getStatistics'])->name('clients.statistics');
    //end clients

    //start leads
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{id}', [LeadController::class, 'show'])->name('leads.show');
    Route::post('/leads/{id}/convert', [LeadController::class, 'convertToUser'])->name('leads.convert');
    Route::patch('/leads/{id}/status', [LeadController::class, 'updateStatus'])->name('leads.updateStatus');
    Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');
    //end leads

    //start categories
    Route::resource('categories', CategoryController::class);
    Route::patch('/categories/{id}/status', [CategoryController::class, 'toggleStatus'])->name('categories.status');
    Route::get('/categories/search/{phrase}', [CategoryController::class, 'search'])->name('categories.search');
    //end categories

    //Start Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/orders', [ReportController::class, 'orders'])->name('orders');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
    });
    //End Reports

    //Start Admin settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/appearance', [SettingController::class, 'appearance'])->name('appearance');
        Route::get('/email', [SettingController::class, 'email'])->name('email');
        Route::get('/payment', [SettingController::class, 'payment'])->name('payment');
        Route::get('/shipping', [SettingController::class, 'shipping'])->name('shipping');
        Route::get('/notification', [SettingController::class, 'notification'])->name('notification');
        Route::get('/about', [SettingController::class, 'about'])->name('about');
        Route::get('/privacy', [SettingController::class, 'privacy'])->name('privacy');
        Route::get('/terms', [SettingController::class, 'terms'])->name('terms');
        Route::get('/refund', [SettingController::class, 'refund'])->name('refund');
        Route::put('/update', [SettingController::class, 'update'])->name('update');
        Route::post('/about/update', [SettingController::class, 'updateAbout'])->name('about.update');
        Route::post('/privacy/update', [SettingController::class, 'updatePrivacy'])->name('privacy.update');
        Route::post('/terms/update', [SettingController::class, 'updateTerms'])->name('terms.update');
        Route::post('/refund/update', [SettingController::class, 'updateRefund'])->name('refund.update');
    });
    Route::resource('language', LanguageController::class);
    Route::patch('/language/{id}/status', [LanguageController::class, 'toggleStatus'])->name('language.status');
    Route::get('/language/search/{phrase}', [LanguageController::class, 'search'])->name('language.search');
    //End Admin Settings

});

Route::post('/change-language', function (Request $request) {
    $lang = $request->input('lang');
    
    // Validate that the language exists and is enabled
    $language = \App\Models\Language::where('code', $lang)
        ->where('status', 'enabled')
        ->first();
    
    if (!$language) {
        // Fallback to default language if requested language is not available
        $defaultLanguage = \App\Models\Language::where('is_default', 1)
            ->where('status', 'enabled')
            ->first();
        
        if ($defaultLanguage) {
            $lang = $defaultLanguage->code;
        } else {
            $lang = config('app.locale', 'ar');
        }
    }
    
    session(['locale' => $lang]);
    app()->setLocale($lang);

    return back();
})->name('change.language');
