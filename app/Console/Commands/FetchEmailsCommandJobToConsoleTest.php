<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helpers\Email_Imap;
use App\Jobs\FetchEmailJob;
use App\Models\Job\MailUserModel; // 假设存在用户模型，用于获取待下载的邮件数据
use App\Models\Job\MailSendUserModel; // 假设存在用户模型，用于获取待下载的邮件数据
use App\Models\Job\MailConfModel; // 假设存在用户模型，用于获取待下载的邮件数据
use App\Helpers\ChuanglanSmsColl;

class FetchEmailsCommandJobToConsoleTest extends Command {

    /**
     * 命令的签名（可带参数/选项）
     * @var string
     */
    protected $signature = 'cron:fetch_emails_job_to_console_test {--provider=} {--message-id=}';

    /**
     * 命令的描述
     * 【创蓝云智】欢迎使用创蓝公司产品，您的账号已开通，请打开 https://www.chuanglan.com ，使用账号 18126476210 临时密码 8N_e#Ti7^X93 登录并修改密码
     * @var string
     */
    protected $description = 'cron:fetch_emails_job_to_console_test --provider=gmail --message-id=0,1,2     测试发送邮件每三小时一次 Job->Console 驱动下载邮件数据（支持指定提供商和邮件ID）';

    
    private $call=[
        "13257225590",#自己
        "13728734406",#志林
        "13128823618",#老板
        "13612829202",#Yu
        "17702061479",#尹月
        "18810164972"#Allen
        ];
    /**
     * 执行命令
     */
    public function handle() {
        // 获取用户输入的参数（若未指定，则默认下载所有待处理邮件）
        $provider = $this->option('provider') ?: 'gmail'; // 默认下载 Gmail 邮件
        $messageId = $this->option('message-id') ?: 0;    // 默认下载 ID 为 1 的邮件
        // 分发 Job 任务（使用 Redis 队列，延迟 20 秒执行）
//        FetchEmailJob::dispatch([
//            'provider' => $provider,
//            'messageId' => $messageId
//        ])
//        ->onConnection('redis')  // 显式指定 Redis 连接
//        ->onQueue('EmailUserge_email_queue')  // 与 MailUserController 中一致的队列
//        ->delay(20);  // 延迟 20 秒执行

        self::getEmail($provider, $messageId, 0);

        $this->info("已触发邮件下载任务：提供商 {$provider}，邮件ID {$messageId}");
        Log::info("已触发邮件下载任务：提供商 {$provider}，邮件ID {$messageId}");
        return 0; // 命令执行成功返回 0
    }

    /**
     * @param type $provider
     * @param type $messageId
     */
    public function getEmail($provider = "gmail", $messageId = 0, $is_sql = 0) {
        $m_esu = new MailSendUserModel();
        $m_ec = new MailConfModel();
        if ($is_sql) {
            $m_ec->getLastSql(1);
        }

        $d_ec = $m_ec->where([["server_type", "=", "imap"], ["imap_mark", "=", "1"]])->orderby("sort", "desc")->get()->toArray();

        $dn_ec = [];
        foreach ($d_ec as $k => $v) {
            $dn_ec[$v["id"]] = $v;
        }

        $w_eu = [["mark", "=", "1"]];
        $d_eu = $m_esu->where($w_eu);
        if (!empty($messageId)) {
            $m_esu = $m_esu->wherein("id", explode(',', $messageId));
        }
        $d_eu = $m_esu->orderby("sort", "desc")->get(["id","name","email","email_config_id","passwords","privated_user","privated_code","phone","mail_mark","updated_at"])->toArray();
        $err_email=[];
        $err_email_int=0;
        foreach ($d_eu as $key => $value) {
            $_s = $dn_ec[$value["email_config_id"]];
            $server = $_s["server"];
            $port = $_s["port"];
            $encryption = "ssl";
            $username = $value["email"];
            $password = $value["passwords"];
            if ($value["privated_user"]) {
                $username = $value["privated_user"];
            }
            if ($value["privated_code"]) {
                $password = $value["privated_code"];
            }

            $log_sendTestMail = ["server" => $server, "port" => $port, "encryption" => $encryption, "username" => trim($username), "password" => $password,];
            $e_s = new Email_Imap($server, $port, $encryption, $username, $password);
            $subject = "test";
            $content = "test " . date("y-m-d H:i:s");
            $sendTestMail = $e_s->sendTestMail("mulyadidwi556@gmail.com", $subject, $content);   #mulyadidwi556@gmail.com彻底报废的Paypa
            $log_sendTestMail["sendTestMail"] = $sendTestMail;
            if (empty($sendTestMail)) {
                if($value["mail_mark"]){
                    //邮件发送失败
                    $err_email[]=$value;
                    $err_email_int++;
                    Log::error("sendTestMail NO:", $log_sendTestMail);
                }
                
            } else {
                //邮件发送成功
                Log::info("sendTestMail Ok:", $log_sendTestMail);
            }
            $this->info(json_encode(["username" => $username, "sendTestMail" => $sendTestMail], 256 + 64));
            sleep(2); ###暂停10秒
//            usleep(30000); ######// 10 * 1000 = 10,000 微秒  暂停25毫秒
        }
        if(!empty($err_email)){
            $err_email_name= array_key_value($err_email, "name",true);
            $err_email_id= array_key_value($err_email, "id",true);
//            print_r([$err_email_name,$err_email_id]);
            $csc=new ChuanglanSmsColl();
            
            
//            $sendSms=$csc->sendSms("13257225590", "您有($err_email_int)个帐号[".implode(",", $err_email_name). "]异常");
//            $sendSms=$csc->sendSms("13257225590", "您正在申请手机注册，验证码为：".implode(",", $err_email_name)."，5分钟内有效！");
            if(!empty($call= $this->call)){
                foreach ($call as $k_1 => $v_1) {
                    $csc->sendSms($v_1, " 您的帐号".implode(",", $err_email_name)."异常，时间". date("Y-m-d").",请及时处理 ");
                }
            }
            
            
            
            
            $csc->sendColl($this->call, implode(",", $err_email_name));
        }
//        $this->info(json_encode($err_email, 256 + 64));
    }
}
