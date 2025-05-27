<?php

namespace App\Admin\Controllers\Email;

use App\Services\Email\MailConfService;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * Email配置
 *
 * @property MailConfService $service
 */
class MailConfController extends AdminController
{
	protected string $serviceName = MailConfService::class;

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
				amis()->TableColumn('server_name', '邮件服务名[abc@gmail.com]'),
				amis()->TableColumn('server_type', '邮件类型'),
				amis()->TableColumn('name', 'Server Name'),
				amis()->TableColumn('server', 'Server'),
				amis()->TableColumn('port', 'Port'),
				amis()->TableColumn('encryption', 'Encryption'),
				amis()->TableColumn('authentication_method', 'Authentication Method'),
				amis()->SwitchControl('imap_mark', 'ImapMark'),
				amis()->SwitchControl('mark', 'Mark'),
				amis()->TableColumn('sort', '排序'),
				amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(),
				amis()->TableColumn('updated_at', admin_trans('admin.updated_at'))->type('datetime')->sortable(),
				$this->rowActions('dialog')
			]);

		return $this->baseList($crud);
	}

	public function form($isEdit = false)
	{
		return $this->baseForm()->body([
			amis()->TextControl('server_name', '邮件服务名[abc@gmail.com]'),
			amis()->RadiosControl('server_type', '邮件类型')->options([["label"=>"IMTP","value"=>"imap"],["label"=>"POP","value"=>"pop"],["label"=>"SMTP","value"=>"smtp"]])->selectFirst(),
			amis()->TextControl('name', 'Server Name'),
			amis()->TextControl('server', 'Server'),
			amis()->RadiosControl('port', 'Port')->options([["label"=>"993","value"=>"993"],["label"=>"995","value"=>"995"],["label"=>"587","value"=>"587"]])->selectFirst(),
			amis()->TextControl('encryption', 'Encryption'),
			amis()->TextControl('authentication_method', 'Authentication Method'),
			amis()->SwitchContainer('imap_mark', 'ImapMark'),
			amis()->SwitchControl('mark', 'Mark'),
			amis()->NumberControl('sort', '排序')->required(1)->value(0)->min(0)->max(999999)->description('越大越靠前'),
			amis()->TextareaControl('remarks', '备注'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('server_name', '邮件服务名[abc@gmail.com]')->static(),
			amis()->TextControl('server_type', '邮件类型')->static(),
			amis()->TextControl('name', 'Server Name')->static(),
			amis()->TextControl('server', 'Server')->static(),
			amis()->TextControl('port', 'Port')->static(),
			amis()->TextControl('encryption', 'Encryption')->static(),
			amis()->TextControl('authentication_method', 'Authentication Method')->static(),
			amis()->TextControl('imap_mark', 'ImapMark')->static(),
			amis()->TextControl('mark', 'Mark')->static(),
			amis()->TextControl('sort', '排序')->static(),
			amis()->TextControl('remarks', '备注')->static(),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}