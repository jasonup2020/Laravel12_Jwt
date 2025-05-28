<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail; // 假设已创建测试邮件类
use App\Models\Job\MailSendUserModel;
use App\Helpers\ChuanglanSmsColl;
use Illuminate\Support\Facades\Log;

class MailSendUserTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:mail_send_user_test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cron artisan core:mail_send_user_test 测试发送邮件每三小时一次';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // 从 mail_users 表获取测试用户（假设需要发送给所有有效用户）
            $testUsers = MailSendUserModel::where([['mark',"=", 1]])->pluck('email')->toArray();

            if (empty($testUsers)) {
                $this->info('无有效测试用户，邮件未发送');
                return 0;
            }

//            // 发送邮件（TestMail 需提前创建，参考 Laravel 邮件文档）
//            foreach ($testUsers as $email) {
//                Mail::to($email)->send(new TestMail());
//                $this->info("测试邮件已发送至：{$email}");
//            }
//            
//            $comm=new ChuanglanSmsColl();
//            

            $this->info('所有测试邮件发送完成');
            Log::info("MailSendUserTest",["time"=> date("Y-m-d H:i:s")]);
            return 0;
        } catch (\Exception $e) {
            $this->error("邮件发送失败：{$e->getMessage()}");
            return 1; // 非零退出码表示命令执行失败
        }
    }
}
