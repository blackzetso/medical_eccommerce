<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrgaSoftService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderToOrgaSoftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * يُنفَّذ بعد انتهاء الطلب حتى تكون عناصر الطلب (OrderItem) محفوظة في قاعدة البيانات.
     */
    public $delay = 3;

    public function __construct(public int $orderId)
    {
        $this->afterCommit();
    }

    public function handle(OrgaSoftService $orgaSoft): void
    {
        if (!$orgaSoft->isEnabled()) {
            return;
        }

        $order = Order::with(['user', 'items.product'])->find($this->orderId);

        if (!$order || $order->items->isEmpty()) {
            Log::warning('OrgaSoft Job: الطلب غير موجود أو لا يحتوي على أصناف', ['order_id' => $this->orderId]);
            return;
        }

        try {
            $result = $orgaSoft->postInvoice($order);

            if (!$result['success']) {
                Log::warning('OrgaSoft: فشل إرسال الفاتورة للديسكتوب', [
                    'order_id' => $order->id,
                    'status'   => $result['status'],
                    'body'     => $result['body'],
                ]);
            } else {
                Log::info('OrgaSoft: تم إرسال الفاتورة للديسكتوب بنجاح', ['order_id' => $order->id]);
            }
        } catch (\Throwable $e) {
            Log::error('OrgaSoft Job: استثناء عند إرسال الفاتورة', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
