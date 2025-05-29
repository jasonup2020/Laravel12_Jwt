<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class FetchEmailConsoleCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $provider;
    protected $messageId;

    public function __construct(string $provider, string $messageId)
    {
        $this->provider = $provider;
        $this->messageId = $messageId;
    }

    public function handle()
    {
        // 在 Job 中调用 Artisan 命令（无超时限制）
        Artisan::call('core:emails_fetch_job_to_console', [
            '--provider' => $this->provider,
            '--message-id' => $this->messageId
        ]);
    }
}