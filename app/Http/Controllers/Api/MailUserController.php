<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Email\MailConfService;
use App\Services\Job\EmailService;
use App\Jobs\FetchEmailJobToConsole;
use App\Jobs\FetchEmailConsoleTOJob;    // 新增
use Illuminate\Support\Facades\Artisan; // 新增 Artisan 引入

use Illuminate\Validation\ValidationException;


/**
 * Email用户
 *
 * @property MailUserService $service
 */
class MailUserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->service = new EmailService();
    }
    
    public function get_conf() {
//        $m_ec=new MailConf();
//        return $m_ec->getEmailConf();
   
        return MailConfService::getEmailConf();
    }
    
    /**
     * 获得用户的Email（Job 驱动 Console）
     * @param Request $request
     * @return type
     */
    public function getEmailUserJ_C(Request $request) {
        Log::info("EmailController->getEmailUserJ_C", $request->all());
        try {
            $validated = $request->validate([
                'provider' => 'required|in:gmail,outlook,orther',  // 新增pop3选项
                'id' => 'required|string|min:1'
            ]);
            
            
            // 显式指定 Redis 连接并推送到 'email_queue' 队列，延时20秒执行
            FetchEmailJobToConsole::dispatch($validated)
                ->onConnection('redis')  // 显式指定 Redis 连接
//                ->onQueue('email_serge_email_queue') //'email_queue' 队列  不要指定队列
                ->delay(2);  ###->delay(20);  // 延迟 20 秒执行

            Log::info('邮件下载任务分发成功', $validated);
            return message("邮件下载任务已提交，稍后查看日志或通知");
        } catch (ValidationException $e) {
            Log::error('请求参数验证失败', ['errors' => $e->errors()]);
            return message("参数错误：" . implode('; ', $e->errors()[array_key_first($e->errors())]), 422);
        } catch (Exception $e) {
            Log::error('邮件下载任务分发失败', ['error' => $e->getMessage()]);
            return message("邮件下载任务提交失败，请稍后重试", 500);
        }
    }
    
    /**
     * 获得用户的Email（Console 驱动 Job）
     * @param Request $request
     * @return type
     */
    public function getEmailUserC_J(Request $request) {
        Log::info("EmailController->getEmailUserC_J", $request->all());
        try {
            $validated = $request->validate([
                'provider' => 'required|in:gmail,outlook,orther',  // 新增pop3选项
                'id' => 'required|string|min:1'
            ]);

            $this->info("core:emails_fetch_job_to_console --provider=".$validated["provider"]." --message-id=".$validated['id']);
            Log::info("core:emails_fetch_job_to_console --provider=".$validated["provider"]." --message-id=".$validated['id']);

            // 关闭 PHP 超时限制（0 表示无限制）
            set_time_limit(600); // 600 秒 = 10 分钟
            ini_set('max_execution_time', 600); // 确保 PHP 脚本可以执行超过 10 分钟
            ini_set('memory_limit', '256M'); // 增加内存限制
            ini_set('max_input_time', 600); // 增加输入时间限制
            ini_set('post_max_size', '256M'); // 增加 POST 数据大小限制
            ini_set('upload_max_filesize', '256M'); // 增加上传文件大小限制
            ini_set('default_socket_timeout', 600); // 增加默认套接字超时时间
            // 调用 Artisan 命令，使用 $validated 中的参数
            $exitCode = Artisan::call('core:emails_fetch_job_to_console', [
                '--provider' => $validated['provider'],  // 从 $validated 中获取 provider
                '--message-id' => $validated['id']       // 从 $validated 中获取 id（对应邮件ID）
            ]);

            if ($exitCode === 0) {
                Log::info('邮件抓取命令执行成功', $validated);
                return message("邮件下载任务已提交，稍后查看日志或通知");
            } else {
                Log::error('邮件抓取命令执行失败，退出码：' . $exitCode, $validated);
                return message("邮件下载任务提交失败（命令执行异常），请检查日志", 500);
            }
        } catch (ValidationException $e) {
            Log::error('请求参数验证失败', ['errors' => $e->errors()]);
            return message("参数错误：" . implode('; ', $e->errors()[array_key_first($e->errors())]), 422);
        } catch (Exception $e) {
            Log::error('邮件抓取命令调用失败', ['error' => $e->getMessage()]);
            return message("邮件下载任务提交失败，请稍后重试", 500);
        }
    }
    
    
    
    
    public function getTestEmailUserC_J(Request $request) {
        Log::info("EmailController->getEmailUserC_J", $request->all());
        try {
            $validated = $request->validate([
                'provider' => 'required|in:gmail,outlook,orther',  // 新增pop3选项
                'id' => 'required|string|min:1'
            ]);

            $this->info("core:emails_fetch_job_to_console --provider=".$validated["provider"]." --message-id=".$validated['id']);
            Log::info("core:fetch_emails_job_to_console_test --provider=".$validated["provider"]." --message-id=".$validated['id']);

            // 关闭 PHP 超时限制（0 表示无限制）
            set_time_limit(600); // 600 秒 = 10 分钟
            ini_set('max_execution_time', 600); // 确保 PHP 脚本可以执行超过 10 分钟
            ini_set('memory_limit', '256M'); // 增加内存限制
            ini_set('max_input_time', 600); // 增加输入时间限制
            ini_set('post_max_size', '256M'); // 增加 POST 数据大小限制
            ini_set('upload_max_filesize', '256M'); // 增加上传文件大小限制
            ini_set('default_socket_timeout', 600); // 增加默认套接字超时时间

            
            // 调用 Artisan 命令，使用 $validated 中的参数
            $exitCode = Artisan::call('core:emails_fetch_job_to_console', [
                '--provider' => $validated['provider'],  // 从 $validated 中获取 provider
                '--message-id' => $validated['id']       // 从 $validated 中获取 id（对应邮件ID）
            ]);

            if ($exitCode === 0) {
                Log::info('邮件抓取命令执行成功', $validated);
                return message("邮件下载任务已提交，稍后查看日志或通知");
            } else {
                Log::error('邮件抓取命令执行失败，退出码：' . $exitCode, $validated);
                return message("邮件下载任务提交失败（命令执行异常），请检查日志", 500);
            }
        } catch (ValidationException $e) {
            Log::error('请求参数验证失败', ['errors' => $e->errors()]);
            return message("参数错误：" . implode('; ', $e->errors()[array_key_first($e->errors())]), 422);
        } catch (Exception $e) {
            Log::error('邮件抓取命令调用失败', ['error' => $e->getMessage()]);
            return message("邮件下载任务提交失败，请稍后重试", 500);
        }
    }
    
    
    
    
}