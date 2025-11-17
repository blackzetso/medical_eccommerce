<?php
namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClientAccountController extends Controller
{
    public function tracking()
    {
        return inertia('Client/Tracking');
    }
    /**
     * إنشاء أوردر جديد من السلة
     */
    public function createOrder(Request $request)
    {
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

        // حساب الإجمالي
        $total = $cartItems->reduce(function ($sum, $item) {
            return $sum + ($item->product->price * $item->quantity);
        }, 0);

        // توليد رقم طلب فريد
        $orderNumber = 'ORD-' . strtoupper(uniqid()) . '-' . rand(1000,9999);

        // إعداد الحقول المطلوبة
        $taxAmount = 0;
        $shippingAmount = 0;
        $discountAmount = 0;
        $currency = 'USD';
        $billingAddress = json_encode([ 'address' => '', 'city' => '', 'country' => '' ]);
        $shippingAddress = json_encode([ 'address' => '', 'city' => '', 'country' => '' ]);

        $order = Order::create([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'subtotal' => $total,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'currency' => $currency,
            'billing_address' => $billingAddress,
            'shipping_address' => $shippingAddress,
            'payment_status' => 'pending',
        ]);

        // إضافة المنتجات للأوردر
        foreach ($cartItems as $item) {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku ?? '',
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'total' => $item->product->price * $item->quantity,
            ]);
        }

        // حذف السلة بعد الإنشاء
        Cart::where('user_id', $userId)->delete();

        return redirect()->route('client.tracking')->with('success', 'تم إنشاء الطلب بنجاح');
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
        if ($cartItem && $quantity > 0) {
            $cartItem->quantity = $quantity;
            $cartItem->save();
        }
        return redirect()->back();
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
            // هنا تعرض طلبات العميل باستخدام Inertia (Vue.js)
            return inertia('Client/Dashboard');
        }

    public function cart()
    {
        $userId = Auth::id();
        $cartItems = [];
        if ($userId) {
            $cartItems = Cart::with('product')->where('user_id', $userId)->get();
            $totalPrice = DB::table('cart')
                ->where('user_id', $userId)
                ->join('products', 'cart.product_id', '=', 'products.id')
                ->selectRaw('SUM(cart.quantity * products.price) as total_price')
                ->value('total_price');
        }
        return inertia('Client/Cart', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice ?? 0,
        ]);
    }
    public function myOrders()
        {
            // هنا تعرض طلبات العميل باستخدام Inertia (Vue.js)
            return inertia('Client/MyOrders');
        }

    public function favorites()
        {
            // هنا تعرض المفضلات باستخدام Inertia (Vue.js)
            return inertia('Client/Favorites');
        }
    /**
     * إضافة منتج إلى السلة
     */
    public function addToCart(\Illuminate\Http\Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        $cartItem = \App\Models\Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            \App\Models\Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return redirect()->back()->with('success', 'تمت إضافة المنتج إلى السلة بنجاح');
    }

    public function checkout()
    {
        return inertia('Client/Checkout');
    }
}
