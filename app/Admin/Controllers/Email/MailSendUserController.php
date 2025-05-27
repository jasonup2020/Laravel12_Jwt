<?php

namespace App\Admin\Controllers\Email;

use App\Services\Email\MailSendUserService;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * Email发送邮件用户
 *
 * @property MailSendUserService $service
 */
class MailSendUserController extends AdminController
{
	protected string $serviceName = MailSendUserService::class;

	public function list()
	{
		$crud = $this->baseCRUD()
			->filterTogglable(false)
			->headerToolbar([
				$this->createButton('dialog'),
				...$this->baseHeaderToolBar()
			])
			->columns([
				amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TableColumn('name', '用户名称'),
				amis()->TableColumn('email', 'Email用户'),
				amis()->TableColumn('email_config_id', 'Email配置ID'),
				amis()->TableColumn('passwords', 'Email用户密码'),
				amis()->TableColumn('privated_user', 'Email_API专业用户'),
				amis()->TableColumn('privated_code', 'Email_API专业密码'),
				amis()->TableColumn('phone', '验证手机号'),
				amis()->TableColumn('latest_mail_uid', '最新一封邮件的UID'),
				amis()->TableColumn('remarks', '备注'),
				amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(),
				amis()->TableColumn('updated_at', admin_trans('admin.updated_at'))->type('datetime')->sortable(),
				$this->rowActions('dialog')
			]);

		return $this->baseList($crud);
	}

	public function form($isEdit = false)
	{
		return $this->baseForm()->body([
			amis()->TextControl('name', '用户名称'),
			amis()->TextControl('email', 'Email用户'),
			amis()->SelectControl('email_config_id', 'Email配置ID')->required()->menuTpl('${label} <span class="text-gray-300 pl-2">${table}</span>')->source('api/email/mail_conf/get_conf'),
			amis()->TextControl('passwords', 'Email用户密码'),
			amis()->TextControl('privated_user', 'Email_API专业用户'),
			amis()->TextControl('privated_code', 'Email_API专业密码'),
			amis()->TextControl('phone', '验证手机号'),
			amis()->TextControl('latest_mail_uid', '最新一封邮件的UID'),
			amis()->TextControl('remarks', '备注'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('name', '用户名称')->static(),
			amis()->TextControl('email', 'Email用户')->static(),
			amis()->TextControl('email_config_id', 'Email配置ID')->static(),
			amis()->TextControl('passwords', 'Email用户密码')->static(),
			amis()->TextControl('privated_user', 'Email_API专业用户')->static(),
			amis()->TextControl('privated_code', 'Email_API专业密码')->static(),
			amis()->TextControl('phone', '验证手机号')->static(),
			amis()->TextControl('latest_mail_uid', '最新一封邮件的UID')->static(),
			amis()->TextControl('remarks', '备注')->static(),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}