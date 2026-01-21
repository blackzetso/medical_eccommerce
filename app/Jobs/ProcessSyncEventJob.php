<?php

namespace App\Jobs;

use App\Models\EventLog;
use App\Services\SyncEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessSyncEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $eventLogId)
    {
        $this->afterCommit();
    }

    /**
     * Backoff in seconds: 1m, 5m, 15m.
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(SyncEventService $service): void
    {
        $service->processByLogId($this->eventLogId);
    }

    public function failed(Throwable $exception): void
    {
        EventLog::where('id', $this->eventLogId)->update([
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ]);
    }
}
