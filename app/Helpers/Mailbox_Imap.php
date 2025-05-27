<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PhpImap\Mailbox;

class Mailbox_Imap
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'host' => env('IMAP_HOST'),
            'port' => env('IMAP_PORT', 993),
            'ssl' => env('IMAP_SSL', true),
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'attachments_dir' => storage_path('app/attachments/'),
        ];
    }

    private function createMailbox($folder = 'INBOX')
    {
        try {
            $sslFlag = $this->config['ssl'] ? '/ssl' : '';
            $validateCert = $this->config['validate_cert'] ? '' : '/novalidate-cert';
            
            return new Mailbox(
                "{" . $this->config['host'] . ":" . $this->config['port'] . "/imap$sslFlag$validateCert}$folder",
                $this->config['username'],
                $this->config['password'],
                $this->config['attachments_dir']
            );
        } catch (Exception $e) {
            Log::error('IMAP连接失败: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getFolders()
    {
        try {
            $mailbox = $this->createMailbox();
            $folders = $mailbox->getFolders();
            
            $formattedFolders = [];
            foreach ($folders as $folder) {
                $formattedFolders[] = [
                    'name' => $folder->name,
                    'path' => $folder->path,
                    'delimiter' => $folder->delimiter,
                ];
            }
            
            return $formattedFolders;
        } catch (Exception $e) {
            Log::error('获取文件夹列表失败: ' . $e->getMessage());
            return ['error' => '获取文件夹列表失败'];
        }
    }

    public function getMails($folder = 'INBOX', $maxResults = 10, $search = 'ALL')
    {
        try {
            $mailbox = $this->createMailbox($folder);
            $mailIds = $mailbox->searchMailbox($search);
            
            if (!$mailIds) {
                return [];
            }
            
            // 获取最新的邮件
            rsort($mailIds);
            $mailIds = array_slice($mailIds, 0, $maxResults);
            
            $mails = [];
            foreach ($mailIds as $mailId) {
                try {
                    $mail = $mailbox->getMail($mailId);
                    
                    $mails[] = [
                        'id' => $mailId,
                        'subject' => $mail->subject,
                        'from' => $this->formatFrom($mail->from),
                        'to' => $this->formatAddresses($mail->to),
                        'date' => $mail->date,
                        'body' => $this->getMailBody($mail),
                        'isSeen' => $mail->isSeen(),
                        'attachments' => $this->formatAttachments($mail->getAttachments()),
                    ];
                } catch (Exception $e) {
                    Log::error("获取邮件 $mailId 失败: " . $e->getMessage());
                }
            }
            
            return $mails;
        } catch (Exception $e) {
            Log::error('获取邮件列表失败: ' . $e->getMessage());
            return ['error' => '获取邮件列表失败'];
        }
    }

    public function markAsRead($mailId, $folder = 'INBOX')
    {
        try {
            $mailbox = $this->createMailbox($folder);
            $mailbox->markMailAsRead($mailId);
            return true;
        } catch (Exception $e) {
            Log::error("标记邮件 $mailId 为已读失败: " . $e->getMessage());
            return false;
        }
    }

    private function formatFrom($from)
    {
        if (is_array($from) && count($from) > 0) {
            $from = $from[0];
            return "{$from->personal} <{$from->mailbox}@{$from->host}>";
        }
        return '未知发件人';
    }

    private function formatAddresses($addresses)
    {
        if (!is_array($addresses) || empty($addresses)) {
            return '未知收件人';
        }
        
        $formatted = [];
        foreach ($addresses as $addr) {
            $formatted[] = "{$addr->personal} <{$addr->mailbox}@{$addr->host}>";
        }
        
        return implode(', ', $formatted);
    }

    private function getMailBody($mail)
    {
        // 优先使用HTML内容
        if (!empty($mail->textHtml)) {
            return $mail->textHtml;
        }
        
        // 否则使用纯文本内容
        return nl2br($mail->textPlain);
    }

    private function formatAttachments($attachments)
    {
        $formatted = [];
        foreach ($attachments as $attachment) {
            $formatted[] = [
                'name' => $attachment->name,
                'size' => $this->formatBytes($attachment->size),
                'path' => $attachment->filePath,
            ];
        }
        return $formatted;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}    