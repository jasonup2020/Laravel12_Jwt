<?php

namespace App\Services\Api;


//use App\Models\MailUsersModel;
//use App\Models\MailConfModel;
//use App\Models\MailOrderModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Helpers\EmailPop3;
use App\Helpers\EmailImap;

use App\Models\Email\MailUser;


class MailUsersService extends BaseService {

    /**
     * 邮件用户模型实例
     * @var MailUsersModel
     */
    protected $model;

    /**
     * 构造函数：初始化邮件用户模型
     */
    public function __construct() {
        $this->model = new MailUser();
    }

    
    /**
     * 获取特定邮件用户关联的邮箱配置信息（自定义方法）
     * @param int $id 用户ID（可选，当前未使用）
     * @return \Illuminate\Http\JsonResponse 邮箱配置数据或错误提示
     */
    public function getEmailUser(int $id = 0) {
        $ret = [];
        if (!empty($id)) {
            // 初始化邮件配置模型
            $m_mc = new MailConfModel();
            // 查询有效（mark=1）的邮箱配置，筛选指定字段
            $d_mc = $m_mc->where(["mark" => 1])
                    ->get([
                        'id',
                        'server_name', // 邮件服务名（如abc@gmail.com）
                        'server_type', // 邮件类型（IMTPimap/POPpop/SMTPsmtp）
                        'name', // Server名称
                        'server', // Server地址
                        'port', // 端口（如993/995/587）
                        'encryption', // 加密方式
                        'authentication_method', // 认证方法
                        'imap_mark', // 状态（0无效/1有效）
                        'mark', // 状态（0无效/1有效）
                        'sort', // 排序
                        'remarks', // 备注
                    ])
                    ->toArray();
            $d_mc_2 = [];
            foreach ($d_mc as $k_1 => $v_1) {
                $d_mc_2[$v_1["id"]] = $v_1;
            }
            $getInfo = $this->model->getInfo($id);

            if (!empty($getInfo)) {
//                $pop3 = new EmailPop3();
                $d_cf = $d_mc_2[$getInfo["email_config_id"]];

                /**
                 * 构造函数：初始化 IMAP 配置
                 * @param string $server IMAP 服务器地址（如 imap.outlook.com）
                 * @param int $port 端口（如 993）
                 * @param string $encryption 加密方式（如 ssl）
                 * @param string $username 邮箱账号
                 * @param string $password 密码/授权码
                 * @param bool $novalidateCert 是否跳过证书验证（默认 false）
                 */
                $username = $getInfo["email"];
                $password = $getInfo["passwords"];
                if (!empty($getInfo["privated_user"])) {
                    $username = $getInfo["privated_user"];
                }
                if (!empty($getInfo["privated_code"])) {
                    $password = $getInfo["privated_code"];
                }

//                $this->GmailImap(["user" => $getInfo, "conf" => $d_cf]);
                $pop3 = new EmailImap($d_cf["server"], $d_cf["port"], "ssl", $username, $password);
                $pop3->connect();
//                $getMessageCount = $pop3->getMessageCount();
//                $getFolders = $pop3->getFolders();
//                $getRecentUids = $pop3->getRecentUids();
                $getLatestMailUid = $pop3->getLatestMailUid();
                $user_Uid = $getInfo["latest_mail_uid"] ?? 0;
                $count_Uid = [];
                $count_Uids = [];
                if ($user_Uid < $getLatestMailUid) {
                    for ($index = $user_Uid; $index < $getLatestMailUid; $index++) {
                        $count_Uid[$index] = $count_Uid_get = $pop3->getMailDetails($index);
                        $count_Uids[] = $count_Uid_get;
                        Log::info("getEmailUser Uid::$index", $count_Uid_get);
                        // 暂停25毫秒
                        usleep(30000); // 10 * 1000 = 10,000 微秒
                    }
                }
                $pop3->close();
                $ret = ["getInfo" => $getInfo, "count_Uids" => json_encode($count_Uids,512)];
            } else {
                return message("NO This ID $id Not Exist", false, [$d_mc], 599);
            }

        }
        // 返回错误响应（当前逻辑未正确使用$id参数，需根据实际需求调整）
        return message("NO", false, $ret, 599);
    }

    public function getPop3() {
        $hostname = '{imap.mailtrap.io:143/imap}';
        $username = 'your-email@example.com';
        $password = 'your-password';

        /* 打开 IMAP 连接 */
        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Mail: ' . imap_last_error());

        /* 获取邮件数量 */
        $emailsCount = imap_num_msg($inbox);

        for ($i = 1; $i <= $emailsCount; $i++) {
            $header = imap_headerinfo($inbox, $i);
            $overview = imap_fetch_overview($inbox, $i, 0);
            echo "Email number $i Date: " . $overview[0]->date . '<br>';
            echo "Subject: " . $header->subject . '<br>';
            echo "From: " . $header->from[0]->mailbox . '@' . $header->from[0]->host . '<br>';
            echo "To: " . $header->to[0]->mailbox . '@' . $header->to[0]->host . '<br><br>';
        }

        /* 关闭连接 */
        imap_close($inbox);
    }

    /*
     * iamp_open()：连接到一个 IMAP 服务器。
      iamp_close()：关闭当前 IMAP 连接。
      iamp_fetch_overview()：获取邮件的概述信息，如主题、发件人、收件时间等。
      iamp_body()：获取指定邮件的正文内容。
      iamp_header()：获取指定邮件的头部信息（From、To、Subject等）。
      iamp_uid()：获取指定邮件的唯一 ID。
      iamp_search()：根据指定的标准搜索邮件。
      iamp_list()：列出当前邮件箱或指定路径下的文件夹。
      iamp_subscribe()：订阅某个文件夹。
      iamp_unsubscribe()：取消订阅某个文件夹。
      iamp_create()：创建新的文件夹。
      iamp_delete()：删除指定的文件夹。
      iamp_rename()：重命名指定的文件夹。
      iamp_move()：将邮件移动到指定的文件夹。
      iamp_copy()：将邮件复制到指定的文件夹。
      iamp_append()：将邮件添加到指定的文件夹。
      iamp_setflag_full()：设置邮件的全局标志。
      iamp_clearflag_full()：清除邮件的全局标志。
      iamp_store()：设置或清除邮件的多个标志。
      iamp_expunge()：彻底删除已被标记为待删除的邮件。
      ————————————————
     */

    public function outLook365Imap($param) {

        // Outlook 365 IMAP配置
        $imapServer = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
        $username = 'your_email@your_domain.com';
        $password = 'your_password';

//         $hostname = '{imap.qq.com:993/imap/ssl}INBOX'; // 邮箱服务器地址和端口（固定为 QQ 邮箱 IMAP 地址）
//        $imapServer = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
        $imapServer = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
        $username = "dickyiskandar7195@outlook.co.id"; #$param["user"]["email"];
        $password = imap_8bit("Wahyupp#81041231"); # "Wahyupp#81041231"; #$param["user"]["passwords"];
        $password = "Wahyupp#81041231"; # "Wahyupp#81041231"; #$param["user"]["passwords"];
        // 新增：参数校验（避免空值）
        if (empty($param['user']['email']) || empty($param['user']['passwords'])) {
            Log::error('Outlook IMAP参数缺失', ['param' => $param]);
            return message("邮箱或密码未提供", false);
        }

        // 新增：记录连接参数（调试用）
        Log::info('Outlook IMAP连接尝试', [
            'email' => $param['user']['email'],
            'server' => 'outlook.office365.com',
            'port' => 993,
            'encryption' => 'ssl'
        ]);

//        $imapServer = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
//        $imapServer = '{outlook.office365.com:993/imap/ssl}INBOX';
//        $username = "dickyiskandar7195@outlook.co.id";
//        $password = imap_8bit("Wahyupp#81041231"); // 修正后的密码赋值
        // 输出调试日志（生产环境需移除）
        Log::info("Outlook IMAP 连接参数", [
            'server' => $imapServer,
            'username' => $username,
            'password_escaped' => $password // 仅用于验证转义是否正确，勿记录明文密码
        ]);

        // 连接服务器
//    $imapStream = imap_open($imapServer, $username, $password) or die('登录失败: ' . imap_last_error());
        // 连接到Outlook 365 IMAP服务器
        $imapStream = imap_open($imapServer, $username, $password) or die('Cannot connect to Outlook 365 Mail: ' . imap_last_error());

        if (!$imapStream) {
            die('Outlook 365 IMAP连接失败: ' . imap_last_error());
        }

        // 获取邮件数量
        $messageCount = imap_num_msg($imapStream);
        echo "Outlook 365 IMAP邮箱中的邮件总数: $messageCount\n";

        // 搜索未读邮件
        $unseenMails = imap_search($imapStream, 'UNSEEN');
        if ($unseenMails) {
            echo "未读邮件数量: " . count($unseenMails) . "\n";
        }

        // 获取最近5封邮件的UID
        $uids = imap_sort($imapStream, SORTARRIVAL, 1, SE_UID);
        if (is_array($uids) && count($uids) > 0) {
            $uids = array_slice($uids, 0, 5);

            echo "\n最近5封邮件:\n";
            foreach ($uids as $uid) {
                $headers = imap_headerinfo($imapStream, imap_msgno($imapStream, $uid));
                echo "\n邮件 UID: $uid\n";
                echo "主题: " . ($headers->subject ?? "(无主题)") . "\n";
                echo "发件人: " . ($headers->fromaddress ?? "未知") . "\n";
                echo "日期: " . ($headers->date ?? "未知") . "\n";

                // 获取邮件结构
                $structure = imap_fetchstructure($imapStream, $uid, FT_UID);

                // 解析邮件内容
                if (isset($structure->parts) && count($structure->parts) > 0) {
                    for ($i = 0; $i < count($structure->parts); $i++) {
                        $part = $structure->parts[$i];
                        if (($part->type == 0 || $part->type == 1) && strtolower($part->subtype) == 'plain') {
                            $body = imap_fetchbody($imapStream, $uid, $i + 1, FT_UID);

                            // 处理编码
                            if ($part->encoding == 3) {
                                $body = base64_decode($body);
                            } elseif ($part->encoding == 4) {
                                $body = quoted_printable_decode($body);
                            }

                            echo "纯文本内容预览: " . substr(strip_tags($body), 0, 100) . "...\n";
                            break;
                        }
                    }
                }

                // 标记邮件为已读
                imap_setflag_full($imapStream, $uid, '\\Seen', ST_UID);
            }
        }

        // 获取邮箱文件夹列表
        $folders = imap_list($imapStream, $imapServer, '*');
        echo "\n邮箱文件夹列表:\n";
        foreach ($folders as $folder) {
            echo "- " . str_replace($imapServer, '', $folder) . "\n";
        }

        // 关闭连接
        imap_close($imapStream);
    }

    public function outLook365Pop3($param) {
// Outlook 365 POP3配置
//$pop3Server = '{outlook.office365.com:995/pop3/ssl/novalidate-cert}INBOX';
//$username = 'your_email@your_domain.com';
//$password = 'your_password';

        $pop3Server = '{outlook.office365.com:995/pop3/ssl/novalidate-cert}INBOX';
        $username = $param["user"]["email"];
        $password = $param["user"]["passwords"];

// 连接到Outlook 365 POP3服务器
        $pop3Stream = imap_open($pop3Server, $username, $password);

        if (!$pop3Stream) {
            die('Outlook 365 POP3连接失败: ' . imap_last_error());
        }

// 获取邮件数量
        $messageCount = imap_num_msg($pop3Stream);
        echo "Outlook 365 POP3邮箱中的邮件总数: $messageCount\n";

// 遍历最近10封邮件
        $start = max(1, $messageCount - 9);
        for ($i = $start; $i <= $messageCount; $i++) {
            $headers = imap_headerinfo($pop3Stream, $i);
            echo "\n邮件 #$i:\n";
            echo "主题: " . ($headers->subject ?? "(无主题)") . "\n";
            echo "发件人: " . ($headers->fromaddress ?? "未知") . "\n";
            echo "日期: " . ($headers->date ?? "未知") . "\n";

            // 获取邮件正文
            $body = imap_fetchbody($pop3Stream, $i, 1);
            if (empty($body)) {
                $body = imap_fetchbody($pop3Stream, $i, 2);
            }
            echo "内容预览: " . substr(strip_tags($body), 0, 100) . "...\n";
        }

// 关闭连接
        imap_close($pop3Stream);
    }

    /**
     * 2025年5月20日14:16:22 测试OK
     * 连接 QQ 邮箱 IMAP 服务器并读取邮件信息（示例方法）
     * @description 用于演示通过 IMAP 协议连接 QQ 邮箱，获取文件夹列表及邮件详情（如主题、发件人、内容预览）
     * @note 示例中使用硬编码的 QQ 邮箱配置，实际使用时需动态获取用户邮箱和密码（如通过 $param 参数传递）
     */
    public function QqImap($param) {
        // QQ 邮箱 IMAP 服务器配置（SSL 加密，端口 993）
        $hostname = '{imap.qq.com:993/imap/ssl}INBOX'; // 邮箱服务器地址和端口（固定为 QQ 邮箱 IMAP 地址）
        $username = '793753307@qq.com'; // 示例邮箱账号（实际应通过参数传递用户邮箱）
        $password = 'yheslxymquwtbbga'; // 示例邮箱授权码（实际应通过参数传递用户密码/授权码）【POP3/IMAP/SMTP/Exchange/CardDAV/CalDAV服务->在第三方登录QQ邮箱，可能存在邮件泄露风险，甚至危害Apple ID安全，建议使用QQ邮箱手机版登录。继续获取授权码登录第三方客户端邮箱。】
        // 连接 QQ 邮箱 IMAP 服务器（失败时输出错误信息）
        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to QQ Mail: ' . imap_last_error());

        // 获取邮箱中所有文件夹列表（如收件箱、草稿箱等）
        $folders = imap_list($inbox, $hostname, '*');

        // 遍历每个文件夹
        foreach ($folders as $folder) {
            // 解析文件夹名称（移除主机名前缀并转换 UTF-7 编码）
            $folder_name = imap_utf7_decode(str_replace($hostname, '', $folder)); // 例如："INBOX" 或 "草稿箱"
            // 打开当前文件夹（使用相同的邮箱账号和密码）
            $folder_resource = imap_open($folder, $username, $password);

            if ($folder_resource) {
                echo "Opened folder: " . $folder_name . "\n"; // 输出已打开的文件夹名称
                // 获取当前文件夹中的邮件总数
                $total_emails = imap_num_msg($folder_resource);
                echo "Total emails: " . $total_emails . "\n";

                // 读取前 20 封邮件（避免过多数据）
                for ($i = 1; $i <= min($total_emails, 20); $i++) {
                    // 获取邮件头部信息（包含主题、发件人、日期等）
                    $header = imap_headerinfo($folder_resource, $i);
                    $subject = $header->subject; // 邮件主题（可能为 base64 编码）
                    $from = $header->from[0]->mailbox . "@" . $header->from[0]->host; // 发件人邮箱地址
                    // 获取邮件正文（优先读取第一部分，若为空则读取第二部分）
                    $body = imap_fetchbody($folder_resource, $i, 1);
                    if (empty($body)) {
                        $body = imap_fetchbody($folder_resource, $i, 2);
                    }

                    // 处理主题编码（QQ 邮箱主题可能使用 base64 编码，格式如 "=?utf-8?B?5Lit5paH?="）
                    $decoded_subject = base64_decode(str_replace("=?utf-8?B?", "", $subject));

                    // 输出邮件摘要信息（主题、发件人、内容预览）
                    echo "Email #$i: From: $from, Subject: $decoded_subject\t 内容预览: " . substr(strip_tags($body), 0, 100) . "...\n\t\n";
                }

                // 关闭当前文件夹资源
                imap_close($folder_resource);
            } else {
                echo "Failed to open folder: " . $folder_name . "\n"; // 文件夹打开失败提示
            }
        }
    }

    /**
     * 连接 Gmail IMAP 服务器并读取邮件信息
     * @description 用于通过 IMAP 协议连接 Gmail 邮箱，获取邮件列表、未读邮件及内容预览
     * @param array $param 包含用户信息的数组（需包含 'user' 键，其中 'email' 为邮箱，'passwords' 为密码/应用专用密码）
     * @note Gmail 需先在设置中启用 IMAP 服务（路径：Gmail 设置 → 转发和 POP/IMAP → 启用 IMAP）
     */
    public function GmailImap($param) {
        // 新增：参数校验（避免空值）
        if (empty($param['user']['email']) || empty($param['user']['passwords'])) {
            Log::error('Gmail IMAP参数缺失', ['param' => $param]);
            return message("邮箱或密码未提供", false);
        }

        // Gmail IMAP 标准配置（SSL 加密，端口 993）
        $imapServer = '{imap.gmail.com:993/imap/ssl}INBOX';
        $username = $param['user']['email']; // 从参数获取邮箱
        $password = imap_8bit($param['user']['passwords']); // 转义密码中的特殊字符（如 #、$ 等）
        // 记录连接参数（调试用）
        Log::info('Gmail IMAP连接尝试', [
            'email' => $username,
            'server' => 'imap.gmail.com',
            'port' => 993,
            'encryption' => 'ssl'
        ]);

        // 连接 Gmail IMAP 服务器（失败时记录错误）
        $imapStream = imap_open($imapServer, $username, $password);
        if (!$imapStream) {
            $error = imap_last_error();
            Log::error('Gmail IMAP连接失败', ['error' => $error]);
            return message("连接失败：{$error}", false);
        }

        // 获取邮箱中邮件总数
        $messageCount = imap_num_msg($imapStream);
        echo "Gmail IMAP邮箱中的邮件总数: $messageCount\n";

        // 搜索未读邮件
        $unseenMails = imap_search($imapStream, 'UNSEEN');
        if ($unseenMails) {
            echo "未读邮件数量: " . count($unseenMails) . "\n";
        }

        // 获取最近5封邮件的UID（按接收时间排序）
        $uids = imap_sort($imapStream, SORTARRIVAL, 1, SE_UID);
        if (is_array($uids) && count($uids) > 0) {
            $uids = array_slice($uids, 0, 5); // 取最近5封

            echo "\n最近5封邮件:\n";
            foreach ($uids as $uid) {
                $msgNo = imap_msgno($imapStream, $uid); // UID 转邮件序号
                $headers = imap_headerinfo($imapStream, $msgNo);

                echo "\n邮件 UID: $uid\n";
                echo "主题: " . ($headers->subject ?? "(无主题)") . "\n";
                echo "发件人: " . ($headers->fromaddress ?? "未知") . "\n";
                echo "日期: " . ($headers->date ?? "未知") . "\n";

                // 解析邮件内容（优先纯文本）
                $structure = imap_fetchstructure($imapStream, $msgNo);
                if (isset($structure->parts) && count($structure->parts) > 0) {
                    foreach ($structure->parts as $index => $part) {
                        if (strtolower($part->subtype) === 'plain' && $part->type === 0) { // 纯文本类型
                            $body = imap_fetchbody($imapStream, $msgNo, $index + 1);

                            // 处理编码（Gmail 常用 base64 或 quoted-printable）
                            if ($part->encoding === 3) {
                                $body = base64_decode($body);
                            } elseif ($part->encoding === 4) {
                                $body = quoted_printable_decode($body);
                            }

                            echo "纯文本内容预览: " . substr(strip_tags($body), 0, 100) . "...\n";
                            break;
                        }
                    }
                }

                // 标记邮件为已读（可选）
                imap_setflag_full($imapStream, $uid, '\\Seen', ST_UID);
            }
        }

        // 获取邮箱文件夹列表（如收件箱、垃圾邮件等）
        $folders = imap_list($imapStream, $imapServer, '*');
        echo "\n邮箱文件夹列表:\n";
        foreach ($folders as $folder) {
            echo "- " . str_replace($imapServer, '', $folder) . "\n";
        }

        // 关闭连接
        imap_close($imapStream);
        return message("Gmail IMAP连接成功", true);
    }

}
