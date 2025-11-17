<?php
namespace App\Http\Controllers;


use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClientAccountController extends Controller
{
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
}
