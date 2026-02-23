<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrgaSoftService
{
    private string $baseUrl;
    private string $apiKey;
    private string $accountId;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl    = rtrim(Setting::get('orgasoft_url', 'http://127.0.0.1:8080'), '/');
        $this->apiKey     = Setting::get('orgasoft_api_key', 'AHMED_ADEL');
        $this->accountId  = Setting::get('orgasoft_account_id', '1');
        $this->enabled    = (bool) Setting::get('orgasoft_enabled', '0');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * POST /INV_ — كتابة فاتورة في الديسكتوب عند إنشاء أوردر من الموقع.
     * ACCOUNT_ID في البودي: من orgasoft_id للمستخدم أو من إعداد orgasoft_account_id.
     * PROD_ID: من product_code إن وُجد، وإلا product_id (يُفضّل تعبئة product_code لمطابقة أورجا).
     */
    public function postInvoice(Order $order): array
    {
        $order->loadMissing(['user', 'items.product']);

        $accountId = $order->user?->orgasoft_id !== null && $order->user->orgasoft_id !== ''
            ? (string) $order->user->orgasoft_id
            : $this->accountId;
        $accountName = $order->user?->name ?? '';

        $items = [];
        foreach ($order->items as $item) {
            $productCode = $item->product?->product_code ?? null;
            $prodId = $productCode !== null && $productCode !== ''
                ? $productCode
                : $item->product_id;

            if ($productCode === null || $productCode === '') {
                Log::warning('OrgaSoft: استخدام product_id بدلاً من product_code (ضع product_code في المنتج لمطابقة أورجا)', [
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                ]);
            }

            $discountPercent = 0;
            if ($item->price > 0 && $item->product) {
                $originalPrice = (float) $item->product->price;
                if ($originalPrice > 0 && $item->price < $originalPrice) {
                    $discountPercent = round((($originalPrice - $item->price) / $originalPrice) * 100, 2);
                }
            }

            $items[] = [
                'INVOICES_H_ID' => $order->id,
                'ACCOUNT_ID'    => is_numeric($accountId) ? (int) $accountId : $accountId,
                'ACCOUNT_NAME'  => $accountName,
                'PROD_ID'       => is_numeric($prodId) ? (int) $prodId : $prodId,
                'DISCOUNT1'     => $discountPercent,
                'TOTAL_QTY'     => $item->quantity,
            ];
        }

        if (empty($items)) {
            Log::warning('OrgaSoft: لا توجد أصناف في الطلب للإرسال', ['order_id' => $order->id]);
            return ['success' => false, 'status' => 0, 'body' => 'No items to send'];
        }

        Log::info('OrgaSoft: إرسال فاتورة إلى أورجا', [
            'order_id' => $order->id,
            'url'      => $this->baseUrl . '/INV_',
            'payload'  => $items,
        ]);

        return $this->post('/INV_', $items, [
            'ACCOUNT_ID' => $this->accountId,
        ]);
    }

    /**
     * POST /stock — الاستعلام عن مخزون مجموعة منتجات.
     * $productIds: مصفوفة من product_code أو product_id
     */
    public function queryStock(array $productIds, int $dateId = 1): array
    {
        return $this->postRaw('/stock', implode(',', $productIds), [
            'ID_DATE' => (string) $dateId,
        ]);
    }

    /**
     * GET /INV_ID — الاستعلام عن فاتورة بالـ ID.
     */
    public function getInvoice(int $invoiceId): array
    {
        return $this->get('/INV_ID', [], [
            'ID_INV' => (string) $invoiceId,
        ]);
    }

    /**
     * GET /INV_R — الاستعلام عن المرتجعات في فترة زمنية.
     * $date1 / $date2: بصيغة dd/mm/yyyy
     * $productIds: مصفوفة اختيارية للتصفية على منتجات بعينها
     */
    public function getReturns(string $date1, string $date2, array $productIds = []): array
    {
        $query = [
            'API-KEY' => $this->apiKey,
            'DATE_1'  => $date1,
            'DATE_2'  => $date2,
        ];

        try {
            $request = Http::timeout(10)
                ->withHeaders(['API-KEY' => $this->apiKey]);

            if (!empty($productIds)) {
                $request = $request->withBody(implode(',', $productIds), 'text/plain');
            }

            $response = $request->get("{$this->baseUrl}/INV_R", $query);

            return [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'body'    => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('OrgaSoftService::getReturns failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'body' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function post(string $path, array $jsonBody, array $extraHeaders = []): array
    {
        $url = "{$this->baseUrl}{$path}";
        $headers = array_merge([
            'API-KEY'       => $this->apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ], $extraHeaders);
        $headerNames = array_keys($headers);
        $headersForLog = array_combine(
            $headerNames,
            array_map(fn ($k) => $k === 'API-KEY' ? '[REDACTED]' : $headers[$k], $headerNames)
        );

        $bodyString = json_encode($jsonBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->withBody($bodyString, 'application/json')
                ->post($url);

            $result = [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'body'    => $response->json() ?? $response->body(),
            ];

            if (!$result['success']) {
                Log::warning('OrgaSoftService POST response not OK', [
                    'url'       => $url,
                    'status'    => $result['status'],
                    'body'      => $result['body'],
                    'sent_len'  => count($jsonBody),
                    'request_headers' => $headersForLog,
                    'request_body'   => $jsonBody,
                ]);
            } else {
                Log::info('OrgaSoft: تم إرسال الفاتورة بنجاح', [
                    'url'    => $url,
                    'status' => $result['status'],
                    'body'   => $result['body'],
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error("OrgaSoftService::post {$path} failed", [
                'url'              => $url,
                'error'            => $e->getMessage(),
                'request_headers'  => $headersForLog,
                'request_body'     => $jsonBody,
            ]);
            return ['success' => false, 'status' => 0, 'body' => $e->getMessage()];
        }
    }

    private function postRaw(string $path, string $rawBody, array $extraHeaders = []): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(array_merge(['API-KEY' => $this->apiKey], $extraHeaders))
                ->withBody($rawBody, 'text/plain')
                ->post("{$this->baseUrl}{$path}");

            return [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'body'    => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("OrgaSoftService::postRaw {$path} failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'body' => $e->getMessage()];
        }
    }

    private function get(string $path, array $query = [], array $extraHeaders = []): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(array_merge(['API-KEY' => $this->apiKey], $extraHeaders))
                ->get("{$this->baseUrl}{$path}", $query);

            return [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'body'    => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("OrgaSoftService::get {$path} failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'body' => $e->getMessage()];
        }
    }
}
