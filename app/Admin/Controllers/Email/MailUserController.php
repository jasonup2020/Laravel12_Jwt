<?php

namespace App\Admin\Controllers\Email;

use App\Services\Email\MailUserService;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * Email用户
 *
 * @property MailUserService $service
 */
class MailUserController extends AdminController
{
	protected string $serviceName = MailUserService::class;

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
				amis()->TableColumn('name', '用户名'),
				amis()->TableColumn('email', 'Email用户'),
				amis()->TableColumn('email_config_id', 'Email配置ID'),
				amis()->TableColumn('passwords', 'Email用户密码'),
				amis()->TableColumn('privated_user', 'Email_API专业用户'),
				amis()->TableColumn('privated_code', 'Email_API专业密码'),
				amis()->TableColumn('latest_mail_uid', '最新一封邮件的 UID'),
				amis()->SwitchControl('mark', '状态'),
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
			amis()->TextControl('name', '用户名'),
			amis()->TextControl('email', 'Email用户'),
			amis()->SelectControl('email_config_id', 'Email配置ID')->options(json_decode(file_get_contents(env("APP_URL")."/api/email/mail_conf/get_conf"), true)),
			amis()->TextControl('passwords', 'Email用户密码'),
			amis()->TextControl('privated_user', 'Email_API专业用户'),
			amis()->TextControl('privated_code', 'Email_API专业密码'),
			amis()->TextControl('phone', '验证手机号'),
			amis()->TextControl('orther_mail', '辅助邮箱'),
			amis()->TextControl('bay_user', '购买人'),
			amis()->TextControl('old_phone', '历史手机号'),
			amis()->TextControl('old_orther_mail', '历史辅助邮箱'),
			amis()->TextControl('latest_mail_uid', '最新一封邮件的 UID'),
			amis()->SwitchContainer('mark', '状态'),
			amis()->NumberControl('sort', '排序')->required(1)->value(0)->min(0)->max(999999)->description('越大越靠前'),
			amis()->TextareaControl('remarks', '备注'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('name', '用户名')->static(),
			amis()->TextControl('email', 'Email用户')->static(),
			amis()->TextControl('email_config_id', 'Email配置ID')->static(),
			amis()->TextControl('passwords', 'Email用户密码')->static(),
			amis()->TextControl('privated_user', 'Email_API专业用户')->static(),
			amis()->TextControl('privated_code', 'Email_API专业密码')->static(),
			amis()->TextControl('phone', '验证手机号')->static(),
			amis()->TextControl('orther_mail', '辅助邮箱')->static(),
			amis()->TextControl('bay_user', '购买人')->static(),
			amis()->TextControl('old_phone', '历史手机号')->static(),
			amis()->TextControl('old_orther_mail', '历史辅助邮箱')->static(),
			amis()->TextControl('latest_mail_uid', '最新一封邮件的 UID')->static(),
			amis()->TextControl('mark', '状态')->static(),
			amis()->NumberControl('sort', '排序'),
			amis()->TextareaControl('remarks', '备注'),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}