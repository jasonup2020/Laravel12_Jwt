<?php

namespace App\Services\Email;

use App\Models\Email\MailUser;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * Email用户
 *
 * @method MailUser getModel()
 * @method MailUser|\Illuminate\Database\Query\Builder query()
 */
class MailUserService extends AdminService
{
	protected string $modelName = MailUser::class;
}