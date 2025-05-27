<?php

namespace App\Admin\Controllers\Email;

use App\Services\Email\CurrencyExchangeRateService;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * 汇率表
 *
 * @property CurrencyExchangeRateService $service
 */
class CurrencyExchangeRateController extends AdminController
{
	protected string $serviceName = CurrencyExchangeRateService::class;

	public function list()
	{
		$crud = $this->baseCRUD()
			->filterTogglable(false)
			->bulkActions([])
			->headerToolbar([
				$this->createButton('dialog'),
				...$this->baseHeaderToolBar()
			])
			->columns([
				amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TableColumn('country', '国家/地区名称'),
				amis()->TableColumn('currency_code', '货币代码（ISO 4217标准，如USD、EUR）'),
				amis()->TableColumn('exchange_rate', '汇率（1 USD 可兑换的目标货币数量）'),
				amis()->TableColumn('updated_at', admin_trans('admin.updated_at'))->type('datetime')->sortable(),
				$this->rowActions([
					$this->rowShowButton('dialog'),
					$this->rowEditButton('dialog'),
				])
			]);

		return $this->baseList($crud);
	}

	public function form($isEdit = false)
	{
		return $this->baseForm()->body([
			amis()->TextControl('country', '国家/地区名称'),
			amis()->TextControl('currency_code', '货币代码（ISO 4217标准，如USD、EUR）'),
			amis()->TextControl('exchange_rate', '汇率（1 USD 可兑换的目标货币数量）'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('country', '国家/地区名称')->static(),
			amis()->TextControl('currency_code', '货币代码（ISO 4217标准，如USD、EUR）')->static(),
			amis()->TextControl('exchange_rate', '汇率（1 USD 可兑换的目标货币数量）')->static(),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}