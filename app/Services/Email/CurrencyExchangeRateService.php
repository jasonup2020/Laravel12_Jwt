<?php

namespace App\Services\Email;

use App\Models\Email\CurrencyExchangeRate;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 汇率表
 *
 * @method CurrencyExchangeRate getModel()
 * @method CurrencyExchangeRate|\Illuminate\Database\Query\Builder query()
 */
class CurrencyExchangeRateService extends AdminService
{
	protected string $modelName = CurrencyExchangeRate::class;
}