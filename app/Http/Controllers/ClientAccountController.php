<?php
namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrgaSoftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientAccountController extends Controller
{
    public function tracking($id = null)
    {
        $userId = Auth::id();
        $order = Order::where('id', $id)
            ->where('user_id', $userId)
            ->select('id', 'order_number', 'status', 'created_at')
            ->firstOrFail();
        
        return inertia('Client/Tracking', [
            'orderId' => $order->id,
            'orderNumber' => $order->order_number,
            'orderStatus' => $order->status,
            'createdAt' => $order->created_at,
        ]);
    }
    /**
     * إنشاء أوردر جديد من السلة
     */
    public function createOrder(Request $request)
    {
        // التحقق من البيانات المدخلة
        $request->validate([
            'delivery_type' => 'required|in:delivery,pickup',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        // جلب عناصر السلة للمستخدم
        $cartItems = Cart::with('product')->where('user_id', $userId)->get();
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'السلة فارغة'
            ], 400);
        }

        // حساب الإجمالي من سعر السلة (بعد الخصائص واللون إن وجدت)
        $total = $cartItems->reduce(function ($sum, $item) {
            if (!is_null($item->total_price)) {
                return $sum + $item->total_price;
            }
            $unit = $item->unit_price ?? $item->product->price;
            return $sum + ($unit * $item->quantity);
        }, 0);

        // توليد رقم طلب فريد (تسلسلي بسيط)
        // الحصول على آخر رقم طلب لتوليد رقم جديد
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $lastOrderId = $lastOrder ? $lastOrder->id : 0;
        // رقم الطلب يبدأ من 100000
        $orderNumber = str_pad(100000 + $lastOrderId + 1, 6, '0', STR_PAD_LEFT);

        // إعداد الحقول المطلوبة
        $deliveryType = $request->input('delivery_type', 'delivery');
        $taxAmount = 0;
        // جلب رسوم التوصيل من الإعدادات
        $shippingAmount = ($deliveryType === 'delivery') ? (float) Setting::get('shipping_cost', 15) : 0;
        $discountAmount = 0;
        $currency = Setting::get('currency', 'EGP');
        $billingAddress = json_encode([ 'address' => '', 'city' => '', 'country' => '' ]);
        $shippingAddress = json_encode([ 'address' => '', 'city' => '', 'country' => '' ]);

        $order = Order::create([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'delivery_type' => $deliveryType,
            'notification_seen' => 0, // طلب جديد غير مرئي
            'subtotal' => $total,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $total + $shippingAmount + $taxAmount - $discountAmount,
            'currency' => $currency,
            'billing_address' => $billingAddress,
            'shipping_address' => $shippingAddress,
            'payment_status' => 'pending',
        ]);

        // إضافة المنتجات للأوردر وخصم الكمية من المخزون
        foreach ($cartItems as $item) {
            $unit = $item->unit_price ?? $item->product->price;
            // قراءة حقل attributes من الكارت بطريقة صريحة لتجنب التضارب مع خاصية Laravel الداخلية
            $cartAttributes = $item->getAttribute('attributes');

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku ?? '',
                'quantity' => $item->quantity,
                'price' => $unit,
                'total' => $unit * $item->quantity,
                // نسخ الخصائص واللون من السلة إلى عناصر الطلب
                'attributes' => $cartAttributes,
                'color' => $item->color,
            ]);

            // خصم الكمية من المخزون إذا كان المنتج يدير المخزون
            if ($item->product->manage_stock) {
                $product = Product::find($item->product_id);
                if ($product) {
                    // قراءة حقل attributes من الكارت
                    $cartAttributes = $item->getAttribute('attributes');
                    
                    // إذا كان المنتج له خصائص وتم تحديدها، خصم من مخزون الخصائص
                    if ($product->hasAttributes() && !empty($cartAttributes) && is_array($cartAttributes)) {
                        // خصم المخزون من كل خاصية محددة
                        foreach ($cartAttributes as $attrId => $attrValueId) {
                            $product->deductAttributeStock($attrId, $attrValueId, $item->quantity);
                        }
                    } else {
                        // إذا لم يكن للمنتج خصائص، خصم من مخزون المنتج
                        $product->stock_quantity = max(0, $product->stock_quantity - $item->quantity);
                        
                        // تحديث in_stock بناءً على الكمية المتبقية
                        if ($product->stock_quantity <= 0) {
                            $product->in_stock = false;
                        }
                        
                        $product->save();
                    }
                }
            }
        }

        // إرسال الفاتورة لسيستم الديسكتوب OrgaSoft (بعد حفظ كل عناصر الطلب)
        try {
            $orgaSoft = app(OrgaSoftService::class);
            if ($orgaSoft->isEnabled()) {
                $order->load(['user', 'items.product']);
                $result = $orgaSoft->postInvoice($order);
                if (!$result['success']) {
                    \Illuminate\Support\Facades\Log::warning('OrgaSoft: فشل إرسال الفاتورة بعد إنشاء الطلب من الموقع', [
                        'order_id' => $order->id,
                        'status'   => $result['status'] ?? null,
                        'body'     => $result['body'] ?? null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('OrgaSoft: فشل إرسال الفاتورة بعد إنشاء الطلب من الموقع', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }

        // حذف السلة بعد الإنشاء
        Cart::where('user_id', $userId)->delete();
        return redirect()->route('client.tracking', ['id' => $order->id])->with('success', 'تم إنشاء الطلب بنجاح');
    }
    /**
     * تحديث كمية منتج في السلة
     */
    public function updateCartItem(\Illuminate\Http\Request $request)
    {
        $userId = Auth::id();
        $id = $request->input('id');
        $quantity = (int) $request->input('quantity', 1);
        $cartItem = Cart::where('user_id', $userId)->where('id', $id)->first();
        
        if (!$cartItem || $quantity <= 0) {
            return redirect()->back()->withErrors([
                'message' => 'عنصر السلة غير موجود'
            ]);
        }

        // تحميل المنتج للتحقق من المخزون
        $cartItem->loadMissing('product');
        $product = $cartItem->product;

        if (!$product) {
            return redirect()->back()->withErrors([
                'message' => 'المنتج غير موجود'
            ]);
        }

        // التحقق من المخزون إذا كان المنتج يدير المخزون
        if ($product->manage_stock) {
            // قراءة حقل attributes من الكارت
            $cartAttributes = $cartItem->getAttribute('attributes');
            $availableStock = null;
            
            // إذا كان المنتج له خصائص وتم تحديدها، استخدم مخزون الخصائص
            if ($product->hasAttributes() && !empty($cartAttributes) && is_array($cartAttributes)) {
                // الحصول على أقل مخزون من الخصائص المحددة
                $stocks = [];
                foreach ($cartAttributes as $attrId => $attrValueId) {
                    $stock = $product->getAttributeStock($attrId, $attrValueId);
                    $stocks[] = $stock;
                }
                
                if (!empty($stocks)) {
                    $availableStock = min($stocks);
                } else {
                    $availableStock = 0;
                }
            } else {
                // إذا لم يكن للمنتج خصائص، استخدم مخزون المنتج
                $availableStock = $product->stock_quantity ?? 0;
                
                // تحديث in_stock ليتوافق مع stock_quantity
                if ($availableStock > 0 && !$product->in_stock) {
                    $product->in_stock = true;
                    $product->save();
                }
            }
            
            // التحقق من توفر المخزون
            if ($availableStock <= 0) {
                return redirect()->back()->withErrors([
                    'message' => 'المنتج غير متوفر في المخزون'
                ]);
            }
            
            // حساب الكمية الإجمالية المطلوبة (الكمية الجديدة - الكمية القديمة + الكمية القديمة في السلة)
            // نحتاج للتحقق من أن الكمية الجديدة لا تتجاوز المخزون المتاح
            if ($quantity > $availableStock) {
                return redirect()->back()->withErrors([
                    'message' => 'الكمية المطلوبة (' . $quantity . ') تتجاوز المخزون المتاح (' . $availableStock . ')'
                ]);
            }
        }

        $cartItem->quantity = $quantity;

        // تحديث الإجمالي بناءً على سعر الوحدة المخزن
        if (!is_null($cartItem->unit_price)) {
            $cartItem->total_price = $cartItem->unit_price * $cartItem->quantity;
        } else {
            // احتياطي: استخدام سعر المنتج إذا لم يكن unit_price موجوداً
            $unit = $product->price ?? 0;
            $cartItem->total_price = $unit * $cartItem->quantity;
        }

        $cartItem->save();
        return redirect()->back()->with('success', 'تم تحديث الكمية بنجاح');
    }

    /**
     * حذف منتج من السلة
     */
    public function removeCartItem(Request $request)
    {
        //dd($request->id);
        $userId = Auth::id();
        $id = $request->input('id');
        $cartItem = Cart::where('user_id', $userId)->where('id', $id)->first();
        if ($cartItem) {
            $cartItem->delete();
        }
        return redirect()->back();
    }
    public function dashboard()
    {
        $user = Auth::user();
        return inertia('Client/Dashboard', [
            'user' => $user
        ]);
    }

    public function cart()
    {
        $userId = Auth::id();
        $cartItems = [];
        if ($userId) {
            $cartItems = Cart::with('product')->where('user_id', $userId)->get();

            // تجهيز خصائص المنتجات مع القيم المختارة لعرضها في السلة
            $productIds = $cartItems->pluck('product_id')->unique()->values()->all();

            if (!empty($productIds)) {
                $rows = DB::table('product_attributes')
                    ->whereIn('product_attributes.product_id', $productIds)
                    ->join('attributes', 'product_attributes.attribute_id', '=', 'attributes.id')
                    ->join('attribute_values', 'product_attributes.attribute_value_id', '=', 'attribute_values.id')
                    ->select(
                        'product_attributes.product_id as product_id',
                        'attributes.id as attribute_id',
                        'attributes.name as attribute_name',
                        'attributes.slug as attribute_slug',
                        'attributes.type as attribute_type',
                        'attribute_values.id as value_id',
                        'attribute_values.value as value_value',
                        'attribute_values.label as value_label',
                        'attribute_values.color_code as value_color_code',
                        'attribute_values.image as value_image',
                        'product_attributes.price_adjustment as price'
                    )
                    ->orderBy('attributes.sort_order')
                    ->orderBy('attribute_values.sort_order')
                    ->get();

                $byProduct = [];
                foreach ($rows as $row) {
                    $pid = $row->product_id;
                    $attrId = $row->attribute_id;

                    if (!isset($byProduct[$pid])) {
                        $byProduct[$pid] = [];
                    }

                    if (!isset($byProduct[$pid][$attrId])) {
                        $byProduct[$pid][$attrId] = [
                            'id' => $attrId,
                            'name' => $row->attribute_name,
                            'slug' => $row->attribute_slug,
                            'type' => $row->attribute_type,
                            'values' => []
                        ];
                    }

                    $byProduct[$pid][$attrId]['values'][] = [
                        'id' => $row->value_id,
                        'attribute_id' => $attrId,
                        'value' => $row->value_value,
                        'label' => $row->value_label,
                        'color_code' => $row->value_color_code,
                        'image' => $row->value_image,
                        'price' => floatval($row->price ?? 0)
                    ];
                }

                // إلحاق الخصائص بكل منتج في عناصر السلة
                foreach ($cartItems as $item) {
                    $pid = $item->product_id;
                    if (isset($byProduct[$pid])) {
                        $item->product->attributes = array_values($byProduct[$pid]);
                    }
                }
            }

            // المجموع الإجمالي من جدول السلة نفسه (بعد احتساب الخصائص واللون)
            $totalPrice = DB::table('cart')
                ->where('user_id', $userId)
                ->selectRaw('SUM(total_price) as total_price')
                ->value('total_price');
        }
        // جلب رسوم التوصيل من الإعدادات
        $shippingCost = (float) Setting::get('shipping_cost', 15);
        
        return inertia('Client/Cart', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice ?? 0,
            'shippingCost' => $shippingCost,
        ]);
    }
    public function myOrders()
    {
        $userId = Auth::id();
        $orders = Order::where('user_id', $userId)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return inertia('Client/MyOrders', [
            'orders' => $orders
        ]);
    }

    public function showOrder($id)
    {
        $userId = Auth::id();
        $order = Order::where('id', $id)
            ->where('user_id', $userId)
            ->with(['items.product:id,name,images,main_image,sku', 'user:id,name,email,location_url'])
            ->firstOrFail();
        
        return inertia('Client/OrderDetails', [
            'order' => $order
        ]);
    }

    public function addToFavorites(Request $request)
    {
        $productId = $request->input('product_id');
        $userId = Auth::id();
        
        if (!$userId) {
            return redirect()->back()->withErrors([
                'message' => 'يجب تسجيل الدخول أولاً'
            ]);
        }
        
        $favorite = Favorite::where('user_id', $userId)->where('product_id', $productId)->first();
        
        if ($favorite) {
            // إذا كان موجوداً، احذفه (toggle off)
            $favorite->delete();
            return redirect()->back()->with('success', 'تم حذف المنتج من المفضلة');
        } else {
            // إذا لم يكن موجوداً، أضفه (toggle on)
            Favorite::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            return redirect()->back()->with('success', 'تم إضافة المنتج إلى المفضلة بنجاح');
        }
    }
    
    public function removeFromFavorites(Request $request)
    {
        $productId = $request->input('product_id');
        $userId = Auth::id();
        $favorite = Favorite::where('user_id', $userId)->where('product_id', $productId)->first();
        if ($favorite) {
            $favorite->delete();
            return redirect()->back()->with('success', 'تم حذف المنتج من المفضلة');
        }
        return redirect()->back();
    }
    public function favorites()
    {
        $userId = Auth::id();
        $favorites = Favorite::where('user_id', $userId)
            ->with('product.category')
            ->get();
        
        return inertia('Client/Favorites', [
            'favorites' => $favorites
        ]);
    }
    /**
     * إضافة منتج إلى السلة
     */
    public function addToCart(\Illuminate\Http\Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);
        $attributes = $request->input('attributes', []);
        $color = $request->input('color');
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->back()->withErrors([
                'message' => 'يجب تسجيل الدخول أولاً'
            ]);
        }

        // جلب المنتج والتحقق من الخصائص
        $product = Product::find($productId);
        
        if (!$product) {
            return redirect()->back()->withErrors([
                'message' => 'المنتج غير موجود'
            ]);
        }

        // جلب الخصائص المطلوبة للمنتج من pivot table
        $productAttributes = DB::table('product_attributes')
            ->where('product_id', $productId)
            ->join('attributes', 'product_attributes.attribute_id', '=', 'attributes.id')
            ->select('attributes.id as attribute_id')
            ->distinct()
            ->get();

        $hasAttributes = $productAttributes->count() > 0;

        // التحقق من الخصائص إذا كان المنتج يحتوي على خصائص
        if ($hasAttributes) {
            $productAttributeIds = $productAttributes->pluck('attribute_id')->toArray();
            
            // التحقق من أن جميع الخصائص محددة
            foreach ($productAttributeIds as $attrId) {
                if (!isset($attributes[$attrId]) || empty($attributes[$attrId])) {
                    return redirect()->back()->withErrors([
                        'message' => 'يرجى تحديد جميع الخصائص المطلوبة'
                    ]);
                }
            }
        }

        // التحقق من المخزون
        if ($product->manage_stock) {
            $availableStock = null;
            
            // إذا كان المنتج له خصائص، استخدم مخزون الخصائص
            if ($hasAttributes && !empty($attributes)) {
                // الحصول على أقل مخزون من الخصائص المحددة
                $stocks = [];
                foreach ($attributes as $attrId => $attrValueId) {
                    $stock = $product->getAttributeStock($attrId, $attrValueId);
                    $stocks[] = $stock;
                }
                
                if (!empty($stocks)) {
                    $availableStock = min($stocks);
                } else {
                    $availableStock = 0;
                }
            } else {
                // إذا لم يكن للمنتج خصائص، استخدم مخزون المنتج
                $availableStock = $product->stock_quantity ?? 0;
            }
            
            // التحقق من توفر المخزون
            if ($availableStock <= 0) {
                return redirect()->back()->withErrors([
                    'message' => 'المنتج غير متوفر في المخزون'
                ]);
            }
            
            // التحقق من أن الكمية المطلوبة متوفرة
            $cartItem = \App\Models\Cart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('attributes', json_encode($attributes))
                ->first();
            
            $currentCartQuantity = $cartItem ? $cartItem->quantity : 0;
            $totalRequestedQuantity = $currentCartQuantity + $quantity;
            
            if ($totalRequestedQuantity > $availableStock) {
                return redirect()->back()->withErrors([
                    'message' => 'الكمية المطلوبة غير متوفرة. الكمية المتاحة: ' . $availableStock
                ]);
            }
        }

        // ترتيب مصفوفة الخصائص حتى لا يختلف الـ JSON بين نفس الاختيارات
        if (is_array($attributes)) {
            ksort($attributes);
        }

        // حساب سعر المنتج (الأساسي + تعديلات الخصائص)
        $basePrice = $product->final_price ?? $product->price;

        $selectedValueIds = [];
        if (is_array($attributes)) {
            $selectedValueIds = array_values($attributes);
        }

        $additionalPrice = 0;
        if (!empty($selectedValueIds)) {
            $additionalPrice = DB::table('product_attributes')
                ->where('product_id', $productId)
                ->whereIn('attribute_value_id', $selectedValueIds)
                ->sum('price_adjustment');
        }

        $unitPrice = (float) $basePrice + (float) $additionalPrice;
        $totalPrice = $unitPrice * $quantity;

        $attributesJson = !empty($attributes) ? json_encode($attributes) : null;

        // البحث عن عنصر سلة لنفس المنتج ونفس الخصائص واللون
        $cartItem = \App\Models\Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->when($attributesJson, function ($query) use ($attributesJson) {
                $query->where('attributes', $attributesJson);
            }, function ($query) {
                $query->whereNull('attributes');
            })
            ->when($color, function ($query) use ($color) {
                $query->where('color', $color);
            }, function ($query) {
                $query->whereNull('color');
            })
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->unit_price = $unitPrice; // في حالة تغير السعر لأي سبب
            $cartItem->total_price = $cartItem->unit_price * $cartItem->quantity;
            $cartItem->save();
        } else {
            \App\Models\Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'attributes' => $attributes,
                'color' => $color,
            ]);
        }

        return redirect()->back()->with('success', 'تم إضافة المنتج إلى السلة بنجاح');
    }

    /**
     * عرض صفحة إعدادات الحساب
     */
    public function accountSettings()
    {
        $user = Auth::user();
        
        return inertia('Client/AccountSettings', [
            'user' => $user
        ]);
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // تحديث الاسم والبريد الإلكتروني
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // تحديث كلمة المرور إذا تم توفيرها
        if (!empty($validated['password'])) {
            // التحقق من كلمة المرور الحالية
            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()->withErrors([
                    'current_password' => 'كلمة المرور الحالية غير صحيحة'
                ]);
            }
            
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        return redirect()->back()->with('success', 'تم تحديث بيانات الحساب بنجاح');
    }

    // public function checkout()
    // {
    //     return inertia('Client/Checkout');
    // }
}
