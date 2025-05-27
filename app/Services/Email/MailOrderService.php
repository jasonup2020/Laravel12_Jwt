<?php

namespace App\Services\Email;

use App\Email\Models\MailOrder;
use Slowlyo\OwlAdmin\Services\AdminService;
use App\Services\Email\MailConfService;

/**
 * Email邮件
 *
 * @method MailOrder getModel()
 * @method MailOrder|\Illuminate\Database\Query\Builder query()
 */
class MailOrderService extends AdminService
{
	protected string $modelName = MailOrder::class;

	public function searchable($query)
	{
		parent::searchable($query);

		$query->when(filled($this->request->input('email_title')), fn($q) => $q->where('email_title', 'like', '%' . filled($this->request->input('email_title')) . '%'));
		$query->when(filled($this->request->input('email_to')), fn($q) => $q->where('email_to', 'like', '%' . filled($this->request->input('email_to')) . '%'));
		$query->when(filled($this->request->input('email_cc')), fn($q) => $q->where('email_cc', 'like', '%' . filled($this->request->input('email_cc')) . '%'));
	}
        
        
}