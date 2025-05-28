<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchEmailJob;
use App\Models\Job\MailUserModel; // 假设存在用户模型，用于获取待下载的邮件数据

class FetchEmailsCommandConsoleToJob extends Command
{
    /**
     * 命令的签名（可带参数/选项）
     * @var string
     */
    protected $signature = 'cron:emails_fetch_console_to_job {--provider=} {--message-id=}';

    /**
     * 命令的描述
     * @var string
     */
    protected $description = 'cron:emails_fetch_console_to_job --provider=gmail --message-id=1    通过 Console->Job 驱动下载邮件数据（支持指定提供商和邮件ID）';

    /**
     * 执行命令
     */
    public function handle()
    {
        // 获取用户输入的参数（若未指定，则默认下载所有待处理邮件）
        $provider = $this->option('provider') ?: 'gmail'; // 默认下载 Gmail 邮件
        $messageId = $this->option('message-id') ?: 1;    // 默认下载 ID 为 1 的邮件

        // 分发 Job 任务（使用 Redis 队列，延迟 20 秒执行）
        FetchEmailConsoleTOJob::dispatch([
            'provider' => $provider,
            'messageId' => $messageId
        ])
        ->onConnection('redis')  // 显式指定 Redis 连接
//        ->onQueue('EmailUserge_email_queue')  // 与 MailUserController 中一致的队列   队列  不要指定队列
        ->delay(20);  // 延迟 20 秒执行###->delay(20);  // 延迟 20 秒执行

        $this->info("已触发邮件下载任务：提供商 {$provider}，邮件ID {$messageId}");
        return 0; // 命令执行成功返回 0
    }
}