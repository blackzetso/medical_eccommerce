<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrgaSoftService;

class OrderObserver
{
    public function __construct(private readonly OrgaSoftService $orgaSoft)
    {
    }

    /**
     * إرسال الفاتورة للديسكتوب يتم من ClientAccountController::createOrder بعد حفظ كل
     * عناصر الطلب. هنا لا نرسل لأن عناصر الطلب لم تُحفظ بعد عند استدعاء created().
     */
    public function created(Order $order): void
    {
        // لا شيء — الإرسال من createOrder بعد حفظ الـ items
    }
}
