<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan; // 新增 Artisan 引入
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class FetchEmailConsoleTOJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;
    
    
    protected $data;

    /**
     * 任务可尝试的次数 
     *
     * @var int
     */
    public $tries = 8;

    /**
     * 任务失败前允许的最大异常数
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * 重试任务前等待的秒数。
     *
     * @var int
     */
    public $backoff = 3;

    /**
     * 任务执行的最大秒数。
     * @var int
     */
    public $timeout = 30;

    

    /**
     * 构造函数接收参数（provider: 邮件提供商，messageId: 邮件ID）
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }


    /**
     * 执行任务
     */
    public function handle()
    {
        try {
            // 调用 Artisan 命令，传递参数
//            Artisan::call('core:emails_fetch_job_to_console', [
//                '--provider' => $this->params['provider'] ?? 'gmail',
//                '--message-id' => $this->params['messageId'] ?? 1
//            ]);

            Log::info('已调用邮件抓取命令', $this->params);
        } catch (\Exception $e) {
            Log::error('邮件抓取命令调用失败:', ['error' => $e->getMessage()]);
        }
    }
}
