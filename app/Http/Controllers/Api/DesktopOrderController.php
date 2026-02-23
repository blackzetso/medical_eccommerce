<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DesktopOrderController extends Controller
{
    /**
     * POST /api/desktop/order
     *
     * يستقبل فاتورة من سيستم الديسكتوب (OrgaSoft) وينشئها كأوردر في الموقع.
     *
     * Body (JSON Array):
     * [
     *   {
     *     "INVOICES_H_ID": 4,
     *     "ACCOUNT_ID": 1243,
     *     "ACCOUNT_NAME": "Ahmed",
     *     "PROD_ID": 107189,
     *     "DISCOUNT1": 2,
     *     "TOTAL_QTY": 10
     *   }, ...
     * ]
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            '*'                  => 'array',
            '*.INVOICES_H_ID'   => 'required|integer',
            '*.ACCOUNT_ID'      => 'required|integer',
            '*.ACCOUNT_NAME'    => 'nullable|string',
            '*.PROD_ID'         => 'required',
            '*.TOTAL_QTY'       => 'required|integer|min:1',
            '*.DISCOUNT1'       => 'nullable|numeric|min:0',
        ]);

        $items = $request->all();

        if (empty($items)) {
            return response()->json(['message' => 'No items provided'], 422);
        }

        // كل عناصر الفاتورة بيكون ليها نفس الـ INVOICES_H_ID
        $desktopInvoiceId = $items[0]['INVOICES_H_ID'];
        $accountId        = $items[0]['ACCOUNT_ID'];
        $accountName      = $items[0]['ACCOUNT_NAME'] ?? '';

        // التحقق من عدم تكرار نفس الفاتورة
        $existingOrder = Order::where('order_number', 'DESKTOP-' . $desktopInvoiceId)->first();
        if ($existingOrder) {
            return response()->json([
                'message'  => 'Invoice already exists',
                'order_id' => $existingOrder->id,
            ], 409);
        }

        // البحث عن المستخدم أو إنشاء مستخدم وهمي للأوردر
        $user = User::find($accountId);

        if (!$user) {
            // إذا مش موجود، نبحث باسمه أو نستخدم أول admin
            $user = User::where('name', $accountName)->first()
                ?? User::first();
        }

        if (!$user) {
            return response()->json(['message' => 'No user found to associate this order with'], 422);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $orderItems = [];

            foreach ($items as $item) {
                // البحث عن المنتج بالـ product_code أو الـ id
                $product = Product::where('product_code', $item['PROD_ID'])->first()
                    ?? Product::find($item['PROD_ID']);

                $unitPrice  = $product ? (float) $product->price : 0;
                $discount   = (float) ($item['DISCOUNT1'] ?? 0);
                $qty        = (int) $item['TOTAL_QTY'];

                if ($discount > 0 && $unitPrice > 0) {
                    $unitPrice = $unitPrice * (1 - $discount / 100);
                }

                $lineTotal   = $unitPrice * $qty;
                $subtotal   += $lineTotal;

                $orderItems[] = [
                    'product_id'   => $product?->id,
                    'product_name' => $product?->name ?? ('Product #' . $item['PROD_ID']),
                    'product_sku'  => $product?->sku ?? '',
                    'quantity'     => $qty,
                    'price'        => $unitPrice,
                    'total'        => $lineTotal,
                ];
            }

            $currency = Setting::get('currency', 'EGP');

            $order = Order::create([
                'order_number'    => 'DESKTOP-' . $desktopInvoiceId,
                'user_id'         => $user->id,
                'status'          => 'pending',
                'delivery_type'   => 'delivery',
                'notification_seen' => 0,
                'subtotal'        => $subtotal,
                'tax_amount'      => 0,
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'total_amount'    => $subtotal,
                'currency'        => $currency,
                'billing_address' => json_encode(['address' => '', 'city' => '', 'country' => '']),
                'shipping_address' => json_encode(['address' => '', 'city' => '', 'country' => '']),
                'payment_status'  => 'pending',
                'notes'           => 'تم استيراد هذا الطلب من سيستم الديسكتوب OrgaSoft (ID: ' . $desktopInvoiceId . ')',
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            DB::commit();

            Log::info('OrgaSoft: تم استيراد أوردر من الديسكتوب بنجاح', [
                'desktop_invoice_id' => $desktopInvoiceId,
                'order_id'           => $order->id,
            ]);

            return response()->json([
                'message'  => 'Order created successfully',
                'order_id' => $order->id,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OrgaSoft: فشل إنشاء الأوردر من الديسكتوب', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/desktop/orders
     *
     * يرجع قائمة الأوردرات المنشأة من الموقع (لمزامنة الديسكتوب).
     * الديسكتوب يقدر يستعلم عن الأوردرات الجديدة منذ آخر تزامن.
     */
    public function index(Request $request): JsonResponse
    {
        $since = $request->query('since'); // datetime string

        $query = Order::with(['user:id,name', 'items.product:id,name,product_code'])
            ->whereDoesntHave('items', fn ($q) => $q->whereNull('product_id'))
            ->orderBy('created_at', 'desc')
            ->limit(100);

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        $orders = $query->get()->map(function (Order $order) {
            return [
                'INVOICES_H_ID' => $order->id,
                'ORDER_NUMBER'  => $order->order_number,
                'ACCOUNT_ID'    => $order->user_id,
                'ACCOUNT_NAME'  => $order->user?->name ?? '',
                'TOTAL_AMOUNT'  => $order->total_amount,
                'STATUS'        => $order->status,
                'CREATED_AT'    => $order->created_at->format('d/m/Y H:i'),
                'ITEMS'         => $order->items->map(fn ($item) => [
                    'PROD_ID'   => $item->product?->product_code ?? $item->product_id,
                    'PROD_NAME' => $item->product_name,
                    'TOTAL_QTY' => $item->quantity,
                    'PRICE'     => $item->price,
                    'TOTAL'     => $item->total,
                ]),
            ];
        });

        return response()->json($orders);
    }
}
