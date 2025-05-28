<?php

namespace App\Console;

use App\Traits\FuncTool;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\File;
use Throwable;

class Kernel extends ConsoleKernel {

    use FuncTool;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
            //
    ];

    /**
     * 定义计划任务和执行事件
     * Define the application's command schedule.
     *
     * cron('* * * * *');    自定义 Cron 计划执行任务
     * everyMinute();    每分钟执行一次任务
     * everyFiveMinutes();    每五分钟执行一次任务
     * everyTenMinutes();    每十分钟执行一次任务
     * everyFifteenMinutes();    每十五分钟执行一次任务
     * everyThirtyMinutes();    每三十分钟执行一次任务
     * hourly();    每小时执行一次任务
     * hourlyAt(17);    每小时第 17 分钟执行一次任务
     * daily();    每天 0 点执行一次任务
     * dailyAt('13:00');    每天 13 点执行一次任务
     * twiceDaily(1, 13);    每天 1 点及 13 点各执行一次任务
     * weekly();    每周日 0 点执行一次任务
     * weeklyOn(1, '8:00');    每周一的 8 点执行一次任务
     * monthly();    每月第一天 0 点执行一次任务
     * monthlyOn(4, '15:00');    每月 4 号的 15 点 执行一次任务
     * quarterly();    每季度第一天 0 点执行一次任务
     * yearly();    每年第一天 0 点执行一次任务
     * timezone('America/New_York');    设置时区
     * weekdays();    限制任务在工作日执行
     * weekends();    限制任务在周末执行
     * sundays();    限制任务在周日执行
     * mondays();    限制任务在周一执行
     * tuesdays();    限制任务在周二执行
     * wednesdays();    限制任务在周三执行
     * thursdays();    限制任务在周四执行
     * fridays();    限制任务在周五执行
     * saturdays();    限制任务在周六执行
     * between($start, $end);    限制任务在 $start 和 $end 区间执行->hourly()->between('7:00', '22:00');
     * when(Closure);    限制任务在闭包返回为真时执行
     * environments($env);    限制任务在特定环境执行
     * withoutOverlapping();    避免任务重复执行
     *
     * @desc  监听schedule方法 ： php artisan schedule:run
     * @desc 手动执行对应进程任务，如：php artisan command:handle (php artisan 名称)
     * @desc 服务器自动执行：
     *      1.编辑定时任务：
     *          crontab -e
     *      2.php多版本可以将php改为版本的绝对路径，项目路径：
     *          * * * * * php /www/wwwroot/ai_interview/artisan schedule:run >> /www/wwwroot/ai_interview/test.txt 2>&1
     *          * * * * * php /www/wwwroot/ai_interview/artisan schedule:run >> /dev/null 2>&1 > cron.txt
     *      3.保存重启cron服务：
     *          CentOS7方法：
     *              重启服务：systemctl restart crond.service
     *          CentOS6方法：
     *              重启服务：service crond restart
     *
     *
     * @param Schedule $schedule
     * @return void
     * @throws Throwable
     */
    protected function schedule(Schedule $schedule) {
        // 每天凌晨 3 点执行汇率更新（可根据需求调整时间）
//        $schedule->command('core:update_exchange_rates')->dailyAt('23:00')->timezone('Asia/Shanghai')->withoutOverlapping(); // 时区设置为上海
//        
//        // 每30秒执行一次 mail_send_user_test 命令
//        $schedule->command('cron:mail_send_user_test')
//                ->everySeconds(30)  // 每30秒触发
//                ->withoutOverlapping()  // 防止任务重叠（若任务执行时间超过30秒）
//                ->onQueue('default');  // 指定队列（根据项目队列配置调整）
//        
//        // 每三小时执行一次邮件测试命令
//        $schedule->command('core:fetch_emails_job_to_console_test --provider=gmail --message-id=0')
//                ->everyThreeHours()
//                ->withoutOverlapping() // 防止任务重叠
//                ->onQueue('default'); // 指定队列（可选，根据项目队列配置调整）
//        $schedule->command('emails:fetch --provider=gmail --message-id=1')->dailyAt('03:00')->onQueue('EmailUserge-email_queue')->withoutOverlapping(); // 避免重复执行
//        
        // 临时测试：每分钟执行一次（测试完成后删除）
        $schedule->command('cron:mail_send_user_test')
                ->everyMinute()
                ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
