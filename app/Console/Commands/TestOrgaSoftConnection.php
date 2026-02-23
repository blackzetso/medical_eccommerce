<?php

namespace App\Console\Commands;

use App\Services\OrgaSoftService;
use Illuminate\Console\Command;

class TestOrgaSoftConnection extends Command
{
    protected $signature = 'orgasoft:test-connection';

    protected $description = 'اختبار الاتصال بـ OrgaSoft من السيرفر (التحقق من الوصول للـ URL والإعدادات)';

    public function handle(OrgaSoftService $orgaSoft): int
    {
        $this->info('Testing OrgaSoft connection from this server...');

        if (!$orgaSoft->isEnabled()) {
            $this->warn('OrgaSoft integration is disabled in settings (orgasoft_enabled).');
            return self::FAILURE;
        }

        $result = $orgaSoft->getInvoice(1);

        if ($result['success']) {
            $this->info('Connection OK. This server can reach OrgaSoft (HTTP 200).');
            $body = $result['body'];
            $this->line('Response: ' . json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            if (is_array($body) && isset($body['errors']) && in_array('NO Items', $body['errors'] ?? [], true)) {
                $this->comment('("NO Items" is expected for getInvoice(1) when that invoice does not exist. The important part is that the connection works.)');
            }
            return self::SUCCESS;
        }

        if ($result['status'] === 0) {
            $this->error('Connection failed (network/timeout or exception).');
            $this->line('Details: ' . ($result['body'] ?? 'unknown'));
            $this->newLine();
            $this->comment('Ensure this server can reach the OrgaSoft URL. Check orgasoft_url in settings and run curl from this server to that URL to verify network/firewall.');
            return self::FAILURE;
        }

        $this->warn('OrgaSoft API reached but returned HTTP ' . $result['status'] . '.');
        $this->line('Response: ' . (is_string($result['body']) ? $result['body'] : json_encode($result['body'], JSON_UNESCAPED_UNICODE)));
        $this->comment('If 401: check orgasoft_api_key. If 404: endpoint or ID may differ. Connection from server is working.');
        return self::SUCCESS;
    }
}
