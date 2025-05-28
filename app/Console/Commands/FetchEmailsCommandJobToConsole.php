<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Helpers\Email_Imap;

use App\Jobs\FetchEmailJob;
use App\Models\Job\MailUserModel; // 假设存在用户模型，用于获取待下载的邮件数据
use App\Models\Job\MailConfModel; // 假设存在用户模型，用于获取待下载的邮件数据

class FetchEmailsCommandJobToConsole extends Command
{
    /**
     * 命令的签名（可带参数/选项）
     * @var string
     */
    protected $signature = 'cron:fetch_emails_job_to_console {--provider=} {--message-id=}';

    /**
     * 命令的描述
     * @var string
     */
    protected $description = 'cron:fetch_emails_job_to_console --provider=gmail --message-id=1,2    通过 Job->Console 驱动下载邮件数据（支持指定提供商和邮件ID）';

    /**
     * 执行命令
     */
    public function handle()
    {
        // 获取用户输入的参数（若未指定，则默认下载所有待处理邮件）
        $provider = $this->option('provider') ?: 'gmail'; // 默认下载 Gmail 邮件
        $messageId = $this->option('message-id') ?: 1;    // 默认下载 ID 为 1 的邮件

        // 分发 Job 任务（使用 Redis 队列，延迟 20 秒执行）
//        FetchEmailJob::dispatch([
//            'provider' => $provider,
//            'messageId' => $messageId
//        ])
//        ->onConnection('redis')  // 显式指定 Redis 连接
//        ->onQueue('EmailUserge_email_queue')  // 与 MailUserController 中一致的队列
//        ->delay(20);  // 延迟 20 秒执行

        
        $this->info("已触发邮件下载任务：提供商 {$provider}，邮件ID {$messageId}");
        Log::info("已触发邮件下载任务：提供商 {$provider}，邮件ID {$messageId}");
        return 0; // 命令执行成功返回 0
    }
    
    
    
    
    /**
     * 
     * 
     * 
     * 
     * @param type $provider
     * @param type $messageId
     */
    public function getEmail($provider="gmail",$messageId=1) {
        $m_eu=new MailUserModel();
        $m_ec=new MailConfModel();
        $m_eu->getLastSql(1);
        $d_ec=$m_ec->where([["server_type","=","imap"],["imap_mark","=","1"]])->orderby("sort","resc")->get()->toArray();

        $d_eu=$m_eu->where([["server_type","=","imap"],["imap_mark","=","1"]])->orderby("sort","resc")->get()->toArray();
        
        
        
        $h_ei=new Email_Imap($server, $port, $encryption, $username, $password);
    }
    
    
    
    
    
    
}