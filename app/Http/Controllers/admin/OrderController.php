<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * عرض قائمة الطلبات
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc');

        // البحث حسب رقم الطلب أو اسم العميل
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // تصفية حسب حالة الدفع
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        $orders = $query->paginate(15);

        return Inertia::render('Admin/theme1/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status']),
            'statuses' => Order::getStatuses(),
            'paymentStatuses' => Order::getPaymentStatuses(),
        ]);
    }

    /**
     * عرض تفاصيل الطلب
     */
    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email,phone,location_url',
            'items.product:id,name,images,sku',
        ]);

        // تجهيز بيانات الخصائص المختارة لكل منتج في الطلب
        $productIds = $order->items->pluck('product_id')->unique()->values()->all();

        $byProduct = [];
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
                    'attribute_values.color_code as value_color_code'
                )
                ->orderBy('attributes.sort_order')
                ->orderBy('attribute_values.sort_order')
                ->get();

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
                        'values' => [],
                    ];
                }

                $byProduct[$pid][$attrId]['values'][] = [
                    'id' => $row->value_id,
                    'attribute_id' => $attrId,
                    'value' => $row->value_value,
                    'label' => $row->value_label,
                    'color_code' => $row->value_color_code,
                ];
            }
        }

        // إضافة مصفوفة الخصائص المختارة لكل عنصر طلب
        $order->items->transform(function ($item) use ($byProduct) {
            $selectedAttributes = [];

            // قراءة حقل attributes من عنصر الطلب بشكل صريح لتجنب التضارب مع خاصية Laravel الداخلية
            $rawAttributes = $item->getAttribute('attributes');

            // لو مافيش خصائص أصلاً نرجع العنصر كما هو
            if (empty($rawAttributes)) {
                $item->selected_attributes = [];
                return $item;
            }

            // إذا كان المخزن JSON كنص، نحوله لمصفوفة
            if (is_string($rawAttributes)) {
                $decoded = json_decode($rawAttributes, true);
                $rawAttributes = is_array($decoded) ? $decoded : [];
            }

            if (is_array($rawAttributes) && isset($byProduct[$item->product_id])) {
                foreach ($rawAttributes as $attrId => $valueId) {
                    if (!isset($byProduct[$item->product_id][$attrId])) {
                        continue;
                    }

                    $attr = $byProduct[$item->product_id][$attrId];
                    $value = collect($attr['values'])->firstWhere('id', (int) $valueId);

                    if ($value) {
                        $selectedAttributes[] = [
                            'attribute_id' => $attrId,
                            'attribute_name' => $attr['name'],
                            'attribute_slug' => $attr['slug'],
                            'value_id' => $value['id'],
                            'value_label' => $value['label'] ?? $value['value'],
                            'value_color_code' => $value['color_code'],
                        ];
                    }
                }
            }

            $item->selected_attributes = $selectedAttributes;

            return $item;
        });

        return Inertia::render('Admin/theme1/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * تحديث حالة الطلب
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // إذا تم إلغاء الطلب، إرجاع الكمية إلى المخزون
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            // تحميل عناصر الطلب مع المنتجات
            $order->load('items.product');
            
            foreach ($order->items as $item) {
                $product = $item->product;
                
                if ($product && $product->manage_stock) {
                    // إرجاع الكمية إلى المخزون
                    $product->stock_quantity = $product->stock_quantity + $item->quantity;
                    
                    // تحديث in_stock إذا كانت الكمية أكبر من 0
                    if ($product->stock_quantity > 0) {
                        $product->in_stock = true;
                    }
                    
                    $product->save();
                }
            }
        }
        // إذا تم تغيير حالة الطلب من "cancelled" إلى حالة أخرى، خصم الكمية مرة أخرى
        elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            // تحميل عناصر الطلب مع المنتجات
            $order->load('items.product');
            
            foreach ($order->items as $item) {
                $product = $item->product;
                
                if ($product && $product->manage_stock) {
                    // خصم الكمية من المخزون
                    $product->stock_quantity = max(0, $product->stock_quantity - $item->quantity);
                    
                    // تحديث in_stock بناءً على الكمية المتبقية
                    if ($product->stock_quantity <= 0) {
                        $product->in_stock = false;
                    }
                    
                    $product->save();
                }
            }
        }

        $order->update([
            'status' => $newStatus,
            'shipped_at' => $newStatus === 'shipped' ? now() : $order->shipped_at,
            'delivered_at' => $newStatus === 'delivered' ? now() : $order->delivered_at,
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب بنجاح');
    }

    /**
     * تحديث حالة الدفع
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $order->update([
            'payment_status' => $request->payment_status,
        ]);

        return back()->with('success', 'تم تحديث حالة الدفع بنجاح');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * التحقق من وجود طلبات غير مرئية (notification_seen = 0) - أي طلب بغض النظر عن الحالة
     */
    public function checkPendingOrders(Request $request)
    {
        // البحث عن أي طلب بـ notification_seen = 0 (بغض النظر عن الحالة)
        $unseenOrders = Order::where('notification_seen', 0)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $unseenCount = $unseenOrders->count();
        
        if ($unseenCount > 0) {
            return response()->json([
                'has_unseen_orders' => true,
                'unseen_count' => $unseenCount
            ]);
        }
        
        return response()->json([
            'has_unseen_orders' => false,
            'unseen_count' => 0
        ]);
    }

    /**
     * تحديث حالة notification_seen لجميع الطلبات (ليس فقط pending)
     */
    public function markOrdersAsSeen(Request $request)
    {
        // تحديث جميع الطلبات التي notification_seen = 0 إلى 1
        $updated = Order::where('notification_seen', 0)
            ->update(['notification_seen' => 1]);
        
        return response()->json([
            'success' => true,
            'updated_count' => $updated,
            'message' => 'تم تحديث حالة الإشعارات بنجاح'
        ]);
    }

    /**
     * إعادة تعيين notification_seen للاختبار (للتطوير فقط)
     */
    public function resetNotificationSeen(Request $request)
    {
        // إعادة تعيين جميع الطلبات إلى notification_seen = 0
        $updated = Order::update(['notification_seen' => 0]);
        
        return response()->json([
            'success' => true,
            'updated_count' => $updated,
            'message' => 'تم إعادة تعيين حالة الإشعارات للاختبار'
        ]);
    }

    /**
     * الحصول على آخر معرف طلب (لتهيئة النظام)
     */
    public function getLastOrderId()
    {
        $lastOrder = Order::orderBy('created_at', 'desc')->first();
        
        return response()->json([
            'last_order_id' => $lastOrder ? $lastOrder->id : 0
        ]);
    }
}
