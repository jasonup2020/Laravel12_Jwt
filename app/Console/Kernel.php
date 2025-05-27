<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // 每天凌晨 3 点执行汇率更新（可根据需求调整时间）
        $schedule->command('core:update_exchange_rates')->dailyAt('23:00')->timezone('Asia/Shanghai')->withoutOverlapping(); // 时区设置为上海
        
        // 每三小时执行一次邮件测试命令
        $schedule->command('core:mail_send_user_test')
                 ->everyThreeHours()
                 ->withoutOverlapping() // 防止任务重叠
                 ->onQueue('default'); // 指定队列（可选，根据项目队列配置调整）
        $schedule->command('emails:fetch --provider=gmail --message-id=1')->dailyAt('03:00')->onQueue('EmailUserge-email_queue')->withoutOverlapping(); // 避免重复执行
    }

    // ... 其他原有代码
}