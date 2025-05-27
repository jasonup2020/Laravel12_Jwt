<?php

namespace App\Admin\Controllers\Email;

use App\Services\Email\MailOrderService;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * Email邮件
 *
 * @property MailOrderService $service
 */
class MailOrderController extends AdminController
{
	protected string $serviceName = MailOrderService::class;

	public function list()
	{
		$crud = $this->baseCRUD()
			->filter($this->baseFilter()->body([
				amis()->TextControl('email_title', 'email_title')->size('md')->clearable(1),
				amis()->TextControl('email_to', 'email_to')->size('md')->clearable(1),
				amis()->TextControl('email_cc', 'email_cc')->size('md')->clearable(1),
			]))
			->headerToolbar([
				$this->createButton('dialog'),
				...$this->baseHeaderToolBar()
			])
			->columns([
				amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TableColumn('email_title', 'Email标题'),
				amis()->TableColumn('email_to', 'Email发件人'),
				amis()->TableColumn('email_cc', 'Email收件人'),
				amis()->TableColumn('email_type', 'Email类型'),
				amis()->TableColumn('email_content', 'Email内容'),
				amis()->TableColumn('email_time', 'Email时间'),
				amis()->SwitchContainer('mark', '有效标识'),
				amis()->TableColumn('sort', '排序'),
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
			amis()->TextControl('email_title', 'Email标题'),
			amis()->TextControl('email_to', 'Email发件人'),
			amis()->TextControl('email_cc', 'Email收件人'),
			amis()->TextControl('email_type', 'Email类型'),
			amis()->TextControl('email_content', 'Email内容'),
			amis()->TextControl('email_time', 'Email时间'),
			amis()->TextControl('extract_money', 'Email里提取到的钱'),
			amis()->TextControl('extract_email', 'Email里提取到的Email'),
			amis()->TextControl('extract_orders', 'Email里提取到订单号'),
			amis()->SwitchControl('mark', '有效标识'),
			amis()->NumberControl('sort', '排序')->required(1)->value(0)->min(0)->max(999999)->description('越大越靠前'),
			amis()->TextareaControl('remarks', '备注'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('email_title', 'Email标题')->static(),
			amis()->TextControl('email_to', 'Email发件人')->static(),
			amis()->TextControl('email_cc', 'Email收件人')->static(),
			amis()->TextControl('email_type', 'Email类型')->static(),
			amis()->TextControl('email_content', 'Email内容')->static(),
			amis()->TextControl('email_time', 'Email时间')->static(),
			amis()->TextControl('extract_money', 'Email里提取到的钱')->static(),
			amis()->TextControl('extract_email', 'Email里提取到的Email')->static(),
			amis()->TextControl('extract_orders', 'Email里提取到订单号')->static(),
			amis()->TextControl('mark', '有效标识')->static(),
			amis()->TextControl('sort', '排序')->static(),
			amis()->TextareaControl('remarks', '备注'),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}