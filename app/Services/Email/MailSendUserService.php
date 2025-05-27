<?php

namespace App\Services\Email;

use App\Models\Email\MailSendUser;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * Email发送邮件用户
 *
 * @method MailSendUser getModel()
 * @method MailSendUser|\Illuminate\Database\Query\Builder query()
 */
class MailSendUserService extends AdminService
{
	protected string $modelName = MailSendUser::class;
}